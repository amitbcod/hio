<?php

namespace Tests\Feature;

use ReflectionMethod;
use Tests\TestCase;

class PackageCartPolicyTest extends TestCase
{
    public function test_package_cart_allows_non_package_items_in_same_cart(): void
    {
        $controller = new \App\Http\Controllers\Frontend\BookingController();
        $method = new ReflectionMethod($controller, 'validateCartItemCompatibility');
        $method->setAccessible(true);

        $method->invoke($controller, [
            ['type' => 'package', 'package_id' => 7],
            ['type' => 'accommodation', 'accommodation_id' => 1],
        ], 'accommodation');

        $this->assertTrue(true);
    }

    public function test_non_package_cart_allows_package_item_in_same_cart(): void
    {
        $controller = new \App\Http\Controllers\Frontend\BookingController();
        $method = new ReflectionMethod($controller, 'validateCartItemCompatibility');
        $method->setAccessible(true);

        $method->invoke($controller, [
            ['type' => 'accommodation', 'accommodation_id' => 1],
            ['type' => 'package', 'package_id' => 7],
        ], 'package');

        $this->assertTrue(true);
    }

    public function test_package_service_selection_prefers_requested_service_over_accommodation_fallback(): void
    {
        $controller = new \App\Http\Controllers\Frontend\TripController();
        $method = new ReflectionMethod($controller, 'resolvePackageServiceType');
        $method->setAccessible(true);

        $itinerary = [
            [
                'accommodation' => 12,
                'activity' => 8,
                'transport' => 5,
            ],
        ];

        $this->assertSame('activity', $method->invoke($controller, $itinerary, 'activity'));
        $this->assertSame('transport', $method->invoke($controller, $itinerary, 'transport'));
        $this->assertSame('accommodation', $method->invoke($controller, $itinerary, 'accommodation'));
        $this->assertSame('accommodation', $method->invoke($controller, $itinerary, null));
    }

    public function test_guest_package_voucher_and_manage_routes_accept_service_type_query(): void
    {
        $downloadMethod = new ReflectionMethod(\App\Http\Controllers\Frontend\GuestTripController::class, 'downloadVoucher');
        $manageMethod = new ReflectionMethod(\App\Http\Controllers\Frontend\GuestTripController::class, 'manageGuests');

        $downloadParams = $downloadMethod->getParameters();
        $manageParams = $manageMethod->getParameters();

        $this->assertSame('request', $downloadParams[3]->getName());
        $this->assertSame('request', $manageParams[1]->getName());
    }
}
