<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\ActivitySchedulingTimeSlot;
use App\Models\BookingGuest;
use App\Models\OperatorDriver;
use App\Models\TransportBooking;
use App\Models\Trip;
use App\Models\TripActionDeadline;
use App\Services\TripOperationsOverview;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

class TripOperationsOverviewTest extends TestCase
{
    private TripOperationsOverview $overview;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.timezone' => 'UTC',
            'database.default' => 'trip_operations_test',
            'database.connections.trip_operations_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('trip_operations_test');
        DB::setDefaultConnection('trip_operations_test');

        Schema::create('trip_action_deadlines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->string('service_type', 30);
            $table->unsignedBigInteger('service_booking_id');
            $table->string('action_type', 50);
            $table->string('action_key', 191)->unique();
            $table->string('label', 255);
            $table->dateTime('target_at')->nullable();
            $table->dateTime('deadline_at')->nullable();
            $table->string('time_zone', 64)->nullable();
            $table->unsignedInteger('override_hours')->nullable();
            $table->string('status', 20)->default('open');
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('changed_by_admin_id')->nullable();
            $table->dateTime('changed_at')->nullable();
            $table->timestamps();
        });

        $this->overview = new TripOperationsOverview();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_next_service_is_selected_by_schedule_and_skips_cancelled_or_completed_services(): void
    {
        Carbon::setTestNow('2026-10-13 08:00:00');
        $trip = $this->trip();
        $accommodation = $this->accommodationBooking(1, '2026-10-16', 'Confirmed');
        $activity = $this->activityBooking(2, '2026-10-14', '10:30:00', 'Confirmed');
        $cancelledTransport = $this->transportBooking(3, '2026-10-13', '09:00:00', 'Cancelled');
        $completedTransport = $this->transportBooking(4, '2026-10-13', '09:30:00', 'Completed');
        $laterTransport = $this->transportBooking(5, '2026-10-15', '09:00:00', 'Confirmed');
        $trip->setRelation('accommodationBookings', new EloquentCollection([$accommodation]));
        $trip->setRelation('activityBookings', new EloquentCollection([$activity]));
        $trip->setRelation('transportBookings', new EloquentCollection([$cancelledTransport, $completedTransport, $laterTransport]));

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);

        $this->assertSame('Activity Walk', $result['next_service']['name']);
        $this->assertSame('Tomorrow, 10:30 · UTC+00:00', $result['next_service']['when']);
        $this->assertSame('14 Oct 2026', $result['travel_dates']['start']);
        $this->assertSame('16 Oct 2026', $result['travel_dates']['end']);
    }

    public function test_pending_actions_are_not_limited_to_due_window_and_exclude_ended_service_orders(): void
    {
        Carbon::setTestNow('2026-10-15 12:00:00');
        $trip = $this->trip();
        $trip->setAttribute('payment_status', 'pending');

        $currentStay = $this->accommodationBooking(60, '2026-10-10', 'Confirmed');
        $currentStay->setAttribute('check_out_date', '2026-10-17');
        $currentStay->setRelation('guests', new EloquentCollection());

        $pastStay = $this->accommodationBooking(61, '2026-10-10', 'Confirmed');
        $pastStay->setAttribute('check_out_date', '2026-10-14');
        $pastStay->setRelation('guests', new EloquentCollection());

        $futureStay = $this->accommodationBooking(67, '2026-10-25', 'Confirmed');
        $futureStay->setAttribute('check_out_date', '2026-10-27');
        $futureStay->setRelation('guests', new EloquentCollection());

        $pastActivity = $this->activityBooking(62, '2026-10-14', '10:00:00', 'Confirmed');
        $pastActivity->setRelation('guests', new EloquentCollection());
        $pastTransport = $this->transportBooking(63, '2026-10-14', '09:00:00', 'Confirmed');
        $futureTransport = $this->transportBooking(64, '2026-10-20', '09:00:00', 'Processing');
        $futureTransport->setAttribute('transport_id', 1);
        $futureTransport->setRelation('bookingRef', null);
        $returnTransport = $this->transportBooking(68, '2026-10-14', '09:00:00', 'Confirmed');
        $returnTransport->setAttribute('transport_id', 1);
        $returnTransport->setAttribute('return_date', '2026-10-22');
        $returnTransport->setAttribute('return_time', '09:00:00');
        $returnTransport->setAttribute('pickup_driver_id', 5);
        $returnTransport->setAttribute('transport_vehicle_id', 9);
        $cancelledTransport = $this->transportBooking(65, '2026-10-20', '10:00:00', 'Cancelled');
        $completedActivity = $this->activityBooking(66, '2026-10-20', '11:00:00', 'Completed');

        $trip->setRelation('accommodationBookings', new EloquentCollection([$currentStay, $pastStay, $futureStay]));
        $trip->setRelation('activityBookings', new EloquentCollection([$pastActivity, $completedActivity]));
        $trip->setRelation('transportBookings', new EloquentCollection([$pastTransport, $futureTransport, $returnTransport, $cancelledTransport]));

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);
        $pendingActions = collect($result['pending_actions']);

        $this->assertSame('payment', $result['actions'][0]['type']);
        $this->assertSame('na', collect($result['due_actions'])->firstWhere('booking_id', 60)['status']);
        $this->assertSame('scheduled', collect($result['due_actions'])->firstWhere('booking_id', 67)['status']);
        $this->assertEqualsCanonicalizing(['booking_confirm', 'driver_assign', 'vehicle_assign'], $pendingActions
            ->where('booking_id', 64)
            ->pluck('type')
            ->all());
        $this->assertSame(['driver_assign'], $pendingActions
            ->where('booking_id', 68)
            ->pluck('type')
            ->all());
        $this->assertTrue($pendingActions->contains(
            fn (array $action): bool => $action['booking_id'] === 60 && $action['type'] === 'guest_details'
        ));
        $this->assertTrue($pendingActions->contains(
            fn (array $action): bool => $action['booking_id'] === 67 && $action['type'] === 'guest_details'
        ));
        $this->assertFalse($pendingActions->contains(
            fn (array $action): bool => in_array($action['booking_id'], [61, 62, 63, 65, 66], true)
        ));
        $this->assertSame(6, $pendingActions->count());
        $this->assertSame('Accommodation #60 — Complete Guest Details', $pendingActions
            ->firstWhere('booking_id', 60)['label']);
        $this->assertSame('Transport #64 — Mark as Confirmed', $pendingActions
            ->firstWhere('booking_id', 64)['label']);
        $this->assertSame('22 Oct 2026, 09:00 · UTC+00:00', $pendingActions
            ->firstWhere('booking_id', 68)['service_date']);
    }

    public function test_pending_actions_include_due_candidates_and_unresolved_assignment_and_license_items_before_due_time(): void
    {
        Carbon::setTestNow('2026-10-13 08:00:00');
        $trip = $this->trip();

        $accommodation = $this->accommodationBooking(70, '2026-10-25', 'Confirmed');
        $accommodation->setRelation('guests', new EloquentCollection());

        $processingTransport = $this->transportBooking(71, '2026-10-25', '09:00:00', 'Processing');
        $processingTransport->setRelation('guests', new EloquentCollection());
        $processingTransport->setAttribute('traveler_first_name', null);
        $processingTransport->setAttribute('traveler_last_name', null);

        $expiredLicenseTransport = $this->transportBooking(72, '2026-10-25', '10:00:00', 'Confirmed');
        $expiredLicenseTransport->setAttribute('pickup_driver_id', 7);
        $expiredLicenseTransport->setAttribute('transport_vehicle_id', 3);
        $driver = new OperatorDriver();
        $driver->forceFill([
            'id' => 7,
            'driver_name' => 'Expired License Driver',
            'license_expiry_date' => '2026-10-12',
        ]);
        $expiredLicenseTransport->setRelation('pickupDriver', $driver);

        $completedTransport = $this->transportBooking(73, '2026-10-25', '11:00:00', 'Confirmed');
        $completedTransport->setAttribute('pickup_driver_id', 8);
        $completedTransport->setAttribute('transport_vehicle_id', 4);

        $trip->setRelation('accommodationBookings', new EloquentCollection([$accommodation]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection([
            $processingTransport,
            $expiredLicenseTransport,
            $completedTransport,
        ]));

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);
        $pendingActions = collect($result['pending_actions']);
        $processingActions = $pendingActions->where('booking_id', 71);

        $this->assertEqualsCanonicalizing(
            ['booking_confirm', 'guest_details', 'driver_assign', 'vehicle_assign'],
            $processingActions->pluck('type')->all()
        );
        $this->assertTrue($pendingActions->contains(
            fn (array $action): bool => $action['booking_id'] === 70 && $action['type'] === 'guest_details'
        ));
        $this->assertTrue($pendingActions->contains(
            fn (array $action): bool => $action['booking_id'] === 72 && $action['type'] === 'driver_license_expire'
        ));
        $this->assertSame($pendingActions->count(), $pendingActions->unique('key')->count());
        $this->assertFalse($pendingActions->contains(
            fn (array $action): bool => $action['booking_id'] === 73
        ));
        $this->assertNotSame('due', collect($result['due_actions'])
            ->first(fn (array $action): bool => $action['booking_id'] === 71 && $action['type'] === 'guest_details')['status']);
    }

    public function test_driver_and_vehicle_deadlines_are_independent_and_can_be_overridden_per_action(): void
    {
        Carbon::setTestNow('2026-10-13 00:00:00');
        $trip = $this->trip();
        $transport = $this->transportBooking(10, '2026-10-15', '12:00:00', 'Confirmed');
        $transport->setAttribute('transport_vehicle_id', null);
        $transport->setAttribute('pickup_driver_id', null);
        $trip->setRelation('transportBookings', new EloquentCollection([$transport]));
        $trip->setRelation('accommodationBookings', new EloquentCollection());
        $trip->setRelation('activityBookings', new EloquentCollection());

        $intervals = $this->defaultIntervals();
        $intervals['driver_assign'] = 12;
        $intervals['vehicle_assign'] = 36;
        $result = $this->overview->apply($trip, $intervals, true);
        $driverAction = collect($result['actions'])->firstWhere('type', 'driver_assign');
        $vehicleAction = collect($result['actions'])->firstWhere('type', 'vehicle_assign');
        $target = Carbon::parse('2026-10-15 12:00:00', 'UTC');

        $this->assertSame('2026-10-15 00:00:00', $driverAction['deadline']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-14 00:00:00', $vehicleAction['deadline']->format('Y-m-d H:i:s'));

        $driverRecord = TripActionDeadline::findOrFail($driverAction['record_id']);
        $driverRecord->override_hours = 48;
        $driverRecord->save();
        $trip->getRelation('actionDeadlines')
            ->firstWhere('id', $driverRecord->id)
            ->override_hours = 48;
        $result = $this->overview->apply($trip, $intervals, true);
        $driverAction = collect($result['actions'])->firstWhere('type', 'driver_assign');
        $vehicleAction = collect($result['actions'])->firstWhere('type', 'vehicle_assign');

        $this->assertSame('2026-10-13 12:00:00', $driverAction['deadline']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-14 00:00:00', $vehicleAction['deadline']->format('Y-m-d H:i:s'));
        $this->assertSame(48, (int) $driverAction['override_hours']);
        $this->assertNull($vehicleAction['override_hours']);
        $this->assertSame('2026-10-15 12:00:00', $target->format('Y-m-d H:i:s'));
    }

    public function test_due_window_and_completed_actions_are_recalculated(): void
    {
        Carbon::setTestNow('2026-10-13 00:00:00');
        $trip = $this->trip();
        $transport = $this->transportBooking(20, '2026-10-14', '12:00:00', 'Confirmed');
        $trip->setRelation('transportBookings', new EloquentCollection([$transport]));
        $trip->setRelation('accommodationBookings', new EloquentCollection());
        $trip->setRelation('activityBookings', new EloquentCollection());

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);
        $vehicle = collect($result['actions'])->firstWhere('type', 'vehicle_assign');
        $this->assertSame('scheduled', $vehicle['status']);

        Carbon::setTestNow('2026-10-13 12:01:00');
        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);
        $vehicle = collect($result['actions'])->firstWhere('type', 'vehicle_assign');
        $this->assertSame('due', $vehicle['status']);

        Carbon::setTestNow('2026-10-14 12:00:00');
        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);
        $vehicle = collect($result['actions'])->firstWhere('type', 'vehicle_assign');
        $this->assertSame('na', $vehicle['status']);

        $transport->booking_status = 'Completed';
        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);
        $this->assertSame('No Action Required', $result['actions'][0]['label']);
        $this->assertSame('completed', TripActionDeadline::findOrFail($vehicle['record_id'])->status);

        $diagnostic = $this->overview->diagnoseActions($trip, $this->defaultIntervals(), false);
        $vehicleDiagnostic = $diagnostic['actions']['vehicle_assign']['evaluations'][0];
        $this->assertSame('completed', $vehicleDiagnostic['action_state']);
        $this->assertSame('na', $vehicleDiagnostic['status']);
        $this->assertSame('ACTION_COMPLETED', $vehicleDiagnostic['reason_code']);

        $transport->booking_status = 'Cancelled';
        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);
        $this->assertSame('No Action Required', $result['actions'][0]['label']);
        $this->assertSame('na', $result['actions'][0]['status']);
        $this->assertSame('completed', TripActionDeadline::findOrFail($vehicle['record_id'])->status);
    }

    public function test_due_status_is_limited_to_the_configured_pre_target_window_for_both_panels(): void
    {
        $trip = $this->trip();
        $booking = $this->accommodationBooking(1, '2026-10-16', 'Confirmed');
        $booking->setRelation('guests', new EloquentCollection());
        $trip->setRelation('accommodationBookings', new EloquentCollection([$booking]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection());
        $intervals = $this->defaultIntervals();
        $intervals['guest_details'] = 12;

        Carbon::setTestNow('2026-10-15 11:59:00');
        $beforeDeadline = collect($this->overview->apply($trip, $intervals, false)['actions'])
            ->firstWhere('type', 'guest_details');
        $this->assertSame('scheduled', $beforeDeadline['status']);

        Carbon::setTestNow('2026-10-15 12:00:00');
        $adminActions = $this->overview->apply($trip, $intervals, true)['actions'];
        $operatorActions = $this->overview->apply($trip, $intervals, false)['actions'];
        $adminGuestAction = collect($adminActions)->firstWhere('type', 'guest_details');
        $operatorGuestAction = collect($operatorActions)->firstWhere('type', 'guest_details');
        $this->assertSame('due', $adminGuestAction['status']);
        $this->assertSame($adminGuestAction['status'], $operatorGuestAction['status']);

        Carbon::setTestNow('2026-10-15 18:00:00');
        $insideWindow = collect($this->overview->apply($trip, $intervals, false)['actions'])
            ->firstWhere('type', 'guest_details');
        $this->assertSame('due', $insideWindow['status']);

        Carbon::setTestNow('2026-10-16 00:00:00');
        $atTarget = collect($this->overview->apply($trip, $intervals, false)['actions'])
            ->firstWhere('type', 'guest_details');
        $this->assertSame('na', $atTarget['status']);

        Carbon::setTestNow('2026-10-16 00:01:00');
        $afterTarget = collect($this->overview->apply($trip, $intervals, false)['actions'])
            ->firstWhere('type', 'guest_details');
        $this->assertSame('na', $afterTarget['status']);
        $this->assertNotSame('overdue', $afterTarget['status']);
    }

    public function test_due_candidates_remain_due_while_payment_or_booking_confirmation_is_pending(): void
    {
        Carbon::setTestNow('2026-10-13 06:00:00');
        $intervals = $this->defaultIntervals();

        foreach ([
            ['payment_status' => 'pending', 'booking_status' => 'Confirmed', 'next_type' => 'payment'],
            ['payment_status' => 'pending_verification', 'booking_status' => 'Confirmed', 'next_type' => 'payment'],
            ['payment_status' => 'paid', 'booking_status' => 'Pending', 'next_type' => 'booking_confirm'],
        ] as $index => $state) {
            $trip = $this->trip();
            $trip->setAttribute('payment_status', $state['payment_status']);
            $booking = $this->accommodationBooking($index + 40, '2026-10-14', $state['booking_status']);
            $booking->setRelation('guests', new EloquentCollection());
            $trip->setRelation('accommodationBookings', new EloquentCollection([$booking]));
            $trip->setRelation('activityBookings', new EloquentCollection());
            $trip->setRelation('transportBookings', new EloquentCollection());

            $result = $this->overview->apply($trip, $intervals, false);

            $this->assertSame($state['next_type'], $result['actions'][0]['type']);
            $guestDetails = collect($result['due_actions'])->firstWhere('type', 'guest_details');
            $this->assertNotNull($guestDetails);
            $this->assertSame('due', $guestDetails['status']);
        }
    }

    public function test_due_presentation_uses_action_target_and_hides_deadlines_for_past_trips(): void
    {
        Carbon::setTestNow('2026-10-13 13:00:00');
        $trip = $this->trip();
        $trip->setAttribute('start_date', '2026-10-12');
        $trip->setAttribute('end_date', '2026-10-15');
        $booking = $this->accommodationBooking(60, '2026-10-14', 'Confirmed');
        $booking->setRelation('guests', new EloquentCollection());
        $trip->setRelation('accommodationBookings', new EloquentCollection([$booking]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection());

        $dueAction = collect($this->overview->apply($trip, $this->defaultIntervals(), false)['due_actions'])
            ->firstWhere('type', 'guest_details');

        $this->assertSame('due', $dueAction['status']);
        $this->assertSame('Complete guest details for accommodation booking #60', $dueAction['action_name']);
        $this->assertArrayNotHasKey('due_explanation', $dueAction);
        $this->assertSame('14 Oct 2026, 00:00 · UTC+00:00', $dueAction['target_display']);
        $this->assertSame('13 Oct 2026, 00:00 · UTC+00:00', $dueAction['due_since_display']);

        $trip->setAttribute('end_date', '2026-10-12');
        $pastTripAction = collect($this->overview->apply($trip, $this->defaultIntervals(), false)['due_actions'])
            ->firstWhere('type', 'guest_details');

        $this->assertSame('na', $pastTripAction['status']);
        $this->assertNull($pastTripAction['target_display']);
        $this->assertNull($pastTripAction['due_since_display']);
    }

    public function test_multiple_due_actions_have_only_their_relevant_deadline_details(): void
    {
        Carbon::setTestNow('2026-10-13 13:00:00');
        $trip = $this->trip();
        $transport = $this->transportBooking(61, '2026-10-14', '12:00:00', 'Confirmed');
        $trip->setAttribute('end_date', '2026-10-15');
        $trip->setRelation('transportBookings', new EloquentCollection([$transport]));
        $trip->setRelation('accommodationBookings', new EloquentCollection());
        $trip->setRelation('activityBookings', new EloquentCollection());

        $dueActions = collect($this->overview->apply($trip, $this->defaultIntervals(), false)['due_actions'])
            ->where('status', 'due');

        $this->assertCount(2, $dueActions);
        $this->assertEqualsCanonicalizing(
            ['driver_assign', 'vehicle_assign'],
            $dueActions->pluck('type')->all()
        );
        foreach ($dueActions as $action) {
            $this->assertNotEmpty($action['action_name']);
            $this->assertArrayNotHasKey('due_explanation', $action);
            $this->assertNotNull($action['target_display']);
            $this->assertNotNull($action['due_since_display']);
            $this->assertArrayNotHasKey('deadline_display', $action);
        }
    }

    public function test_accommodation_without_a_stored_check_in_time_is_shown_as_date_only(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');
        $trip = $this->trip();
        $trip->setRelation('accommodationBookings', new EloquentCollection([
            $this->accommodationBooking(30, '2026-10-14', 'Confirmed'),
        ]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection());

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);

        $this->assertSame('14 Oct 2026 · Time not specified', $result['next_service']['when']);
    }

    public function test_airport_transfer_precedes_accommodation_when_its_pickup_is_before_check_in(): void
    {
        Carbon::setTestNow('2026-10-19 08:00:00');
        $trip = $this->trip();
        $accommodation = $this->accommodationBooking(31, '2026-10-20', 'Confirmed');
        $accommodation->accommodation->setAttribute('checkin_time', '14:00:00');
        $transport = $this->transportBooking(32, '2026-10-20', '09:00:00', 'Confirmed', 'airport_transfer');
        $trip->setRelation('accommodationBookings', new EloquentCollection([$accommodation]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection([$transport]));

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);

        $this->assertSame('Airport Transfer — Airport to Hotel', $result['next_service']['name']);
    }

    public function test_accommodation_precedes_airport_transfer_when_check_in_is_earlier(): void
    {
        Carbon::setTestNow('2026-10-19 08:00:00');
        $trip = $this->trip();
        $accommodation = $this->accommodationBooking(33, '2026-10-20', 'Confirmed');
        $accommodation->accommodation->setAttribute('checkin_time', '10:00:00');
        $transport = $this->transportBooking(34, '2026-10-20', '18:00:00', 'Confirmed', 'airport_transfer');
        $trip->setRelation('accommodationBookings', new EloquentCollection([$accommodation]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection([$transport]));

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);

        $this->assertSame('Test Lodge — Check-in', $result['next_service']['name']);
    }

    public function test_same_day_airport_transfer_precedes_accommodation_with_unspecified_check_in_time(): void
    {
        Carbon::setTestNow('2026-10-19 08:00:00');
        $trip = $this->trip();
        $accommodation = $this->accommodationBooking(35, '2026-10-20', 'Confirmed');
        $transport = $this->transportBooking(36, '2026-10-20', '09:00:00', 'Confirmed', 'airport_transfer');
        $trip->setRelation('accommodationBookings', new EloquentCollection([$accommodation]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection([$transport]));

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);

        $this->assertSame('Airport Transfer — Airport to Hotel', $result['next_service']['name']);
    }

    public function test_airport_route_without_transport_service_type_precedes_same_day_package_accommodation(): void
    {
        Carbon::setTestNow('2026-10-29 08:00:00');
        $trip = $this->trip();
        $accommodation = $this->accommodationBooking(39, '2026-10-30', 'Confirmed');
        $accommodation->accommodation->setAttribute('property_name', 'Amit Resort');
        $transport = $this->transportBooking(40, '2026-10-30', '09:00:00', 'Confirmed');
        $transport->setAttribute('route_from', 'Airport');
        $transport->setAttribute('route_to', 'Hotel');
        $trip->setRelation('accommodationBookings', new EloquentCollection([$accommodation]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection([$transport]));

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);

        $this->assertSame('Airport Transfer — Airport to Hotel', $result['next_service']['name']);
        $this->assertSame('Tomorrow, 09:00 · UTC+00:00', $result['next_service']['when']);
    }

    public function test_hotel_to_airport_transfer_does_not_receive_arrival_transfer_priority(): void
    {
        Carbon::setTestNow('2026-10-19 08:00:00');
        $trip = $this->trip();
        $accommodation = $this->accommodationBooking(37, '2026-10-20', 'Confirmed');
        $transport = $this->transportBooking(38, '2026-10-20', '09:00:00', 'Confirmed', 'airport_transfer');
        $transport->setAttribute('route_from', 'Hotel');
        $transport->setAttribute('route_to', 'Airport');
        $trip->setRelation('accommodationBookings', new EloquentCollection([$accommodation]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection([$transport]));

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);

        $this->assertSame('Test Lodge — Check-in', $result['next_service']['name']);
    }

    public function test_driver_license_deadline_uses_the_assigned_drivers_real_expiry_date(): void
    {
        Carbon::setTestNow('2026-10-13 00:00:00');
        $trip = $this->trip();
        $transport = $this->transportBooking(40, '2026-10-20', '14:00:00', 'Confirmed');
        $transport->setAttribute('pickup_driver_id', 7);
        $transport->setAttribute('transport_vehicle_id', 3);
        $driver = new OperatorDriver();
        $driver->forceFill([
            'id' => 7,
            'driver_name' => 'Test Driver',
            'license_expiry_date' => '2026-10-14',
        ]);
        $transport->setRelation('pickupDriver', $driver);
        $trip->setRelation('transportBookings', new EloquentCollection([$transport]));
        $trip->setRelation('accommodationBookings', new EloquentCollection());
        $trip->setRelation('activityBookings', new EloquentCollection());

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);
        $licenseAction = collect($result['actions'])->firstWhere('type', 'driver_license_expire');

        $this->assertNotNull($licenseAction);
        $this->assertSame('2026-10-13 00:00:00', $licenseAction['deadline']->format('Y-m-d H:i:s'));
        $this->assertSame('due', $licenseAction['status']);
    }

    public function test_action_diagnostic_uses_the_exact_override_and_reports_due_before_target(): void
    {
        Carbon::setTestNow('2026-10-14 00:01:00');
        $trip = $this->trip();
        $booking = $this->accommodationBooking(1, '2026-10-16', 'Confirmed');
        $booking->setRelation('guests', new EloquentCollection());
        $trip->setRelation('accommodationBookings', new EloquentCollection([$booking]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection());

        $record = new TripActionDeadline();
        $record->forceFill([
            'trip_id' => 1,
            'service_type' => 'accommodation',
            'service_booking_id' => 1,
            'action_type' => 'guest_details',
            'action_key' => 'accommodation:1:guest_details',
            'label' => 'Accommodation #1 — Complete Guest Details',
            'target_at' => '2026-10-16 00:00:00',
            'deadline_at' => '2026-10-15 06:00:00',
            'time_zone' => 'UTC',
            'override_hours' => 48,
            'status' => 'open',
        ]);
        $record->save();
        $trip->setRelation('actionDeadlines', new EloquentCollection([$record]));

        $diagnostic = $this->overview->diagnoseActions($trip, $this->defaultIntervals(), false);
        $guestAction = $diagnostic['actions']['guest_details']['evaluations'][0];

        $this->assertSame('Accommodation #1 — Complete Guest Details', $diagnostic['current_next_action']);
        $this->assertSame(24, $guestAction['deadline_setting_hours']);
        $this->assertSame(48, (int) $guestAction['admin_override_hours']);
        $this->assertSame('2026-10-14 00:00:00Z', $guestAction['calculated_deadline']);
        $this->assertSame('due', $guestAction['status']);
        $this->assertFalse($guestAction['deadline_has_passed']);
        $this->assertNull($guestAction['reason_code']);
    }

    public function test_active_deadlines_match_their_booking_keys_and_reopen_stale_completed_records(): void
    {
        Carbon::setTestNow('2026-10-14 00:00:00');
        $trip = $this->trip();
        $firstBooking = $this->accommodationBooking(1, '2026-09-10', 'Confirmed');
        $firstBooking->setRelation('guests', new EloquentCollection());
        $secondBooking = $this->accommodationBooking(2, '2026-09-12', 'Confirmed');
        $secondBooking->setRelation('guests', new EloquentCollection());
        $trip->setRelation('accommodationBookings', new EloquentCollection([$firstBooking, $secondBooking]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection());

        $firstRecord = new TripActionDeadline();
        $firstRecord->forceFill([
            'trip_id' => 1,
            'service_type' => 'accommodation',
            'service_booking_id' => 1,
            'action_type' => 'guest_details',
            'action_key' => 'accommodation:1:guest_details',
            'label' => 'Accommodation #1 — Complete Guest Details',
            'target_at' => '2026-09-10 00:00:00',
            'deadline_at' => '2026-09-08 00:00:00',
            'time_zone' => 'UTC',
            'override_hours' => 48,
            'status' => 'completed',
        ]);
        $firstRecord->save();
        $secondRecord = new TripActionDeadline();
        $secondRecord->forceFill([
            'trip_id' => 1,
            'service_type' => 'accommodation',
            'service_booking_id' => 2,
            'action_type' => 'guest_details',
            'action_key' => 'accommodation:2:guest_details',
            'label' => 'Accommodation #2 — Complete Guest Details',
            'target_at' => '2026-09-12 00:00:00',
            'deadline_at' => '2026-09-11 18:00:00',
            'time_zone' => 'UTC',
            'override_hours' => 6,
            'status' => 'open',
        ]);
        $secondRecord->save();
        $trip->setRelation('actionDeadlines', new EloquentCollection([$firstRecord, $secondRecord]));

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);
        $firstAction = collect($result['actions'])->firstWhere('booking_id', 1);
        $secondAction = collect($result['actions'])->firstWhere('booking_id', 2);

        $this->assertSame('2026-09-08 00:00:00', $firstAction['deadline']->format('Y-m-d H:i:s'));
        $this->assertSame(48, (int) $firstAction['override_hours']);
        $this->assertSame('na', $firstAction['status']);
        $this->assertSame('2026-09-11 18:00:00', $secondAction['deadline']->format('Y-m-d H:i:s'));
        $this->assertSame(6, (int) $secondAction['override_hours']);
        $this->assertSame('na', $secondAction['status']);
        $this->assertSame('open', $firstRecord->fresh()->status);
    }

    public function test_action_diagnostic_marks_payment_blocked_guest_details_and_does_not_mutate_deadline_record(): void
    {
        Carbon::setTestNow('2026-10-14 00:00:00');
        $trip = $this->trip();
        $trip->setAttribute('payment_status', 'pending');
        $booking = $this->accommodationBooking(1, '2026-09-10', 'Confirmed');
        $booking->setRelation('guests', new EloquentCollection());
        $trip->setRelation('accommodationBookings', new EloquentCollection([$booking]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection());

        $record = new TripActionDeadline();
        $record->forceFill([
            'trip_id' => 1,
            'service_type' => 'accommodation',
            'service_booking_id' => 1,
            'action_type' => 'guest_details',
            'action_key' => 'accommodation:1:guest_details',
            'label' => 'Accommodation #1 — Complete Guest Details',
            'target_at' => '2026-09-10 00:00:00',
            'deadline_at' => '2026-09-09 06:00:00',
            'time_zone' => 'UTC',
            'status' => 'completed',
        ]);
        $record->save();
        $trip->setRelation('actionDeadlines', new EloquentCollection([$record]));

        $diagnostic = $this->overview->diagnoseActions($trip, $this->defaultIntervals(), false);
        $guestAction = $diagnostic['actions']['guest_details']['evaluations'][0];

        $this->assertSame('Awaiting Payment', $diagnostic['current_next_action']);
        $this->assertTrue($guestAction['required']);
        $this->assertSame('blocked', $guestAction['action_state']);
        $this->assertSame('payment', $guestAction['blocked_by']);
        $this->assertSame('na', $guestAction['status']);
        $this->assertSame('PAYMENT_PENDING', $guestAction['reason_code']);
        $this->assertSame('completed', $record->fresh()->status);
    }

    public function test_action_diagnostic_reports_booking_confirmation_as_a_workflow_dependency(): void
    {
        Carbon::setTestNow('2026-10-14 00:00:00');
        $trip = $this->trip();
        $booking = $this->accommodationBooking(1, '2026-10-16', 'Pending');
        $booking->setRelation('guests', new EloquentCollection());
        $trip->setRelation('accommodationBookings', new EloquentCollection([$booking]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection());

        $diagnostic = $this->overview->diagnoseActions($trip, $this->defaultIntervals(), false);
        $guestAction = $diagnostic['actions']['guest_details']['evaluations'][0];

        $this->assertSame('Accommodation #1 — Mark as Confirmed', $diagnostic['current_next_action']);
        $this->assertTrue($guestAction['required']);
        $this->assertSame('blocked', $guestAction['action_state']);
        $this->assertSame('booking_confirmation', $guestAction['blocked_by']);
        $this->assertSame('na', $guestAction['status']);
        $this->assertSame('BOOKING_CONFIRMATION_PENDING', $guestAction['reason_code']);
    }

    public function test_action_diagnostic_reports_missing_target_for_a_required_action(): void
    {
        Carbon::setTestNow('2026-10-14 00:00:00');
        $trip = $this->trip();
        $booking = $this->accommodationBooking(1, '2026-10-16', 'Confirmed');
        $booking->check_in_date = null;
        $booking->setRelation('guests', new EloquentCollection());
        $trip->setRelation('accommodationBookings', new EloquentCollection([$booking]));
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection());

        $diagnostic = $this->overview->diagnoseActions($trip, $this->defaultIntervals(), false);
        $guestAction = $diagnostic['actions']['guest_details']['evaluations'][0];

        $this->assertTrue($guestAction['required']);
        $this->assertNull($guestAction['target_datetime']);
        $this->assertSame('na', $guestAction['status']);
        $this->assertSame('TARGET_DATETIME_MISSING', $guestAction['reason_code']);
    }

    public function test_action_diagnostic_reports_actions_without_applicable_service_as_not_required(): void
    {
        Carbon::setTestNow('2026-10-14 00:00:00');
        $trip = $this->trip();
        $trip->setRelation('accommodationBookings', new EloquentCollection());
        $trip->setRelation('activityBookings', new EloquentCollection());
        $trip->setRelation('transportBookings', new EloquentCollection());

        $diagnostic = $this->overview->diagnoseActions($trip, $this->defaultIntervals(), false);

        $this->assertSame('No Action Required', $diagnostic['current_next_action']);
        $this->assertSame('na', $diagnostic['actions']['driver_assign']['status']);
        $this->assertSame('ACTION_NOT_REQUIRED', $diagnostic['actions']['driver_assign']['reason_code']);
        $this->assertSame('not_applicable', $diagnostic['actions']['driver_assign']['evaluations'][0]['action_state']);
    }

    public function test_service_local_time_and_offset_are_used_when_an_iana_zone_is_available(): void
    {
        Carbon::setTestNow('2026-10-13 08:00:00');
        $trip = $this->trip();
        $activity = $this->activityBooking(50, '2026-10-13', '10:30:00', 'Confirmed');
        $activity->setAttribute('time_zone', 'Europe/Paris');
        $trip->setRelation('accommodationBookings', new EloquentCollection());
        $trip->setRelation('activityBookings', new EloquentCollection([$activity]));
        $trip->setRelation('transportBookings', new EloquentCollection());

        $result = $this->overview->apply($trip, $this->defaultIntervals(), false);

        $this->assertSame('Today, 10:30 · UTC+02:00', $result['next_service']['when']);
    }

    private function trip(): Trip
    {
        $trip = new Trip();
        $trip->setAttribute('id', 1);
        $trip->setAttribute('payment_status', 'paid');
        $trip->setRelation('actionDeadlines', new EloquentCollection());

        return $trip;
    }

    private function accommodationBooking(int $id, string $checkIn, string $status): AccommodationBooking
    {
        $booking = new AccommodationBooking();
        $booking->forceFill([
            'id' => $id,
            'check_in_date' => $checkIn,
            'booking_status' => $status,
            'adults' => 1,
        ]);
        $property = new Accommodation();
        $property->setAttribute('property_name', 'Test Lodge');
        $booking->setRelation('accommodation', $property);
        $booking->setRelation('guests', new EloquentCollection([$this->completeGuest()]));

        return $booking;
    }

    private function activityBooking(int $id, string $date, string $startTime, string $status): ActivityBooking
    {
        $booking = new ActivityBooking();
        $booking->forceFill([
            'id' => $id,
            'activity_date' => $date,
            'booking_status' => $status,
            'adults' => 1,
            'variant_name' => 'Morning Walk',
        ]);
        $activity = new Activity();
        $activity->setAttribute('activity_name', 'Activity Walk');
        $slot = new ActivitySchedulingTimeSlot();
        $slot->setAttribute('start_time', $startTime);
        $booking->setRelation('activity', $activity);
        $booking->setRelation('activityTimeSlot', $slot);
        $booking->setRelation('guests', new EloquentCollection([$this->completeGuest()]));

        return $booking;
    }

    private function transportBooking(
        int $id,
        string $date,
        string $time,
        string $status,
        ?string $serviceType = null
    ): TransportBooking
    {
        $booking = new TransportBooking();
        $booking->forceFill([
            'id' => $id,
            'pickup_date' => $date,
            'pickup_time' => $time,
            'service_type' => $serviceType,
            'booking_status' => $status,
            'total_passengers' => 1,
            'route_from' => 'Airport',
            'route_to' => 'Hotel',
            'traveler_first_name' => 'Guest',
            'traveler_last_name' => 'Person',
            'traveler_dob' => '1990-01-01',
            'traveler_nationality' => 'Mauritian',
        ]);
        $booking->setRelation('transport', null);
        $booking->setRelation('pickupDriver', null);
        $booking->setRelation('returnDriver', null);
        $booking->setRelation('driver', null);
        $booking->setRelation('guests', new EloquentCollection([$this->completeGuest()]));

        return $booking;
    }

    private function completeGuest(): BookingGuest
    {
        $guest = new BookingGuest();
        $guest->forceFill([
            'first_name' => 'Guest',
            'last_name' => 'Person',
            'dob' => '1990-01-01',
            'nationality' => 'Mauritian',
        ]);

        return $guest;
    }

    private function defaultIntervals(): array
    {
        return [
            'guest_details' => 24,
            'driver_license_expire' => 24,
            'driver_assign' => 24,
            'vehicle_assign' => 24,
        ];
    }
}
