<?php

namespace Tests\Feature;

use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\BookingRef;
use App\Models\TravelerAccount;
use App\Models\TransportBooking;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminTripListingTest extends TestCase
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

        foreach (['trip_action_deadlines', 'trip_action_deadline_settings', 'bli_traveller_allocations', 'booking_guests', 'activity_scheduling_timeslots', 'travellers', 'payment_transactions', 'booking_line_items', 'bookings', 'transport_bookings', 'transports', 'activity_bookings', 'activities', 'accommodation_bookings', 'accommodation_rooms', 'accommodations', 'booking_refs', 'trips', 'traveler_accounts'] as $table) {
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

        if (!Schema::hasColumn('admin_users', 'admin_type')) {
            Schema::table('admin_users', fn (Blueprint $table) => $table->string('admin_type', 20)->default('global'));
        }
        if (!Schema::hasColumn('admin_users', 'country_id')) {
            Schema::table('admin_users', fn (Blueprint $table) => $table->unsignedBigInteger('country_id')->nullable());
        }
        DB::table('admin_users')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Trip Listing Admin',
                'email' => 'trip-listing-admin@example.test',
                'password_hash' => 'test',
                'role' => 'super_admin',
                'status' => 'active',
                'admin_type' => 'global',
                'country_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

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
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->nullable();
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
            $table->string('relation')->nullable();
            $table->date('dob')->nullable();
            $table->string('nationality')->nullable();
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
        \App\Models\BookingGuest::create([
            'booking_id' => 1,
            'booking_type' => 'accommodation',
            'guest_number' => 2,
            'first_name' => 'Sarah',
            'last_name' => 'Doe',
        ]);
        \App\Models\BookingGuest::create([
            'booking_id' => 1,
            'booking_type' => 'activity',
            'guest_number' => 2,
            'first_name' => 'Jamie',
            'last_name' => 'Doe',
        ]);
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
        $response->assertSee('colspan="10"', false);
        $response->assertSeeInOrder([
            '>Trip</th>',
            '>Travel / next service</th>',
            '>Authority and Travel Party</th>',
            '>Booking</th>',
            '>Traveller</th>',
            '>Payment</th>',
            '>Attention / Priority</th>',
            '>Next Action</th>',
            '>Due / Overdue</th>',
        ], false);
        $response->assertSee('Trip ID: ' . $trip->getKey());
        $response->assertSee('View Pending Actions');
        $response->assertSee('<dialog id="pending-actions-' . $trip->id . '"', false);
        $response->assertSee('dialog.showModal()', false);
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
        $response->assertSee('<th>Booking</th>', false);
        $response->assertSee('admin-status-badge--warning', false);
        $response->assertSee('admin-status-badge--success', false);
        $response->assertSee('admin-status-badge--danger', false);
        $response->assertSee('admin-status-badge--success">Paid</span>', false);
        $response->assertSee('admin-status-badge--danger">Cancelled</span>', false);
        $response->assertSee('admin-status-badge--warning">Processing</span>', false);
        $response->assertSee('admin-status-badge--success">Confirmed</span>', false);
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
        $response->assertSee('Participant Names:</strong> Jane Doe, Sarah Doe', false);
        $response->assertSee('Check-in:</strong> 01/10/2026', false);
        $response->assertSee('Check-out:</strong> 02/10/2026', false);
        $response->assertSee('Date:</strong> 02/10/2026', false);
        $response->assertSee('Time:</strong> 09:00:00 - 11:00:00', false);
        $response->assertSee('Participant Names:</strong> Alex Doe, Jamie Doe', false);
        $response->assertSee('Passenger Names:</strong> Alex Doe, Jane Doe', false);
        $response->assertSee('Vehicles:</strong> 1', false);
    }

    public function test_admin_trip_listing_uses_ordered_service_dates_when_trip_dates_are_missing(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow('2026-10-15 12:00:00');

        $trip = Trip::create(['title' => 'Transport Dates Trip']);
        $transport = \App\Models\Transport::create(['vehicle_display_name' => 'Airport Shuttle']);
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

        $response = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index'));

        $response->assertOk();
        $response->assertSee('20 Oct 2026 - 23 Oct 2026');
        $response->assertSee('20 Oct 2026 – 23 Oct 2026');
        $response->assertSee('20 Oct 2026, 16:00 · UTC+00:00');
        $viewTrip = collect($response->viewData('trips')->items())->firstWhere('id', $trip->id);
        $this->assertSame('20 Oct 2026', $viewTrip->operations_overview['travel_dates']['start']);
        $this->assertSame('23 Oct 2026', $viewTrip->operations_overview['travel_dates']['end']);
        $this->assertSame('20 Oct 2026, 16:00 · UTC+00:00', $viewTrip->operations_overview['next_service']['when']);
    }

    public function test_admin_trip_listing_keeps_an_earlier_activity_as_the_next_service(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow('2026-10-15 12:00:00');

        $trip = Trip::create(['title' => 'Activity Before Transport']);
        $activity = \App\Models\Activity::create(['activity_name' => 'Island Tour']);
        ActivityBooking::create([
            'trip_id' => $trip->id,
            'activity_id' => $activity->id,
            'booking_status' => 'Confirmed',
            'activity_date' => '2026-10-19',
        ]);
        $transport = \App\Models\Transport::create(['vehicle_display_name' => 'Airport Shuttle']);
        $transportBooking = TransportBooking::create([
            'transport_id' => $transport->id,
            'booking_status' => 'Confirmed',
            'pickup_date' => '2026-10-20',
            'pickup_time' => '16:00:00',
        ]);
        $transportBooking->forceFill(['trip_id' => $trip->id])->save();

        $response = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index'));

        $response->assertOk();
        $response->assertSee('Island Tour');
        $response->assertSee('19 Oct 2026 - 20 Oct 2026');
        $viewTrip = collect($response->viewData('trips')->items())->firstWhere('id', $trip->id);
        $this->assertSame('Island Tour', $viewTrip->operations_overview['next_service']['name']);
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
            'traveller_information' => 'missing',
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
        $paymentFilter->assertSee('admin-status-badge--warning">Pending Verification</span>', false);
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

    public function test_admin_trip_listing_filters_missing_and_complete_traveller_information(): void
    {
        $accountHolder = TravelerAccount::firstOrFail();
        $completeTrip = Trip::create([
            'traveler_account_id' => $accountHolder->id,
            'title' => 'Complete Traveller Trip',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-02',
        ]);
        $completeTrip->travellers()->create([
            'name' => 'Jane Doe',
            'relationship' => 'self',
            'date_of_birth' => '1985-01-01',
        ]);

        $completeBooking = AccommodationBooking::create([
            'trip_id' => $completeTrip->id,
            'accommodation_id' => \App\Models\Accommodation::firstOrFail()->id,
            'booking_reference' => 'ACC-COMPLETE',
            'booking_status' => 'Confirmed',
            'adults' => 1,
            'children' => 0,
        ]);
        \App\Models\BookingGuest::create([
            'booking_id' => $completeBooking->id,
            'booking_type' => 'accommodation',
            'guest_number' => 1,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'relation' => 'self',
            'dob' => '1985-01-01',
            'nationality' => 'Mauritian',
        ]);

        $missingResponse = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index', [
            'traveller_information' => 'missing',
        ]));
        $missingResponse->assertOk();
        $missingResponse->assertSee('Trip 202');
        $missingResponse->assertDontSee('Complete Traveller Trip');

        $completeResponse = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index', [
            'traveller_information' => 'complete',
        ]));
        $completeResponse->assertOk();
        $completeResponse->assertSee('Complete Traveller Trip');
        $completeResponse->assertDontSee('Trip 202');
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
        $view->assertSee('<dialog id="pending-actions-' . $trip->id . '"', false);
        $view->assertSee('No pending actions for this trip.');
        $view->assertSee('onchange="this.form.requestSubmit()"', false);
        $view->assertDontSee('>Save</button>', false);
    }

    public function test_admin_status_badges_normalize_status_case_and_share_colors(): void
    {
        foreach ([
            'pAiD' => 'success',
            'Verified & Settled' => 'success',
            'CONFIRMED' => 'success',
            'Pending' => 'warning',
            'PROCESSING' => 'warning',
            'Cancel' => 'danger',
            'Cancelled' => 'danger',
        ] as $status => $tone) {
            $this->view('admin.partials._status_badge', ['status' => $status])
                ->assertSee('admin-status-badge--' . $tone, false)
                ->assertSee($status);
        }
    }

    public function test_admin_trip_listing_passes_due_for_an_action_inside_its_configured_window(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow('2026-10-13 06:00:00');
        DB::table('trip_action_deadline_settings')->insert([
            'action_type' => 'guest_details',
            'interval_hours' => 18,
            'country_id' => DB::table('countries')->where('iso_code', 'MU')->value('id'),
        ]);

        $trip = Trip::firstOrFail();
        $trip->start_date = '2026-10-13';
        $trip->end_date = '2026-10-15';
        $trip->save();
        $accommodation = \App\Models\Accommodation::create(['property_name' => 'Due Window Hotel']);
        $booking = AccommodationBooking::create([
            'trip_id' => $trip->id,
            'accommodation_id' => $accommodation->id,
            'booking_status' => 'Confirmed',
            'check_in_date' => '2026-10-14',
            'check_out_date' => '2026-10-15',
            'adults' => 1,
            'children' => 0,
        ]);
        $secondBooking = AccommodationBooking::create([
            'trip_id' => $trip->id,
            'accommodation_id' => $accommodation->id,
            'booking_status' => 'Confirmed',
            'check_in_date' => '2026-10-14',
            'check_out_date' => '2026-10-15',
            'adults' => 1,
            'children' => 0,
        ]);
        $beforeWarningBooking = AccommodationBooking::create([
            'trip_id' => $trip->id,
            'accommodation_id' => $accommodation->id,
            'booking_status' => 'Confirmed',
            'check_in_date' => '2026-10-25',
            'check_out_date' => '2026-10-27',
            'adults' => 1,
            'children' => 0,
        ]);

        $response = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index'));

        $response->assertOk();
        $response->assertSee('>DUE</span>', false);
        $response->assertDontSee('OVERDUE');
        $viewTrip = collect($response->viewData('trips')->items())->firstWhere('id', $trip->id);
        $viewAction = collect($viewTrip->due_actions)->firstWhere('booking_id', $booking->id);
        $this->assertSame('guest_details', $viewAction['type']);
        $this->assertSame('due', $viewAction['status']);
        $this->assertSame('14 Oct 2026, 00:00 · UTC+00:00', $viewAction['target_display']);
        $response->assertDontSee('The configured warning window has started.');
        $response->assertSee('Deadline: 14 Oct 2026, 00:00 · UTC+00:00');
        $response->assertSee('Complete guest details for accommodation booking #' . $booking->id);
        $response->assertSee('Complete guest details for accommodation booking #' . $secondBooking->id);
        $response->assertSee('View Pending Actions');
        $response->assertSee('<dialog id="pending-actions-' . $trip->id . '"', false);
        $response->assertSee('Accommodation #' . $beforeWarningBooking->id . ' — Complete Guest Details');
        $viewBeforeWarning = collect($viewTrip->due_actions)->firstWhere('booking_id', $beforeWarningBooking->id);
        $this->assertSame('scheduled', $viewBeforeWarning['status']);
        $this->assertTrue(collect($viewTrip->operations_overview['pending_actions'])
            ->contains(fn (array $action): bool => $action['booking_id'] === $beforeWarningBooking->id));
        $this->assertSame(2, substr_count($response->getContent(), 'Deadline:'));
        $this->assertSame(2, substr_count($response->getContent(), 'Due since:'));
        Carbon::setTestNow();
    }

    public function test_admin_trip_listing_shows_due_independently_of_payment_and_never_after_target(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow('2026-10-13 06:00:00');
        DB::table('trip_action_deadline_settings')->insert([
            'action_type' => 'guest_details',
            'interval_hours' => 18,
            'country_id' => DB::table('countries')->where('iso_code', 'MU')->value('id'),
        ]);

        $createTrip = function (string $title, string $targetDate, string $paymentStatus): array {
            $trip = Trip::create([
                'title' => $title,
                'start_date' => $targetDate,
                'end_date' => $targetDate,
                'status' => 'planned',
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
            $accommodation = \App\Models\Accommodation::create([
                'property_name' => $title . ' Hotel',
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

        [$dueTrip, $dueBooking] = $createTrip('Admin Due Trip', '2026-10-14', 'paid');
        [$pastTrip, $pastBooking] = $createTrip('Admin Past Target Trip', '2026-10-12', 'paid');
        [$blockedTrip, $blockedBooking] = $createTrip('Admin Payment Blocked Trip', '2026-10-14', 'pending');

        $response = $this->withSession(['admin_id' => 1])->get(route('admin.trips.index'));

        $response->assertOk();
        $response->assertSee('>DUE</span>', false);
        $response->assertDontSee('OVERDUE');
        $viewTrips = collect($response->viewData('trips')->items());
        $viewActionFor = function (Trip $trip, int $bookingId) use ($viewTrips): ?array {
            $viewTrip = $viewTrips->firstWhere('id', $trip->id);
            return collect($viewTrip->due_actions)->firstWhere('booking_id', $bookingId);
        };

        $dueAction = $viewActionFor($dueTrip, $dueBooking->id);
        $pastAction = $viewActionFor($pastTrip, $pastBooking->id);
        $blockedTripData = $viewTrips->firstWhere('id', $blockedTrip->id);
        $blockedDueAction = $viewActionFor($blockedTrip, $blockedBooking->id);
        $blockedNextAction = collect($blockedTripData->next_actions)->firstWhere('booking_id', 0);

        $this->assertSame('due', $dueAction['status']);
        $this->assertSame('na', $pastAction['status']);
        $this->assertNull($pastAction['target_display']);
        $this->assertNull($pastAction['due_since_display']);
        $this->assertSame('payment', $blockedNextAction['type']);
        $this->assertSame('due', $blockedDueAction['status']);
        $this->assertSame('na', collect($blockedTripData->next_actions)->first()['status']);
    }
}
