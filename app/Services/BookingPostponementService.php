<?php

namespace App\Services;

use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\ActivitySchedulingTimeSlot;
use App\Models\AdminUser;
use App\Models\BookingScheduleChange;
use App\Models\Operator;
use App\Models\OperatorUser;
use App\Models\TransportBooking;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BookingPostponementService
{
    private const BOOKING_MODELS = [
        'accommodation' => AccommodationBooking::class,
        'activity' => ActivityBooking::class,
        'transport' => TransportBooking::class,
    ];

    public function loadForDetails(string $serviceType, int $bookingId): Model
    {
        $booking = $this->bookingQuery($serviceType, $bookingId)->firstOrFail();
        $this->loadBookingRelations($booking, $serviceType);

        return $booking;
    }

    public function details(Model $booking, string $serviceType, bool $isAdmin): array
    {
        $this->loadBookingRelations($booking, $serviceType);

        return collect($this->scheduleLegs($booking, $serviceType))
            ->map(function (array $leg) use ($booking, $serviceType, $isAdmin): array {
                $active = $this->isEligibleStatus($booking);
                $current = $this->scheduleDateTime($leg['date'], $leg['time']);
                $operatorCanPostpone = $current !== null && $current->gt(now(config('app.timezone')));
                $unavailableReason = ! $active
                    ? __('booking_postponement.ineligible_status')
                    : ($current === null
                        ? __('booking_postponement.original_time_missing')
                        : ($operatorCanPostpone ? null : __('booking_postponement.booking_time_passed')));

                return $leg + [
                    'active' => $active,
                    'can_submit' => $active && ($isAdmin || $operatorCanPostpone),
                    'unavailable_reason' => $unavailableReason,
                    'expected_schedule' => $this->scheduleToken($booking, $serviceType, $leg['leg']),
                ];
            })
            ->all();
    }

    public function activitySlotsForDate(
        int $bookingId,
        string $date,
        Model $actor,
        bool $isAdmin
    ): array {
        $booking = $this->bookingQuery('activity', $bookingId)->firstOrFail();
        $this->authorizeBooking($booking, 'activity', $actor, $isAdmin);
        $selectedDate = Carbon::createFromFormat('!Y-m-d', $date, config('app.timezone'));
        if (! $selectedDate || $selectedDate->toDateString() !== $date) {
            throw ValidationException::withMessages([
                'date' => __('booking_postponement.invalid_schedule'),
            ]);
        }

        $booking->loadMissing('activity');
        if (! $booking->activity) {
            $this->reject(__('booking_postponement.schedule_missing'));
        }

        return $this->eligibleActivitySlots($booking, $selectedDate)
            ->map(function (array $availableSlot): array {
                $slot = $availableSlot['slot'];

                return [
                    'id' => $slot->getKey(),
                    'variant_id' => $slot->variant_id,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'capacity' => (int) $slot->capacity_per_slot,
                    'available' => $availableSlot['available'],
                    'display' => trim(($slot->start_time ?? '') . ' - ' . ($slot->end_time ?? '') . ($slot->duration ? ' (' . $slot->duration . ')' : '')),
                ];
            })
            ->values()
            ->all();
    }

    private function eligibleActivitySlots(ActivityBooking $booking, Carbon $date)
    {
        $booking->loadMissing('activity');
        $activity = $booking->activity;
        if (! $activity) {
            $this->reject(__('booking_postponement.schedule_missing'));
        }
        if (! $this->activityVariantAllowsParticipants($booking)) {
            return collect();
        }

        $isBlackout = Schema::hasTable('activity_blackout_dates')
            && $activity->blackoutDates()
                ->where(function ($query) use ($booking) {
                    $query->whereNull('variant_id')
                        ->orWhere('variant_id', $booking->variant_id);
                })
                ->whereDate('start_date', '<=', $date->toDateString())
                ->whereDate('end_date', '>=', $date->toDateString())
                ->exists();
        if ($isBlackout) {
            return collect();
        }

        $participantCount = (int) ($booking->adults ?? 0)
            + (int) ($booking->children ?? 0)
            + (int) ($booking->infants ?? 0);
        $bookedParticipantsBySlot = app(ActivitySlotAvailabilityService::class)
            ->bookedParticipantsBySlot(
                (int) $booking->activity_id,
                $date->toDateString(),
                (int) $booking->getKey()
            );

        return ActivitySchedulingTimeSlot::query()
            ->where('activity_id', $booking->activity_id)
            ->where(function ($query) use ($booking) {
                if ($booking->variant_id) {
                    $query->whereNull('variant_id')
                        ->orWhere('variant_id', $booking->variant_id);
                } else {
                    $query->whereNull('variant_id');
                }
            })
            ->orderBy('start_time')
            ->get()
            ->filter(function (ActivitySchedulingTimeSlot $slot) use ($date): bool {
                $days = array_map('strtolower', array_map('strval', $slot->days_of_week ?? []));
                if ($days && ! in_array(strtolower($date->format('l')), $days, true)) {
                    return false;
                }

                $startsAt = $this->scheduleDateTime(
                    $date->toDateString(),
                    $this->timeString($slot->start_time)
                );

                return $startsAt
                    && $startsAt->gte(now(config('app.timezone'))->addMinutes(max(0, (int) ($slot->lead_time_minutes ?? 0))));
            })
            ->map(function (ActivitySchedulingTimeSlot $slot) use ($bookedParticipantsBySlot, $participantCount): ?array {
                $occupied = $bookedParticipantsBySlot[(int) $slot->getKey()] ?? 0;
                $capacity = max(0, (int) ($slot->capacity_per_slot ?? 0));
                $available = max(0, $capacity - $occupied);
                if ($capacity <= 0 || $available < $participantCount) {
                    return null;
                }

                return ['slot' => $slot, 'available' => $available];
            })
            ->filter()
            ->keyBy(fn (array $availableSlot) => (int) $availableSlot['slot']->getKey());
    }

    public function postpone(
        Request $request,
        string $serviceType,
        int $bookingId,
        Model $actor,
        bool $isAdmin
    ): void {
        if (! isset(self::BOOKING_MODELS[$serviceType])) {
            abort(404);
        }

        DB::transaction(function () use ($request, $serviceType, $bookingId, $actor, $isAdmin): void {
            $booking = $this->bookingQuery($serviceType, $bookingId)->lockForUpdate()->firstOrFail();
            $this->authorizeBooking($booking, $serviceType, $actor, $isAdmin);
            $this->loadBookingRelations($booking, $serviceType);

            if (! $this->isEligibleStatus($booking)) {
                $this->reject(__('booking_postponement.ineligible_status'));
            }

            $leg = $serviceType === 'transport'
                ? $request->validate(['leg' => ['required', 'in:pickup,return']])['leg']
                : 'primary';
            $currentLeg = collect($this->scheduleLegs($booking, $serviceType))
                ->firstWhere('leg', $leg);
            if (! $currentLeg) {
                $this->reject(__('booking_postponement.schedule_missing'));
            }

            $validated = $request->validate([
                'expected_schedule' => ['required', 'string', 'size:64'],
                'reason' => ['required', 'string', 'max:1000'],
                'new_date' => ['required', 'date_format:Y-m-d'],
                'new_time' => [$serviceType === 'transport' ? 'required' : 'nullable', 'date_format:H:i'],
                'new_time_slot_id' => [$serviceType === 'activity' ? 'required' : 'nullable', 'integer'],
            ]);

            if (! hash_equals(
                $this->scheduleToken($booking, $serviceType, $leg),
                $validated['expected_schedule']
            )) {
                $this->reject(__('booking_postponement.schedule_changed'));
            }
            if (! $currentLeg['date']) {
                $this->reject(__('booking_postponement.schedule_missing'));
            }

            $previousSchedule = $this->fullSchedule($booking, $serviceType);
            $originalDateTime = $this->scheduleDateTime($currentLeg['date'], $currentLeg['time']);
            $now = now(config('app.timezone'));

            if (! $isAdmin && (! $originalDateTime || ! $originalDateTime->gt($now))) {
                $this->reject($originalDateTime
                    ? __('booking_postponement.booking_time_passed')
                    : __('booking_postponement.original_time_missing'));
            }

            $newDate = Carbon::createFromFormat('!Y-m-d', $validated['new_date'], config('app.timezone'));
            $newTime = match ($serviceType) {
                'accommodation' => $currentLeg['time'],
                'activity' => $this->activitySlotTime($booking, (int) $validated['new_time_slot_id']),
                'transport' => $validated['new_time'],
            };
            $newDateTime = $this->scheduleDateTime($newDate->toDateString(), $newTime);

            if ($newDateTime ? ! $newDateTime->gt($now) : ! $newDate->startOfDay()->gt($now->startOfDay())) {
                $this->reject(__('booking_postponement.new_schedule_must_be_future'));
            }

            if ($originalDateTime) {
                if (! $newDateTime || ! $newDateTime->gt($originalDateTime)) {
                    $this->reject(__('booking_postponement.new_schedule_must_be_later'));
                }
            } elseif ($newDate->toDateString() <= Carbon::parse($currentLeg['date'], config('app.timezone'))->toDateString()) {
                $this->reject(__('booking_postponement.new_schedule_must_be_later'));
            }

            $newSchedule = match ($serviceType) {
                'accommodation' => $this->postponeAccommodation($booking, $newDate, $newDateTime),
                'activity' => $this->postponeActivity($booking, $newDate, (int) $validated['new_time_slot_id']),
                'transport' => $this->postponeTransport($booking, $leg, $newDate, (string) $newTime),
            };

            $booking->save();

            BookingScheduleChange::create([
                'service_type' => $serviceType,
                'service_booking_id' => $booking->getKey(),
                'previous_schedule' => $previousSchedule,
                'new_schedule' => $newSchedule,
                'reason' => trim($validated['reason']),
                'actor_type' => $actor->getMorphClass(),
                'actor_id' => $actor->getKey(),
                'actor_role' => $isAdmin ? 'admin' : 'operator',
            ]);
        });
    }

    private function bookingQuery(string $serviceType, int $bookingId)
    {
        $model = self::BOOKING_MODELS[$serviceType] ?? abort(404);

        return $model::query()->whereKey($bookingId);
    }

    private function loadBookingRelations(Model $booking, string $serviceType): void
    {
        $relations = [
            'accommodation' => ['accommodation', 'room', 'scheduleChanges.actor'],
            'activity' => ['activity.schedulingTimeSlots', 'scheduleChanges.actor'],
            'transport' => [
                'transport.operator',
                'vehicle',
                'pickupDriver',
                'returnDriver',
                'driver',
                'scheduleChanges.actor',
            ],
        ];
        $booking->loadMissing($relations[$serviceType]);
    }

    private function authorizeBooking(Model $booking, string $serviceType, Model $actor, bool $isAdmin): void
    {
        if ($isAdmin) {
            if (! $actor instanceof AdminUser) {
                abort(403);
            }

            return;
        }

        if ($actor instanceof Operator) {
            $ownerIds = [(int) $actor->getKey()];
            $businessId = $actor->business_id;
        } elseif ($actor instanceof OperatorUser) {
            $businessId = $actor->business_id;
            $owners = Operator::query();
            if (filled($actor->operator_id)) {
                $owners->where('operator_id', $actor->operator_id);
            } else {
                $owners->whereRaw('1 = 0');
            }
            if ($businessId) {
                $owners->orWhere('business_id', $businessId);
            }
            $ownerIds = $owners->pluck('id')->map(fn ($id) => (int) $id)->all();
        } else {
            abort(403);
        }

        $service = match ($serviceType) {
            'accommodation' => $booking->accommodation,
            'activity' => $booking->activity,
            'transport' => $booking->transport,
        };
        if (! $service) {
            abort(404);
        }

        $ownsService = in_array((int) ($service->operator_id ?? 0), $ownerIds, true);
        if (
            $serviceType === 'accommodation'
            && $businessId
            && (int) ($service->business_id ?? 0) === (int) $businessId
        ) {
            $ownsService = true;
        }

        if (! $ownsService) {
            abort(404);
        }
    }

    private function isEligibleStatus(Model $booking): bool
    {
        return ! in_array(
            strtolower(trim((string) $booking->booking_status)),
            ['cancelled', 'canceled', 'completed'],
            true
        );
    }

    private function scheduleLegs(Model $booking, string $serviceType): array
    {
        if ($serviceType === 'accommodation') {
            $accommodation = $booking->accommodation;
            $stayNights = $booking->check_in_date && $booking->check_out_date
                ? $booking->check_in_date->diffInDays($booking->check_out_date)
                : null;

            return [[
                'leg' => 'primary',
                'label' => __('booking_postponement.stay'),
                'date' => $this->dateString($booking->check_in_date),
                'time' => $this->timeString($accommodation?->checkin_time),
                'checkout_date' => $this->dateString($booking->check_out_date),
                'checkout_time' => $this->timeString($accommodation?->checkout_time),
                'stay_nights' => $stayNights,
            ]];
        }

        if ($serviceType === 'activity') {
            $participantSlotValues = $this->participantTimeSlotIds($booking);
            $participantSlots = array_values(array_unique($participantSlotValues));
            $hasIncompleteParticipantSlots = is_array($booking->participant_time_slots)
                && count($participantSlotValues) !== count($booking->participant_time_slots);
            if ($hasIncompleteParticipantSlots) {
                $participantSlots = [];
            }
            $effectiveSlotId = $hasIncompleteParticipantSlots
                ? null
                : ($participantSlots
                    ? (count($participantSlots) === 1 ? $participantSlots[0] : null)
                    : $booking->activity_time_slot_id);
            $slot = $effectiveSlotId
                ? $booking->activity?->schedulingTimeSlots?->firstWhere('timeslot_id', $effectiveSlotId)
                : null;
            $slotLabel = $hasIncompleteParticipantSlots
                ? __('booking_postponement.participant_slot_missing')
                : (count($participantSlots) > 1
                    ? __('booking_postponement.multiple_participant_slots')
                    : ($slot
                        ? trim($this->timeString($slot->start_time) . ' - ' . $this->timeString($slot->end_time))
                        : null));

            return [[
                'leg' => 'primary',
                'label' => __('booking_postponement.activity'),
                'date' => $this->dateString($booking->activity_date),
                'time' => $this->timeString($slot?->start_time),
                'time_slot_id' => $effectiveSlotId,
                'time_slot_label' => $slotLabel,
            ]];
        }

        $legs = [[
            'leg' => 'pickup',
            'label' => __('booking_postponement.pickup'),
            'date' => $this->dateString($booking->pickup_date),
            'time' => $this->timeString($booking->pickup_time),
        ]];
        if ($booking->return_date) {
            $legs[] = [
                'leg' => 'return',
                'label' => __('booking_postponement.return'),
                'date' => $this->dateString($booking->return_date),
                'time' => $this->timeString($booking->return_time),
            ];
        }

        return $legs;
    }

    private function fullSchedule(Model $booking, string $serviceType): array
    {
        return match ($serviceType) {
            'accommodation' => [
                'check_in_date' => $this->dateString($booking->check_in_date),
                'check_out_date' => $this->dateString($booking->check_out_date),
                'check_in_time' => $this->timeString($booking->accommodation?->checkin_time),
                'check_out_time' => $this->timeString($booking->accommodation?->checkout_time),
            ],
            'activity' => [
                'activity_date' => $this->dateString($booking->activity_date),
                'activity_time_slot_id' => $booking->activity_time_slot_id,
                'activity_time_slot' => $this->scheduleLegs($booking, 'activity')[0]['time_slot_label'],
                'participant_time_slots' => $booking->participant_time_slots ?? [],
            ],
            'transport' => [
                'pickup_date' => $this->dateString($booking->pickup_date),
                'pickup_time' => $this->timeString($booking->pickup_time),
                'return_date' => $this->dateString($booking->return_date),
                'return_time' => $this->timeString($booking->return_time),
            ],
        };
    }

    private function scheduleToken(Model $booking, string $serviceType, string $leg): string
    {
        $schedule = $serviceType === 'transport'
            ? array_intersect_key(
                $this->fullSchedule($booking, $serviceType),
                $leg === 'return'
                    ? ['return_date' => true, 'return_time' => true]
                    : ['pickup_date' => true, 'pickup_time' => true]
            )
            : $this->fullSchedule($booking, $serviceType);

        return hash_hmac('sha256', json_encode($schedule, JSON_THROW_ON_ERROR), (string) config('app.key'));
    }

    private function postponeAccommodation(AccommodationBooking $booking, Carbon $newDate, ?Carbon $newDateTime): array
    {
        if (! $booking->check_in_date || ! $booking->check_out_date) {
            $this->reject(__('booking_postponement.schedule_missing'));
        }
        if (! $booking->check_out_date->gt($booking->check_in_date)) {
            $this->reject(__('booking_postponement.schedule_missing'));
        }

        $stayNights = $booking->check_in_date->diffInDays($booking->check_out_date);
        if ($stayNights < 1) {
            $this->reject(__('booking_postponement.schedule_missing'));
        }

        $this->assertRoomAvailability($booking, $newDate, $newDate->copy()->addDays($stayNights));
        $booking->check_in_date = $newDate->toDateString();
        $booking->check_out_date = $newDate->copy()->addDays($stayNights)->toDateString();

        return [
            'check_in_date' => $booking->check_in_date->toDateString(),
            'check_out_date' => $booking->check_out_date->toDateString(),
            'check_in_time' => $this->timeString($booking->accommodation?->checkin_time),
            'check_out_time' => $this->timeString($booking->accommodation?->checkout_time),
        ];
    }

    private function postponeActivity(ActivityBooking $booking, Carbon $newDate, int $newTimeSlotId): array
    {
        $activity = $booking->activity;
        if (! $this->activityVariantAllowsParticipants($booking)) {
            $this->reject(__('booking_postponement.activity_variant_capacity'));
        }

        $availableSlot = $this->eligibleActivitySlots($booking, $newDate)->get($newTimeSlotId);
        $slot = $availableSlot['slot'] ?? null;
        if (! $activity || ! $slot) {
            $this->reject(__('booking_postponement.activity_slot_unavailable'));
        }

        $newDateTime = $this->scheduleDateTime($newDate->toDateString(), $this->timeString($slot->start_time));
        if (! $newDateTime) {
            $this->reject(__('booking_postponement.original_time_missing'));
        }

        $booking->activity_date = $newDate->toDateString();
        $booking->activity_time_slot_id = $slot->getKey();
        $participantTimeSlots = $booking->participant_time_slots;
        if (is_array($participantTimeSlots) && $participantTimeSlots !== []) {
            $booking->participant_time_slots = array_fill_keys(
                array_keys($participantTimeSlots),
                $slot->getKey()
            );
        }

        return [
            'activity_date' => $newDate->toDateString(),
            'activity_time_slot_id' => $slot->getKey(),
            'activity_time_slot' => trim($this->timeString($slot->start_time) . ' - ' . $this->timeString($slot->end_time)),
            'participant_time_slots' => $booking->participant_time_slots ?? [],
        ];
    }

    private function activityVariantAllowsParticipants(ActivityBooking $booking): bool
    {
        if (! $booking->variant_id || ! Schema::hasTable('activity_variants')) {
            return true;
        }

        $maximumParticipants = DB::table('activity_variants')
            ->where('variant_id', $booking->variant_id)
            ->where('activity_id', $booking->activity_id)
            ->value('max_participants');
        $participantCount = (int) ($booking->adults ?? 0)
            + (int) ($booking->children ?? 0)
            + (int) ($booking->infants ?? 0);

        return $maximumParticipants === null
            || (int) $maximumParticipants <= 0
            || $participantCount <= (int) $maximumParticipants;
    }

    private function activitySlotTime(ActivityBooking $booking, int $timeSlotId): ?string
    {
        $slot = ActivitySchedulingTimeSlot::query()
            ->where('activity_id', $booking->activity_id)
            ->whereKey($timeSlotId)
            ->first();

        if (
            ! $slot
            || ($slot->variant_id && (int) $slot->variant_id !== (int) $booking->variant_id)
        ) {
            $this->reject(__('booking_postponement.invalid_activity_slot'));
        }

        return $this->timeString($slot->start_time);
    }

    private function participantTimeSlotIds(ActivityBooking $booking): array
    {
        $slots = $booking->participant_time_slots;
        if (! is_array($slots)) {
            return [];
        }

        return collect(array_values($slots))
            ->filter(fn ($slotId) => is_numeric($slotId) && (int) $slotId > 0)
            ->map(fn ($slotId) => (int) $slotId)
            ->all();
    }

    private function postponeTransport(TransportBooking $booking, string $leg, Carbon $newDate, string $newTime): array
    {
        $transport = $booking->transport;
        if (! $transport) {
            $this->reject(__('booking_postponement.schedule_missing'));
        }

        $start = $this->scheduleDateTime($newDate->toDateString(), $newTime);
        if (! $start) {
            $this->reject(__('booking_postponement.invalid_schedule'));
        }
        if (
            $transport->earliest_pickup_time
            && $newTime < $this->timeString($transport->earliest_pickup_time)
        ) {
            $this->reject(__('booking_postponement.transport_time_unavailable'));
        }
        if (
            $transport->latest_pickup_time
            && $newTime > $this->timeString($transport->latest_pickup_time)
        ) {
            $this->reject(__('booking_postponement.transport_time_unavailable'));
        }

        if ($leg === 'pickup') {
            $booking->pickup_date = $newDate->toDateString();
            $booking->pickup_time = $newTime;
        } else {
            if (! $booking->return_date) {
                $this->reject(__('booking_postponement.schedule_missing'));
            }
            $booking->return_date = $newDate->toDateString();
            $booking->return_time = $newTime;
        }

        if ($booking->return_date && $booking->return_time && $booking->pickup_date && $booking->pickup_time) {
            $pickup = $this->scheduleDateTime($this->dateString($booking->pickup_date), $this->timeString($booking->pickup_time));
            $return = $this->scheduleDateTime($this->dateString($booking->return_date), $this->timeString($booking->return_time));
            if ($pickup && $return && ! $return->gt($pickup)) {
                $this->reject(__('booking_postponement.return_must_follow_pickup'));
            }
        }

        return $this->fullSchedule($booking, 'transport');
    }

    private function assertRoomAvailability(AccommodationBooking $booking, Carbon $newCheckIn, Carbon $newCheckOut): void
    {
        $room = $booking->room;
        if (! $room) {
            $this->reject(__('booking_postponement.room_unavailable'));
        }

        $roomsRequired = max(1, (int) ($booking->rooms_booked ?? 1));
        $baseUnits = (int) ($room->allotment ?? $room->quantity ?? 0);
        $baseUnits = $baseUnits > 0 ? $baseUnits : 1;

        if (! \App\Http\Controllers\Frontend\HomeController::roomMatchesSelectedGuestRequirements(
            $room->toArray(),
            (int) ($booking->adults ?? 0),
            (int) ($booking->children ?? 0),
            (int) ($booking->infants ?? 0),
            $roomsRequired
        )) {
            $this->reject(__('booking_postponement.room_unavailable'));
        }

        $activeRooms = $booking->accommodation?->rooms;
        $activeRoomCount = $activeRooms
            ? $activeRooms->filter(function ($accommodationRoom): bool {
                $status = trim((string) ($accommodationRoom->status ?? ''));

                return $status === '' || strcasecmp($status, 'Active') === 0;
            })->count()
            : 0;
        $allowGlobalFallback = $activeRoomCount <= 1;
        $usesInventory = Schema::hasTable('accommodation_inventory');

        for ($date = $newCheckIn->copy(); $date->lt($newCheckOut); $date->addDay()) {
            $dateKey = $date->toDateString();
            $bookedUnits = AccommodationBooking::query()
                ->where('accommodation_id', $booking->accommodation_id)
                ->where('room_id', $booking->room_id)
                ->whereIn('booking_status', ['Pending', 'Confirmed'])
                ->whereKeyNot($booking->getKey())
                ->whereDate('check_in_date', '<=', $dateKey)
                ->whereDate('check_out_date', '>', $dateKey)
                ->sum('rooms_booked');
            if ($allowGlobalFallback) {
                $bookedUnits += AccommodationBooking::query()
                    ->where('accommodation_id', $booking->accommodation_id)
                    ->where(function ($query) {
                        $query->whereNull('room_id')->orWhere('room_id', 0);
                    })
                    ->whereIn('booking_status', ['Pending', 'Confirmed'])
                    ->whereKeyNot($booking->getKey())
                    ->whereDate('check_in_date', '<=', $dateKey)
                    ->whereDate('check_out_date', '>', $dateKey)
                    ->sum('rooms_booked');
            }

            if ($usesInventory) {
                $inventoryQuery = DB::table('accommodation_inventory')
                    ->where('accommodation_id', $booking->accommodation_id)
                    ->whereDate('date', $dateKey);
                $inventory = $inventoryQuery->where('room_id', $booking->room_id)->first();

                if (! $inventory && $allowGlobalFallback) {
                    $inventory = DB::table('accommodation_inventory')
                        ->where('accommodation_id', $booking->accommodation_id)
                        ->whereDate('date', $dateKey)
                        ->where(function ($query) {
                            $query->whereNull('room_id')->orWhere('room_id', 0);
                        })
                        ->first();
                }

                if ($inventory) {
                    $isBlocked = (bool) ($inventory->is_blocked ?? false)
                        || (bool) ($inventory->stop_sell ?? false);
                    $sellableUnits = (int) ($inventory->sellable_units ?? 0);
                    $soldUnits = (int) ($inventory->sold_units ?? 0);
                    $availableUnitsRaw = $inventory->available_units ?? null;
                    $availableUnits = $availableUnitsRaw !== null
                        ? (int) $availableUnitsRaw
                        : $sellableUnits - $soldUnits;
                    $hasExplicitInventory = $isBlocked
                        || $sellableUnits > 0
                        || $soldUnits > 0
                        || $availableUnits > 0;

                    if ($hasExplicitInventory) {
                        $sellableUnits = $sellableUnits > 0 ? $sellableUnits : $baseUnits;
                        $usedUnits = max($soldUnits, (int) $bookedUnits);
                        $availableUnits = $isBlocked
                            ? 0
                            : max(min($availableUnits, $sellableUnits - $usedUnits), 0);
                    } else {
                        $availableUnits = max($baseUnits - (int) $bookedUnits, 0);
                    }

                    if ($availableUnits < $roomsRequired) {
                        $this->reject(__('booking_postponement.room_unavailable'));
                    }
                    continue;
                }
            }

            if ($bookedUnits + $roomsRequired > $baseUnits) {
                $this->reject(__('booking_postponement.room_unavailable'));
            }
        }
    }

    private function dateString($date): ?string
    {
        return $date ? Carbon::parse($date, config('app.timezone'))->toDateString() : null;
    }

    private function timeString($time): ?string
    {
        return filled($time) ? substr((string) $time, 0, 5) : null;
    }

    private function scheduleDateTime(?string $date, ?string $time): ?Carbon
    {
        if (! $date || ! $time) {
            return null;
        }

        return Carbon::createFromFormat(
            '!Y-m-d H:i',
            $date . ' ' . $time,
            config('app.timezone')
        );
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['schedule' => $message]);
    }
}
