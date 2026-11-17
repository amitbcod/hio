<?php

namespace App\Services;

use App\Mail\BookingNotification;
use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\Booking;
use App\Models\BookingEmailNotification;
use App\Models\BookingRef;
use App\Models\Group;
use App\Models\Package;
use App\Models\TransportBooking;
use App\Models\Traveller;
use App\Models\TravelerAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BookingEmailNotificationService
{
    public function notifyBookingReference(int $bookingRefId, ?string $bookingType = null): void
    {
        $bookingRef = BookingRef::with([
            'trip.traveler',
            'trip.travellers',
            'bookings.lineItems',
            'paymentTransactions',
        ])->find($bookingRefId);

        if (!$bookingRef) {
            Log::error('Booking email notification skipped: booking reference not found.', [
                'booking_ref_id' => $bookingRefId,
            ]);

            return;
        }

        $hasSuccessfulPayment = $bookingRef->paymentTransactions->contains(
            fn ($payment): bool => $payment->status === 'paid' || $payment->settlement_status === 'verified_settled'
        ) || ($bookingRef->paymentTransaction
            && ($bookingRef->paymentTransaction->status === 'paid'
                || $bookingRef->paymentTransaction->settlement_status === 'verified_settled'));

        if (!$hasSuccessfulPayment) {
            Log::info('Booking email notification skipped because payment is not confirmed.', [
                'booking_ref_id' => $bookingRefId,
            ]);

            return;
        }

        $accommodations = AccommodationBooking::query()
            ->where('booking_ref_id', $bookingRefId)
            ->with(['accommodation.operator', 'room'])
            ->get();
        $activities = ActivityBooking::query()
            ->where('booking_ref_id', $bookingRefId)
            ->with('activity.operator')
            ->get();
        $transports = TransportBooking::query()
            ->where('booking_ref_id', $bookingRefId)
            ->with(['transport.operator', 'vehicle.vehicleName'])
            ->get();

        $this->notify($bookingRef, $accommodations, $activities, $transports, $bookingType);
    }

    private function notify(
        BookingRef $bookingRef,
        Collection $accommodations,
        Collection $activities,
        Collection $transports,
        ?string $bookingType
    ): void {
        $serviceBookings = $accommodations->concat($activities)->concat($transports)->values();
        $rootBookings = $bookingRef->bookings;
        $bookingType ??= $this->resolveBookingType($rootBookings);
        $bookingName = $this->resolveBookingName($bookingRef, $rootBookings, $bookingType);
        $bookingReference = (string) ($bookingRef->booking_ref_code ?: $serviceBookings->first()?->booking_reference ?: $bookingRef->id);
        $traveller = $this->travellerDetails($bookingRef, $serviceBookings);
        $serviceDetails = $serviceBookings
            ->map(fn (Model $booking): array => $this->serviceDetails($booking))
            ->values()
            ->all();
        $status = $this->bookingStatus($bookingRef, $rootBookings, $serviceBookings);
        [$startDate, $endDate] = $this->bookingDates($bookingRef, $serviceBookings);
        $travellerEmail = $this->travellerEmail($bookingRef, $serviceBookings);
        $travellerName = (string) ($traveller['name'] ?? 'Traveller');

        $this->dispatch(
            notificationKey: 'booking-ref:' . $bookingRef->id . ':traveller',
            bookingRef: $bookingRef,
            audience: 'traveller',
            relatedId: null,
            serviceBookingIds: $serviceBookings->map(fn (Model $booking): int => (int) $booking->id)->all(),
            recipientEmail: $travellerEmail,
            recipientName: $travellerName,
            bookingType: $bookingType,
            bookingReference: $bookingReference,
            bookingName: $bookingName,
            bookingStatus: $status,
            services: $serviceDetails,
            traveller: $traveller,
            startDate: $startDate,
            endDate: $endDate,
        );

        $operatorGroups = [];
        foreach ($serviceBookings as $booking) {
            $operator = $this->operatorFor($booking);
            $operatorKey = $operator?->id ? 'operator:' . $operator->id : 'missing:' . $booking::class;
            $operatorGroups[$operatorKey]['operator'] = $operator;
            $operatorGroups[$operatorKey]['bookings'][] = $booking;
        }

        foreach ($operatorGroups as $operatorKey => $group) {
            $operatorBookings = collect($group['bookings']);
            $operator = $group['operator'];
            $operatorServices = $operatorBookings
                ->map(fn (Model $booking): array => $this->serviceDetails($booking))
                ->values()
                ->all();
            $operatorIds = $operatorBookings->map(fn (Model $booking): int => (int) $booking->id)->sort()->values()->all();
            $key = 'booking-ref:' . $bookingRef->id . ':' . $operatorKey . ':' . implode('-', $operatorIds);

            $this->dispatch(
                notificationKey: $key,
                bookingRef: $bookingRef,
                audience: 'operator',
                relatedId: $operator?->id,
                serviceBookingIds: $operatorIds,
                recipientEmail: $operator?->email,
                recipientName: (string) ($operator?->full_name ?: $operator?->name ?: 'Operator'),
                bookingType: $bookingType,
                bookingReference: $bookingReference,
                bookingName: $bookingName,
                bookingStatus: $status,
                services: $operatorServices,
                traveller: $traveller,
                startDate: $startDate,
                endDate: $endDate,
            );
        }
    }

    private function dispatch(
        string $notificationKey,
        BookingRef $bookingRef,
        string $audience,
        ?int $relatedId,
        array $serviceBookingIds,
        ?string $recipientEmail,
        string $recipientName,
        string $bookingType,
        string $bookingReference,
        string $bookingName,
        string $bookingStatus,
        array $services,
        ?array $traveller,
        ?string $startDate,
        ?string $endDate
    ): void {
        $validEmail = is_string($recipientEmail) && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)
            ? trim($recipientEmail)
            : null;
        $notification = DB::transaction(function () use (
            $notificationKey,
            $bookingRef,
            $audience,
            $relatedId,
            $serviceBookingIds,
            $recipientEmail,
            $validEmail
        ): ?BookingEmailNotification {
            $record = BookingEmailNotification::query()
                ->where('notification_key', $notificationKey)
                ->lockForUpdate()
                ->first();

            if ($record && in_array($record->status, ['dispatched', 'processing'], true)) {
                if ($record->status === 'processing' && $record->processing_at?->lt(now()->subMinutes(15))) {
                    $record->status = 'failed';
                } else {
                    return null;
                }
            }

            $record ??= new BookingEmailNotification(['notification_key' => $notificationKey]);
            $record->booking_ref_id = $bookingRef->id;
            $record->trip_id = $bookingRef->trip_id;
            $record->audience = $audience;
            $record->related_operator_id = $relatedId;
            $record->service_booking_ids = array_values($serviceBookingIds);
            $record->recipient_email = $validEmail ?? $recipientEmail;
            $record->status = $validEmail ? 'processing' : 'failed';
            $record->attempts = (int) $record->attempts + 1;
            $record->processing_at = $validEmail ? now() : null;
            $record->failed_at = $validEmail ? null : now();
            $record->failure_reason = $validEmail ? null : 'Recipient email is missing or invalid.';
            $record->save();

            return $validEmail ? $record : null;
        });

        if (!$validEmail) {
            Log::warning('Booking email notification has no valid recipient.', [
                'booking_ref_id' => $bookingRef->id,
                'audience' => $audience,
                'related_operator_id' => $relatedId,
                'recipient_email' => $recipientEmail,
                'service_booking_ids' => $serviceBookingIds,
            ]);

            return;
        }

        if (!$notification) {
            return;
        }

        try {
            Mail::to($validEmail)->send(new BookingNotification(
                audience: $audience,
                bookingType: $bookingType,
                bookingReference: $bookingReference,
                bookingName: $bookingName,
                bookingStatus: $bookingStatus,
                recipientName: $recipientName,
                services: $services,
                traveller: $traveller,
                startDate: $startDate,
                endDate: $endDate,
            ));

            $notification->update([
                'status' => 'dispatched',
                'dispatched_at' => now(),
                'processing_at' => null,
                'failure_reason' => null,
            ]);
        } catch (Throwable $exception) {
            $notification->update([
                'status' => 'failed',
                'processing_at' => null,
                'failed_at' => now(),
                'failure_reason' => mb_substr($exception->getMessage(), 0, 4000),
            ]);
            Log::error('Booking email notification dispatch failed.', [
                'booking_ref_id' => $bookingRef->id,
                'audience' => $audience,
                'recipient_email' => $validEmail,
                'service_booking_ids' => $serviceBookingIds,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function serviceDetails(Model $booking): array
    {
        if ($booking instanceof AccommodationBooking) {
            $name = $booking->accommodation?->property_name
                ?? $booking->accommodation?->accommodation_name
                ?? $booking->accommodation?->name
                ?? 'Accommodation';
            $roomName = $booking->room?->room_type ?? $booking->room?->name;

            return [
                'type' => 'Accommodation',
                'reference' => $booking->booking_reference,
                'name' => $name,
                'status' => $booking->booking_status,
                'date' => $this->dateRange($booking->check_in_date, $booking->check_out_date),
                'details' => array_filter([
                    'Booking date' => $booking->created_at?->format('M d, Y'),
                    'Room' => $roomName,
                    'Rooms' => $booking->rooms_booked,
                    'Adults' => $booking->adults,
                    'Children' => $booking->children,
                    'Amount' => $this->amount($booking->total_amount, $booking->currency),
                ], fn ($value): bool => $value !== null && $value !== ''),
            ];
        }

        if ($booking instanceof ActivityBooking) {
            $name = $booking->activity?->activity_name ?? $booking->activity?->title ?? 'Activity';

            return [
                'type' => 'Activity',
                'reference' => $booking->booking_reference,
                'name' => $name,
                'status' => $booking->booking_status,
                'date' => $this->dateRange($booking->activity_date, $booking->activity_date),
                'details' => array_filter([
                    'Booking date' => $booking->created_at?->format('M d, Y'),
                    'Option' => $booking->variant_name,
                    'Adults' => $booking->adults,
                    'Children' => $booking->children,
                    'Amount' => $this->amount($booking->total_amount, $booking->currency),
                ], fn ($value): bool => $value !== null && $value !== ''),
            ];
        }

        if ($booking instanceof TransportBooking) {
            return [
                'type' => 'Transport',
                'reference' => $booking->booking_reference,
                'name' => $booking->transport?->vehicle_display_name
                    ?? $booking->transport?->vehicle_name
                    ?? 'Transport',
                'status' => $booking->booking_status,
                'date' => $this->dateRange($booking->pickup_date, $booking->return_date),
                'details' => array_filter([
                    'Booking date' => $booking->created_at?->format('M d, Y'),
                    'Pickup time' => $booking->pickup_time,
                    'Pickup location' => $booking->pickup_address ?: $booking->route_from,
                    'Drop-off location' => $booking->dropoff_address ?: $booking->route_to,
                    'Vehicle' => $booking->vehicle?->vehicle_name ?? $booking->other_vehicle_name,
                    'Passengers' => $booking->total_passengers ?: ((int) $booking->adults + (int) $booking->children),
                    'Amount' => $this->amount($booking->total_amount, $booking->currency),
                ], fn ($value): bool => $value !== null && $value !== ''),
            ];
        }

        return [];
    }

    private function operatorFor(Model $booking): mixed
    {
        return match (true) {
            $booking instanceof AccommodationBooking => $booking->accommodation?->operator,
            $booking instanceof ActivityBooking => $booking->activity?->operator,
            $booking instanceof TransportBooking => $booking->transport?->operator,
            default => null,
        };
    }

    private function travellerEmail(BookingRef $bookingRef, Collection $serviceBookings): ?string
    {
        $email = $serviceBookings->first(fn (Model $booking): bool => filled($booking->guest_email ?? null))?->guest_email;

        if (filled($email)) {
            return (string) $email;
        }

        $leadEmail = $bookingRef->trip?->travellers
            ->first(fn (Traveller $traveller): bool => strtolower((string) $traveller->relationship) === 'lead' && filled($traveller->email))
            ?->email;

        return $leadEmail ?: $bookingRef->trip?->traveler?->email;
    }

    private function travellerDetails(BookingRef $bookingRef, Collection $serviceBookings): ?array
    {
        $lead = $bookingRef->trip?->travellers
            ->first(fn (Traveller $traveller): bool => strtolower((string) $traveller->relationship) === 'lead');
        $booking = $serviceBookings->first(fn (Model $item): bool => filled($item->guest_name ?? null));

        if (!$lead && !$booking && !$bookingRef->trip?->traveler) {
            return null;
        }

        return [
            'name' => $lead?->name
                ?? $booking?->guest_name
                ?? $bookingRef->trip?->traveler?->full_name
                ?? $bookingRef->trip?->traveler?->email,
            'email' => $lead?->email ?? $booking?->guest_email ?? $bookingRef->trip?->traveler?->email,
            'phone' => $lead?->phone ?? $booking?->guest_phone ?? null,
            'passengers' => $serviceBookings->max(fn (Model $item): int => (int) ($item->total_passengers
                ?? ((int) ($item->adults ?? 0) + (int) ($item->children ?? 0)))) ?: 1,
        ];
    }

    private function bookingStatus(BookingRef $bookingRef, Collection $rootBookings, Collection $services): string
    {
        $payment = $bookingRef->paymentTransactions->sortByDesc('id')->first();
        $agentPending = $rootBookings->contains(
            fn (Booking $booking): bool => $booking->agent_verification_status === 'pending_admin_verification'
        );
        if ($agentPending) {
            return 'Received — pending administrator review and payment verification';
        }

        if (in_array($payment?->status, ['paid'], true) || $payment?->settlement_status === 'verified_settled') {
            return 'Payment received — ' . ($services->pluck('booking_status')->unique()->implode(', ') ?: 'booking received');
        }
        if ($payment && in_array($payment->status, ['pending', 'failed', 'refunded'], true)) {
            return match ($payment->status) {
                'pending' => 'Payment pending verification',
                'failed' => 'Payment failed',
                default => 'Payment refunded',
            };
        }

        $method = strtolower((string) $services->first()?->payment_method);
        return str_contains($method, 'cod')
            ? 'Booking received — payment due on arrival; operator confirmation pending'
            : 'Booking received — operator confirmation pending';
    }

    private function resolveBookingType(Collection $rootBookings): string
    {
        if ($rootBookings->contains(fn (Booking $booking): bool => $booking->booking_type === 'open-group')) {
            return 'group';
        }
        if ($rootBookings->contains(fn (Booking $booking): bool => $booking->booking_type === 'package')) {
            return 'package';
        }

        $types = $rootBookings->pluck('booking_type')->filter()->unique()->values();
        return $types->count() === 1 && in_array($types->first(), ['accommodation', 'activity', 'transport'], true)
            ? $types->first()
            : 'booking';
    }

    private function resolveBookingName(BookingRef $bookingRef, Collection $rootBookings, string $bookingType): string
    {
        if (in_array($bookingType, ['group', 'package'], true)) {
            foreach ($rootBookings as $rootBooking) {
                foreach ($rootBooking->lineItems as $lineItem) {
                    if ($lineItem->service_type !== 'package') {
                        continue;
                    }
                    $model = $bookingType === 'group'
                        ? Group::find($lineItem->service_id)
                        : Package::find($lineItem->service_id);
                    if ($model) {
                        return (string) ($model->name ?? $model->title ?? ucfirst($bookingType) . ' booking');
                    }
                }
            }
        }

        return (string) ($bookingRef->trip?->title ?: 'HolidaysIO booking');
    }

    private function bookingDates(BookingRef $bookingRef, Collection $services): array
    {
        $dates = collect([
            ...$services->map(fn (Model $booking) => $booking->check_in_date ?? $booking->activity_date ?? $booking->pickup_date),
            ...$services->map(fn (Model $booking) => $booking->check_out_date ?? $booking->activity_date ?? $booking->return_date ?? $booking->pickup_date),
        ])->filter()->map(fn ($date) => (string) $date)->sort()->values();

        return [
            $dates->first() ?: $bookingRef->trip?->start_date?->toDateString(),
            $dates->last() ?: $bookingRef->trip?->end_date?->toDateString(),
        ];
    }

    private function dateRange(mixed $start, mixed $end): ?string
    {
        if (!$start) {
            return null;
        }

        $startDate = $start->format('M d, Y');
        $endDate = $end?->format('M d, Y');

        return $endDate && $endDate !== $startDate ? $startDate . ' – ' . $endDate : $startDate;
    }

    private function amount(mixed $amount, mixed $currency): ?string
    {
        return $amount === null ? null : trim((string) $currency . ' ' . number_format((float) $amount, 2));
    }
}
