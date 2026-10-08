<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\Trip;
use App\Models\TravelerAccount;
use App\Models\TransportBooking;
use App\Services\TripListingFilters;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(Request $request, TripListingFilters $tripListingFilters)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $filters = $request->validate([
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'payment_status' => ['nullable', 'string', 'max:100'],
            'trip_type' => ['nullable', 'in:Trip,Group Trip,Package Trip'],
            'traveller' => ['nullable', 'string', 'max:255'],
            'booking_reference' => ['nullable', 'string', 'max:255'],
            'trip' => ['nullable', 'string', 'max:255'],
        ]);

        $tripScope = Trip::query();
        $paymentStatuses = $tripListingFilters->paymentStatusOptions(clone $tripScope);
        $trips = $tripListingFilters->apply(clone $tripScope, $filters)
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
            $trip->trip_type = $tripListingFilters->resolveTripType($trip);
            $commonBookingCounts = $tripListingFilters->resolveCommonBookingCounts($trip);
            $trip->common_booking_ref_count = $commonBookingCounts['booking_refs'];
            $trip->common_booking_bli_count = $commonBookingCounts['blis'];
            $trip->booking_status_counts = $tripListingFilters->resolveBookingStatusCounts($trip);
            $trip->total_amounts = $tripListingFilters->resolveTripTotalAmounts($trip);
            $travellerDisplay = $tripListingFilters->resolveTravellerDisplay($trip);
            $trip->traveller_display_name = $travellerDisplay['name'];
            $trip->travel_party_size = $travellerDisplay['party_size'];
            $trip->payment_status = $this->resolvePaymentStatus($trip);
            $trip->payment_status_display = $tripListingFilters->resolvePaymentStatusDisplay($trip);
            $trip->payment_status_badges = $tripListingFilters->paymentStatusBadges($trip->payment_status_display);
            $trip->next_actions = $this->resolveNextActions($trip);
        }

        $tripTypes = ['Trip', 'Group Trip', 'Package Trip'];

        return view('admin.trips.index', compact('trips', 'filters', 'paymentStatuses', 'tripTypes'));
    }

    public function confirmBooking(Request $request, string $bookingType, $booking)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $bookingTypeKey = strtolower($bookingType);

        $targetBooking = match ($bookingTypeKey) {
            'accommodation' => AccommodationBooking::findOrFail($booking),
            'activity' => ActivityBooking::findOrFail($booking),
            'transport' => TransportBooking::findOrFail($booking),
            default => abort(404),
        };

        if ($targetBooking->booking_status === 'Cancelled') {
            return back()->with('error', 'Cancelled bookings cannot be updated.');
        }

        $targetBooking->booking_status = 'Confirmed';
        $targetBooking->save();

        return back()->with('success', ucfirst($bookingTypeKey) . ' booking marked as confirmed.');
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

    private function resolveNextActions(Trip $trip): array
    {
        $paymentStatus = strtolower((string) $this->resolvePaymentStatus($trip));
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
                    'url' => route('admin.trips.confirm-booking', ['bookingType' => 'accommodation', 'booking' => $booking->id]),
                ];
            }
        }

        foreach ($trip->activityBookings as $booking) {
            if ((string) ($booking->booking_status ?? '') !== 'Confirmed') {
                $actions[] = [
                    'type' => 'confirm',
                    'label' => 'Activity #' . $booking->id . ' — Mark as Confirmed',
                    'url' => route('admin.trips.confirm-booking', ['bookingType' => 'activity', 'booking' => $booking->id]),
                ];
            }
        }

        foreach ($trip->transportBookings as $booking) {
            if ((string) ($booking->booking_status ?? '') !== 'Confirmed') {
                $actions[] = [
                    'type' => 'confirm',
                    'label' => 'Transport #' . $booking->id . ' — Mark as Confirmed',
                    'url' => route('admin.trips.confirm-booking', ['bookingType' => 'transport', 'booking' => $booking->id]),
                ];
                continue;
            }

            if ($booking instanceof TransportBooking && !$booking->hasCompleteAssignment()) {
                $actions[] = [
                    'type' => 'assign',
                    'label' => 'Transport #' . $booking->id . ' — Assign Driver & Vehicle',
                    'url' => route('admin.transport.booking.details', $booking->id),
                ];
            } else {
                $actions[] = [
                    'type' => 'status',
                    'label' => 'Transport #' . $booking->id . ' — Driver & Vehicle Assigned',
                ];
            }
        }

        if (empty($actions)) {
            $actions[] = [
                'type' => 'completed',
                'label' => 'No Action Required',
            ];
        }

        return $actions;
    }

    public function show(Trip $trip)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $trip->load('bookings.lineItems.travellers', 'travellers');
        return view('admin.trips.show', compact('trip'));
    }

    public function create()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $travellers = TravelerAccount::all();
        return view('admin.trips.create', compact('travellers'));
    }

    public function store(Request $request)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $request->validate([
            'traveler_account_id' => 'required|exists:traveler_accounts,id',
            'title' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
        ]);

        Trip::create($request->all());

        return redirect()->route('admin.trips.index')->with('success', 'Trip created successfully.');
    }

    public function updatePriority(Request $request, Trip $trip)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $request->validate([
            'priority' => ['required', 'in:low,normal,high,urgent'],
        ]);

        $trip->update(['priority' => $request->priority]);

        return back()->with('success', 'Trip priority updated to ' . $trip->fresh()->priority_label . '.');
    }

    public function edit(Trip $trip)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $travellers = TravelerAccount::all();
        return view('admin.trips.edit', compact('trip', 'travellers'));
    }

    public function update(Request $request, Trip $trip)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $request->validate([
            'traveler_account_id' => 'required|exists:traveler_accounts,id',
            'title' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:planned,active,completed,cancelled',
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
        ]);

        $trip->update($request->all());

        return redirect()->route('admin.trips.index')->with('success', 'Trip updated successfully.');
    }
}
