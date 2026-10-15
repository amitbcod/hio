<?php

namespace App\Services;

use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\OperatorDriver;
use App\Models\TransportBooking;
use App\Models\Trip;
use App\Models\TripActionDeadline;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Illuminate\Database\Eloquent\Model;

class TripOperationsOverview
{
    private const ACTIVE_STATUSES = ['pending', 'processing', 'confirmed', 'scheduled'];

    public function apply(Trip $trip, array $intervals, bool $admin): array
    {
        $timeZone = config('app.timezone', 'UTC');
        $candidates = $this->scheduledServices($trip);
        $nextService = collect($candidates)
            ->filter(function (array $service): bool {
                $now = CarbonImmutable::now($service['time_zone']);

                return $service['date_only']
                    ? $service['at']->toDateString() >= $now->toDateString()
                    : $service['at']->greaterThanOrEqualTo($now);
            })
            ->sortBy(fn (array $service) => $service['at']->getTimestamp())
            ->first();
        $servicesByDate = collect($candidates)
            ->sortBy(fn (array $service) => $service['at']->getTimestamp())
            ->values();

        $actions = $this->operationalActions($trip, $candidates, $admin);
        $pendingActions = $this->pendingActions($trip, $candidates, $admin);
        $dueCandidates = $this->serviceActions($trip, $candidates, $admin, false);
        $deadlineData = $this->persistActions(
            $trip,
            $actions,
            $intervals,
            $timeZone,
            array_column($dueCandidates, 'key')
        );
        $dueActions = $this->evaluateDueActions($trip, $dueCandidates, $intervals);

        return [
            'travel_dates' => $servicesByDate->isEmpty() ? null : [
                'start' => $servicesByDate->first()['at']->format('d M Y'),
                'end' => $servicesByDate->last()['at']->format('d M Y'),
            ],
            'next_service' => $nextService ? [
                'name' => $nextService['name'],
                'when' => $this->formatServiceTime($nextService),
            ] : null,
            'actions' => $deadlineData,
            'pending_actions' => $pendingActions,
            'due_actions' => $dueActions,
            'time_zone_fallback' => $timeZone,
        ];
    }

