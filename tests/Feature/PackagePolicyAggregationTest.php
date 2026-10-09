<?php

namespace Tests\Feature;

use ReflectionMethod;
use Tests\TestCase;

class PackagePolicyAggregationTest extends TestCase
{
    public function test_payment_before_deadline_uses_highest_percentage_across_operators()
    {
        $controller = new \App\Http\Controllers\Admin\PackageController();
        $method = new ReflectionMethod($controller, 'selectDeadlinePreference');
        $method->setAccessible(true);

        $result = $method->invoke($controller, [
            '20% Payment',
            '100% Payment',
        ], ['100% Payment', '50% Payment', '20% Payment', '0% Payment'], 'payment');

        $this->assertSame('100% Payment', $result);
    }

    public function test_activity_variant_requires_package_rate_for_each_specificity()
    {
        $controller = new \App\Http\Controllers\Admin\PackageController();
        $method = new ReflectionMethod($controller, 'activityVariantHasPackageRates');
        $method->setAccessible(true);

        $fullPackageRateSet = [
            ['season' => 'Package', 'rate_specificity' => 'Per Person'],
            ['season' => 'One Season', 'rate_specificity' => 'Per Person'],
            ['season' => 'Package', 'rate_specificity' => 'Per Equipment'],
            ['season' => 'One Season', 'rate_specificity' => 'Per Equipment'],
        ];

        $partialPackageRateSet = [
            ['season' => 'Package', 'rate_specificity' => 'Per Person'],
            ['season' => 'One Season', 'rate_specificity' => 'Per Person'],
            ['season' => 'One Season', 'rate_specificity' => 'Per Equipment'],
        ];

        $this->assertTrue($method->invoke($controller, $fullPackageRateSet));
        $this->assertFalse($method->invoke($controller, $partialPackageRateSet));
    }

    public function test_transport_routes_require_package_price_on_every_route()
    {
        $controller = new \App\Http\Controllers\Admin\PackageController();
        $method = new ReflectionMethod($controller, 'transportRoutesHavePackagePrice');
        $method->setAccessible(true);

        $validRoutes = [
            ['pricing' => ['package_price' => 120]],
            ['pricing' => ['package_price' => 150]],
        ];

        $invalidRoutes = [
            ['pricing' => ['package_price' => 120]],
            ['pricing' => ['default_price' => 150]],
        ];

        $this->assertTrue($method->invoke($controller, $validRoutes));
        $this->assertFalse($method->invoke($controller, $invalidRoutes));
    }

    public function test_package_activity_rate_does_not_require_valid_dates()
    {
        $validator = \Illuminate\Support\Facades\Validator::make([
            'season' => 'Package',
            'rate_specificity' => 'Per Equipment',
            'equipment_rate' => 500,
        ], [
            'season' => 'nullable|string|max:100',
            'valid_from' => 'nullable|date|date_format:Y-m-d|required_if:season,One Season|required_if:season,High|required_if:season,Low|required_if:season,Peak',
            'valid_to' => 'nullable|date|date_format:Y-m-d|after_or_equal:valid_from|required_if:season,One Season|required_if:season,High|required_if:season,Low|required_if:season,Peak',
            'rate_specificity' => 'required|in:Per Person,Per Equipment',
            'equipment_rate' => 'nullable|required_if:rate_specificity,Per Equipment|numeric|min:0',
        ]);

        $this->assertTrue($validator->passes());
        $this->assertSame([], $validator->errors()->all());
    }

    public function test_transport_selection_suffix_is_normalized_before_route_lookup()
    {
        $service = new \App\Services\PackagePricingService();
        $method = new ReflectionMethod($service, 'normalizeTransportRouteKey');
        $method->setAccessible(true);

        $this->assertSame('TRN-2-hotel-transfer-airport-east', $method->invoke($service, 'TRN-2-hotel-transfer-airport-east-fwd'));
        $this->assertSame('TRN-2-hotel-transfer-airport-east', $method->invoke($service, 'TRN-2-hotel-transfer-airport-east-rev'));
        $this->assertSame('TRN-2-hotel-transfer-airport-east', $method->invoke($service, 'TRN-2-hotel-transfer-airport-east'));
    }

    public function test_stale_transport_routes_without_a_transport_are_ignored_during_package_pricing()
    {
        $service = new \App\Services\PackagePricingService();
        $route = new \App\Models\TransportRoute([
            'route_id' => 'TRN-1',
            'route_from' => 'Airport',
            'route_to' => 'Hotel',
        ]);
        $route->setRelation('transport', null);

        $this->assertSame(0.0, $service->getGroupTransportRouteAmount($route, 2));
        $this->assertSame(0.0, $service->getTransportRouteAmount($route, 2));
    }

