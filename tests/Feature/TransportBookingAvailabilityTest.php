<?php

namespace Tests\Feature;

use App\Http\Controllers\Frontend\BookingController;
use App\Models\Transport;
use App\Models\TransportBooking;
use App\Models\TransportServiceRoutePair;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TransportBookingAvailabilityTest extends TestCase
{
    public function test_it_detects_overlapping_transport_bookings_for_the_same_vehicle(): void
    {
        Schema::dropIfExists('transport_service_route_pairs');
        Schema::dropIfExists('transport_bookings');
        Schema::dropIfExists('transports');

        Schema::create('transports', function ($table) {
            $table->id();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->string('service_id')->nullable();
            $table->string('vehicle_name')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->integer('seating_capacity')->nullable();
            $table->string('status')->nullable();
            $table->string('approval_status')->nullable();
            $table->timestamps();
        });

        Schema::create('transport_service_route_pairs', function ($table) {
            $table->id();
            $table->string('service_type');
            $table->string('route_from');
            $table->string('route_to');
            $table->boolean('is_active')->default(true);
            $table->integer('trip_time_minutes')->default(0);
            $table->integer('buffer_time_minutes')->default(0);
            $table->timestamps();
        });

        Schema::create('transport_bookings', function ($table) {
            $table->id();
            $table->unsignedBigInteger('transport_id')->nullable();
            $table->string('booking_reference')->nullable();
            $table->string('route_from')->nullable();
            $table->string('route_to')->nullable();
            $table->date('pickup_date')->nullable();
            $table->string('pickup_time')->nullable();
            $table->date('return_date')->nullable();
            $table->string('return_time')->nullable();
            $table->string('booking_status')->nullable();
            $table->timestamps();
        });

        TransportServiceRoutePair::create([
            'service_type' => 'airport_transfer',
            'route_from' => 'Airport',
            'route_to' => 'North',
            'is_active' => true,
            'trip_time_minutes' => 60,
            'buffer_time_minutes' => 30,
        ]);

        $transport = Transport::create([
            'operator_id' => 1,
            'service_id' => 'TRNTEST01',
            'vehicle_name' => 'Sedan',
            'vehicle_type' => 'Car',
            'seating_capacity' => 4,
            'status' => 'Active',
            'approval_status' => 'Approved',
        ]);

        TransportBooking::create([
            'transport_id' => $transport->id,
            'booking_reference' => 'REF-1',
            'route_from' => 'Airport',
            'route_to' => 'North',
            'pickup_date' => '2026-07-27',
            'pickup_time' => '17:00:00',
            'booking_status' => 'Pending',
        ]);

        $controller = new BookingController();
        $method = new \ReflectionMethod($controller, 'detectTransportAvailabilityConflict');
        $method->setAccessible(true);

        $conflict = $method->invoke($controller, [
            'transport_id' => $transport->id,
            'route_from' => 'Airport',
            'route_to' => 'North',
            'pickup_date' => '2026-07-27',
            'pickup_time' => '17:00:00',
            'return_date' => null,
            'return_time' => null,
        ], null);

        $this->assertNotNull($conflict);
        $this->assertSame('Sorry, this vehicle is already booked for the selected time slot.', $conflict['message']);
    }
}