    public function diagnoseActions(Trip $trip, array $intervals, bool $admin): array
    {
        $services = $this->scheduledServices($trip);
        $requiredActions = $this->serviceActions($trip, $services, $admin, false);
        $paymentBlocked = strtolower((string) ($trip->payment_status ?? '')) !== 'paid';
        $currentActions = $this->operationalActions($trip, $services, $admin);
        $records = $trip->relationLoaded('actionDeadlines')
            ? $trip->getRelation('actionDeadlines')
            : $trip->actionDeadlines()->get();
        $types = ['guest_details', 'driver_license_expire', 'driver_assign', 'vehicle_assign'];
        $diagnostics = [];

        foreach ($types as $type) {
            $actions = array_values(array_filter(
                $requiredActions,
                fn (array $action): bool => $action['type'] === $type
            ));
            $evaluations = [];

            foreach ($actions as $action) {
                $record = $records->firstWhere('action_key', $action['key']);
                $interval = $intervals[$type] ?? null;
                $override = $record?->override_hours;
                $hours = $override ?? $interval;
                $target = $action['target'];
                $deadline = $target && $hours !== null
                    ? $target->utc()->subHours((int) $hours)
                    : null;
                $assessment = $this->assessDeadline($deadline, $target);
                $booking = $this->serviceBooking($trip, $action['service_type'], $action['booking_id']);
                $bookingConfirmationBlocked = in_array(
                    strtolower(trim((string) $booking?->booking_status)),
                    ['pending', 'processing'],
                    true
                );
                $blockedBy = $paymentBlocked
                    ? 'payment'
                    : ($bookingConfirmationBlocked ? 'booking_confirmation' : null);
                $reason = $paymentBlocked
                    ? 'PAYMENT_PENDING'
                    : ($bookingConfirmationBlocked
                        ? 'BOOKING_CONFIRMATION_PENDING'
                        : ($target === null
                            ? 'TARGET_DATETIME_MISSING'
                            : ($hours === null ? 'DEADLINE_SETTING_MISSING' : null)));

                $evaluations[] = [
                    'action_key' => $action['key'],
                    'service_type' => $action['service_type'],
                    'service_booking_id' => $action['booking_id'],
                    'action_label' => $this->diagnosticActionLabel($action),
                    'required' => true,
                    'action_state' => $blockedBy ? 'blocked' : 'open',
                    'blocked_by' => $blockedBy,
                    'record_status' => $record?->status,
                    'target_datetime' => $target?->utc()->format('Y-m-d H:i:s\Z'),
                    'target_source' => $action['target_source'],
                    'deadline_setting_hours' => $interval,
                    'admin_override_hours' => $override,
                    'effective_interval_hours' => $hours,
                    'calculated_deadline' => $deadline?->format('Y-m-d H:i:s\Z'),
                    'time_zone' => $action['time_zone'],
                    'remaining_hours_until_deadline' => $assessment['remaining_hours'],
                    'within_due_warning_window' => $assessment['within_due_window'],
                    'deadline_has_passed' => $assessment['deadline_passed'],
                    'status' => $blockedBy ? 'na' : ($reason ? 'na' : $assessment['status']),
                    'reason_code' => $reason,
                ];
            }

            if ($evaluations === []) {
                $typeRecords = $records->where('action_type', $type)->values();
                $record = $typeRecords->first();
                $status = $record?->status;
                $reason = $status === 'cancelled'
                    ? 'ACTION_CANCELLED'
                    : (in_array($status, ['completed', 'complete'], true)
                        ? 'ACTION_COMPLETED'
                        : 'ACTION_NOT_REQUIRED');
                $recordBookingExists = $record
                    ? collect([
                        'accommodation' => $trip->accommodationBookings,
                        'activity' => $trip->activityBookings,
                        'transport' => $trip->transportBookings,
                    ])->get($record->service_type)?->contains(
                        fn (Model $booking): bool => (int) $booking->getKey() === (int) $record->service_booking_id
                    )
                    : null;
                if ($record && $recordBookingExists === false) {
                    $reason = 'SERVICE_BOOKING_NOT_FOUND';
                }
                $evaluations[] = [
                    'action_key' => $record?->action_key,
                    'service_type' => $record?->service_type,
                    'service_booking_id' => $record?->service_booking_id,
                    'action_label' => null,
                    'required' => false,
                    'action_state' => $recordBookingExists === false ? 'not_applicable' : ($status ?: 'not_applicable'),
                    'blocked_by' => null,
                    'record_status' => $status,
                    'target_datetime' => $record?->target_at?->utc()->format('Y-m-d H:i:s\Z'),
                    'target_source' => $record ? 'persisted_action_record' : 'none',
                    'deadline_setting_hours' => $intervals[$type] ?? null,
                    'admin_override_hours' => $record?->override_hours,
                    'effective_interval_hours' => $record?->override_hours ?? ($intervals[$type] ?? null),
                    'calculated_deadline' => $record?->deadline_at?->utc()->format('Y-m-d H:i:s\Z'),
                    'time_zone' => $record?->time_zone ?? config('app.timezone', 'UTC'),
                    'remaining_hours_until_deadline' => $record?->deadline_at
                        ? $this->assessDeadline($record->deadline_at, $record->target_at)['remaining_hours']
                        : null,
                    'within_due_warning_window' => false,
                    'deadline_has_passed' => $record?->deadline_at
                        ? $this->assessDeadline($record->deadline_at, $record->target_at)['deadline_passed']
                        : false,
                    'status' => 'na',
                    'reason_code' => $reason,
                ];
            }

            $aggregateStatus = $this->aggregateDiagnosticStatus($evaluations);
            $diagnosticForAggregate = collect($evaluations)->firstWhere('status', $aggregateStatus);
            $diagnostics[$type] = [
                'required' => collect($evaluations)->contains(fn (array $evaluation): bool => $evaluation['required']),
                'status' => $aggregateStatus,
                'reason_code' => $diagnosticForAggregate
                    ? $diagnosticForAggregate['reason_code']
                    : $evaluations[0]['reason_code'],
                'evaluations' => $evaluations,
            ];
        }
        $currentActionType = $currentActions[0]['type'] ?? null;
        $currentActionStatus = isset($diagnostics[$currentActionType])
            ? $diagnostics[$currentActionType]['status']
            : 'na';

        return [
            'current_next_action' => isset($currentActions[0])
                ? $this->diagnosticActionLabel($currentActions[0])
                : null,
            'current_next_action_status' => $currentActionStatus,
            'payment_status' => $trip->payment_status ?? null,
            'payment_blocked' => $paymentBlocked,
            'actions' => $diagnostics,
        ];
    }

