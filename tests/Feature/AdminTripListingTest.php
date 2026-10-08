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

        foreach (['bli_traveller_allocations', 'booking_guests', 'activity_scheduling_timeslots', 'travellers', 'payment_transactions', 'booking_line_items', 'bookings', 'transport_bookings', 'transports', 'activity_bookings', 'activities', 'accommodation_bookings', 'accommodation_rooms', 'accommodations', 'booking_refs', 'trips', 'traveler_accounts'] as $table) {
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

        Schema::create('bli_traveller_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bli_id');
            $table->unsignedBigInteger('traveller_id');
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
            $table->unsignedBigInteger('transport_booking_id')->nullable();
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
            $table->string('settlement_status')->nullable();
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
        $room = \App\Models\AccommodationRoom::create(['accommodation_id' => $accommodation->id, 'name' => 'Deluxe Room', 'room_name' => 'Deluxe Room Type']);

        AccommodationBooking::create([
            'trip_id' => $trip->id,
            'booking_ref_id' => $bookingRef->id,
            'accommodation_id' => $accommodation->id,
            'room_id' => $room->id,
            'booking_reference' => 'ACC-202-1',
            'booking_status' => 'Cancelled',
            'check_in_date' => '2026-10-01',
            'check_out_date' => '2026-10-02',
            'guest_name' => 'Jane Doe',
            'rooms_booked' => 2,
            'adults' => 2,
            'children' => 1,
            'total_amount' => 200.00,
        ]);
        \App\Models\BookingGuest::create(['booking_id' => 1, 'booking_type' => 'accommodation', 'guest_number' => 1, 'first_name' => 'Jane', 'last_name' => 'Doe']);

        $activity = \App\Models\Activity::create(['activity_name' => 'Island Tour']);

        ActivityBooking::create([
            'trip_id' => $trip->id,
            'booking_ref_id' => $bookingRef->id,
            'activity_id' => $activity->id,
            'booking_reference' => 'ACT-202-1',
            'booking_status' => 'Processing',
            'activity_date' => '2026-10-02',
            'activity_time_slot_id' => 1,
            'guest_name' => 'Jane Doe',
            'adults' => 2,
            'children' => 1,
            'total_amount' => 120.00,
        ]);
        DB::table('activity_scheduling_timeslots')->insert([
            'timeslot_id' => 1,
            'activity_id' => $activity->id,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \App\Models\BookingGuest::create(['booking_id' => 1, 'booking_type' => 'activity', 'guest_number' => 1, 'first_name' => 'Alex', 'last_name' => 'Doe']);

        $transport = \App\Models\Transport::create(['vehicle_display_name' => 'Airport Shuttle']);

        $transportBooking = TransportBooking::create([
            'trip_id' => $trip->id,
            'booking_ref_id' => $bookingRef->id,
            'transport_id' => $transport->id,
            'booking_reference' => 'TRS-202-1',
            'booking_status' => 'Confirmed',
            'route_from' => 'Airport',
            'route_to' => 'Hotel',
            'pickup_date' => '2026-10-01',
            'pickup_time' => '08:30:00',
            'guest_name' => 'Jane Doe',
            'traveler_first_name' => 'Jane',
            'traveler_last_name' => 'Doe',
            'total_passengers' => 3,
            'adults' => 2,
            'children' => 1,
            'transport_vehicle_id' => 1,
            'total_amount' => 90.00,
        ]);
        $transportBooking->forceFill([
            'trip_id' => $trip->id,
            'booking_ref_id' => $bookingRef->id,
        ])->save();
        \App\Models\BookingLineItem::create([
            'booking_id' => $booking->id,
            'service_type' => 'transport',
            'service_id' => $transport->id,
            'transport_booking_id' => $transportBooking->id,
            'quantity' => 3,
            'price' => 90.00,
        ]);
        \App\Models\BookingGuest::create(['booking_id' => $transportBooking->id, 'booking_type' => 'transport', 'guest_number' => 1, 'first_name' => 'Alex', 'last_name' => 'Doe']);
    }

    public function test_admin_trip_listing_shows_grouped_service_bookings_and_links(): void
    {
        $trip = Trip::firstOrFail();
        $travellers = $trip->travellers()->createMany([
            ['name' => 'Jane Doe', 'relationship' => 'self'],
            ['name' => 'Alex Doe', 'relationship' => 'guest'],
        ]);
        $transportLineItem = \App\Models\BookingLineItem::where('transport_booking_id', 1)->firstOrFail();
        DB::table('bli_traveller_allocations')->insert([
            ['bli_id' => $transportLineItem->id, 'traveller_id' => $travellers[0]->id],
            ['bli_id' => $transportLineItem->id, 'traveller_id' => $travellers[1]->id],
        ]);

        $response = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index'));

        $response->assertOk();
        $response->assertSee('data-trip-toggle', false);
        $response->assertSee('aria-label="Expand trip details"', false);
        $response->assertSee('colspan="8"', false);
        $response->assertSee('Trip 202');
        $response->assertSee('Group Trip');
        $response->assertSee('Travel Party Size: 2');
        $response->assertSee('Jane Doe');
        $response->assertSee('1 Booking Refs · 3 BLIs');
        $response->assertSee('Total Amount USD410');
        $response->assertSee('Confirmed: 1');
        $response->assertSee('Processing: 1');
        $response->assertSee('Cancelled: 1');
        $response->assertDontSee('<th>Status</th>', false);
        $response->assertSee('<th>Booking Status</th>', false);
        $response->assertSee('background:#fef3c7; color:#92400e;', false);
        $response->assertSee('background:#dcfce7; color:#166534;', false);
        $response->assertSee('background:#fee2e2; color:#991b1b;', false);
        $response->assertSee('Paid</span>', false);
        $response->assertSee('BR-202-20261001-0001');
        $response->assertSee('Payment Status');
        $response->assertSee('Paid');
        $response->assertSee('Accommodation');
        $response->assertSee('Activity');
        $response->assertSee('Transport');
        $response->assertSee('Assign Driver & Vehicle');
        $response->assertSee('Next Step:', false);
        $response->assertDontSee('btn-success');
        $response->assertSee('/admin/accommodation/bookings/1');
        $response->assertSee('/admin/activity/bookings/1');
        $response->assertSee('/admin/transport/bookings/1');
        $response->assertSee('Deluxe Room Type');
        $response->assertSee('Rooms:</strong> 2', false);
        $response->assertSee('Participants:</strong> Adults: 2 · Children: 1 · Total: 3', false);
        $response->assertSee('Participant Names:</strong> Jane Doe', false);
        $response->assertSee('Check-in:</strong> 01/10/2026', false);
        $response->assertSee('Check-out:</strong> 02/10/2026', false);
        $response->assertSee('Date:</strong> 02/10/2026', false);
        $response->assertSee('Time:</strong> 09:00:00 - 11:00:00', false);
        $response->assertSee('Participant Names:</strong> Alex Doe', false);
        $response->assertSee('Passenger Names:</strong> Alex Doe, Jane Doe', false);
        $response->assertSee('Vehicles:</strong> 1', false);
    }

    public function test_admin_trip_listing_filters_all_fields_together(): void
    {
        $traveler = TravelerAccount::create(['full_name' => 'Alex Smith']);
        $trip = Trip::create([
            'traveler_account_id' => $traveler->id,
            'title' => 'Island Escape',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-05',
            'status' => 'planned',
        ]);
        $bookingRef = BookingRef::create([
            'trip_id' => $trip->id,
            'booking_ref_code' => 'BR-OTHER-20261101',
        ]);
        $booking = \App\Models\Booking::create([
            'trip_id' => $trip->id,
            'booking_ref_id' => $bookingRef->id,
            'total_amount' => 100,
            'status' => 'pending',
            'booking_type' => 'package',
        ]);
        \App\Models\PaymentTransaction::create([
            'booking_id' => $booking->id,
            'booking_ref_id' => $bookingRef->id,
            'amount' => 100,
            'method' => 'bank_transfer',
            'status' => 'pending',
            'settlement_status' => 'pending_verification',
        ]);

        $normalTrip = Trip::create([
            'title' => 'Normal Trip',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-11',
            'status' => 'planned',
        ]);

        $response = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index', [
            'from_date' => '2026-10-01',
            'to_date' => '2026-10-03',
            'payment_status' => 'paid',
            'trip_type' => 'Group Trip',
            'traveller' => 'jane',
            'booking_reference' => 'br-202-',
            'trip' => '202',
        ]));

        $response->assertOk();
        $response->assertSee('Trip 202');
        $response->assertDontSee('Island Escape');
        $response->assertSee('value="pending_verification"', false);
        $response->assertSee('Pending Verification');

        $paymentFilter = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index', [
            'payment_status' => 'pending_verification',
        ]));
        $paymentFilter->assertSee('Island Escape');
        $paymentFilter->assertSee('Pending Verification</span>', false);
        $paymentFilter->assertSee('Pending Verification</span>', false);
        $paymentFilter->assertSee('background:#fef3c7; color:#92400e;', false);
        $paymentFilter->assertDontSee('Trip 202');

        $travellerFilter = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index', [
            'traveller' => 'SMITH',
        ]));
        $travellerFilter->assertSee('Island Escape');
        $travellerFilter->assertDontSee('Trip 202');

        $referenceFilter = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index', [
            'booking_reference' => 'ACC-202-1',
        ]));
        $referenceFilter->assertSee('Trip 202');
        $referenceFilter->assertDontSee('Island Escape');

        $tripFilter = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index', [
            'trip' => 'Island Escape',
        ]));
        $tripFilter->assertSee('Island Escape');
        $tripFilter->assertDontSee('Trip 202');

        $fromDateFilter = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index', [
            'from_date' => '2026-10-04',
        ]));
        $fromDateFilter->assertSee('Island Escape');
        $fromDateFilter->assertDontSee('Trip 202');

        $toDateFilter = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index', [
            'to_date' => '2026-10-02',
        ]));
        $toDateFilter->assertSee('Trip 202');
        $toDateFilter->assertDontSee('Island Escape');

        $packageTypeFilter = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index', [
            'trip_type' => 'Package Trip',
        ]));
        $packageTypeFilter->assertSee('Island Escape');
        $packageTypeFilter->assertDontSee('Trip 202');

        $normalTypeFilter = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index', [
            'trip_type' => 'Trip',
        ]));
        $normalTypeFilter->assertSee('Normal Trip');
        $normalTypeFilter->assertDontSee('Island Escape');

        $reset = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index'));
        $reset->assertSee('Trip 202');
        $reset->assertSee('Island Escape');
        $reset->assertSee('Normal Trip');
    }

    public function test_admin_can_update_trip_priority_and_visibly_persist_it(): void
    {
        $traveler = TravelerAccount::create(['full_name' => 'Sam Brown']);
        $trip = Trip::create([
            'traveler_account_id' => $traveler->id,
            'title' => 'Priority Trip',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-03',
            'status' => 'planned',
            'priority' => 'normal',
        ]);

        $response = $this->withSession(['admin_id' => 1])->from(route('admin.trips.index'))->post(route('admin.trips.update-priority', $trip), [
            'priority' => 'urgent',
        ]);

        $response->assertRedirect();
        $trip->refresh();
        $this->assertSame('urgent', $trip->priority);
        $this->assertSame('Urgent', $trip->priority_label);

        $view = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index'));
        $view->assertOk();
        $view->assertSee('Priority Trip');
        $view->assertSee('Urgent');
    }
}
