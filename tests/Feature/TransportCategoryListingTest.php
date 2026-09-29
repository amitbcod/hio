<?php

namespace Tests\Feature;

use App\Http\Controllers\Frontend\HomeController;
use ReflectionMethod;
use Tests\TestCase;

class TransportCategoryListingTest extends TestCase
{
    public function test_vehicle_name_facet_groups_records_by_master_id_without_operator_names(): void
    {
        $controller = new HomeController();
        $method = new ReflectionMethod($controller, 'buildSidebarDefinitions');
        $method->setAccessible(true);

        $items = collect([
            ['vehicle_name_id' => 10, 'vehicle_name' => 'Hyundai County - ABC Transport Ltd', 'vehicle_name_filter_label' => 'Hyundai County', 'vehicle_type' => 'Coaster'],
            ['vehicle_name_id' => 10, 'vehicle_name' => 'Hyundai County - XYZ Tours Ltd', 'vehicle_name_filter_label' => 'Hyundai County', 'vehicle_type' => 'Coaster'],
            ['vehicle_name_id' => 10, 'vehicle_name' => 'Hyundai County - PQR Travel Ltd', 'vehicle_name_filter_label' => 'Hyundai County', 'vehicle_type' => 'Coaster'],
            ['vehicle_name_id' => 11, 'vehicle_name' => 'Toyota Corolla - ABC Transport Ltd', 'vehicle_name_filter_label' => 'Toyota Corolla', 'vehicle_type' => 'Sedan'],
        ]);

        $definitions = $method->invoke($controller, $items, 'transport');
        $options = collect($definitions)->firstWhere('key', 'vehicle_name_id')['options'];

        $this->assertSame([
            ['value' => '10', 'label' => 'Hyundai County', 'count' => 3],
            ['value' => '11', 'label' => 'Toyota Corolla', 'count' => 1],
        ], $options);
    }

    public function test_vehicle_name_and_type_filters_keep_all_matching_transport_records(): void
    {
        $controller = new HomeController();
        $method = new ReflectionMethod($controller, 'applySidebarFilters');
        $method->setAccessible(true);

        $items = collect([
            ['id' => 101, 'vehicle_name_id' => 10, 'vehicle_type' => 'Coaster'],
            ['id' => 102, 'vehicle_name_id' => 10, 'vehicle_type' => 'Coaster'],
            ['id' => 103, 'vehicle_name_id' => 10, 'vehicle_type' => 'Sedan'],
            ['id' => 104, 'vehicle_name_id' => 11, 'vehicle_type' => 'Coaster'],
        ]);

        $filtered = $method->invoke($controller, $items, 'transport', [
            'vehicle_name_id' => ['10', '11'],
            'vehicle_type' => ['Coaster'],
            'seating_capacity' => [],
        ]);

        $this->assertSame([101, 102, 104], $filtered->pluck('id')->all());
    }

}