    public function test_package_day_route_selection_parses_scalar_and_array_route_payloads()
    {
        $controller = new \App\Http\Controllers\Frontend\BookingController();
        $method = new ReflectionMethod($controller, 'extractPackageSelectedRouteIdentifiers');
        $method->setAccessible(true);

        $dayEntry = [
            'transport_schedule' => [
                'service-1' => [
                    'TRN-2-hotel-transfer-airport-east' => 'TRN-2-hotel-transfer-airport-east',
                    3 => ['route_id' => 'TRN-3-luxury-shuttle', 'selected' => true],
                    'TRN-2-hotel-transfer-airport-east-fwd' => ['selected_route' => 'TRN-2-hotel-transfer-airport-east', 'selected' => true],
                ],
            ],
        ];

        $identifiers = $method->invoke($controller, $dayEntry);

        $this->assertContains('TRN-2-hotel-transfer-airport-east', $identifiers);
        $this->assertContains('TRN-3-luxury-shuttle', $identifiers);
        $this->assertNotContains('N/A', $identifiers);
    }

    public function test_package_activity_id_ignores_placeholder_arrays()
    {
        $controller = new \App\Http\Controllers\Frontend\BookingController();
        $method = new ReflectionMethod($controller, 'normalizePackageSelectableId');
        $method->setAccessible(true);

        $this->assertSame(12, $method->invoke($controller, ['N/A', '12']));
        $this->assertNull($method->invoke($controller, ['N/A']));
        $this->assertSame(12, $method->invoke($controller, '12'));
    }

    public function test_package_metadata_arrays_are_not_treated_as_real_itinerary_days()
    {
        $controller = new \App\Http\Controllers\Frontend\BookingController();
        $method = new ReflectionMethod($controller, 'isMeaningfulPackageDayEntry');
        $method->setAccessible(true);

        $metadataEntry = [
            'content' => ['title' => 'package title'],
            'discounts' => ['activity' => 10],
            'pricing_modes' => ['activity' => 'discount_offer'],
        ];

        $this->assertFalse($method->invoke($controller, $metadataEntry));

        $placeholderActivityEntry = [
            'activity' => 'Activity',
            'variant_id' => '1',
        ];

        $this->assertFalse($method->invoke($controller, $placeholderActivityEntry));

        $realDayEntry = [
            'accommodation' => '1',
            'activity' => '2',
            'transport' => '3',
            'rooms' => [1, 2],
        ];

        $this->assertTrue($method->invoke($controller, $realDayEntry));
    }

    public function test_package_pricing_service_ignores_metadata_only_itinerary_rows()
    {
        $package = new \App\Models\Package();
        $package->id = 1;
        $package->name = 'Amit test';
        $package->no_of_days = 2;
        $package->itinerary = [
            0 => [
                'content' => ['title' => 'package title'],
                'discounts' => ['activity' => 10],
                'pricing_modes' => ['activity' => 'discount_offer'],
            ],
            1 => [
                'content' => ['title' => 'package title'],
                'discounts' => ['activity' => 10],
                'pricing_modes' => ['activity' => 'discount_offer'],
            ],
        ];

        $service = new \App\Services\PackagePricingService();
        $breakdown = $service->calculatePackageTotalDetailed($package, 2, 0, 0);

        $this->assertSame(0.0, (float) $breakdown['total']);
        $this->assertSame([], $breakdown['items']);
    }

    public function test_package_transport_discount_offer_applies_discount_to_direct_rate(): void
    {
        $package = new \App\Models\Package([
            'itinerary' => [
                'pricing_modes' => ['transport' => 'discount_offer'],
                'discounts' => ['transport' => 10],
            ],
        ]);
        $service = new \App\Services\PackagePricingService();
        $method = new ReflectionMethod($service, 'applyPackageTransportDiscount');
        $method->setAccessible(true);

        $this->assertSame(81.0, $method->invoke($service, 90.0, $package));
    }

    public function test_package_transport_rate_is_not_discounted_again(): void
    {
        $package = new \App\Models\Package([
            'itinerary' => [
                'pricing_modes' => ['transport' => 'package_rate'],
                'discounts' => ['transport' => 10],
            ],
        ]);
        $service = new \App\Services\PackagePricingService();
        $method = new ReflectionMethod($service, 'applyPackageTransportDiscount');
        $method->setAccessible(true);

        $this->assertSame(90.0, $method->invoke($service, 90.0, $package));
    }

    public function test_package_transport_forward_and_reverse_selections_remain_separate_booking_legs(): void
    {
        $controller = new \App\Http\Controllers\Frontend\BookingController();
        $method = new ReflectionMethod($controller, 'extractPackageSelectedRouteGroups');
        $method->setAccessible(true);

        $groups = $method->invoke($controller, [
            'transport_schedule' => [
                'airport_transfer' => [
                    'TRN-13-airport-transfer-airport-south-east-fwd' => [
                        'selected' => '1',
                        'start_hour' => '10',
                        'start_min' => '00',
                    ],
                    'TRN-13-airport-transfer-airport-south-east-rev' => [
                        'selected' => '1',
                        'start_hour' => '11',
                        'start_min' => '30',
                    ],
                ],
            ],
        ]);

        $this->assertCount(1, $groups);
        $this->assertCount(2, $groups[0]['legs']);
        $this->assertSame('forward', $groups[0]['legs'][0]['direction']);
        $this->assertSame('10:00:00', $groups[0]['legs'][0]['pickup_time']);
        $this->assertSame('reverse', $groups[0]['legs'][1]['direction']);
        $this->assertSame('11:30:00', $groups[0]['legs'][1]['pickup_time']);
        $this->assertTrue($groups[0]['legs'][0]['is_return_pair']);
        $this->assertTrue($groups[0]['legs'][1]['is_return_pair']);
        $this->assertFalse($groups[0]['add_return']);
    }

