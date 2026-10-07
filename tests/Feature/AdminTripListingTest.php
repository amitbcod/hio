<?php

namespace Tests\Feature;

use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\BookingRef;
use App\Models\TravelerAccount;
use App\Models\TransportBooking;
use App\Models\Trip;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminTripListingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('PRAGMA foreign_keys = OFF');

        foreach (['transport_bookings', 'transports', 'activity_bookings', 'activities', 'accommodation_bookings', 'accommodation_rooms', 'accommodations', 'booking_refs', 'trips', 'traveler_accounts'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }

        DB::statement('PRAGMA foreign_keys = ON');

        Schema::create('traveler_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->timestamps();
        });

        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('traveler_account_id')->nullable();
            $table->string('title')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_refs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->string('booking_ref_code')->nullable();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('booking_ref_id')->nullable();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->nullable();
            $table->string('booking_type')->nullable();
            $table->boolean('is_admin_created')->default(false);
            $table->timestamps();
        });

        Schema::create('booking_line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('service_type')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('trip_type')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('booking_ref_id')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('method')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->string('property_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('accommodation_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accommodation_id')->nullable();
            $table->string('name')->nullable();
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
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
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
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('transports', function (Blueprint $table) {
            $table->id();
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
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        $traveler = TravelerAccount::create(['full_name' => 'Jane Doe']);

        $trip = Trip::create([
            'traveler_account_id' => $traveler->id,
            'title' => 'Trip 202',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-03',
            'status' => 'planned',
        ]);

        $bookingRef = BookingRef::create([
            'trip_id' => $trip->id,
            'booking_ref_code' => 'BR-202-20261001-0001',
        ]);

        $booking = \App\Models\Booking::create([
            'trip_id' => $trip->id,
            'booking_ref_id' => $bookingRef->id,
            'total_amount' => 410.00,
            'status' => 'pending',
            'booking_type' => 'open-group',
            'is_admin_created' => true,
        ]);

        \App\Models\BookingLineItem::create([
            'booking_id' => $booking->id,
            'service_type' => 'package',
            'service_id' => 1,
            'trip_type' => 'ONE_WAY',
            'quantity' => 1,
            'price' => 410.00,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-03',
            'status' => 'active',
        ]);

        DB::table('payment_transactions')->insert([
            'booking_id' => $booking->id,
            'booking_ref_id' => $bookingRef->id,
            'amount' => 410.00,
            'method' => 'bank_transfer',
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $accommodation = \App\Models\Accommodation::create(['property_name' => 'Ocean View Hotel']);
        $room = \App\Models\AccommodationRoom::create(['accommodation_id' => $accommodation->id, 'name' => 'Deluxe Room']);

        AccommodationBooking::create([
            'trip_id' => $trip->id,
            'booking_ref_id' => $bookingRef->id,
            'accommodation_id' => $accommodation->id,
            'room_id' => $room->id,
            'booking_reference' => 'ACC-202-1',
            'booking_status' => 'Confirmed',
            'check_in_date' => '2026-10-01',
            'check_out_date' => '2026-10-02',
            'total_amount' => 200.00,
        ]);

        $activity = \App\Models\Activity::create(['activity_name' => 'Island Tour']);

        ActivityBooking::create([
            'trip_id' => $trip->id,
            'booking_ref_id' => $bookingRef->id,
            'activity_id' => $activity->id,
            'booking_reference' => 'ACT-202-1',
            'booking_status' => 'Confirmed',
            'activity_date' => '2026-10-02',
            'total_amount' => 120.00,
        ]);

        $transport = \App\Models\Transport::create(['vehicle_display_name' => 'Airport Shuttle']);

        TransportBooking::create([
            'trip_id' => $trip->id,
            'booking_ref_id' => $bookingRef->id,
            'transport_id' => $transport->id,
            'booking_reference' => 'TRS-202-1',
            'booking_status' => 'Confirmed',
            'route_from' => 'Airport',
            'route_to' => 'Hotel',
            'pickup_date' => '2026-10-01',
            'total_amount' => 90.00,
        ]);
    }

    public function test_admin_trip_listing_shows_grouped_service_bookings_and_links(): void
    {
        $response = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index'));

        $response->assertOk();
        $response->assertSee('Trip 202');
        $response->assertSee('Group Trip');
        $response->assertSee('BR-202-20261001-0001');
        $response->assertSee('Accommodation');
        $response->assertSee('Activity');
        $response->assertSee('Transport');
        $response->assertSee('Assign Driver & Vehicle');
        $response->assertSee('/admin/accommodation/bookings/1');
        $response->assertSee('/admin/activity/bookings/1');
        $response->assertSee('/admin/transport/bookings/1');
    }
}