    private function scheduledServices(Trip $trip): array
    {
        $services = [];

        foreach ($trip->accommodationBookings as $booking) {
            if (! $this->isDeliveryStatus($booking)) {
                continue;
            }

            $date = $this->datePart($booking->check_in_date);
            if ($date) {
                $services[] = [
                    'name' => trim((string) ($booking->accommodation?->property_name ?? 'Accommodation')) . ' — Check-in',
                    'at' => CarbonImmutable::parse($date, $this->bookingTimeZone($booking))->startOfDay(),
                    'time_zone' => $this->bookingTimeZone($booking),
                    'date_only' => true,
                    'type' => 'accommodation',
                    'booking' => $booking,
                    'key' => 'accommodation:' . $booking->id . ':check_in',
                ];
            }
        }

        foreach ($trip->activityBookings as $booking) {
            if (! $this->isDeliveryStatus($booking)) {
                continue;
            }

            $date = $this->datePart($booking->activity_date);
            if (! $date) {
                continue;
            }

            $timeZone = $this->bookingTimeZone($booking);
            $startTime = $booking->activityTimeSlot?->start_time;
            $at = CarbonImmutable::parse($date . ' ' . ($startTime ?: '00:00:00'), $timeZone);
            $activityName = trim((string) ($booking->activity?->activity_name ?? $booking->variant_name ?? 'Activity'));
            $services[] = [
                'name' => $activityName,
                'at' => $at,
                'time_zone' => $timeZone,
                'date_only' => ! $startTime,
                'type' => 'activity',
                'booking' => $booking,
                'key' => 'activity:' . $booking->id . ':activity',
            ];
        }

        foreach ($trip->transportBookings as $booking) {
            if (! $this->isDeliveryStatus($booking)) {
                continue;
            }

            $timeZone = $this->bookingTimeZone($booking);
            foreach ([
                ['date' => $booking->pickup_date, 'time' => $booking->pickup_time, 'label' => $booking->service_type ?: 'Transfer', 'key' => 'pickup'],
                ['date' => $booking->return_date, 'time' => $booking->return_time, 'label' => 'Return Transfer', 'key' => 'return'],
            ] as $leg) {
                $date = $this->datePart($leg['date']);
                if (! $date) {
                    continue;
                }
                $route = trim((string) ($booking->route_from ?? ''));
                $destination = trim((string) ($booking->route_to ?? ''));
                $routeText = $route !== '' || $destination !== '' ? ' — ' . trim($route . ' to ' . $destination) : '';
                $services[] = [
                    'name' => trim($leg['label']) . $routeText,
                    'at' => CarbonImmutable::parse($date . ' ' . ($leg['time'] ?: '00:00:00'), $timeZone),
                    'time_zone' => $timeZone,
                    'date_only' => ! $leg['time'],
                    'type' => 'transport',
                    'booking' => $booking,
                    'key' => 'transport:' . $booking->id . ':' . $leg['key'],
                ];
            }
        }

        return $services;
    }

    private function operationalActions(Trip $trip, array $services, bool $admin): array
    {
        if (strtolower((string) ($trip->payment_status ?? '')) !== 'paid') {
            return [$this->paymentAction($trip)];
        }

        $actions = $this->serviceActions($trip, $services, $admin);

        return $actions !== [] ? $actions : [$this->noActionRequired($trip)];
    }

