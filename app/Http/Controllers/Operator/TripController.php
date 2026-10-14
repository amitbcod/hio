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
use App\Models\TripActionDeadlineSetting;
use App\Services\TripListingFilters;
use App\Services\TripOperationsOverview;
use App\Services\VoucherAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
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
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();

        if (! $operator) {
            abort(403);
        }

        Log::info('OPERATOR_TRIP_LISTING_DIAGNOSTIC', [
            'operator_id' => $operator->id ?? $operator->operator_id ?? null,
            'business_id' => $operator->business_id ?? null,
            'guard' => Auth::guard('operator')->check() ? 'operator' : 'operator_staff',
            'request_filters' => $request->query->all(),
            'route' => $request->route()?->getName(),
        ]);

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
            'traveller_information' => ['nullable', 'in:missing,complete'],
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

        $latestDiagnosticTripIds = (clone $tripScope)
            ->select('trips.id')
            ->distinct()
            ->orderByDesc('trips.id')
            ->limit(10)
            ->pluck('trips.id')
            ->all();

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
            $trip->operations_overview = $operationsOverview->apply($trip, $deadlineIntervals, false);
            $trip->next_actions = $trip->operations_overview['actions'];
            $trip->due_actions = $trip->operations_overview['due_actions'];
            $trip->common_booking_reference = $this->resolvePrimaryBookingReference($trip);
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

        Log::info('OPERATOR_TRIP_LISTING_DIAGNOSTIC_RESULT', [
            'operator_id' => $operator->id ?? $operator->operator_id ?? null,
            'trip_count' => $trips->total(),
            'items_on_page' => $trips->count(),
            'service_scope' => [
                'accommodation_ids_count' => $accommodationIds->count(),
                'activity_ids_count' => $activityIds->count(),
                'transport_ids_count' => $transportIds->count(),
            ],
            'first_trip_ids' => collect($trips->items())->pluck('id')->take(10)->values()->all(),
        ]);

        $viewActionsByTrip = collect($trips->items())
            ->mapWithKeys(fn (Trip $trip): array => [$trip->id => [
                'next_actions' => $trip->next_actions,
                'due_actions' => $trip->due_actions,
            ]]);
        $this->logDueOverdueActionDiagnostics($latestDiagnosticTripIds, $deadlineIntervals, $operationsOverview, $tripListingFilters, $accommodationIds, $activityIds, $transportIds, $viewActionsByTrip);

        return view('operator.trips.index', compact('trips', 'filters', 'paymentStatuses', 'tripTypes'));
    }

    private function logDueOverdueActionDiagnostics(
        array $tripIds,
        array $intervals,
        TripOperationsOverview $operationsOverview,
        TripListingFilters $tripListingFilters,
        Collection $accommodationIds,
        Collection $activityIds,
        Collection $transportIds,
        Collection $viewActionsByTrip
    ): void
    {
        if ($tripIds === []) {
            return;
        }

        $actionTypes = ['guest_details', 'driver_license_expire', 'driver_assign', 'vehicle_assign'];
        $trips = Trip::whereIn('id', $tripIds)
            ->with([
                'bookings.payments',
                'accommodationBookings.accommodation',
                'accommodationBookings.guests',
                'activityBookings.activity',
                'activityBookings.activityTimeSlot',
                'activityBookings.guests',
                'transportBookings.transport.operator.profile',
                'transportBookings.pickupDriver',
                'transportBookings.returnDriver',
                'transportBookings.driver',
                'transportBookings.guests',
                'actionDeadlines',
            ])
            ->orderByDesc('id')
            ->get();

        foreach ($trips as $trip) {
            $trip->accommodationBookings = $this->scopeAccommodationBookings($trip, $accommodationIds);
            $trip->activityBookings = $this->scopeActivityBookings($trip, $activityIds);
            $trip->transportBookings = $this->scopeTransportBookings($trip, $transportIds);
            $trip->payment_status = $this->resolvePaymentStatus($trip);
            $trip->trip_type = $tripListingFilters->resolveTripType($trip);
            $diagnostic = $operationsOverview->diagnoseActions($trip, $intervals, false);

            foreach ($actionTypes as $actionType) {
                $action = $diagnostic['actions'][$actionType];
                $nowUtc = CarbonImmutable::now('UTC');
                $actionValues = collect($action['evaluations'])
                    ->map(fn (array $evaluation): array => [
                        'service_type' => $evaluation['service_type'],
                        'service_booking_id' => $evaluation['service_booking_id'],
                        'status' => $evaluation['status'],
                        'reason_code' => $evaluation['reason_code'],
                    ])
                    ->all();
                $representativeEvaluation = collect($action['evaluations'])
                    ->firstWhere('status', $action['status']) ?? $action['evaluations'][0];
                $viewTripData = $viewActionsByTrip->get($trip->id, []);
                $viewActionStatuses = collect($viewTripData['due_actions'] ?? [])
                    ->where('type', $actionType)
                    ->map(fn (array $viewAction): array => [
                        'action_key' => $viewAction['key'],
                        'service_booking_id' => $viewAction['booking_id'],
                        'status' => $viewAction['status'],
                    ])
                    ->values()
                    ->all();
                $nextActionStatuses = collect($viewTripData['next_actions'] ?? [])
                    ->where('type', $actionType)
                    ->pluck('status')
                    ->values()
                    ->all();

                Log::info('DUE_OVERDUE_ACTION_DIAGNOSTIC', [
                    'trip_id' => $trip->id,
                    'trip_type' => $trip->trip_type,
                    'payment_status' => $diagnostic['payment_status'],
                    'payment_blocks_service_actions' => $diagnostic['payment_blocked'],
                    'current_next_action' => $diagnostic['current_next_action'],
                    'current_next_action_status' => $diagnostic['current_next_action_status'],
                    'next_action_statuses_returned_to_view' => $nextActionStatuses,
                    'action_type' => $actionType,
                    'action_status' => $action['status'],
                    'action_state' => $representativeEvaluation['action_state'],
                    'required' => $action['required'],
                    'blocked_by' => $representativeEvaluation['blocked_by'],
                    'deadline_setting_hours' => $representativeEvaluation['deadline_setting_hours'],
                    'admin_override_hours' => $representativeEvaluation['admin_override_hours'],
                    'effective_interval_hours' => $representativeEvaluation['effective_interval_hours'],
                    'target_datetime' => $representativeEvaluation['target_datetime'],
                    'target_datetime_source' => $representativeEvaluation['target_source'],
                    'calculated_deadline' => $representativeEvaluation['calculated_deadline'],
                    'time_zone' => $representativeEvaluation['time_zone'],
                    'comparison_time_utc' => $nowUtc->format('Y-m-d H:i:s\Z'),
                    'remaining_hours_until_deadline' => $representativeEvaluation['remaining_hours_until_deadline'],
                    'within_due_warning_window' => $representativeEvaluation['within_due_warning_window'],
                    'deadline_has_passed' => $representativeEvaluation['deadline_has_passed'],
                    'due_overdue_value_returned_to_view' => count($viewActionStatuses) === 1
                        ? $viewActionStatuses[0]['status']
                        : ($viewActionStatuses !== []
                            ? collect($viewActionStatuses)->pluck('status')->all()
                            : 'na'),
                    'diagnostic_action_status' => $action['status'],
                    'listing_view_action_statuses' => $viewActionStatuses,
                    'listing_view_status_source' => $viewActionsByTrip->has($trip->id)
                        ? 'trip.due_actions'
                        : 'diagnostic_evaluation_not_on_current_page',
                    'action_values_returned_to_view' => $actionValues,
                    'action_evaluations' => $action['evaluations'],
                    'reason_code' => $action['reason_code'],
                ]);
            }
        }
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

}
