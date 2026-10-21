<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\AccommodationRoom;
use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\BookingRef;
use App\Models\Operator;
use App\Models\Transport;
use App\Models\TransportBooking;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OperatorTripListingTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('PRAGMA foreign_keys = OFF');

        foreach (['trip_action_deadlines', 'trip_action_deadline_settings', 'bli_traveller_allocations', 'booking_guests', 'activity_scheduling_timeslots', 'travellers', 'traveler_accounts', 'transport_bookings', 'transports', 'activity_bookings', 'activities', 'accommodation_bookings', 'accommodation_rooms', 'accommodations', 'booking_refs', 'trips', 'operators', 'operator_users', 'businesses'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }

        DB::statement('PRAGMA foreign_keys = ON');

        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('iso_code', 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        DB::table('countries')->updateOrInsert(
            ['iso_code' => 'MU'],
            ['name' => 'Mauritius', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
        );

        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->json('package_policy')->nullable();
            $table->json('group_policy')->nullable();
            $table->timestamps();
        });

        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('operator_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password_hash')->nullable();
            $table->timestamps();
        });

        Schema::create('traveler_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('traveler_account_id')->nullable();
            $table->string('title')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->timestamps();
        });

        Schema::create('trip_action_deadline_settings', function (Blueprint $table) {
            $table->id();
            $table->string('action_type', 50);
            $table->unsignedInteger('interval_hours')->default(24);
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('updated_by_admin_id')->nullable();
            $table->timestamps();
            $table->unique(['country_id', 'action_type']);
        });

        Schema::create('trip_action_deadlines', function (Blueprint $table) {
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

        Schema::create('travellers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('relationship')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_guests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('booking_type');
            $table->integer('guest_number')->default(1);
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('relation')->nullable();
            $table->date('dob')->nullable();
            $table->string('nationality')->nullable();
            $table->timestamps();
        });

        Schema::create('bli_traveller_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bli_id');
            $table->unsignedBigInteger('traveller_id');
        });

        Schema::create('activity_scheduling_timeslots', function (Blueprint $table) {
            $table->unsignedBigInteger('timeslot_id')->primary();
            $table->unsignedBigInteger('activity_id');
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_refs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->string('booking_ref_code')->nullable();
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->timestamps();
        });

        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('property_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('accommodation_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accommodation_id')->nullable();
            $table->string('name')->nullable();
            $table->string('room_name')->nullable();
            $table->timestamps();
        });

        Schema::create('accommodation_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('booking_ref_id')->nullable();
            $table->unsignedBigInteger('accommodation_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->string('booking_reference')->nullable();
            $table->string('booking_status')->nullable();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->string('guest_name')->nullable();
            $table->unsignedInteger('rooms_booked')->default(1);
            $table->unsignedSmallInteger('adults')->default(2);
            $table->unsignedSmallInteger('children')->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->string('activity_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('activity_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('booking_ref_id')->nullable();
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->string('booking_reference')->nullable();
            $table->string('booking_status')->nullable();
            $table->date('activity_date')->nullable();
            $table->unsignedBigInteger('activity_time_slot_id')->nullable();
            $table->string('guest_name')->nullable();
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('transports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->string('approval_status')->default('Draft');
            $table->string('vehicle_display_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('transport_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('booking_ref_id')->nullable();
            $table->unsignedBigInteger('transport_id')->nullable();
            $table->string('booking_reference')->nullable();
            $table->string('booking_status')->nullable();
            $table->string('route_from')->nullable();
            $table->string('route_to')->nullable();
            $table->date('pickup_date')->nullable();
            $table->time('pickup_time')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('traveler_first_name')->nullable();
            $table->string('traveler_middle_name')->nullable();
            $table->string('traveler_last_name')->nullable();
            $table->unsignedInteger('total_passengers')->default(1);
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            $table->unsignedBigInteger('transport_vehicle_id')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function test_operator_trip_listing_only_shows_trips_with_their_own_services(): void
    {
        config(['app.timezone' => 'UTC']);

        DB::table('businesses')->insert([
            ['id' => 100, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 200, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);
        $operator = Operator::create(['id' => 10, 'name' => 'Operator One', 'email' => 'one@example.test', 'business_id' => 100]);
        $otherOperator = Operator::create(['id' => 20, 'name' => 'Operator Two', 'email' => 'two@example.test', 'business_id' => 200]);

        $ownedAccommodation = Accommodation::create(['id' => 1, 'operator_id' => 10, 'business_id' => 100, 'property_name' => 'Owned Property']);
        $room = AccommodationRoom::create(['id' => 1, 'accommodation_id' => 1, 'name' => 'Deluxe', 'room_name' => 'Standard yes']);
        $ownedActivity = Activity::create(['id' => 1, 'operator_id' => 10, 'activity_name' => 'Owned Activity']);
        $ownedTransport = Transport::create(['id' => 1, 'operator_id' => 10, 'vehicle_display_name' => 'Owned Bus']);
        $otherAccommodation = Accommodation::create(['id' => 2, 'operator_id' => 20, 'business_id' => 200, 'property_name' => 'Other Property']);

        $accountHolder = \App\Models\TravelerAccount::create(['full_name' => 'John Smith']);
        $mixedTripAccountHolder = \App\Models\TravelerAccount::create(['full_name' => 'Customer Not Travelling']);
        $visibleTrip = Trip::create(['id' => 1, 'traveler_account_id' => $accountHolder->id, 'title' => 'Visible Trip']);
        $hiddenTrip = Trip::create(['id' => 2, 'title' => 'Hidden Trip']);
        $mixedTrip = Trip::create(['id' => 3, 'traveler_account_id' => $mixedTripAccountHolder->id, 'title' => 'Mixed Trip']);
        $visibleTrip->travellers()->createMany([
            ['name' => 'John Smith', 'relationship' => 'self'],
            ['name' => 'Sarah Smith', 'relationship' => 'guest'],
        ]);
        $mixedTrip->travellers()->createMany([
            ['name' => 'Sarah Jones', 'relationship' => 'lead'],
            ['name' => 'David Jones', 'relationship' => 'guest'],
        ]);

        $visibleRef = BookingRef::create([
            'id' => 1,
            'trip_id' => 1,
            'booking_ref_code' => 'BR-OP-001',
            'created_at' => '2026-10-20 14:35:22',
            'updated_at' => '2026-10-20 14:35:22',
        ]);
        $mixedRef = BookingRef::create(['id' => 2, 'trip_id' => 3, 'booking_ref_code' => 'BR-OP-003']);

        AccommodationBooking::create(['id' => 1, 'trip_id' => 1, 'booking_ref_id' => $visibleRef->id, 'accommodation_id' => 1, 'room_id' => $room->id, 'booking_reference' => 'ACC-001', 'booking_status' => 'Confirmed', 'check_in_date' => '2026-10-01', 'check_out_date' => '2026-10-03', 'rooms_booked' => 2, 'adults' => 2, 'children' => 1, 'total_amount' => 120.00]);
        \App\Models\BookingGuest::create(['booking_id' => 1, 'booking_type' => 'accommodation', 'guest_number' => 1, 'first_name' => 'John', 'last_name' => 'Smith']);
        \App\Models\BookingGuest::create(['booking_id' => 1, 'booking_type' => 'accommodation', 'guest_number' => 2, 'first_name' => 'Sarah', 'last_name' => 'Smith']);
        \App\Models\BookingGuest::create(['booking_id' => 1, 'booking_type' => 'accommodation', 'guest_number' => 3, 'first_name' => 'John', 'last_name' => 'Smith']);
        ActivityBooking::create(['id' => 1, 'trip_id' => 1, 'booking_ref_id' => $visibleRef->id, 'activity_id' => 1, 'booking_reference' => 'ACT-001', 'booking_status' => 'Confirmed', 'activity_date' => '2026-10-02', 'activity_time_slot_id' => 1, 'adults' => 2, 'children' => 1, 'total_amount' => 80.00]);
        DB::table('activity_scheduling_timeslots')->insert(['timeslot_id' => 1, 'activity_id' => 1, 'start_time' => '09:00:00', 'end_time' => '11:00:00', 'created_at' => now(), 'updated_at' => now()]);
        \App\Models\BookingGuest::create(['booking_id' => 1, 'booking_type' => 'activity', 'guest_number' => 1, 'first_name' => 'Sarah', 'last_name' => 'Smith']);
        \App\Models\BookingGuest::create(['booking_id' => 1, 'booking_type' => 'activity', 'guest_number' => 2, 'first_name' => 'Alex', 'last_name' => 'Smith']);
        $visibleTransportBooking = TransportBooking::create(['id' => 1, 'transport_id' => 1, 'booking_reference' => 'TRN-001', 'booking_status' => 'Confirmed', 'route_from' => 'Airport', 'route_to' => 'Hotel', 'pickup_date' => '2026-10-01', 'pickup_time' => '08:30:00', 'total_passengers' => 3, 'adults' => 2, 'children' => 1, 'traveler_first_name' => 'John', 'traveler_last_name' => 'Smith', 'transport_vehicle_id' => 1, 'total_amount' => 90.00]);
        $visibleTransportBooking->forceFill(['trip_id' => 1, 'booking_ref_id' => $visibleRef->id])->save();
        \App\Models\BookingGuest::create(['booking_id' => 1, 'booking_type' => 'transport', 'guest_number' => 1, 'first_name' => 'Sarah', 'last_name' => 'Smith']);
        \App\Models\BookingGuest::create(['booking_id' => 1, 'booking_type' => 'transport', 'guest_number' => 2, 'first_name' => 'David', 'last_name' => 'Smith']);
        $secondVisibleRef = BookingRef::create(['id' => 3, 'trip_id' => 1, 'booking_ref_code' => 'BR-OP-001-B']);
        ActivityBooking::create(['id' => 3, 'trip_id' => 1, 'booking_ref_id' => $secondVisibleRef->id, 'activity_id' => 1, 'booking_reference' => 'ACT-001-B', 'booking_status' => 'Confirmed', 'total_amount' => 40.00]);

        AccommodationBooking::create(['id' => 2, 'trip_id' => 3, 'booking_ref_id' => $mixedRef->id, 'accommodation_id' => 2, 'booking_reference' => 'ACC-OTHER', 'booking_status' => 'Cancelled', 'total_amount' => 60.00]);
        \App\Models\BookingGuest::create([
            'booking_id' => 2,
            'booking_type' => 'accommodation',
            'guest_number' => 1,
            'first_name' => 'Private',
            'last_name' => 'Guest',
        ]);
        ActivityBooking::create(['id' => 2, 'trip_id' => 3, 'booking_ref_id' => $mixedRef->id, 'activity_id' => 1, 'booking_reference' => 'ACT-OWN', 'booking_status' => 'Confirmed', 'total_amount' => 50.00]);

        AccommodationBooking::create(['id' => 3, 'trip_id' => 2, 'booking_ref_id' => 99, 'accommodation_id' => 2, 'booking_reference' => 'ACC-HIDDEN', 'booking_status' => 'Confirmed', 'total_amount' => 70.00]);

        $completeTrip = Trip::create(['id' => 4, 'traveler_account_id' => $accountHolder->id, 'title' => 'Complete Trip']);
        $completeTrip->travellers()->create([
            'name' => 'Sarah Jones',
            'relationship' => 'lead',
            'date_of_birth' => '1985-01-01',
        ]);
        $completeTrip->travellers()->create([
            'name' => 'David Jones',
            'relationship' => 'child',
            'date_of_birth' => '2015-01-01',
        ]);
        $completeBooking = AccommodationBooking::create([
            'id' => 4,
            'trip_id' => $completeTrip->id,
            'accommodation_id' => $ownedAccommodation->id,
            'room_id' => $room->id,
            'booking_reference' => 'ACC-COMPLETE',
            'booking_status' => 'Confirmed',
            'adults' => 1,
            'children' => 1,
        ]);
        \App\Models\BookingGuest::create([
            'booking_id' => $completeBooking->id,
            'booking_type' => 'accommodation',
            'guest_number' => 1,
            'first_name' => 'Sarah',
            'last_name' => 'Jones',
            'relation' => 'lead',
            'dob' => '1985-01-01',
            'nationality' => 'Mauritian',
        ]);
        \App\Models\BookingGuest::create([
            'booking_id' => $completeBooking->id,
            'booking_type' => 'accommodation',
            'guest_number' => 2,
            'first_name' => 'David',
            'last_name' => 'Jones',
            'relation' => 'child',
            'dob' => '2015-01-01',
            'nationality' => 'Mauritian',
        ]);

        $categoryMismatchTrip = Trip::create(['id' => 5, 'traveler_account_id' => $accountHolder->id, 'title' => 'Category Mismatch Trip']);
        $categoryMismatchTrip->travellers()->createMany([
            ['name' => 'John Smith', 'relationship' => 'self'],
            ['name' => 'Sarah Jones', 'relationship' => 'guest'],
            ['name' => 'David Jones', 'relationship' => 'guest'],
        ]);
        $categoryMismatchBooking = ActivityBooking::create([
            'id' => 4,
            'trip_id' => $categoryMismatchTrip->id,
            'activity_id' => $ownedActivity->id,
            'booking_reference' => 'ACT-CATEGORY-MISMATCH',
            'booking_status' => 'Confirmed',
            'adults' => 2,
            'children' => 1,
        ]);
        foreach ([
            ['first_name' => 'John', 'last_name' => 'Smith', 'relation' => 'self'],
            ['first_name' => 'Sarah', 'last_name' => 'Jones', 'relation' => 'spouse'],
            ['first_name' => 'David', 'last_name' => 'Jones', 'relation' => 'spouse'],
        ] as $index => $guest) {
            \App\Models\BookingGuest::create([
                'booking_id' => $categoryMismatchBooking->id,
                'booking_type' => 'activity',
                'guest_number' => $index + 1,
                ...$guest,
                'dob' => '1985-01-01',
                'nationality' => 'Mauritian',
            ]);
        }

        $this->actingAs($operator, 'operator');

        $response = $this->get(route('operator.trips.index'));

        $response->assertOk();
        $response->assertSee('id="sidebar" class="col-md-3 net-section"', false);
        $response->assertSee('id="registrationStepsCollapse"', false);
        $response->assertSee('background:rgba(255,255,255,0.18);', false);
        $response->assertSee('function toggleSidebar()', false);
        $response->assertDontSee('class="operator-shell"', false);
        $response->assertSee('Visible Trip');
        $response->assertSee('Mixed Trip');
        $response->assertSee('aria-label="Expand trip details"', false);
        $response->assertSee('>+</button>', false);
        $response->assertSee('aria-expanded="false"', false);
        $response->assertSee('id="operator-trip-1" hidden', false);
        $response->assertSee('class="operator-trip-listing"', false);
        $response->assertSee('View Pending Actions');
        $response->assertSee('No pending actions for this trip.');
        $response->assertSee('dialog.showModal()', false);
        $response->assertSeeInOrder([
            '>Trip</th>',
            '>Travel / next service</th>',
            '>Authority and Travel Party</th>',
            '>Booking</th>',
            '>Payment</th>',
            '>Attention / Priority</th>',
            '>Next Action</th>',
            '>Due / Overdue</th>',
        ], false);
        $response->assertDontSee('>Traveller</th>', false);
        $response->assertSee('name="traveller_information"', false);
        $response->assertSee('Next Step:</strong> Awaiting Payment', false);
        $response->assertDontSee('background:#16a34a');
        $response->assertDontSee('Hidden Trip');
        $response->assertDontSee('Private Guest');
        $response->assertSee('1 Booking Refs · 1 BLIs');
        $response->assertSee('BR-OP-001');
        $response->assertSee('2 Booking Refs · 4 BLIs');
        $response->assertSee('Total Amount USD330');
        $response->assertSee('Total Amount USD50');
        $response->assertSee('Confirmed: 4');
        $response->assertDontSee('Cancelled: 1');
        $response->assertSee('>Booking</th>', false);
        $response->assertSee('Pending</span>', false);
        $response->assertSee('Travel Party Size: 3');
        $response->assertSee('20 Oct 2026, 14:35');
        $response->assertSee('Booking Authority / Account Holder:');
        $response->assertSee('Travellers:');
        $response->assertSee('John Smith');
        $response->assertSee('Customer Not Travelling');
        $response->assertDontSee('Account Holder not travelling');
        $response->assertDontSee('Responsible: Not specified');
        $response->assertSee('Standard yes');
        $response->assertSee('/operator/accommodation/bookings/1');
        $response->assertSee('/operator/activity/bookings/1');
        $response->assertSee('/operator/transport/1/bookings/1');
        $response->assertSee('Check-in:</strong> 01/10/2026', false);
        $response->assertSee('Check-out:</strong> 03/10/2026', false);
        $response->assertSee('Rooms:</strong> 2', false);
        $response->assertSee('Participant Names:</strong> John Smith, Sarah Smith, John Smith', false);
        $response->assertSee('Date:</strong> 02/10/2026', false);
        $response->assertSee('Time:</strong> 09:00:00 - 11:00:00', false);
        $response->assertSee('Participants:</strong> Adults: 2 · Children: 1 · Total: 3', false);
        $response->assertSee('Participant Names:</strong> Sarah Smith, Alex Smith', false);
        $response->assertSee('Passengers:</strong> 3', false);
        $response->assertSee('Pickup:</strong> 01/10/2026 08:30:00', false);
        $response->assertSee('Passenger Names:</strong> Sarah Smith, David Smith, John Smith', false);
        $response->assertSee('Vehicles:</strong> 1', false);
        $response->assertDontSee('ACC-OTHER');

        $nonOwnedReferenceSearch = $this->get(route('operator.trips.index', [
            'booking_reference' => 'ACC-OTHER',
        ]));
        $nonOwnedReferenceSearch->assertDontSee('Mixed Trip');

        $ownedReferenceSearch = $this->get(route('operator.trips.index', [
            'booking_reference' => 'ACT-OWN',
        ]));
        $ownedReferenceSearch->assertSee('Mixed Trip');
        $ownedReferenceSearch->assertDontSee('ACC-OTHER');

        $combinedFilter = $this->get(route('operator.trips.index', [
            'booking_reference' => 'ACT-OWN',
            'traveller_information' => 'missing',
        ]));
        $combinedFilter->assertSee('Mixed Trip');
        $combinedFilter->assertDontSee('Visible Trip');

        $otherOperatorTripSearch = $this->get(route('operator.trips.index', [
            'trip' => '2',
        ]));
        $otherOperatorTripSearch->assertDontSee('Hidden Trip');

        $missingResponse = $this->get(route('operator.trips.index', ['traveller_information' => 'missing']));
        $missingResponse->assertOk();
        $missingResponse->assertSee('Visible Trip');
        $missingResponse->assertSee('Mixed Trip');
        $missingResponse->assertSee('Category Mismatch Trip');
        $missingResponse->assertDontSee('Complete Trip');
        $missingResponse->assertDontSee('Hidden Trip');

        $completeResponse = $this->get(route('operator.trips.index', ['traveller_information' => 'complete']));
        $completeResponse->assertOk();
        $completeResponse->assertSee('Complete Trip');
        $completeResponse->assertDontSee('Category Mismatch Trip');
        $completeResponse->assertDontSee('Visible Trip');
        $completeResponse->assertDontSee('Mixed Trip');
        $completeResponse->assertDontSee('Hidden Trip');
    }

    public function test_operator_trip_listing_uses_ordered_service_dates_within_operator_scope(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow('2026-10-15 12:00:00');
        DB::table('businesses')->insert([
            'id' => 100,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $operator = Operator::create([
            'id' => 10,
            'name' => 'Operator One',
            'email' => 'one@example.test',
            'business_id' => 100,
        ]);
        $transport = Transport::create([
            'id' => 1,
            'operator_id' => $operator->id,
            'vehicle_display_name' => 'Airport Shuttle',
        ]);
        $trip = Trip::create(['id' => 10, 'title' => 'Transport Dates Trip']);
        foreach ([
            ['pickup_date' => '2026-10-23', 'pickup_time' => '10:00:00'],
            ['pickup_date' => '2026-10-20', 'pickup_time' => '16:00:00'],
        ] as $details) {
            $booking = TransportBooking::create([
                'transport_id' => $transport->id,
                'booking_status' => 'Processing',
                'route_from' => 'Airport',
                'route_to' => 'North',
                ...$details,
            ]);
            $booking->forceFill(['trip_id' => $trip->id])->save();
        }

        $activity = Activity::create([
            'id' => 1,
            'operator_id' => $operator->id,
            'activity_name' => 'Island Tour',
        ]);
        $activityFirstTrip = Trip::create(['id' => 11, 'title' => 'Activity Before Transport']);
        ActivityBooking::create([
            'trip_id' => $activityFirstTrip->id,
            'activity_id' => $activity->id,
            'booking_status' => 'Confirmed',
            'activity_date' => '2026-10-19',
        ]);
        $laterTransport = TransportBooking::create([
            'transport_id' => $transport->id,
            'booking_status' => 'Confirmed',
            'pickup_date' => '2026-10-20',
            'pickup_time' => '16:00:00',
        ]);
        $laterTransport->forceFill(['trip_id' => $activityFirstTrip->id])->save();

        $response = $this->actingAs($operator, 'operator')->get(route('operator.trips.index'));

        $response->assertOk();
        $response->assertSee('20 Oct 2026 - 23 Oct 2026');
        $response->assertSee('20 Oct 2026 – 23 Oct 2026');
        $response->assertSee('20 Oct 2026, 16:00 · UTC+00:00');
        $response->assertSee('19 Oct 2026 - 20 Oct 2026');
        $viewTrips = collect($response->viewData('trips')->items());
        $transportTrip = $viewTrips->firstWhere('id', $trip->id);
        $this->assertSame('20 Oct 2026', $transportTrip->operations_overview['travel_dates']['start']);
        $this->assertSame('23 Oct 2026', $transportTrip->operations_overview['travel_dates']['end']);
        $this->assertSame('20 Oct 2026, 16:00 · UTC+00:00', $transportTrip->operations_overview['next_service']['when']);
        $activityTrip = $viewTrips->firstWhere('id', $activityFirstTrip->id);
        $this->assertSame('Island Tour', $activityTrip->operations_overview['next_service']['name']);
    }

    public function test_operator_trip_listing_passes_due_for_an_action_inside_its_configured_window(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow('2026-10-13 06:00:00');
        DB::table('businesses')->insert([
            'id' => 100,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $operator = Operator::create([
            'id' => 10,
            'name' => 'Operator One',
            'email' => 'one@example.test',
            'business_id' => 100,
        ]);
        $accommodation = Accommodation::create([
            'id' => 1,
            'operator_id' => 10,
            'business_id' => 100,
            'property_name' => 'Owned Property',
        ]);
        $trip = Trip::create(['id' => 1, 'title' => 'Due Window Trip']);
        $bookingRef = BookingRef::create(['id' => 1, 'trip_id' => 1, 'booking_ref_code' => 'DUE-001']);
        $booking = \App\Models\Booking::create([
            'trip_id' => $trip->id,
            'booking_ref_id' => $bookingRef->id,
            'total_amount' => 100,
            'status' => 'confirmed',
            'booking_type' => 'open-group',
        ]);
        \App\Models\PaymentTransaction::create([
            'booking_id' => $booking->id,
            'booking_ref_id' => $bookingRef->id,
            'amount' => 100,
            'method' => 'card',
            'status' => 'paid',
        ]);
        $serviceBooking = AccommodationBooking::create([
            'id' => 5,
            'trip_id' => $trip->id,
            'booking_ref_id' => $bookingRef->id,
            'accommodation_id' => $accommodation->id,
            'booking_status' => 'Confirmed',
            'check_in_date' => '2026-10-14',
            'check_out_date' => '2026-10-15',
            'adults' => 1,
            'children' => 0,
        ]);
        $beforeWarningBooking = AccommodationBooking::create([
            'id' => 6,
            'trip_id' => $trip->id,
            'booking_ref_id' => $bookingRef->id,
            'accommodation_id' => $accommodation->id,
            'booking_status' => 'Confirmed',
            'check_in_date' => '2026-10-25',
            'check_out_date' => '2026-10-27',
            'adults' => 1,
            'children' => 0,
        ]);
        for ($index = 0; $index < 4; $index++) {
            AccommodationBooking::create([
                'trip_id' => $trip->id,
                'booking_ref_id' => $bookingRef->id,
                'accommodation_id' => $accommodation->id,
                'booking_status' => 'Confirmed',
                'check_in_date' => '2026-10-14',
                'check_out_date' => '2026-10-15',
                'adults' => 1,
                'children' => 0,
            ]);
        }
        DB::table('trip_action_deadline_settings')->insert([
            'action_type' => 'guest_details',
            'interval_hours' => 18,
            'country_id' => DB::table('countries')->where('iso_code', 'MU')->value('id'),
        ]);

        $response = $this->actingAs($operator, 'operator')->get(route('operator.trips.index'));

        $response->assertOk();
        $response->assertSee('>DUE</span>', false);
        $response->assertDontSee('OVERDUE');
        $viewTrip = collect($response->viewData('trips')->items())->firstWhere('id', $trip->id);
        $viewAction = collect($viewTrip->due_actions)->firstWhere('booking_id', $serviceBooking->id);
        $this->assertSame('guest_details', $viewAction['type']);
        $this->assertSame('due', $viewAction['status']);
        $this->assertSame('14 Oct 2026, 00:00 · UTC+00:00', $viewAction['target_display']);
        $response->assertSee('Complete guest details for accommodation booking #' . $serviceBooking->id);
        $response->assertSee('+ 4 more');
        $response->assertSee('aria-expanded="false" aria-controls="additional-due-actions-' . $trip->id . '"', false);
        $response->assertSee('<div id="additional-due-actions-' . $trip->id . '" hidden>', false);
        $response->assertSee('Show less', false);
        $this->assertCount(5, collect($viewTrip->due_actions)->where('status', 'due'));
        $pendingActions = collect($viewTrip->operations_overview['pending_actions']);
        foreach (collect($viewTrip->due_actions)->where('status', 'due') as $dueAction) {
            $this->assertTrue($pendingActions->contains('key', $dueAction['key']));
            $response->assertSee($pendingActions->firstWhere('key', $dueAction['key'])['label']);
        }
        $response->assertSee('<dialog id="pending-actions-' . $trip->id . '"', false);
        $this->assertSame(1, substr_count($response->getContent(), "const trigger = event.target.closest('[data-pending-actions-trigger]');"));
        $response->assertSee('dialog.showModal()', false);
        $response->assertSee('Accommodation #' . $beforeWarningBooking->id . ' — Complete Guest Details');
        $viewBeforeWarning = collect($viewTrip->due_actions)->firstWhere('booking_id', $beforeWarningBooking->id);
        $this->assertSame('scheduled', $viewBeforeWarning['status']);
        $this->assertTrue(collect($viewTrip->operations_overview['pending_actions'])
            ->contains(fn (array $action): bool => $action['booking_id'] === $beforeWarningBooking->id));
        $response->assertDontSee('The configured warning window has started.');
        $response->assertSee('Deadline: 14 Oct 2026, 00:00 · UTC+00:00');
    }

    public function test_operator_trip_listing_shows_due_independently_of_payment_and_never_after_target(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow('2026-10-13 06:00:00');
        DB::table('trip_action_deadline_settings')->insert([
            'action_type' => 'guest_details',
            'interval_hours' => 18,
            'country_id' => DB::table('countries')->where('iso_code', 'MU')->value('id'),
        ]);
        DB::table('businesses')->insert([
            'id' => 100,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $operator = Operator::create([
            'id' => 10,
            'name' => 'Operator One',
            'email' => 'one@example.test',
            'business_id' => 100,
        ]);
        $accommodation = Accommodation::create([
            'id' => 1,
            'operator_id' => 10,
            'business_id' => 100,
            'property_name' => 'Owned Property',
        ]);
        $createTrip = function (string $title, string $targetDate, string $paymentStatus) use ($accommodation): array {
            static $nextTripId = 901;
            $trip = Trip::forceCreate([
                'id' => $nextTripId++,
                'title' => $title,
            ]);
            $bookingRef = BookingRef::create([
                'trip_id' => $trip->id,
                'booking_ref_code' => strtoupper(str_replace(' ', '-', $title)),
            ]);
            $booking = \App\Models\Booking::create([
                'trip_id' => $trip->id,
                'booking_ref_id' => $bookingRef->id,
                'total_amount' => 100,
                'status' => 'confirmed',
                'booking_type' => 'open-group',
            ]);
            \App\Models\PaymentTransaction::create([
                'booking_id' => $booking->id,
                'booking_ref_id' => $bookingRef->id,
                'amount' => 100,
                'method' => 'card',
                'status' => $paymentStatus,
            ]);
            $serviceBooking = AccommodationBooking::create([
                'trip_id' => $trip->id,
                'booking_ref_id' => $bookingRef->id,
                'accommodation_id' => $accommodation->id,
                'booking_status' => 'Confirmed',
                'check_in_date' => $targetDate,
                'check_out_date' => Carbon::parse($targetDate)->addDay()->toDateString(),
                'adults' => 1,
                'children' => 0,
            ]);

            return [$trip, $serviceBooking];
        };

        [$dueTrip, $dueBooking] = $createTrip('Operator Due Trip', '2026-10-14', 'paid');
        [$pastTrip, $pastBooking] = $createTrip('Operator Past Target Trip', '2026-10-12', 'paid');
        [$blockedTrip, $blockedBooking] = $createTrip('Operator Payment Blocked Trip', '2026-10-14', 'pending');

        $response = $this->actingAs($operator, 'operator')->get(route('operator.trips.index'));

        $response->assertOk();
        $response->assertSee('>DUE</span>', false);
        $response->assertDontSee('OVERDUE');
        $viewTrips = collect($response->viewData('trips')->items());
        $viewActionFor = function (Trip $trip, int $bookingId) use ($viewTrips): ?array {
            $viewTrip = $viewTrips->firstWhere('id', $trip->id);
            $this->assertNotNull($viewTrip, 'Expected trip in operator view: ' . $trip->title);
            return collect($viewTrip->due_actions)->firstWhere('booking_id', $bookingId);
        };

        $dueAction = $viewActionFor($dueTrip, $dueBooking->id);
        $pastAction = $viewActionFor($pastTrip, $pastBooking->id);
        $blockedDueAction = $viewActionFor($blockedTrip, $blockedBooking->id);
        $blockedTripData = $viewTrips->firstWhere('id', $blockedTrip->id);
        $blockedNextAction = collect($blockedTripData->next_actions)->firstWhere('booking_id', 0);

        $this->assertSame('due', $dueAction['status'] ?? null, json_encode($dueAction));
        $response->assertDontSee('aria-controls="additional-due-actions-' . $dueTrip->id . '"', false);
        $this->assertSame('na', $pastAction['status'] ?? null, json_encode($pastAction));
        $this->assertNull($pastAction['target_display']);
        $this->assertNull($pastAction['due_since_display']);
        $this->assertSame('payment', $blockedNextAction['type']);
        $this->assertSame('due', $blockedDueAction['status'] ?? null, json_encode($blockedDueAction));
        $this->assertSame('na', collect($blockedTripData->next_actions)->first()['status']);
    }
}