    public function test_package_transport_legs_use_directional_rates_and_split_discounts(): void
    {
        $transport = new \App\Models\Transport(['return_discount_percentage' => 10]);
        $transport->id = 13;
        $route = new \App\Models\TransportRoute([
            'id' => 372,
            'route_id' => 'TRN-13-airport-south-east',
            'route_from' => 'Airport',
            'route_to' => 'South East',
            'pricing' => [
                'default_price' => 100,
                'departure_price' => 80,
                'package_price' => 60,
                'package_departure_price' => 50,
            ],
        ]);
        $transport->setRelation('routes', collect([$route]));
        $package = new \App\Models\Package([
            'itinerary' => [
                'pricing_modes' => ['transport' => 'discount_offer'],
                'discounts' => ['transport' => 10],
            ],
        ]);
        $service = new \App\Services\PackagePricingService();

        $this->assertSame(81.0, $service->getTransportRouteLegAmount($transport, $route, $package, false, true));
        $this->assertSame(64.8, $service->getTransportRouteLegAmount($transport, $route, $package, true, true));
    }

    public function test_unpaired_reverse_package_leg_matches_package_breakdown_rate(): void
    {
        $transport = new \App\Models\Transport(['return_discount_percentage' => 50]);
        $transport->id = 13;
        $route = new \App\Models\TransportRoute([
            'id' => 372,
            'route_id' => 'TRN-13-airport-south-east',
            'route_from' => 'Airport',
            'route_to' => 'South East',
            'pricing' => [
                'default_price' => 80,
                'departure_price' => 100,
            ],
        ]);
        $transport->setRelation('routes', collect([$route]));
        $package = new \App\Models\Package([
            'itinerary' => [
                'pricing_modes' => ['transport' => 'discount_offer'],
                'discounts' => ['transport' => 10],
            ],
        ]);
        $service = new \App\Services\PackagePricingService();

        $this->assertSame(72.0, $service->getTransportRouteLegAmount($transport, $route, $package, true, false));
        $this->assertSame(45.0, $service->getTransportRouteLegAmount($transport, $route, $package, true, true));
    }

    public function test_package_rate_legs_keep_package_rates_and_return_discount(): void
    {
        $transport = new \App\Models\Transport(['return_discount_percentage' => 10]);
        $transport->id = 13;
        $route = new \App\Models\TransportRoute([
            'id' => 372,
            'route_id' => 'TRN-13-airport-south-east',
            'route_from' => 'Airport',
            'route_to' => 'South East',
            'pricing' => [
                'default_price' => 100,
                'departure_price' => 80,
                'package_price' => 60,
                'package_departure_price' => 50,
            ],
        ]);
        $transport->setRelation('routes', collect([$route]));
        $package = new \App\Models\Package([
            'itinerary' => [
                'pricing_modes' => ['transport' => 'package_rate'],
                'discounts' => ['transport' => 10],
            ],
        ]);
        $service = new \App\Services\PackagePricingService();

        $this->assertSame(54.0, $service->getTransportRouteLegAmount($transport, $route, $package, false, true));
        $this->assertSame(45.0, $service->getTransportRouteLegAmount($transport, $route, $package, true, true));
    }

    public function test_group_discount_offer_is_applied_to_each_transport_leg(): void
    {
        $transport = new \App\Models\Transport(['return_discount_percentage' => 0]);
        $transport->id = 13;
        $route = new \App\Models\TransportRoute([
            'id' => 372,
            'route_id' => 'TRN-13-airport-south-east',
            'route_from' => 'Airport',
            'route_to' => 'South East',
            'pricing' => [
                'default_price' => 100,
                'departure_price' => 80,
                'group_price' => 90,
                'group_departure_price' => 70,
            ],
        ]);
        $transport->setRelation('routes', collect([$route]));
        $group = new \App\Models\Group([
            'itinerary' => [
                'pricing_modes' => ['transport' => 'discount_offer'],
                'discounts' => ['transport' => 10],
            ],
        ]);
        $service = new \App\Services\PackagePricingService();

        $this->assertSame(81.0, $service->getTransportRouteLegAmount($transport, $route, $group, false, false));
        $this->assertSame(63.0, $service->getTransportRouteLegAmount($transport, $route, $group, true, false));
    }

}
