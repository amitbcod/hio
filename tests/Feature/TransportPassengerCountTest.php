<?php

namespace Tests\Feature;

use App\Http\Controllers\Frontend\BookingController;
use Tests\TestCase;

class TransportPassengerCountTest extends TestCase
{
    public function test_it_uses_the_canonical_transport_passenger_count_for_outbound_and_return_bookings(): void
    {
        $controller = new BookingController();

        $method = new \ReflectionMethod($controller, 'resolveTransportPassengerCount');
        $method->setAccessible(true);

        $this->assertSame(3, $method->invokeArgs($controller, [['total_passengers' => 3]]));
        $this->assertSame(4, $method->invokeArgs($controller, [['adults' => 2, 'children' => 2, 'infants' => 0]]));
        $this->assertSame(5, $method->invokeArgs($controller, [['adults' => 2, 'children' => 2, 'infants' => 1]]));
        $this->assertSame(3, $method->invokeArgs($controller, [['passengers' => 3]]));
    }
}
