<?php

namespace Tests\Feature;

use App\Models\AccommodationRoom;
use ReflectionMethod;
use Tests\TestCase;

class PackageAvailabilityFilterTest extends TestCase
{
    public function test_package_guest_room_logic_requires_enough_matching_rooms(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'matchesPackageGuestCapacity');
        $method->setAccessible(true);

        $rooms = collect([
            (object) ['capacity' => 2, 'children_capacity' => 1, 'infant_capacity' => 0, 'max_person_capacity' => 4, 'quantity' => 1],
            (object) ['capacity' => 2, 'children_capacity' => 1, 'infant_capacity' => 0, 'max_person_capacity' => 4, 'quantity' => 1],
        ]);

        $this->assertTrue($method->invoke($controller, $rooms, 4, 2, 2));
        $this->assertFalse($method->invoke($controller, $rooms, 4, 2, 3));
    }
}
