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

        $this->assertSame('guestId', $downloadParams[3]->getName());
        $this->assertSame('request', $manageParams[3]->getName());
    }

    public function test_package_generated_bookings_are_filtered_out_of_trip_detail_display(): void
    {
        $controller = new \App\Http\Controllers\Frontend\TripController();
        $method = new ReflectionMethod($controller, 'filterPackageGeneratedBookings');
        $method->setAccessible(true);

        $bookings = collect([
            (object) ['id' => 1, 'source_channel' => 'Package'],
            (object) ['id' => 2, 'source_channel' => 'Manual'],
            (object) ['id' => 3, 'source_channel' => 'package'],
        ]);

        $filtered = $method->invoke($controller, $bookings, true);

        $this->assertCount(1, $filtered);
        $this->assertSame(2, $filtered->first()->id);
    }

    public function test_group_cart_item_is_built_like_package_item(): void
    {
        $controller = new \App\Http\Controllers\Frontend\BookingController();
        $request = new \Illuminate\Http\Request([
            'group_id' => 42,
            'group_name' => 'Spring Escape',
            'group_total_price' => 1250,
            'adults' => 2,
            'children' => 1,
            'infants' => 0,
            'currency' => 'USD',
            'group_image' => '/storage/group.jpg',
            'nights' => 4,
            'days' => 5,
            'group_start_date' => '2026-09-10',
        ]);

        $method = new ReflectionMethod($controller, 'buildGroupCartItem');
        $method->setAccessible(true);

        $item = $method->invoke($controller, $request);

        $this->assertSame('package', $item['type']);
        $this->assertSame(42, $item['package_id']);
        $this->assertSame('Spring Escape', $item['package_name']);
        $this->assertSame('2026-09-10', $item['check_in']);
        $this->assertSame(1250.0, $item['total_price']);
    }
}