    private function serviceActions(Trip $trip, array $services, bool $admin, bool $respectBookingConfirmation = true): array
    {
        $actions = [];
        $serviceTargets = collect($services)->keyBy(fn (array $service) => $service['type'] . ':' . $service['booking']->id);

        foreach ([
            'accommodation' => $trip->accommodationBookings,
            'activity' => $trip->activityBookings,
            'transport' => $trip->transportBookings,
        ] as $type => $bookings) {
            foreach ($bookings as $booking) {
                $status = strtolower(trim((string) $booking->booking_status));
                if (in_array($status, ['cancelled', 'canceled', 'completed'], true)) {
                    continue;
                }

                $target = $serviceTargets->get($type . ':' . $booking->id);
                $targetAt = $target['at'] ?? null;
                $timeZone = $target['time_zone'] ?? $this->bookingTimeZone($booking);

                if (in_array($status, ['pending', 'processing'], true)) {
                    if ($respectBookingConfirmation) {
                        $actions[] = $this->action(
                            $type,
                            $booking,
                            'booking_confirm',
                            ucfirst($type) . ' #' . $booking->id . ' — Mark as Confirmed',
                            null,
                            $timeZone,
                            $admin
                        );
                        continue;
                    }
                }

                if (! $this->hasCompleteGuestDetails($booking, $type)) {
                    $actions[] = $this->action(
                        $type,
                        $booking,
                        'guest_details',
                        ucfirst($type) . ' #' . $booking->id . ' — Complete Guest Details',
                        $targetAt,
                        $timeZone,
                        $admin,
                        '',
                        $target['key'] ?? $type . '_booking.schedule'
                    );
                }

                if (! $booking instanceof TransportBooking) {
                    continue;
                }

                $pickupTarget = $this->transportTarget($booking, 'pickup');
                $hasPickupDriver = ! empty($booking->pickup_driver_id ?? $booking->driver_id);
                $hasReturnLeg = ! empty($booking->return_date);
                $hasReturnDriver = ! empty($booking->return_driver_id);
                if (! $hasPickupDriver || ($hasReturnLeg && ! $hasReturnDriver)) {
                    $driverTarget = ! $hasPickupDriver
                        ? $pickupTarget
                        : $this->transportTarget($booking, 'return');
                    $actions[] = $this->action(
                        'transport',
                        $booking,
                        'driver_assign',
                        'Transport #' . $booking->id . ' — Assign ' . (! $hasPickupDriver ? 'Pickup Driver' : 'Return Driver'),
                        $driverTarget,
                        $timeZone,
                        $admin,
                        '',
                        $hasPickupDriver ? 'transport_booking.return_date_time' : 'transport_booking.pickup_date_time'
                    );
                }

                if (! $booking->hasVehicleAssignment()) {
                    $actions[] = $this->action(
                        'transport',
                        $booking,
                        'vehicle_assign',
                        'Transport #' . $booking->id . ' — Assign Vehicle',
                        $pickupTarget,
                        $timeZone,
                        $admin,
                        '',
                        'transport_booking.pickup_date_time'
                    );
                }

                foreach ([
                    [$booking->pickupDriver ?? $booking->driver, $pickupTarget, 'pickup'],
                    [$booking->returnDriver, $this->transportTarget($booking, 'return'), 'return'],
                ] as [$driver, $legTarget, $leg]) {
                    if (! $driver instanceof OperatorDriver || ! $driver->license_expiry_date) {
                        continue;
                    }
                    $expiryDate = $this->datePart($driver->license_expiry_date);
                    if (! $expiryDate) {
                        continue;
                    }
                    $expiry = CarbonImmutable::parse($expiryDate, $timeZone)->startOfDay();
                    if ($legTarget && $expiry->lessThanOrEqualTo($legTarget)) {
                        $actions[] = $this->action(
                            'transport',
                            $booking,
                            'driver_license_expire',
                            'Transport #' . $booking->id . ' — Driver License Expire (' . $driver->driver_name . ')',
                            $expiry,
                            $timeZone,
                            $admin,
                            'driver-' . $driver->id . '-' . $leg,
                            'operator_driver.license_expiry_date'
                        );
                    }
                }
            }
        }

        return $actions;
    }

