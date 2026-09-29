<?php

namespace Tests\Feature;

use App\Http\Controllers\Frontend\TripController;
use App\Models\Booking;
use App\Models\BookingLineItem;
use App\Models\BookingRef;
use App\Models\Group;
use App\Models\Package;
use App\Models\Trip;
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
}
