<?php

namespace Tests\Feature;

use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\ActivitySchedulingTimeSlot;
use App\Models\AdminUser;
use App\Models\Operator;
use App\Models\TransportBooking;
use App\Services\BookingPostponementService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingPostponementTest extends TestCase
{
    private BookingPostponementService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'postponement_test',
            'database.connections.postponement_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('postponement_test');
        DB::setDefaultConnection('postponement_test');

        Schema::create('accommodations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('checkin_time')->nullable();
            $table->string('checkout_time')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('accommodation_rooms', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('accommodation_id')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->unsignedInteger('allotment')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('children_capacity')->nullable();
            $table->unsignedInteger('infant_capacity')->nullable();
            $table->unsignedInteger('max_person_capacity')->nullable();
            $table->timestamps();
        });
        Schema::create('accommodation_bookings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('accommodation_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->string('booking_reference')->nullable();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->unsignedInteger('rooms_booked')->default(1);
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            $table->unsignedInteger('infants')->default(0);
            $table->string('booking_status')->nullable();
            $table->timestamps();
        });
        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('activity_scheduling_timeslots', function (Blueprint $table): void {
            $table->increments('timeslot_id');
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedInteger('capacity_per_slot')->nullable();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->unsignedInteger('lead_time_minutes')->nullable();
            $table->json('days_of_week')->nullable();
            $table->timestamps();
        });
        Schema::create('activity_bookings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('activity_time_slot_id')->nullable();
            $table->date('activity_date')->nullable();
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            $table->unsignedInteger('infants')->default(0);
            $table->json('participant_time_slots')->nullable();
            $table->string('booking_status')->nullable();
            $table->timestamps();
        });
        Schema::create('operators', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->timestamps();
        });
        Schema::create('transports', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->string('approval_status')->nullable();
            $table->time('earliest_pickup_time')->nullable();
            $table->time('latest_pickup_time')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('transport_bookings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('transport_id')->nullable();
            $table->unsignedBigInteger('transport_vehicle_id')->nullable();
            $table->unsignedBigInteger('pickup_driver_id')->nullable();
            $table->unsignedBigInteger('return_driver_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('route_from')->nullable();
            $table->string('route_to')->nullable();
            $table->date('pickup_date')->nullable();
            $table->time('pickup_time')->nullable();
            $table->date('return_date')->nullable();
            $table->time('return_time')->nullable();
            $table->string('booking_status')->nullable();
            $table->timestamps();
        });
        Schema::create('transport_service_route_pairs', function (Blueprint $table): void {
            $table->id();
            $table->string('service_type');
            $table->string('route_from');
            $table->string('route_to');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('transport_vehicles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('transport_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        Schema::create('operator_drivers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->string('driver_status')->nullable();
            $table->timestamps();
        });
        Schema::create('booking_schedule_changes', function (Blueprint $table): void {
            $table->id();
            $table->string('service_type');
            $table->unsignedBigInteger('service_booking_id');
            $table->json('previous_schedule');
            $table->json('new_schedule');
            $table->text('reason');
            $table->string('actor_type');
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_role');
            $table->timestamps();
        });

        $this->service = new BookingPostponementService();
    }

    public function test_admin_can_postpone_overdue_accommodation_and_audit_the_change(): void
    {
        $accommodation = \App\Models\Accommodation::create([
            'operator_id' => 71,
            'checkin_time' => '15:00',
            'checkout_time' => '11:00',
        ]);
        $room = $this->createRoom($accommodation->id);
        $booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'room_id' => $room->id,
            'booking_reference' => 'ACC-1',
            'check_in_date' => now()->subDays(2)->toDateString(),
            'check_out_date' => now()->subDay()->toDateString(),
            'rooms_booked' => 1,
            'booking_status' => 'Confirmed',
        ]);
        $admin = new AdminUser();
        $admin->id = 5;

        $action = $this->service->details(
            $this->service->loadForDetails('accommodation', $booking->id),
            'accommodation',
            true
        )[0];
        $newDate = now()->addDays(3)->toDateString();

        $this->service->postpone(
            Request::create('/', 'POST', [
                'expected_schedule' => $action['expected_schedule'],
                'new_date' => $newDate,
                'reason' => 'Property maintenance',
            ]),
            'accommodation',
            $booking->id,
            $admin,
            true
        );

        $booking->refresh();
        $this->assertSame($newDate, $booking->check_in_date->toDateString());
        $this->assertSame(
            Carbon::parse($newDate)->addDay()->toDateString(),
            $booking->check_out_date->toDateString()
        );
        $this->assertSame('Confirmed', $booking->booking_status);
        $this->assertDatabaseHas('booking_schedule_changes', [
            'service_type' => 'accommodation',
            'service_booking_id' => $booking->id,
            'actor_role' => 'admin',
            'reason' => 'Property maintenance',
        ]);
    }

    public function test_operator_cannot_postpone_a_booking_without_a_configured_original_time(): void
    {
        $accommodation = \App\Models\Accommodation::create([
            'operator_id' => 71,
            'checkin_time' => null,
        ]);
        $room = $this->createRoom($accommodation->id);
        $booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'room_id' => $room->id,
            'booking_reference' => 'ACC-2',
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
            'booking_status' => 'Confirmed',
        ]);
        $operator = new Operator();
        $operator->id = 71;

        $action = $this->service->details(
            $this->service->loadForDetails('accommodation', $booking->id),
            'accommodation',
            false
        )[0];
        $this->assertFalse($action['can_submit']);

        try {
            $this->service->postpone(
                Request::create('/', 'POST', [
                    'expected_schedule' => $action['expected_schedule'],
                    'new_date' => now()->addDays(5)->toDateString(),
                    'reason' => 'Updated plans',
                ]),
                'accommodation',
                $booking->id,
                $operator,
                false
            );
            $this->fail('A missing configured check-in time should prevent operator postponement.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('schedule', $exception->errors());
        }

        $this->assertSame(
            now()->addDays(2)->toDateString(),
            $booking->fresh()->check_in_date->toDateString()
        );
    }

    public function test_operator_can_postpone_their_own_future_accommodation_booking(): void
    {
        $accommodation = \App\Models\Accommodation::create([
            'operator_id' => 71,
            'checkin_time' => '15:00',
            'checkout_time' => '11:00',
        ]);
        $room = $this->createRoom($accommodation->id);
        $booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'room_id' => $room->id,
            'booking_reference' => 'ACC-FUTURE',
            'check_in_date' => now()->addDays(3)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(),
            'booking_status' => 'Confirmed',
        ]);
        $operator = new Operator();
        $operator->id = 71;
        $action = $this->service->details(
            $this->service->loadForDetails('accommodation', $booking->id),
            'accommodation',
            false
        )[0];
        $newDate = now()->addDays(4)->toDateString();

        $this->service->postpone(
            Request::create('/', 'POST', [
                'expected_schedule' => $action['expected_schedule'],
                'new_date' => $newDate,
                'reason' => 'Guest requested a later arrival',
            ]),
            'accommodation',
            $booking->id,
            $operator,
            false
        );

        $this->assertSame($newDate, $booking->fresh()->check_in_date->toDateString());
    }

    public function test_accommodation_postponement_rejects_a_sold_out_room_and_preserves_dates(): void
    {
        $accommodation = \App\Models\Accommodation::create([
            'operator_id' => 71,
            'checkin_time' => '15:00',
            'checkout_time' => '11:00',
        ]);
        $room = $this->createRoom($accommodation->id, 1);
        $booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'room_id' => $room->id,
            'booking_reference' => 'ACC-SOLD-OUT',
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
            'rooms_booked' => 1,
            'booking_status' => 'Confirmed',
        ]);
        $newDate = now()->addDays(7)->toDateString();
        AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'room_id' => $room->id,
            'booking_reference' => 'ACC-CONFLICT',
            'check_in_date' => $newDate,
            'check_out_date' => Carbon::parse($newDate)->addDays(2)->toDateString(),
            'rooms_booked' => 1,
            'booking_status' => 'Confirmed',
        ]);
        $admin = new AdminUser();
        $admin->id = 5;
        $action = $this->service->details(
            $this->service->loadForDetails('accommodation', $booking->id),
            'accommodation',
            true
        )[0];

        try {
            $this->service->postpone(
                Request::create('/', 'POST', [
                    'expected_schedule' => $action['expected_schedule'],
                    'new_date' => $newDate,
                    'reason' => 'Avoid sold out dates',
                ]),
                'accommodation',
                $booking->id,
                $admin,
                true
            );
            $this->fail('A postponed stay must not exceed remaining room availability.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('schedule', $exception->errors());
        }

        $this->assertSame(now()->addDays(2)->toDateString(), $booking->fresh()->check_in_date->toDateString());
        $this->assertDatabaseMissing('booking_schedule_changes', [
            'service_type' => 'accommodation',
            'service_booking_id' => $booking->id,
        ]);
    }

    public function test_operator_cannot_postpone_another_operators_booking(): void
    {
        $accommodation = \App\Models\Accommodation::create([
            'operator_id' => 71,
            'checkin_time' => '15:00',
        ]);
        $room = $this->createRoom($accommodation->id);
        $booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'room_id' => $room->id,
            'booking_reference' => 'ACC-3',
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
            'booking_status' => 'Confirmed',
        ]);
        $operator = new Operator();
        $operator->id = 72;
        $action = $this->service->details(
            $this->service->loadForDetails('accommodation', $booking->id),
            'accommodation',
            false
        )[0];

        try {
            $this->service->postpone(
                Request::create('/', 'POST', [
                    'expected_schedule' => $action['expected_schedule'],
                    'new_date' => now()->addDays(5)->toDateString(),
                    'reason' => 'Unauthorized update',
                ]),
                'accommodation',
                $booking->id,
                $operator,
                false
            );
            $this->fail("Operators must not postpone another operator's booking.");
        } catch (NotFoundHttpException) {
            $this->assertSame(
                now()->addDays(2)->toDateString(),
                $booking->fresh()->check_in_date->toDateString()
            );
        }
    }

    public function test_operator_can_change_activity_date_and_time_slot(): void
    {
        $activity = \App\Models\Activity::create(['operator_id' => 71]);
        $oldSlot = \App\Models\ActivitySchedulingTimeSlot::create([
            'activity_id' => $activity->id,
            'capacity_per_slot' => 5,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'days_of_week' => [],
        ]);
        $newSlot = \App\Models\ActivitySchedulingTimeSlot::create([
            'activity_id' => $activity->id,
            'capacity_per_slot' => 5,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'days_of_week' => [],
        ]);
        $booking = ActivityBooking::create([
            'activity_id' => $activity->id,
            'activity_time_slot_id' => $oldSlot->getKey(),
            'activity_date' => now()->addDays(2)->toDateString(),
            'adults' => 2,
            'participant_time_slots' => [1 => $oldSlot->getKey(), 2 => $oldSlot->getKey()],
            'booking_status' => 'Confirmed',
        ]);
        $operator = new Operator();
        $operator->id = 71;

        $action = $this->service->details(
            $this->service->loadForDetails('activity', $booking->id),
            'activity',
            false
        )[0];
        $newDate = now()->addDays(3)->toDateString();

        $this->service->postpone(
            Request::create('/', 'POST', [
                'expected_schedule' => $action['expected_schedule'],
                'new_date' => $newDate,
                'new_time_slot_id' => $newSlot->getKey(),
                'reason' => 'Activity rescheduled',
            ]),
            'activity',
            $booking->id,
            $operator,
            false
        );

        $this->assertSame($newDate, $booking->fresh()->activity_date->toDateString());
        $this->assertSame($newSlot->getKey(), $booking->fresh()->activity_time_slot_id);
        $this->assertSame(
            [$newSlot->getKey(), $newSlot->getKey()],
            array_values($booking->fresh()->participant_time_slots)
        );
    }

    public function test_activity_postponement_rejects_a_slot_without_enough_capacity(): void
    {
        $activity = \App\Models\Activity::create(['operator_id' => 71]);
        $currentSlot = ActivitySchedulingTimeSlot::create([
            'activity_id' => $activity->id,
            'capacity_per_slot' => 5,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'days_of_week' => [],
        ]);
        $newSlot = ActivitySchedulingTimeSlot::create([
            'activity_id' => $activity->id,
            'capacity_per_slot' => 2,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'days_of_week' => [],
        ]);
        $newDate = now()->addDays(3)->toDateString();
        $booking = ActivityBooking::create([
            'activity_id' => $activity->id,
            'activity_time_slot_id' => $currentSlot->getKey(),
            'activity_date' => now()->addDays(2)->toDateString(),
            'adults' => 2,
            'participant_time_slots' => [1 => $currentSlot->getKey(), 2 => $currentSlot->getKey()],
            'booking_status' => 'Confirmed',
        ]);
        ActivityBooking::create([
            'activity_id' => $activity->id,
            'activity_time_slot_id' => $newSlot->getKey(),
            'activity_date' => $newDate,
            'adults' => 1,
            'participant_time_slots' => [1 => $newSlot->getKey()],
            'booking_status' => 'Pending',
        ]);
        $operator = new Operator();
        $operator->id = 71;
        $action = $this->service->details(
            $this->service->loadForDetails('activity', $booking->id),
            'activity',
            false
        )[0];

        try {
            $this->service->postpone(
                Request::create('/', 'POST', [
                    'expected_schedule' => $action['expected_schedule'],
                    'new_date' => $newDate,
                    'new_time_slot_id' => $newSlot->getKey(),
                    'reason' => 'Avoid an overbooked slot',
                ]),
                'activity',
                $booking->id,
                $operator,
                false
            );
            $this->fail('The selected activity slot does not have capacity for all booked participants.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('schedule', $exception->errors());
        }

        $this->assertSame(
            now()->addDays(2)->toDateString(),
            $booking->fresh()->activity_date->toDateString()
        );
        $this->assertSame($currentSlot->getKey(), $booking->fresh()->activity_time_slot_id);
    }

    public function test_activity_slots_are_loaded_for_the_booking_variant_and_date(): void
    {
        $activity = \App\Models\Activity::create(['operator_id' => 71]);
        $eligibleSlot = ActivitySchedulingTimeSlot::create([
            'activity_id' => $activity->id,
            'variant_id' => 18,
            'capacity_per_slot' => 5,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'days_of_week' => [],
        ]);
        ActivitySchedulingTimeSlot::create([
            'activity_id' => $activity->id,
            'variant_id' => 19,
            'capacity_per_slot' => 5,
            'start_time' => '16:00',
            'end_time' => '17:00',
            'days_of_week' => [],
        ]);
        $booking = ActivityBooking::create([
            'activity_id' => $activity->id,
            'variant_id' => 18,
            'activity_time_slot_id' => $eligibleSlot->getKey(),
            'activity_date' => now()->addDays(2)->toDateString(),
            'adults' => 2,
            'booking_status' => 'Confirmed',
        ]);
        Schema::table('activity_bookings', function (Blueprint $table): void {
            $table->dropColumn('infants');
        });
        $operator = new Operator();
        $operator->id = 71;

        $slots = $this->service->activitySlotsForDate(
            $booking->id,
            now()->addDays(3)->toDateString(),
            $operator,
            false
        );

        $this->assertCount(1, $slots);
        $this->assertSame($eligibleSlot->getKey(), $slots[0]['id']);
        $this->assertSame(18, (int) $slots[0]['variant_id']);
        $this->assertSame('14:00 - 15:00', $slots[0]['display']);
        $this->assertSame(5, $slots[0]['available']);
    }

    public function test_operator_can_postpone_return_leg_without_changing_pickup_schedule(): void
    {
        $transport = \App\Models\Transport::create(['operator_id' => 71]);
        $booking = TransportBooking::create([
            'transport_id' => $transport->id,
            'transport_vehicle_id' => 777,
            'pickup_driver_id' => 888,
            'return_driver_id' => 999,
            'route_from' => 'Airport',
            'route_to' => 'Hotel',
            'pickup_date' => now()->addDays(2)->toDateString(),
            'pickup_time' => '09:00',
            'return_date' => now()->addDays(4)->toDateString(),
            'return_time' => '16:00',
            'booking_status' => 'Confirmed',
        ]);
        $operator = new Operator();
        $operator->id = 71;

        $actions = $this->service->details(
            $this->service->loadForDetails('transport', $booking->id),
            'transport',
            false
        );
        $pickupBefore = $booking->pickup_date->toDateString() . ' ' . $booking->pickup_time;
        $newReturnDate = now()->addDays(5)->toDateString();

        $this->service->postpone(
            Request::create('/', 'POST', [
                'leg' => 'return',
                'expected_schedule' => collect($actions)->firstWhere('leg', 'return')['expected_schedule'],
                'new_date' => $newReturnDate,
                'new_time' => '17:00',
                'reason' => 'Return transfer delayed',
            ]),
            'transport',
            $booking->id,
            $operator,
            false
        );

        $booking->refresh();
        $this->assertSame($pickupBefore, $booking->pickup_date->toDateString() . ' ' . $booking->pickup_time);
        $this->assertSame($newReturnDate, $booking->return_date->toDateString());
        $this->assertSame('17:00', substr((string) $booking->return_time, 0, 5));
        $this->assertSame(777, (int) $booking->transport_vehicle_id);
        $this->assertSame(888, (int) $booking->pickup_driver_id);
        $this->assertSame(999, (int) $booking->return_driver_id);
    }

    public function test_operator_cannot_postpone_an_overdue_booking_even_if_form_was_opened_earlier(): void
    {
        $transport = \App\Models\Transport::create(['operator_id' => 71]);
        $booking = TransportBooking::create([
            'transport_id' => $transport->id,
            'route_from' => 'Airport',
            'route_to' => 'Hotel',
            'pickup_date' => now()->addMinutes(5)->toDateString(),
            'pickup_time' => now()->addMinutes(5)->format('H:i'),
            'booking_status' => 'Confirmed',
        ]);
        $operator = new Operator();
        $operator->id = 71;
        $action = $this->service->details(
            $this->service->loadForDetails('transport', $booking->id),
            'transport',
            false
        )[0];

        Carbon::setTestNow(now()->addMinutes(10));
        try {
            $this->service->postpone(
                Request::create('/', 'POST', [
                    'leg' => 'pickup',
                    'expected_schedule' => $action['expected_schedule'],
                    'new_date' => now()->addDay()->toDateString(),
                    'new_time' => '12:00',
                    'reason' => 'Delayed',
                ]),
                'transport',
                $booking->id,
                $operator,
                false
            );
            $this->fail('The server must recheck the original schedule at submission time.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('schedule', $exception->errors());
        } finally {
            Carbon::setTestNow();
        }

        $this->assertSame(
            now()->addMinutes(5)->toDateString(),
            $booking->fresh()->pickup_date->toDateString()
        );
    }

    public function test_transport_postponement_does_not_require_resource_reassignment(): void
    {
        $transport = \App\Models\Transport::create(['operator_id' => 71]);
        $booking = TransportBooking::create([
            'transport_id' => $transport->id,
            'transport_vehicle_id' => 777,
            'pickup_driver_id' => 888,
            'route_from' => 'Airport',
            'route_to' => 'Hotel',
            'pickup_date' => now()->addDays(2)->toDateString(),
            'pickup_time' => '09:00',
            'booking_status' => 'Confirmed',
        ]);
        $newDate = now()->addDays(3)->toDateString();
        TransportBooking::create([
            'transport_id' => $transport->id,
            'route_from' => 'Airport',
            'route_to' => 'Hotel',
            'pickup_date' => $newDate,
            'pickup_time' => '09:30',
            'booking_status' => 'Confirmed',
        ]);
        $operator = new Operator();
        $operator->id = 71;
        $action = $this->service->details(
            $this->service->loadForDetails('transport', $booking->id),
            'transport',
            false
        )[0];

        $this->service->postpone(
            Request::create('/', 'POST', [
                'leg' => 'pickup',
                'expected_schedule' => $action['expected_schedule'],
                'new_date' => $newDate,
                'new_time' => '09:00',
                'reason' => 'Keep the existing driver and vehicle assignment',
            ]),
            'transport',
            $booking->id,
            $operator,
            false
        );

        $booking->refresh();
        $this->assertSame($newDate, $booking->pickup_date->toDateString());
        $this->assertSame('09:00', substr((string) $booking->pickup_time, 0, 5));
        $this->assertSame(777, (int) $booking->transport_vehicle_id);
        $this->assertSame(888, (int) $booking->pickup_driver_id);
    }

    private function createRoom(int $accommodationId, int $quantity = 3): \App\Models\AccommodationRoom
    {
        return \App\Models\AccommodationRoom::create([
            'accommodation_id' => $accommodationId,
            'quantity' => $quantity,
            'allotment' => $quantity,
            'capacity' => 2,
            'children_capacity' => 2,
            'infant_capacity' => 1,
            'max_person_capacity' => 5,
        ]);
    }
}