    private function pendingActions(Trip $trip, array $services, bool $admin): array
    {
        return collect($this->serviceActions($trip, $services, $admin))
            ->merge($this->serviceActions($trip, $services, $admin, false))
            ->unique('key')
            ->reject(fn (array $action): bool => $this->bookingOrderHasEnded($trip, $action, $services))
            ->map(function (array $action) use ($trip, $services): array {
                $booking = $this->serviceBooking($trip, $action['service_type'], $action['booking_id']);
                $service = $this->serviceForAction($action, $services);

                return [
                    'key' => $action['key'],
                    'label' => $action['label'],
                    'type' => $action['type'],
                    'service_type' => $action['service_type'],
                    'booking_id' => $action['booking_id'],
                    'booking_reference' => $booking?->getAttribute('booking_reference')
                        ?: ($booking?->relationLoaded('bookingRef')
                            ? $booking->getRelation('bookingRef')?->booking_ref_code
                            : null),
                    'booking_status' => $booking?->getAttribute('booking_status'),
                    'service_date' => $service ? $this->formatServiceTime($service) : null,
                    'url' => $action['url'],
                ];
            })
            ->values()
            ->all();
    }

    private function bookingOrderHasEnded(Trip $trip, array $action, array $services): bool
    {
        $matchingServices = collect($services)->filter(
            fn (array $service): bool => $service['type'] === $action['service_type']
                && (int) $service['booking']->getKey() === $action['booking_id']
        );

        if ($action['service_type'] === 'accommodation') {
            $booking = $matchingServices->first()['booking']
                ?? $this->serviceBooking($trip, $action['service_type'], $action['booking_id']);
            $checkoutDate = $booking ? $this->datePart($booking->getAttribute('check_out_date')) : null;
            if ($checkoutDate) {
                $timeZone = $this->bookingTimeZone($booking);

                return $checkoutDate < CarbonImmutable::now($timeZone)->toDateString();
            }

            return $matchingServices->isNotEmpty()
                && $this->serviceHasEnded($matchingServices->first());
        }

        $relevantService = $this->serviceForAction($action, $services);
        if ($action['type'] === 'booking_confirm' || $action['type'] === 'guest_details') {
            $relevantService = $matchingServices
                ->sortBy(fn (array $service) => $service['at']->getTimestamp())
                ->last();
        }

        return $relevantService !== null && $this->serviceHasEnded($relevantService);
    }

    private function serviceForAction(array $action, array $services): ?array
    {
        $matchingServices = collect($services)->filter(
            fn (array $service): bool => $service['type'] === $action['service_type']
                && (int) $service['booking']->getKey() === $action['booking_id']
        );
        if ($matchingServices->isEmpty()) {
            return null;
        }

        if ($action['type'] === 'driver_license_expire') {
            $leg = str_ends_with((string) $action['key'], '-return') ? 'return' : 'pickup';
            return $matchingServices->first(
                fn (array $service): bool => str_ends_with($service['key'], ':' . $leg)
            );
        }

        if ($action['target'] instanceof CarbonImmutable) {
            $matchingTarget = $matchingServices->first(
                fn (array $service): bool => $service['at']->equalTo($action['target'])
            );
            if ($matchingTarget) {
                return $matchingTarget;
            }
        }

        return $matchingServices
            ->sortBy(fn (array $service) => $service['at']->getTimestamp())
            ->first();
    }

    private function serviceHasEnded(array $service): bool
    {
        $now = CarbonImmutable::now($service['time_zone']);

        return $service['date_only']
            ? $service['at']->toDateString() < $now->toDateString()
            : $service['at']->lessThan($now);
    }

    private function paymentAction(Trip $trip): array
    {
        return [
            'key' => 'payment:' . $trip->id,
            'type' => 'payment',
            'label' => 'Awaiting Payment',
            'target' => null,
            'time_zone' => config('app.timezone', 'UTC'),
            'service_type' => 'trip',
            'booking_id' => 0,
            'url' => null,
            'status' => 'na',
            'blocked_by' => 'payment',
        ];
    }

    private function noActionRequired(Trip $trip): array
    {
        return [
            'key' => 'completed:' . $trip->id,
            'type' => 'completed',
            'label' => 'No Action Required',
            'target' => null,
            'time_zone' => config('app.timezone', 'UTC'),
            'service_type' => 'trip',
            'booking_id' => 0,
            'url' => null,
        ];
    }

