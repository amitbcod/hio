<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\Transport;
use App\Models\TransportServiceRoutePair;
use App\Models\TransportBooking;

class TransportAddToCartConflictTest extends TestCase
{
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        // Create minimal tables required for the test to avoid running full migrations
        Schema::create('transports', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_name')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->integer('seating_capacity')->nullable();
            $table->string('approval_status')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_visible_to_travellers')->default(true);
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('transport_service_route_pairs', function (Blueprint $table) {
            $table->id();
            $table->string('service_type')->nullable();
            $table->string('route_from')->nullable();
            $table->string('route_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('trip_time_minutes')->nullable();
            $table->integer('buffer_time_minutes')->nullable();
            $table->timestamps();
        });

        Schema::create('transport_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_id')->nullable();
            $table->string('route_from')->nullable();
            $table->string('route_to')->nullable();
            $table->date('pickup_date')->nullable();
            $table->time('pickup_time')->nullable();
            $table->string('booking_status')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('currency')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('transport_bookings');
        Schema::dropIfExists('transport_service_route_pairs');
        Schema::dropIfExists('transports');

        parent::tearDown();
    }

    public function test_cannot_add_overlapping_transport_to_cart_when_db_booking_exists()
    {
        // Create a transport
        $transport = Transport::create([
            'vehicle_name' => 'Test Car',
            'vehicle_type' => 'Car',
            'seating_capacity' => 4,
            'approval_status' => 'Approved',
            'is_published' => 1,
            'is_visible_to_travellers' => 1,
            'status' => 'Active',
        ]);

        // Add route pair with trip and buffer
        $pair = TransportServiceRoutePair::create([
            'service_type' => 'airport_transfer',
            'route_from' => 'Airport',
            'route_to' => 'Belle Mare',
            'is_active' => true,
            'trip_time_minutes' => 60,
            'buffer_time_minutes' => 30,
        ]);

        // Existing booking at 11:00
        $existing = TransportBooking::create([
            'transport_id' => $transport->id,
            'route_from' => 'Airport',
            'route_to' => 'Belle Mare',
            'pickup_date' => '2026-07-28',
            'pickup_time' => '11:00:00',
            'booking_status' => 'Confirmed',
            'total_amount' => 100.00,
            'currency' => 'USD',
        ]);

        // Attempt to add overlapping booking at 11:05
        // Call controller directly to avoid routing/middleware issues in this constrained test environment
        $request = \Illuminate\Http\Request::create(route('frontend.booking.cart.add'), 'POST', [
            'type' => 'transport',
            'transport_id' => $transport->id,
            'route_from' => 'Airport',
            'route_to' => 'Belle Mare',
            'pickup_date' => '2026-07-28',
            'pickup_time' => '11:05',
            'passengers' => 1,
            'price_per_passenger' => 100,
            'source' => 'detail',
        ]);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $controller = $this->app->make(\App\Http\Controllers\Frontend\BookingController::class);
        $response = $controller->addToCart($request);

        $status = $response->getStatusCode();
        if ($status === 422) {
            $payload = json_decode($response->getContent(), true);
            $this->assertFalse($payload['success']);
            $this->assertStringContainsString('not available', strtolower($payload['message']));
        } else {
            // Redirect response with flashed error
            $this->assertEquals(302, $status);
            $session = $response->getSession();
            $error = $session->get('error') ?? $session->get('message') ?? '';
            $this->assertStringContainsString('not available', strtolower((string) $error));
        }
    }
}
