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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OperatorTripListingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('PRAGMA foreign_keys = OFF');

        foreach (['bli_traveller_allocations', 'booking_guests', 'activity_scheduling_timeslots', 'travellers', 'traveler_accounts', 'transport_bookings', 'transports', 'activity_bookings', 'activities', 'accommodation_bookings', 'accommodation_rooms', 'accommodations', 'booking_refs', 'trips', 'operators', 'operator_users'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }

        DB::statement('PRAGMA foreign_keys = ON');

        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->json('package_policy')->nullable();
            $table->json('group_policy')->nullable();
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

        Schema::create('travellers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->string('name');
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
        $operator = Operator::create(['id' => 10, 'name' => 'Operator One', 'email' => 'one@example.test', 'business_id' => 100]);
        $otherOperator = Operator::create(['id' => 20, 'name' => 'Operator Two', 'email' => 'two@example.test', 'business_id' => 200]);

        $ownedAccommodation = Accommodation::create(['id' => 1, 'operator_id' => 10, 'business_id' => 100, 'property_name' => 'Owned Property']);
        $room = AccommodationRoom::create(['id' => 1, 'accommodation_id' => 1, 'name' => 'Deluxe', 'room_name' => 'Standard yes']);
        $ownedActivity = Activity::create(['id' => 1, 'operator_id' => 10, 'activity_name' => 'Owned Activity']);
        $ownedTransport = Transport::create(['id' => 1, 'operator_id' => 10, 'vehicle_display_name' => 'Owned Bus']);
        $otherAccommodation = Accommodation::create(['id' => 2, 'operator_id' => 20, 'business_id' => 200, 'property_name' => 'Other Property']);

        $accountHolder = \App\Models\TravelerAccount::create(['full_name' => 'John Smith']);
        $visibleTrip = Trip::create(['id' => 1, 'traveler_account_id' => $accountHolder->id, 'title' => 'Visible Trip']);
        $hiddenTrip = Trip::create(['id' => 2, 'title' => 'Hidden Trip']);
        $mixedTrip = Trip::create(['id' => 3, 'traveler_account_id' => $accountHolder->id, 'title' => 'Mixed Trip']);
        $visibleTrip->travellers()->createMany([
            ['name' => 'John Smith', 'relationship' => 'self'],
            ['name' => 'Sarah Smith', 'relationship' => 'guest'],
        ]);
        $mixedTrip->travellers()->createMany([
            ['name' => 'Sarah Jones', 'relationship' => 'lead'],
            ['name' => 'David Jones', 'relationship' => 'guest'],
        ]);

        $visibleRef = BookingRef::create(['id' => 1, 'trip_id' => 1, 'booking_ref_code' => 'BR-OP-001']);
        $mixedRef = BookingRef::create(['id' => 2, 'trip_id' => 3, 'booking_ref_code' => 'BR-OP-003']);

        AccommodationBooking::create(['id' => 1, 'trip_id' => 1, 'booking_ref_id' => $visibleRef->id, 'accommodation_id' => 1, 'room_id' => $room->id, 'booking_reference' => 'ACC-001', 'booking_status' => 'Confirmed', 'check_in_date' => '2026-10-01', 'check_out_date' => '2026-10-03', 'rooms_booked' => 2, 'adults' => 2, 'children' => 1, 'total_amount' => 120.00]);
        \App\Models\BookingGuest::create(['booking_id' => 1, 'booking_type' => 'accommodation', 'guest_number' => 1, 'first_name' => 'John', 'last_name' => 'Smith']);
        ActivityBooking::create(['id' => 1, 'trip_id' => 1, 'booking_ref_id' => $visibleRef->id, 'activity_id' => 1, 'booking_reference' => 'ACT-001', 'booking_status' => 'Confirmed', 'activity_date' => '2026-10-02', 'activity_time_slot_id' => 1, 'adults' => 2, 'children' => 1, 'total_amount' => 80.00]);
        DB::table('activity_scheduling_timeslots')->insert(['timeslot_id' => 1, 'activity_id' => 1, 'start_time' => '09:00:00', 'end_time' => '11:00:00', 'created_at' => now(), 'updated_at' => now()]);
        \App\Models\BookingGuest::create(['booking_id' => 1, 'booking_type' => 'activity', 'guest_number' => 1, 'first_name' => 'Sarah', 'last_name' => 'Smith']);
        $visibleTransportBooking = TransportBooking::create(['id' => 1, 'transport_id' => 1, 'booking_reference' => 'TRN-001', 'booking_status' => 'Confirmed', 'route_from' => 'Airport', 'route_to' => 'Hotel', 'pickup_date' => '2026-10-01', 'pickup_time' => '08:30:00', 'total_passengers' => 3, 'adults' => 2, 'children' => 1, 'traveler_first_name' => 'John', 'traveler_last_name' => 'Smith', 'transport_vehicle_id' => 1, 'total_amount' => 90.00]);
        $visibleTransportBooking->forceFill(['trip_id' => 1, 'booking_ref_id' => $visibleRef->id])->save();
        \App\Models\BookingGuest::create(['booking_id' => 1, 'booking_type' => 'transport', 'guest_number' => 1, 'first_name' => 'Sarah', 'last_name' => 'Smith']);
        $secondVisibleRef = BookingRef::create(['id' => 3, 'trip_id' => 1, 'booking_ref_code' => 'BR-OP-001-B']);
        ActivityBooking::create(['id' => 3, 'trip_id' => 1, 'booking_ref_id' => $secondVisibleRef->id, 'activity_id' => 1, 'booking_reference' => 'ACT-001-B', 'booking_status' => 'Confirmed', 'total_amount' => 40.00]);

        AccommodationBooking::create(['id' => 2, 'trip_id' => 3, 'booking_ref_id' => $mixedRef->id, 'accommodation_id' => 2, 'booking_reference' => 'ACC-OTHER', 'booking_status' => 'Cancelled', 'total_amount' => 60.00]);
        ActivityBooking::create(['id' => 2, 'trip_id' => 3, 'booking_ref_id' => $mixedRef->id, 'activity_id' => 1, 'booking_reference' => 'ACT-OWN', 'booking_status' => 'Confirmed', 'total_amount' => 50.00]);

        AccommodationBooking::create(['id' => 3, 'trip_id' => 2, 'booking_ref_id' => 99, 'accommodation_id' => 2, 'booking_reference' => 'ACC-HIDDEN', 'booking_status' => 'Confirmed', 'total_amount' => 70.00]);

        $this->actingAs($operator, 'operator');

        $response = $this->get(route('operator.trips.index'));

        $response->assertOk();
        $response->assertSee('Visible Trip');
        $response->assertSee('Mixed Trip');
        $response->assertSee('aria-label="Expand trip details"', false);
        $response->assertSee('>+</button>', false);
        $response->assertSee('aria-expanded="false"', false);
        $response->assertSee('id="operator-trip-1" hidden', false);
        $response->assertSee('Next Step:</strong> Awaiting Payment', false);
        $response->assertDontSee('background:#16a34a');
        $response->assertDontSee('Hidden Trip');
        $response->assertSee('1 Booking Refs · 1 BLIs');
        $response->assertSee('BR-OP-001');
        $response->assertSee('2 Booking Refs · 4 BLIs');
        $response->assertSee('Total Amount USD330');
        $response->assertSee('Total Amount USD50');
        $response->assertSee('Confirmed: 4');
        $response->assertDontSee('Cancelled: 1');
        $response->assertSee('Booking Status');
        $response->assertSee('Pending</span>', false);
        $response->assertSee('Travel Party Size: 2');
        $response->assertSee('John Smith');
        $response->assertSee('Account Holder not travelling · Responsible: Sarah Jones');
        $response->assertSee('Standard yes');
        $response->assertSee('/operator/accommodation/bookings/1');
        $response->assertSee('/operator/activity/bookings/1');
        $response->assertSee('/operator/transport/1/bookings/1');
        $response->assertSee('Check-in:</strong> 01/10/2026', false);
        $response->assertSee('Check-out:</strong> 03/10/2026', false);
        $response->assertSee('Rooms:</strong> 2', false);
        $response->assertSee('Participant Names:</strong> John Smith', false);
        $response->assertSee('Date:</strong> 02/10/2026', false);
        $response->assertSee('Time:</strong> 09:00:00 - 11:00:00', false);
        $response->assertSee('Participants:</strong> Adults: 2 · Children: 1 · Total: 3', false);
        $response->assertSee('Participant Names:</strong> Sarah Smith', false);
        $response->assertSee('Passengers:</strong> 3', false);
        $response->assertSee('Pickup:</strong> 01/10/2026 08:30:00', false);
        $response->assertSee('Passenger Names:</strong> Sarah Smith', false);
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

        $otherOperatorTripSearch = $this->get(route('operator.trips.index', [
            'trip' => '2',
        ]));
        $otherOperatorTripSearch->assertDontSee('Hidden Trip');
    }
}