    private function action(
        string $serviceType,
        Model $booking,
        string $type,
        string $label,
        ?CarbonImmutable $target,
        string $timeZone,
        bool $admin,
        string $scope = '',
        string $targetSource = 'unknown'
    ): array {
        $route = match ($serviceType) {
            'accommodation' => $admin
                ? route('admin.accommodation.booking.details', $booking->getKey())
                : route('operator.accommodation.booking.details', $booking->getKey()),
            'activity' => $admin
                ? route('admin.activity.booking.details', $booking->getKey())
                : route('operator.activity.booking.details', $booking->getKey()),
            'transport' => $admin
                ? route('admin.transport.booking.details', $booking->getKey())
                : (filled($booking->getAttribute('transport_id'))
                    ? route('operator.transport.booking.details', [
                        'transport' => $booking->getAttribute('transport_id'),
                        'booking' => $booking->getKey(),
                    ])
                    : null),
            default => null,
        };

        return [
            'key' => $serviceType . ':' . $booking->getKey() . ':' . $type . ($scope !== '' ? ':' . $scope : ''),
            'type' => $type,
            'label' => $label,
            'target' => $target,
            'time_zone' => $timeZone,
            'service_type' => $serviceType,
            'booking_id' => (int) $booking->getKey(),
            'url' => $route,
            'target_source' => $targetSource,
        ];
    }

    private function persistActions(
        Trip $trip,
        array $actions,
        array $intervals,
        string $fallbackTimeZone,
        array $retainedActionKeys = []
    ): array
    {
        $records = $trip->relationLoaded('actionDeadlines')
            ? $trip->getRelation('actionDeadlines')
            : $trip->actionDeadlines()->get();
        $activeKeys = [];
        $knownBookingIds = [
            'accommodation' => $trip->accommodationBookings->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'activity' => $trip->activityBookings->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'transport' => $trip->transportBookings->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ];
        $result = [];

        foreach ($actions as $action) {
            $persistable = in_array($action['type'], [
                'guest_details',
                'driver_license_expire',
                'driver_assign',
                'vehicle_assign',
            ], true);
            $record = $persistable ? $records->firstWhere('action_key', $action['key']) : null;
            $hours = $record?->override_hours ?? ($intervals[$action['type']] ?? null);
            $target = $action['target'];
            $targetUtc = $target?->utc();
            $deadlineUtc = $targetUtc && $hours !== null ? $targetUtc->subHours((int) $hours) : null;

            if ($persistable) {
                $activeKeys[] = $action['key'];
                $isNewRecord = ! $record;
                if (! $record) {
                    $record = new TripActionDeadline();
                    $record->trip_id = $trip->id;
                    $record->service_type = $action['service_type'];
                    $record->service_booking_id = $action['booking_id'];
                    $record->action_type = $action['type'];
                    $record->action_key = $action['key'];
                }
                $record->label = $action['label'];
                $record->target_at = $targetUtc;
                $record->deadline_at = $deadlineUtc;
                $record->time_zone = $action['time_zone'] ?: $fallbackTimeZone;
                $record->status = 'open';
                $record->completed_at = null;
                if ($isNewRecord || $record->isDirty()) {
                    $record->save();
                }
                if ($isNewRecord) {
                    $records->push($record);
                }
            }

            $status = $persistable
                ? $this->assessDeadline($deadlineUtc, $targetUtc)['status']
                : 'na';

            $result[] = array_merge($action, [
                'record_id' => $record?->id,
                'deadline' => $deadlineUtc,
                'status' => $status,
                'deadline_display' => $deadlineUtc
                    ? $this->formatDeadline($deadlineUtc, $action['time_zone'])
                    : 'N/A',
                'override_hours' => $record?->override_hours,
            ]);
        }

        $activeKeys = array_merge($activeKeys, $retainedActionKeys);

        foreach ($records as $record) {
            $bookingIsInScope = in_array(
                (int) $record->service_booking_id,
                $knownBookingIds[$record->service_type] ?? [],
                true
            );
            if ($bookingIsInScope && $record->status === 'open' && ! in_array($record->action_key, $activeKeys, true)) {
                $booking = collect([
                    'accommodation' => $trip->accommodationBookings,
                    'activity' => $trip->activityBookings,
                    'transport' => $trip->transportBookings,
                ])->get($record->service_type)?->firstWhere('id', (int) $record->service_booking_id);
                $bookingStatus = strtolower(trim((string) ($booking?->booking_status ?? '')));
                $record->status = in_array($bookingStatus, ['cancelled', 'canceled'], true)
                    ? 'cancelled'
                    : 'completed';
                $record->completed_at = now('UTC');
                $record->save();
            }
        }

        return $result;
    }

