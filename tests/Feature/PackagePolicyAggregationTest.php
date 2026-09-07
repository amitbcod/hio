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

}
