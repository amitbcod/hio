<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\Trip;
use App\Models\TripActionDeadline;
use App\Models\TripActionDeadlineSetting;
use App\Models\TravelerAccount;
use App\Models\TransportBooking;
use App\Services\TripListingFilters;
use App\Services\TripOperationsOverview;
use App\Services\VoucherAvailability;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(
        Request $request,
        TripListingFilters $tripListingFilters,
        VoucherAvailability $voucherAvailability,
        TripOperationsOverview $operationsOverview
    )
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $filters = $request->validate([
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'payment_status' => ['nullable', 'string', 'max:100'],
            'trip_type' => ['nullable', 'in:Trip,Group Trip,Package Trip'],
            'traveller_information' => ['nullable', 'in:missing,complete'],
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
            'activityBookings.activityTimeSlot',
            'activityBookings.bookingRef',
            'activityBookings.guests',
            'transportBookings.transport.operator.profile',
            'transportBookings.transport.vehicleName',
            'transportBookings.pickupDriver',
            'transportBookings.returnDriver',
            'transportBookings.driver',
            'transportBookings.bookingRef',
            'transportBookings.guests',
            'actionDeadlines',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $deadlineIntervals = TripActionDeadlineSetting::intervals();
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
            $trip->listing_booking_reference = $tripListingFilters->resolvePrimaryBookingReference($trip);
            $trip->payment_status = $this->resolvePaymentStatus($trip);
            $trip->payment_status_display = $tripListingFilters->resolvePaymentStatusDisplay($trip);
            $trip->payment_status_badges = $tripListingFilters->paymentStatusBadges($trip->payment_status_display);
            $trip->operations_overview = $operationsOverview->apply($trip, $deadlineIntervals, true);
            $trip->next_actions = $trip->operations_overview['actions'];
            $trip->due_actions = $trip->operations_overview['due_actions'];
            foreach ([
                'accommodation' => $trip->accommodationBookings,
                'activity' => $trip->activityBookings,
                'transport' => $trip->transportBookings,
            ] as $serviceType => $serviceBookings) {
                foreach ($serviceBookings as $serviceBooking) {
                    $serviceBooking->setAttribute(
                        'voucher_available',
                        $voucherAvailability->isAvailable($trip, $serviceBooking, $serviceType)
                    );
                    $serviceBooking->setAttribute(
                        'participant_names',
                        $tripListingFilters->resolveServiceParticipantNames($trip, $serviceBooking, $serviceType)
                    );
                }
            }
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

    public function updateActionDeadline(Request $request, Trip $trip, TripActionDeadline $actionDeadline)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        abort_unless(
            (int) $actionDeadline->trip_id === (int) $trip->id
                && $actionDeadline->status === 'open',
            404
        );

        $validated = $request->validate([
            'override_hours' => ['nullable', 'integer', 'min:0', 'max:8760'],
        ]);

        $hours = $validated['override_hours'] ?? null;
        $actionDeadline->override_hours = $hours;
        $actionDeadline->changed_by_admin_id = $hours === null ? null : (int) session('admin_id');
        $actionDeadline->changed_at = $hours === null ? null : now('UTC');
        $actionDeadline->deadline_at = $actionDeadline->target_at && $hours !== null
            ? $actionDeadline->target_at->subHours((int) $hours)
            : ($actionDeadline->target_at
                ? $actionDeadline->target_at->subHours(
                    (int) (TripActionDeadlineSetting::intervals()[$actionDeadline->action_type] ?? 24)
                )
                : null);
        $actionDeadline->save();

        return back()->with('success', 'Action deadline updated.');
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