    private function evaluateDueActions(Trip $trip, array $actions, array $intervals): array
    {
        $records = $trip->relationLoaded('actionDeadlines')
            ? $trip->getRelation('actionDeadlines')
            : $trip->actionDeadlines()->get();
        $result = [];

        foreach ($actions as $action) {
            if (! in_array($action['type'], [
                'guest_details',
                'driver_license_expire',
                'driver_assign',
                'vehicle_assign',
            ], true)) {
                continue;
            }

            $record = $records->firstWhere('action_key', $action['key']);
            $hours = $record?->override_hours ?? ($intervals[$action['type']] ?? null);
            $targetUtc = $action['target']?->utc();
            $deadlineUtc = $targetUtc && $hours !== null
                ? $targetUtc->subHours((int) $hours)
                : null;
            $assessment = $this->assessDeadline($deadlineUtc, $targetUtc);
            $status = $this->tripHasEnded($trip)
                ? 'na'
                : $assessment['status'];
            $due = $status === 'due';

            $result[] = array_merge($action, [
                'record_id' => $record?->id,
                'deadline' => $deadlineUtc,
                'status' => $status,
                'action_name' => $this->dueActionName($action),
                'target_display' => $due && $targetUtc
                    ? $this->formatDeadline($targetUtc, $action['time_zone'])
                    : null,
                'due_since_display' => $due && $deadlineUtc
                    ? $this->formatDeadline($deadlineUtc, $action['time_zone'])
                    : null,
                'override_hours' => $record?->override_hours,
            ]);
        }

        return $result;
    }

    private function dueActionName(array $action): string
    {
        $service = ucfirst($action['service_type']);
        $booking = '#' . $action['booking_id'];

        return match ($action['type']) {
            'guest_details' => 'Complete guest details for ' . strtolower($service) . ' booking ' . $booking,
            'driver_license_expire' => 'Check driver licence expiry for transport booking ' . $booking,
            'driver_assign' => 'Assign a driver for transport booking ' . $booking,
            'vehicle_assign' => 'Assign a vehicle for transport booking ' . $booking,
            default => $action['label'],
        };
    }

    private function tripHasEnded(Trip $trip): bool
    {
        $endDate = $this->datePart($trip->end_date);
        if ($endDate === null) {
            return false;
        }

        $timeZone = $this->validTimeZone((string) config('app.timezone', 'UTC'));
        $endOfTrip = CarbonImmutable::parse($endDate, $timeZone)->endOfDay();

        return CarbonImmutable::now($timeZone)->greaterThan($endOfTrip);
    }

    private function assessDeadline(?CarbonImmutable $deadline, ?CarbonImmutable $target): array
    {
        if (! $deadline || ! $target) {
            return [
                'status' => 'na',
                'remaining_hours' => null,
                'within_due_window' => false,
                'deadline_passed' => false,
            ];
        }

        $now = CarbonImmutable::now('UTC');
        $remainingHours = $now->diffInMinutes($deadline, false) / 60;
        $withinDueWindow = $now->greaterThanOrEqualTo($deadline) && $now->lessThan($target);

        return [
            'status' => $withinDueWindow
                ? 'due'
                : ($now->lessThan($deadline) ? 'scheduled' : 'na'),
            'remaining_hours' => $remainingHours,
            'within_due_window' => $withinDueWindow,
            'deadline_passed' => $now->greaterThanOrEqualTo($target),
        ];
    }

