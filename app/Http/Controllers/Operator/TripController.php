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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class TripController extends Controller
{
    public function index()
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();

        if (! $operator) {
            abort(403);
        }

        $accommodationIds = Accommodation::where(function ($query) use ($operator) {
            $query->where('operator_id', $operator->id)
                ->orWhere('business_id', $operator->business_id);
        })->pluck('id');

        $activityIds = Activity::where('operator_id', $operator->id)->pluck('id');
        $transportIds = Transport::where('operator_id', $operator->id)->pluck('id');

        $tripIds = Trip::query()
            ->where(function ($query) use ($accommodationIds) {
                if ($accommodationIds->isNotEmpty()) {
                    $query->whereHas('accommodationBookings', function ($bookingQuery) use ($accommodationIds) {
                        $bookingQuery->whereIn('accommodation_id', $accommodationIds);
                    });
                }
            })
            ->orWhere(function ($query) use ($activityIds) {
                if ($activityIds->isNotEmpty()) {
                    $query->whereHas('activityBookings', function ($bookingQuery) use ($activityIds) {
                        $bookingQuery->whereIn('activity_id', $activityIds);
                    });
                }
            })
            ->orWhere(function ($query) use ($transportIds) {
                if ($transportIds->isNotEmpty()) {
                    $query->whereHas('transportBookings', function ($bookingQuery) use ($transportIds) {
                        $bookingQuery->whereIn('transport_id', $transportIds);
                    });
                }
            })
            ->pluck('id');

        $trips = Trip::whereIn('id', $tripIds)
            ->with([
                'traveler',
                'bookingRefs',
                'bookings.lineItems',
                'bookings.payments',
                'accommodationBookings.accommodation',
                'accommodationBookings.room',
                'accommodationBookings.bookingRef',
                'activityBookings.activity',
                'activityBookings.bookingRef',
                'transportBookings.transport.operator.profile',
                'transportBookings.transport.vehicleName',
                'transportBookings.bookingRef',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        foreach ($trips as $trip) {
            $trip->accommodationBookings = $this->scopeAccommodationBookings($trip, $accommodationIds);
            $trip->activityBookings = $this->scopeActivityBookings($trip, $activityIds);
            $trip->transportBookings = $this->scopeTransportBookings($trip, $transportIds);
            $trip->trip_type = $this->resolveTripType($trip);
            $trip->payment_status = $this->resolvePaymentStatus($trip);
            $trip->next_actions = $this->resolveNextActions($trip);
            $trip->common_booking_reference = $this->resolvePrimaryBookingReference($trip);
        }

        return view('operator.trips.index', compact('trips'));
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

    private function resolveTripType(Trip $trip): string
    {
        $tripBookings = $trip->bookings()->with('lineItems')->get();

        foreach ($tripBookings as $booking) {
            $bookingType = strtolower((string) ($booking->booking_type ?? ''));
            if (in_array($bookingType, ['open-group', 'close-group', 'group'], true)) {
                return 'Group Trip';
            }

            foreach ($booking->lineItems ?? collect() as $lineItem) {
                if (strtolower((string) ($lineItem->service_type ?? '')) === 'package') {
                    return 'Package Trip';
                }
            }

            if (in_array($bookingType, ['package'], true)) {
                return 'Package Trip';
            }
        }

        return 'Trip';
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
