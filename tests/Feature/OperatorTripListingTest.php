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

        foreach (['transport_bookings', 'transports', 'activity_bookings', 'activities', 'accommodation_bookings', 'accommodation_rooms', 'accommodations', 'booking_refs', 'trips', 'operators', 'operator_users'] as $table) {
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

        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('priority', 20)->default('normal');
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

        $visibleTrip = Trip::create(['id' => 1, 'title' => 'Visible Trip']);
        $hiddenTrip = Trip::create(['id' => 2, 'title' => 'Hidden Trip']);
        $mixedTrip = Trip::create(['id' => 3, 'title' => 'Mixed Trip']);

        $visibleRef = BookingRef::create(['id' => 1, 'trip_id' => 1, 'booking_ref_code' => 'BR-OP-001']);
        $mixedRef = BookingRef::create(['id' => 2, 'trip_id' => 3, 'booking_ref_code' => 'BR-OP-003']);

        AccommodationBooking::create(['id' => 1, 'trip_id' => 1, 'booking_ref_id' => $visibleRef->id, 'accommodation_id' => 1, 'room_id' => $room->id, 'booking_reference' => 'ACC-001', 'booking_status' => 'Confirmed', 'total_amount' => 120.00]);
        ActivityBooking::create(['id' => 1, 'trip_id' => 1, 'booking_ref_id' => $visibleRef->id, 'activity_id' => 1, 'booking_reference' => 'ACT-001', 'booking_status' => 'Confirmed', 'total_amount' => 80.00]);
        TransportBooking::create(['id' => 1, 'trip_id' => 1, 'booking_ref_id' => $visibleRef->id, 'transport_id' => 1, 'booking_reference' => 'TRN-001', 'booking_status' => 'Confirmed', 'route_from' => 'Airport', 'route_to' => 'Hotel', 'total_amount' => 90.00]);

        AccommodationBooking::create(['id' => 2, 'trip_id' => 3, 'booking_ref_id' => $mixedRef->id, 'accommodation_id' => 2, 'booking_reference' => 'ACC-OTHER', 'booking_status' => 'Confirmed', 'total_amount' => 60.00]);
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
        $response->assertSee('BR-OP-001');
        $response->assertSee('Standard yes');
        $response->assertSee('/operator/accommodation/bookings/1');
        $response->assertSee('/operator/activity/bookings/1');
        $response->assertSee('/operator/transport/1/bookings/1');
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