    private function aggregateDiagnosticStatus(array $evaluations): string
    {
        foreach (['due', 'scheduled'] as $status) {
            if (collect($evaluations)->contains(fn (array $evaluation): bool => $evaluation['status'] === $status)) {
                return $status;
            }
        }

        return 'na';
    }

    private function diagnosticActionLabel(array $action): string
    {
        if ($action['type'] === 'driver_license_expire') {
            return 'Transport #' . $action['booking_id'] . ' — Driver License Expire';
        }

        return $action['label'];
    }

    private function serviceBooking(Trip $trip, string $serviceType, int $bookingId): ?Model
    {
        $relation = match ($serviceType) {
            'accommodation' => 'accommodationBookings',
            'activity' => 'activityBookings',
            'transport' => 'transportBookings',
            default => null,
        };

        return $relation
            ? $trip->getRelation($relation)->firstWhere('id', $bookingId)
            : null;
    }

    private function hasCompleteGuestDetails(Model $booking, string $type): bool
    {
        $required = match ($type) {
            'accommodation' => (int) ($booking->adults ?? 0) + (int) ($booking->children ?? 0) + (int) ($booking->infants ?? 0),
            'activity' => (int) ($booking->adults ?? 0) + (int) ($booking->children ?? 0) + (int) ($booking->infants ?? 0),
            'transport' => max(1, (int) ($booking->total_passengers ?? ((int) ($booking->adults ?? 0) + (int) ($booking->children ?? 0)))),
            default => 0,
        };
        if ($required < 1) {
            return true;
        }

        $guests = $booking->relationLoaded('guests') ? $booking->getRelation('guests') : collect();
        if ($guests->count() === $required) {
            return $guests->every(fn ($guest) => filled($guest->first_name)
                && filled($guest->last_name)
                && filled($guest->dob)
                && filled($guest->nationality));
        }

        if ($type === 'transport' && $required === 1) {
            return filled($booking->traveler_first_name)
                && filled($booking->traveler_last_name)
                && filled($booking->traveler_dob)
                && filled($booking->traveler_nationality);
        }

        return false;
    }

    private function formatServiceTime(array $service): string
    {
        $at = $service['at'];
        if ($service['date_only']) {
            return $at->format('d M Y') . ' · Time not specified';
        }

        $today = CarbonImmutable::now($service['time_zone'])->startOfDay();
        $dateLabel = $at->isSameDay($today)
            ? 'Today'
            : ($at->isSameDay($today->addDay()) ? 'Tomorrow' : $at->format('d M Y'));

        return $dateLabel . ', ' . $at->format('H:i') . ' · UTC' . $at->format('P');
    }

    private function formatDeadline(CarbonImmutable $deadline, string $timeZone): string
    {
        $local = $deadline->setTimezone($timeZone);

        return $local->format('d M Y, H:i') . ' · UTC' . $local->format('P');
    }

    private function validTimeZone(string $timeZone): string
    {
        return in_array($timeZone, DateTimeZone::listIdentifiers(), true) ? $timeZone : 'UTC';
    }

    private function bookingTimeZone(Model $booking): string
    {
        $candidate = $booking->getAttribute('time_zone');
        if (is_string($candidate) && in_array($candidate, DateTimeZone::listIdentifiers(), true)) {
            return $candidate;
        }

        $fallback = (string) config('app.timezone', 'UTC');

        return in_array($fallback, DateTimeZone::listIdentifiers(), true) ? $fallback : 'UTC';
    }

    private function transportTarget(TransportBooking $booking, string $leg): ?CarbonImmutable
    {
        $date = $this->datePart($leg === 'return' ? $booking->return_date : $booking->pickup_date);
        if (! $date) {
            return null;
        }
        $time = $leg === 'return' ? $booking->return_time : $booking->pickup_time;

        return CarbonImmutable::parse($date . ' ' . ($time ?: '00:00:00'), $this->bookingTimeZone($booking));
    }

    private function isDeliveryStatus(Model $booking): bool
    {
        return in_array(strtolower(trim((string) $booking->booking_status)), self::ACTIVE_STATUSES, true);
    }

    private function datePart(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : CarbonImmutable::parse((string) $value)->format('Y-m-d');
    }
}
