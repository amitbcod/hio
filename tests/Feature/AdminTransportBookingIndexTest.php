<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\TransportBookingController;
use App\Models\Operator;
use App\Models\TransportBooking;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminTransportBookingIndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'admin_transport_test',
            'database.connections.admin_transport_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('admin_transport_test');
        DB::setDefaultConnection('admin_transport_test');

        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->string('business_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
        Schema::create('operator_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->string('business_legal_name')->nullable();
            $table->timestamps();
        });
        Schema::create('transport_vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
        });
        Schema::create('transport_vehicle_names', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_vehicle_type_id')->nullable();
            $table->string('name')->nullable();
            $table->integer('seat_capacity')->nullable();
        });
        Schema::create('transports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->unsignedBigInteger('vehicle_name_id')->nullable();
            $table->string('vehicle_name')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('registration_number')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('transport_vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_id')->nullable();
            $table->string('license_number')->nullable();
            $table->string('registration_number')->nullable();
        });
        Schema::create('operator_drivers', function (Blueprint $table) {
            $table->id();
            $table->string('driver_name')->nullable();
        });
        Schema::create('transport_booking_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_booking_id')->nullable();
            $table->unsignedBigInteger('transport_vehicle_id')->nullable();
            $table->unsignedBigInteger('operator_driver_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('status')->nullable();
        });
        Schema::create('traveler_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
        });
        Schema::create('transport_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_id')->nullable();
            $table->unsignedBigInteger('transport_vehicle_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('pickup_driver_id')->nullable();
            $table->unsignedBigInteger('return_driver_id')->nullable();
            $table->unsignedBigInteger('traveler_account_id')->nullable();
            $table->string('booking_reference')->nullable();
            $table->string('trip_type')->nullable();
            $table->string('transport_group_reference')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->string('route_from')->nullable();
            $table->string('route_to')->nullable();
            $table->date('pickup_date')->nullable();
            $table->string('pickup_time')->nullable();
            $table->date('return_date')->nullable();
            $table->string('return_time')->nullable();
            $table->unsignedInteger('adults')->nullable();
            $table->unsignedInteger('children')->nullable();
            $table->unsignedInteger('total_passengers')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('currency')->nullable();
            $table->string('booking_status')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('source_channel')->nullable();
            $table->boolean('is_guest')->default(false);
            $table->timestamp('booked_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_admin_lists_all_operators_and_each_return_booking_without_transport_relation(): void
    {
        Operator::create(['id' => 1, 'operator_id' => 1, 'business_name' => 'Operator One', 'email' => 'one@example.test']);
        Operator::create(['id' => 2, 'operator_id' => 2, 'business_name' => 'Operator Two', 'email' => 'two@example.test']);

        $firstTransport = DB::table('transports')->insertGetId([
            'operator_id' => 1,
            'vehicle_name' => 'Hyundai County',
            'vehicle_type' => 'Coaster',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $secondTransport = DB::table('transports')->insertGetId([
            'operator_id' => 2,
            'vehicle_name' => 'Toyota Corolla',
            'vehicle_type' => 'Sedan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([
            ['transport_id' => $firstTransport, 'booking_reference' => 'OUT-1', 'trip_type' => 'OUTBOUND'],
            ['transport_id' => $firstTransport, 'booking_reference' => 'RET-1', 'trip_type' => 'RETURN'],
            ['transport_id' => $secondTransport, 'booking_reference' => 'ONE-2', 'trip_type' => 'ONE_WAY'],
            ['transport_id' => null, 'booking_reference' => 'LEGACY-ORPHAN', 'trip_type' => 'ONE_WAY'],
        ] as $row) {
            TransportBooking::create($row + [
                'guest_name' => 'Test Traveller',
                'route_from' => 'Airport',
                'route_to' => 'North',
                'pickup_date' => '2026-09-29',
                'adults' => 2,
                'total_passengers' => 2,
                'total_amount' => 100,
                'currency' => 'USD',
                'booking_status' => TransportBooking::STATUS_PROCESSING,
                'booked_at' => now(),
            ]);
        }

        $response = (new TransportBookingController())->index(Request::create('/admin/transport/bookings', 'GET'));
        $bookings = $response->getData()['bookings'];

        $this->assertSame(4, $bookings->total());
        $this->assertSame(['OUT-1', 'RET-1', 'ONE-2', 'LEGACY-ORPHAN'], $bookings->getCollection()->pluck('booking_reference')->all());
        $this->assertNull($bookings->getCollection()->firstWhere('booking_reference', 'LEGACY-ORPHAN')->transport);
    }

    public function test_admin_operator_filter_is_optional_and_does_not_restrict_default_query(): void
    {
        Operator::create(['id' => 1, 'operator_id' => 1, 'business_name' => 'Operator One']);
        $transportId = DB::table('transports')->insertGetId([
            'operator_id' => 1,
            'vehicle_name' => 'Hyundai County',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        TransportBooking::create([
            'transport_id' => $transportId,
            'booking_reference' => 'FILTER-1',
            'booking_status' => TransportBooking::STATUS_PROCESSING,
            'booked_at' => now(),
        ]);

        $all = (new TransportBookingController())->index(Request::create('/admin/transport/bookings', 'GET'));
        $filtered = (new TransportBookingController())->index(Request::create('/admin/transport/bookings', 'GET', ['operator_id' => '1']));

        $this->assertSame(1, $all->getData()['bookings']->total());
        $this->assertSame(1, $filtered->getData()['bookings']->total());
    }
}
