<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\Booking;
use App\Models\Transport;
use App\Models\TransportBooking;
use App\Models\Trip;
use App\Services\TripListingFilters;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(Request $request, TripListingFilters $tripListingFilters)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();

        if (! $operator) {
            abort(403);
        }

        $accommodationIds = Accommodation::where(function ($query) use ($operator) {
            $query->where('operator_id', $operator->id);
            if ($operator->business_id !== null) {
                $query->orWhere('business_id', $operator->business_id);
            }
        })->pluck('id');

        $activityIds = Activity::where('operator_id', $operator->id)->pluck('id');
        $transportIds = Transport::where('operator_id', $operator->id)->pluck('id');

        $filters = $request->validate([
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'payment_status' => ['nullable', 'string', 'max:100'],
            'trip_type' => ['nullable', 'in:Trip,Group Trip,Package Trip'],
            'traveller' => ['nullable', 'string', 'max:255'],
            'booking_reference' => ['nullable', 'string', 'max:255'],
            'trip' => ['nullable', 'string', 'max:255'],
        ]);

        $tripScope = Trip::query()->where(function ($query) use ($accommodationIds, $activityIds, $transportIds) {
            $hasOwnedServices = false;

            if ($accommodationIds->isNotEmpty()) {
                $query->whereHas('accommodationBookings', fn ($bookingQuery) => $bookingQuery
                    ->whereIn('accommodation_id', $accommodationIds));
                $hasOwnedServices = true;
            }

            if ($activityIds->isNotEmpty()) {
                $method = $hasOwnedServices ? 'orWhereHas' : 'whereHas';
                $query->{$method}('activityBookings', fn ($bookingQuery) => $bookingQuery
                    ->whereIn('activity_id', $activityIds));
                $hasOwnedServices = true;
            }

            if ($transportIds->isNotEmpty()) {
                $method = $hasOwnedServices ? 'orWhereHas' : 'whereHas';
                $query->{$method}('transportBookings', fn ($bookingQuery) => $bookingQuery
                    ->whereIn('transport_id', $transportIds));
                $hasOwnedServices = true;
            }

            if (!$hasOwnedServices) {
                $query->whereRaw('1 = 0');
            }
        });

        $paymentStatuses = $tripListingFilters->paymentStatusOptions(clone $tripScope);
        $operatorServiceIds = [
            'accommodations' => $accommodationIds,
            'activities' => $activityIds,
            'transports' => $transportIds,
        ];

        $trips = $tripListingFilters->apply(clone $tripScope, $filters, $operatorServiceIds)
            ->with([
                'traveler',
                'travellers',
                'bookingRefs',
                'bookingRefs.paymentTransactions',
                'bookingRefs.paymentTransaction',
                'bookings.lineItems.travellers',
                'bookings.payments',
                'accommodationBookings.accommodation',
                'accommodationBookings.room',
                'accommodationBookings.bookingRef',
                'accommodationBookings.guests',
                'activityBookings.activity.schedulingTimeSlots',
                'activityBookings.bookingRef',
                'activityBookings.guests',
                'transportBookings.transport.operator.profile',
                'transportBookings.transport.vehicleName',
                'transportBookings.bookingRef',
                'transportBookings.guests',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        foreach ($trips as $trip) {
            $trip->accommodationBookings = $this->scopeAccommodationBookings($trip, $accommodationIds);
            $trip->activityBookings = $this->scopeActivityBookings($trip, $activityIds);
            $trip->transportBookings = $this->scopeTransportBookings($trip, $transportIds);
            $commonBookingCounts = $tripListingFilters->resolveCommonBookingCounts($trip);
            $trip->common_booking_ref_count = $commonBookingCounts['booking_refs'];
            $trip->common_booking_bli_count = $commonBookingCounts['blis'];
            $trip->booking_status_counts = $tripListingFilters->resolveBookingStatusCounts($trip);
            $trip->total_amounts = $tripListingFilters->resolveTripTotalAmounts($trip, false);
            $travellerDisplay = $tripListingFilters->resolveTravellerDisplay($trip);
            $trip->traveller_display_name = $travellerDisplay['name'];
            $trip->travel_party_size = $travellerDisplay['party_size'];
            $trip->trip_type = $tripListingFilters->resolveTripType($trip);
            $trip->payment_status = $this->resolvePaymentStatus($trip);
            $trip->payment_status_display = $tripListingFilters->resolvePaymentStatusDisplay($trip);
            $trip->payment_status_badges = $tripListingFilters->paymentStatusBadges($trip->payment_status_display);
            $trip->next_actions = $this->resolveNextActions($trip);
            $trip->common_booking_reference = $this->resolvePrimaryBookingReference($trip);
        }

        $tripTypes = ['Trip', 'Group Trip', 'Package Trip'];

        return view('operator.trips.index', compact('trips', 'filters', 'paymentStatuses', 'tripTypes'));
    }

    private function scopeAccommodationBookings(Trip $trip, Collection $accommodationIds): Collection
    {
        return $trip->accommodationBookings
            ->filter(fn (AccommodationBooking $booking) => $accommodationIds->contains($booking->accommodation_id));
    }

    private function scopeActivityBookings(Trip $trip, Collection $activityIds): Collection
    {
        return $trip->activityBookings
            ->filter(fn (ActivityBooking $booking) => $activityIds->contains($booking->activity_id));
    }

    private function scopeTransportBookings(Trip $trip, Collection $transportIds): Collection
    {
        return $trip->transportBookings
            ->filter(fn (TransportBooking $booking) => $transportIds->contains($booking->transport_id));
    }

    private function resolvePaymentStatus(Trip $trip): string
    {
        $payments = collect();

        foreach ($trip->bookings as $booking) {
            $payments = $payments->merge($booking->payments ?? collect());
        }

        foreach ($payments as $payment) {
            if (strtolower((string) ($payment->status ?? '')) === 'paid') {
                return 'paid';
            }
        }

        return 'pending';
    }

    private function resolvePrimaryBookingReference(Trip $trip): ?string
    {
        $commonReference = $trip->bookingRefs
            ->pluck('booking_ref_code')
            ->first(fn ($reference) => is_string($reference) && trim($reference) !== '');

        if ($commonReference) {
            return $commonReference;
        }

        $references = collect()
            ->merge($trip->accommodationBookings->map(fn ($booking) => $booking->bookingRef?->booking_ref_code))
            ->merge($trip->activityBookings->map(fn ($booking) => $booking->bookingRef?->booking_ref_code))
            ->merge($trip->transportBookings->map(fn ($booking) => $booking->bookingRef?->booking_ref_code))
            ->filter(fn ($reference) => is_string($reference) && trim($reference) !== '')
            ->unique();

        return $references->first() ?: null;
    }

    private function resolveNextActions(Trip $trip): array
    {
        $paymentStatus = strtolower((string) $trip->payment_status);
        $actions = [];

        if ($paymentStatus !== 'paid') {
            $actions[] = [
                'type' => 'payment',
                'label' => 'Awaiting Payment',
            ];

            return $actions;
        }

        foreach ($trip->accommodationBookings as $booking) {
            if ((string) ($booking->booking_status ?? '') !== 'Confirmed') {
                $actions[] = [
                    'type' => 'confirm',
                    'label' => 'Accommodation #' . $booking->id . ' — Mark as Confirmed',
                    'url' => route('operator.accommodation.booking.status', $booking->id),
                ];
            }
        }

        foreach ($trip->activityBookings as $booking) {
            if ((string) ($booking->booking_status ?? '') !== 'Confirmed') {
                $actions[] = [
                    'type' => 'confirm',
                    'label' => 'Activity #' . $booking->id . ' — Mark as Confirmed',
                    'url' => route('operator.activity.booking.status', $booking->id),
                ];
            }
        }

        foreach ($trip->transportBookings as $booking) {
            if ((string) ($booking->booking_status ?? '') !== 'Confirmed') {
                $actions[] = [
                    'type' => 'confirm',
                    'label' => 'Transport #' . $booking->id . ' — Mark as Confirmed',
                    'url' => route('operator.transport.booking.status', $booking->id),
                ];
                continue;
            }

            if (! $booking->hasCompleteAssignment()) {
                $actions[] = [
                    'type' => 'assign',
                    'label' => 'Transport #' . $booking->id . ' — Assign Driver & Vehicle',
                    'url' => route('operator.transport.booking.details', ['transport' => $booking->transport_id, 'booking' => $booking->id]),
                ];
            }
        }

        if (empty($actions)) {
            $actions[] = ['type' => 'completed', 'label' => 'No Action Required'];
        }

        return $actions;
    }
}
