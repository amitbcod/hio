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

    public function test_package_sidebar_filter_values_are_normalized_from_request(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'normalizeFilterValues');
        $method->setAccessible(true);

        $this->assertSame(['Hotel', 'Apartment'], $method->invoke($controller, ['Hotel', 'Apartment', '']));
        $this->assertSame(['Half Board'], $method->invoke($controller, 'Half Board'));
    }

    public function test_package_meal_plan_filter_uses_only_room_assigned_rate_plans(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'resolveRoomMealPlans');
        $method->setAccessible(true);

        $room = new class {
            public function rates()
            {
                return collect([
                    (object) ['meal_plan' => 'Full Board', 'is_rate_plan' => true],
                    (object) ['meal_plan' => 'Breakfast', 'is_rate_plan' => true],
                    (object) ['meal_plan' => 'Room Only', 'is_rate_plan' => false],
                ]);
            }
        };

        $mealPlans = $method->invoke($controller, $room);

        $this->assertContains('Full Board', $mealPlans);
        $this->assertContains('Breakfast', $mealPlans);
        $this->assertNotContains('Room Only', $mealPlans);
    }

    public function test_resolve_room_meal_plans_uses_rate_plan_query_when_relation_is_a_builder(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'resolveRoomMealPlans');
        $method->setAccessible(true);

        $builder = new class {
            public array $items;

            public function __construct()
            {
                $this->items = [
                    (object) ['meal_plan' => 'Breakfast', 'is_rate_plan' => true],
                    (object) ['meal_plan' => 'Half Board', 'is_rate_plan' => true],
                    (object) ['meal_plan' => 'Room Only', 'is_rate_plan' => false],
                ];
            }

            public function where($column, $operator = null, $value = null)
            {
                return $this;
            }

            public function whereNotNull($column)
            {
                return $this;
            }

            public function get()
            {
                return collect($this->items);
            }
        };

        $room = new class($builder) {
            public $ratesBuilder;

            public function __construct($ratesBuilder)
            {
                $this->ratesBuilder = $ratesBuilder;
            }

            public function rates()
            {
                return $this->ratesBuilder;
            }
        };

        $mealPlans = $method->invoke($controller, $room);

        $this->assertContains('Breakfast', $mealPlans);
        $this->assertContains('Half Board', $mealPlans);
        $this->assertNotContains('Room Only', $mealPlans);
    }

    public function test_package_list_view_keeps_meal_plan_filter_but_removes_budget_range(): void
    {
        $html = view('frontend.packages-list', [
            'packages' => collect(),
            'regionOptions' => [],
            'region' => 'all',
            'travelingDate' => null,
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'roomsRequired' => 1,
            'packageFilterOptions' => [
                'property_types' => [],
                'meal_plans' => [
                    ['value' => 'Breakfast', 'count' => 2],
                    ['value' => 'Full Board', 'count' => 1],
                ],
            ],
            'selectedPropertyTypes' => [],
            'selectedMealPlans' => [],
        ])->render();

        $this->assertStringContainsString('Meal Plan', $html);
        $this->assertStringContainsString('Breakfast', $html);
        $this->assertStringContainsString('Full Board', $html);
        $this->assertStringNotContainsString('Budget Range', $html);
    }
}
