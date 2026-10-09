<?php

namespace Tests\Feature;

use App\Http\Controllers\Frontend\TripController;
use App\Models\Booking;
use App\Models\BookingLineItem;
use App\Models\BookingRef;
use App\Models\Group;
use App\Models\Package;
use App\Models\Trip;
use App\Services\VoucherAvailability;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class TripInvoiceOrderPricingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'invoice_test',
            'database.connections.invoice_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('invoice_test');
        DB::setDefaultConnection('invoice_test');

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
            $table->string('booking_ref_code')->unique();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('booking_ref_id')->nullable();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->nullable();
            $table->string('booking_type')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('service_type');
            $table->unsignedBigInteger('service_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            $table->string('booking_status')->nullable();
            $table->timestamps();
        });

        Schema::create('accommodation_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->timestamps();
        });

        Schema::create('booking_guests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('booking_type');
            $table->unsignedInteger('guest_number')->default(1);
            $table->string('first_name');
            $table->string('last_name');
            $table->date('dob')->nullable();
            $table->string('nationality')->nullable();
            $table->timestamps();
        });

        Schema::create('travellers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('relationship')->nullable();
            $table->timestamps();
        });

        Schema::create('bli_traveller_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bli_id');
            $table->unsignedBigInteger('traveller_id');
            $table->timestamps();
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->json('itinerary')->nullable();
            $table->timestamps();
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->json('itinerary')->nullable();
            $table->timestamps();
        });
    }

    public function test_invoice_parent_orders_are_scoped_to_the_selected_reference_for_all_order_types(): void
    {
        $trip = Trip::create([
            'title' => 'Invoice source test',
            'status' => 'planned',
        ]);

        $orderCases = [
            ['code' => 'BR-NORMAL', 'type' => 'direct', 'service' => 'accommodation', 'amount' => 800.00],
            ['code' => 'BR-PACKAGE', 'type' => 'package', 'service' => 'package', 'amount' => 1714.00],
            ['code' => 'BR-GROUP', 'type' => 'open-group', 'service' => 'package', 'amount' => 2885.00],
        ];
        $references = [];

        foreach ($orderCases as $case) {
            $reference = BookingRef::create([
                'trip_id' => $trip->id,
                'booking_ref_code' => $case['code'],
                'total_amount' => $case['amount'],
            ]);
            $references[$case['type']] = $reference;

            $booking = Booking::create([
                'trip_id' => $trip->id,
                'booking_ref_id' => $reference->id,
                'total_amount' => $case['amount'],
                'booking_type' => $case['type'],
                'status' => 'pending',
            ]);

            BookingLineItem::create([
                'booking_id' => $booking->id,
                'service_type' => $case['service'],
                'service_id' => 1,
                'quantity' => 1,
                'price' => $case['amount'],
                'status' => 'active',
            ]);
        }

        $controller = new TripController();
        $method = new ReflectionMethod($controller, 'getInvoiceParentBookings');
        $method->setAccessible(true);

        foreach ($orderCases as $case) {
            $parents = $method->invoke($controller, $trip, $references[$case['type']]);
            $this->assertCount(1, $parents);
            $this->assertSame($case['type'], $parents->first()->booking_type);
            $this->assertSame($case['amount'], (float) $parents->first()->lineItems->first()->price);
            $this->assertSame($case['amount'], (float) $references[$case['type']]->total_amount);
        }
    }

    public function test_legacy_orders_without_reference_foreign_key_use_their_reference_time_window(): void
    {
        $trip = Trip::create([
            'title' => 'Legacy invoice source test',
            'status' => 'planned',
        ]);
        $firstReference = BookingRef::create([
            'trip_id' => $trip->id,
            'booking_ref_code' => 'BR-LEGACY-1',
            'total_amount' => 100,
            'created_at' => '2026-09-29 10:20:00',
            'updated_at' => '2026-09-29 10:20:00',
        ]);
        $secondReference = BookingRef::create([
            'trip_id' => $trip->id,
            'booking_ref_code' => 'BR-LEGACY-2',
            'total_amount' => 200,
            'created_at' => '2026-09-29 10:40:00',
            'updated_at' => '2026-09-29 10:40:00',
        ]);

        foreach ([['amount' => 100, 'created_at' => '2026-09-29 10:10:00'], ['amount' => 200, 'created_at' => '2026-09-29 10:30:00']] as $order) {
            $booking = Booking::create([
                'trip_id' => $trip->id,
                'booking_ref_id' => null,
                'total_amount' => $order['amount'],
                'booking_type' => 'direct',
                'status' => 'pending',
            ]);
            DB::table('bookings')->where('id', $booking->id)->update([
                'created_at' => $order['created_at'],
                'updated_at' => $order['created_at'],
            ]);
        }

        $controller = new TripController();
        $method = new ReflectionMethod($controller, 'getInvoiceParentBookings');
        $method->setAccessible(true);

        $this->assertSame([100.0], $method->invoke($controller, $trip, $firstReference)->pluck('total_amount')->map(fn ($amount) => (float) $amount)->all());
        $this->assertSame([200.0], $method->invoke($controller, $trip, $secondReference)->pluck('total_amount')->map(fn ($amount) => (float) $amount)->all());
    }

    public function test_trip_detail_resolves_group_model_when_package_and_group_ids_collide(): void
    {
        Package::create(['id' => 1, 'name' => 'Amit test']);
        Group::create(['id' => 1, 'name' => 'test']);

        $controller = new TripController();
        $method = new ReflectionMethod($controller, 'resolvePackageOrGroupModelForBooking');
        $method->setAccessible(true);

        $group = $method->invoke($controller, 1, 'open-group');
        $package = $method->invoke($controller, 1, 'package');

        $this->assertInstanceOf(Group::class, $group);
        $this->assertSame('test', $group->name);
        $this->assertInstanceOf(Package::class, $package);
        $this->assertSame('Amit test', $package->name);
    }

    public function test_group_trip_pricing_resolution_selects_the_group_breakdown_source(): void
    {
        Package::create(['id' => 1, 'name' => 'Amit test']);
        Group::create(['id' => 1, 'name' => 'test']);
        $trip = Trip::create(['title' => 'Group price source test', 'status' => 'planned']);
        $groupBooking = Booking::create([
            'trip_id' => $trip->id,
            'booking_type' => 'open-group',
            'total_amount' => 2885,
            'status' => 'pending',
        ]);
        BookingLineItem::create([
            'booking_id' => $groupBooking->id,
            'service_type' => 'package',
            'service_id' => 1,
            'quantity' => 1,
            'price' => 2885,
            'status' => 'active',
        ]);

        $controller = new TripController();
        $resolver = new ReflectionMethod($controller, 'resolvePackageOrGroupModelForBooking');
        $resolver->setAccessible(true);
        $pricingModel = $resolver->invoke($controller, 1, $groupBooking->booking_type);

        $this->assertInstanceOf(Group::class, $pricingModel);
        $this->assertSame('test', $pricingModel->name);
        $this->assertSame('open-group', $groupBooking->booking_type);
        $this->assertSame(2885.0, (float) $groupBooking->lineItems->first()->price);
    }

    public function test_trip_detail_pricing_mapping_keeps_same_transport_separate_by_day(): void
    {
        $controller = new TripController();
        $method = new ReflectionMethod($controller, 'groupTripPricingItemsByDayAndType');
        $method->setAccessible(true);

        $amounts = $method->invoke($controller, [
            ['day' => 1, 'type' => 'Accommodation', 'name' => 'Resort', 'amount' => 500],
            ['day' => 1, 'type' => 'Activity', 'name' => 'Tour', 'amount' => 360],
            ['day' => 1, 'type' => 'Transport', 'name' => 'Airport to South East', 'amount' => 75],
            ['day' => 2, 'type' => 'Accommodation', 'name' => 'Apartment', 'amount' => 1000],
            ['day' => 2, 'type' => 'Activity', 'name' => 'Other tour', 'amount' => 900],
            ['day' => 2, 'type' => 'Transport', 'name' => 'Airport to South East', 'amount' => 50],
        ]);

        $this->assertSame([75.0], $amounts['1|transport']);
        $this->assertSame([50.0], $amounts['2|transport']);
        $this->assertSame([500.0], $amounts['1|accommodation']);
        $this->assertSame([1000.0], $amounts['2|accommodation']);
        $this->assertSame([360.0], $amounts['1|activity']);
        $this->assertSame([900.0], $amounts['2|activity']);
    }

    public function test_voucher_download_response_contains_pdf_and_service_specific_attachment_name(): void
    {
        $controller = new TripController();
        $method = new ReflectionMethod($controller, 'createVoucherPdfResponse');
        $method->setAccessible(true);

        $response = $method->invoke(
            $controller,
            '<h1>Accommodation Voucher</h1>',
            'accommodation',
            'ACC/2026-211',
            'Traveler'
        );

        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertSame(
            'attachment; filename="accommodation-voucher-ACC2026-211.pdf"',
            $response->headers->get('Content-Disposition')
        );
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_package_participant_voucher_details_support_missing_and_present_birth_dates(): void
    {
        $controller = new TripController();
        $method = new ReflectionMethod($controller, 'buildVoucherGuestDetails');
        $method->setAccessible(true);

        $details = $method->invoke($controller, [
            (object) ['first_name' => 'Group', 'last_name' => 'Guest', 'id' => 89],
            (object) [
                'first_name' => 'Package',
                'last_name' => 'Guest',
                'dob' => '1990-04-06',
                'nationality' => 'Mauritius',
            ],
        ]);

        $this->assertSame(['Group Guest', 'Package Guest'], $details['names']);
        $this->assertStringContainsString('<td>-</td>', $details['rows']);
        $this->assertStringContainsString('<td>06/04/1990</td>', $details['rows']);
        $this->assertStringContainsString('<td>Mauritius</td>', $details['rows']);
    }

    public function test_manage_guests_renders_individual_activity_vouchers_for_saved_participants(): void
    {
        request()->query->set('service_type', 'activity');

        $trip = new Trip(['title' => 'Group activity trip']);
        $trip->id = 81;

        $booking = (object) [
            'id' => 30,
            'booking_reference' => 'GROUP-81',
            'adults' => 3,
            'children' => 0,
            'guests' => collect([
                (object) [
                    'id' => 401,
                    'first_name' => 'Participant',
                    'last_name' => 'One',
                    'nationality' => 'Unknown',
                    'dob' => null,
                ],
                (object) [
                    'id' => 402,
                    'first_name' => 'Participant',
                    'last_name' => 'Two',
                    'nationality' => 'Unknown',
                    'dob' => null,
                ],
            ]),
        ];

        $html = view('frontend.traveler.manage-guests', [
            'trip' => $trip,
            'booking' => $booking,
            'savedGuests' => collect(),
            'countries' => [],
            'activityTimeSlots' => collect(),
        ])->render();

        $this->assertStringContainsString('/trips/81/booking/30/download-voucher/401?service_type=activity', $html);
        $this->assertStringContainsString('/trips/81/booking/30/download-voucher/402?service_type=activity', $html);
        $this->assertStringContainsString('name="service_type" value="activity"', $html);
    }

    public function test_group_package_guest_updates_target_the_bli_even_when_booking_ids_overlap(): void
    {
        $trip = Trip::create([
            'traveler_account_id' => 17,
            'title' => 'Group participant update test',
            'status' => 'planned',
        ]);
        $parentBooking = Booking::create([
            'trip_id' => $trip->id,
            'booking_type' => 'open-group',
            'total_amount' => 100,
            'status' => 'pending',
        ]);
        $lineItem = BookingLineItem::create([
            'booking_id' => $parentBooking->id,
            'service_type' => 'package',
            'service_id' => 1,
            'quantity' => 1,
            'price' => 100,
            'status' => 'active',
        ]);
        DB::table('activity_bookings')->insert([
            'id' => $lineItem->id,
            'trip_id' => $trip->id,
            'adults' => 1,
            'children' => 0,
        ]);

        $traveler = new \App\Models\TravelerAccount();
        $traveler->id = 17;
        \Illuminate\Support\Facades\Auth::guard('traveler')->setUser($traveler);

        $request = \Illuminate\Http\Request::create('/traveler/trips/' . $trip->id . '/booking/' . $lineItem->id . '/manage-guests', 'POST', [
            'service_type' => 'activity',
            'guests' => [
                [
                    'first_name' => 'Group',
                    'last_name' => 'Participant',
                    'dob' => '1990-01-01',
                ],
            ],
        ]);

        $response = (new TripController())->updateGuests($request, $trip, $lineItem->id);

        $this->assertSame(1, \App\Models\Traveller::where('trip_id', $trip->id)->count());
        $this->assertSame(1, DB::table('bli_traveller_allocations')->where('bli_id', $lineItem->id)->count());
        $this->assertSame(0, DB::table('booking_guests')->count());
        $countMethod = new ReflectionMethod((new TripController()), 'countPackageTravellersForTrip');
        $countMethod->setAccessible(true);
        $this->assertSame(1, $countMethod->invoke(new TripController(), $lineItem, $trip));
        $this->assertStringContainsString('service_type=activity', $response->getTargetUrl());
    }

    public function test_package_manage_guest_booked_count_matches_trip_detail_count(): void
    {
        $trip = Trip::create([
            'title' => 'Package booked guest count test',
            'status' => 'planned',
        ]);
        \App\Models\Traveller::create([
            'trip_id' => $trip->id,
            'name' => 'First Guest',
            'relationship' => 'guest',
        ]);
        \App\Models\Traveller::create([
            'trip_id' => $trip->id,
            'name' => 'Second Guest',
            'relationship' => 'guest',
        ]);

        $controller = new TripController();
        $method = new ReflectionMethod($controller, 'getTripBookedGuestCount');
        $method->setAccessible(true);

        $this->assertSame(2, $method->invoke($controller, $trip));
    }

    public function test_voucher_availability_uses_service_payment_and_activity_completeness(): void
    {
        $trip = new Trip();
        $trip->id = 202;

        $paidReference = new BookingRef(['trip_id' => $trip->id]);
        $paidReference->id = 1;
        $paidReference->setRelation('paymentTransactions', collect([
            new \App\Models\PaymentTransaction(['status' => 'paid']),
        ]));
        $paidReference->setRelation('paymentTransaction', null);

        $pendingReference = new BookingRef(['trip_id' => $trip->id]);
        $pendingReference->id = 2;
        $pendingReference->setRelation('paymentTransactions', collect([
            new \App\Models\PaymentTransaction(['status' => 'pending']),
        ]));
        $pendingReference->setRelation('paymentTransaction', null);

        $paidButPendingSettlementReference = new BookingRef(['trip_id' => $trip->id]);
        $paidButPendingSettlementReference->id = 3;
        $paidButPendingSettlementReference->setRelation('paymentTransactions', collect([
            new \App\Models\PaymentTransaction([
                'status' => 'paid',
                'settlement_status' => 'pending_verification',
            ]),
        ]));
        $paidButPendingSettlementReference->setRelation('paymentTransaction', null);

        $settledReference = new BookingRef(['trip_id' => $trip->id]);
        $settledReference->id = 4;
        $settledReference->setRelation('paymentTransactions', collect([
            new \App\Models\PaymentTransaction([
                'status' => 'pending',
                'settlement_status' => 'verified_settled',
            ]),
        ]));
        $settledReference->setRelation('paymentTransaction', null);

        $refundedReference = new BookingRef(['trip_id' => $trip->id]);
        $refundedReference->id = 5;
        $refundedReference->setRelation('paymentTransactions', collect([
            new \App\Models\PaymentTransaction([
                'status' => 'refunded',
                'settlement_status' => 'verified_settled',
            ]),
        ]));
        $refundedReference->setRelation('paymentTransaction', null);

        $trip->setRelation('bookingRefs', collect([
            $paidReference,
            $pendingReference,
            $paidButPendingSettlementReference,
            $settledReference,
            $refundedReference,
        ]));

        $accommodation = new \App\Models\AccommodationBooking(['booking_ref_id' => 1]);
        $accommodation->setRelation('bookingRef', $paidReference);

        $pendingAccommodation = new \App\Models\AccommodationBooking(['booking_ref_id' => 2]);
        $pendingAccommodation->setRelation('bookingRef', $pendingReference);
        $paidButPendingSettlementAccommodation = new \App\Models\AccommodationBooking(['booking_ref_id' => 3]);
        $settledAccommodation = new \App\Models\AccommodationBooking(['booking_ref_id' => 4]);
        $refundedAccommodation = new \App\Models\AccommodationBooking(['booking_ref_id' => 5]);

        $transport = new \App\Models\TransportBooking();
        $transport->setAttribute('booking_ref_id', 1);
        $transport->setRelation('bookingRef', $paidReference);

        $completeActivity = new \App\Models\ActivityBooking([
            'booking_ref_id' => 1,
            'adults' => 2,
            'children' => 1,
        ]);
        $completeActivity->setRelation('bookingRef', $paidReference);
        $completeActivity->setRelation('guests', collect([
            new \App\Models\BookingGuest([
                'guest_number' => 1,
                'first_name' => 'One',
                'last_name' => 'Guest',
                'dob' => '1990-01-01',
                'nationality' => 'Mauritius',
            ]),
            new \App\Models\BookingGuest([
                'guest_number' => 2,
                'first_name' => 'Two',
                'last_name' => 'Guest',
                'dob' => '1991-01-01',
                'nationality' => 'Mauritius',
            ]),
            new \App\Models\BookingGuest([
                'guest_number' => 3,
                'first_name' => 'Three',
                'last_name' => 'Guest',
                'dob' => '1992-01-01',
                'nationality' => 'Mauritius',
            ]),
        ]));

        $incompleteActivity = new \App\Models\ActivityBooking([
            'booking_ref_id' => 1,
            'adults' => 2,
            'children' => 1,
        ]);
        $incompleteActivity->setRelation('bookingRef', $paidReference);
        $incompleteActivity->setRelation('guests', $completeActivity->guests->take(2));

        $missingFieldActivity = new \App\Models\ActivityBooking([
            'booking_ref_id' => 1,
            'adults' => 1,
            'children' => 0,
        ]);
        $missingFieldActivity->setRelation('bookingRef', $paidReference);
        $missingFieldActivity->setRelation('guests', collect([
            new \App\Models\BookingGuest([
                'guest_number' => 1,
                'first_name' => 'Missing',
                'last_name' => '',
                'dob' => '1990-01-01',
                'nationality' => 'Mauritius',
            ]),
        ]));

        $unpaidActivity = new \App\Models\ActivityBooking([
            'booking_ref_id' => 2,
            'adults' => 1,
            'children' => 0,
        ]);
        $unpaidActivity->setRelation('bookingRef', $pendingReference);
        $unpaidActivity->setRelation('guests', $missingFieldActivity->guests);

        $availability = new VoucherAvailability();

        $this->assertTrue($availability->isAvailable($trip, $accommodation, 'accommodation'));
        $this->assertFalse($availability->isAvailable($trip, $pendingAccommodation, 'accommodation'));
        $this->assertFalse($availability->isAvailable($trip, $paidButPendingSettlementAccommodation, 'accommodation'));
        $this->assertTrue($availability->isAvailable($trip, $settledAccommodation, 'accommodation'));
        $this->assertFalse($availability->isAvailable($trip, $refundedAccommodation, 'accommodation'));
        $this->assertTrue($availability->isAvailable($trip, $transport, 'transport'));
        $this->assertTrue($availability->isAvailable($trip, $completeActivity, 'activity'));
        $this->assertFalse($availability->isAvailable($trip, $incompleteActivity, 'activity'));
        $this->assertFalse($availability->isAvailable($trip, $missingFieldActivity, 'activity'));
        $this->assertFalse($availability->isAvailable($trip, $unpaidActivity, 'activity'));
    }

    public function test_package_activity_availability_uses_complete_bli_traveller_allocations_when_guest_rows_are_absent(): void
    {
        $trip = new Trip();
        $trip->id = 202;

        $reference = new BookingRef(['trip_id' => $trip->id]);
        $reference->id = 1;
        $reference->setRelation('paymentTransactions', collect([
            new \App\Models\PaymentTransaction(['status' => 'paid']),
        ]));
        $reference->setRelation('paymentTransaction', null);
        $trip->setRelation('bookingRefs', collect([$reference]));

        $makePackageLineItem = static function (bool $complete): BookingLineItem {
            $lineItem = new BookingLineItem(['service_type' => 'package']);
            $lineItem->setRelation('travellers', collect([
                new \App\Models\Traveller([
                    'trip_id' => 202,
                    'name' => 'Alex Guest',
                    'date_of_birth' => '1990-01-01',
                ]),
                new \App\Models\Traveller([
                    'trip_id' => 202,
                    'name' => 'Jamie Guest',
                    'date_of_birth' => $complete ? '1991-01-01' : null,
                ]),
            ]));

            return $lineItem;
        };

        $parentBooking = new Booking(['booking_ref_id' => 1]);
        $parentBooking->setRelation('lineItems', collect([$makePackageLineItem(true)]));
        $trip->setRelation('bookings', collect([$parentBooking]));

        $activity = new \App\Models\ActivityBooking([
            'booking_ref_id' => 1,
            'adults' => 2,
            'children' => 0,
        ]);
        $activity->setRelation('guests', collect());

        $incompleteTrip = new Trip();
        $incompleteTrip->id = $trip->id;
        $incompleteTrip->setRelation('bookingRefs', collect([$reference]));
        $incompleteParentBooking = new Booking(['booking_ref_id' => 1]);
        $incompleteParentBooking->setRelation('lineItems', collect([$makePackageLineItem(false)]));
        $incompleteTrip->setRelation('bookings', collect([$incompleteParentBooking]));

        $availability = new VoucherAvailability();

        $this->assertTrue($availability->isAvailable($trip, $activity, 'activity'));
        $this->assertFalse($availability->isAvailable($incompleteTrip, $activity, 'activity'));
    }

}
