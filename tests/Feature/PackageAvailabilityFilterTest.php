<?php

namespace Tests\Feature;

use App\Models\AccommodationRoom;
use Illuminate\Support\Facades\Schema;
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

    public function test_package_matching_room_selects_capacity_compatible_room_for_guest_count(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'selectBestMatchingAccommodationRoom');
        $method->setAccessible(true);

        $rooms = collect([
            (object) ['id' => 1, 'capacity' => 1, 'children_capacity' => 0, 'infant_capacity' => 0, 'max_person_capacity' => 1, 'quantity' => 1],
            (object) ['id' => 2, 'capacity' => 2, 'children_capacity' => 0, 'infant_capacity' => 0, 'max_person_capacity' => 2, 'quantity' => 1],
        ]);

        $matched = $method->invoke($controller, $rooms, 2, 0, 0);

        $this->assertNotNull($matched);
        $this->assertSame(2, (int) $matched->id);
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

    public function test_legacy_room_rate_rows_still_count_as_meal_plan_options(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'resolveRoomMealPlans');
        $method->setAccessible(true);

        $room = new class {
            public function rates()
            {
                return collect([
                    (object) ['meal_plan' => 'Breakfast', 'is_rate_plan' => 0, 'room_id' => 3],
                    (object) ['meal_plan' => 'Full Board', 'is_rate_plan' => 0, 'room_id' => 3],
                    (object) ['meal_plan' => 'Room Only', 'is_rate_plan' => 0, 'room_id' => null],
                ]);
            }
        };

        $mealPlans = $method->invoke($controller, $room);

        $this->assertContains('Breakfast', $mealPlans);
        $this->assertContains('Full Board', $mealPlans);
        $this->assertNotContains('Room Only', $mealPlans);
    }

    public function test_has_many_room_relation_fetches_all_meal_plan_rows_before_filtering(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'resolveRoomMealPlans');
        $method->setAccessible(true);

        $relation = new class {
            public function get()
            {
                return collect([
                    (object) ['meal_plan' => 'Half Board', 'is_rate_plan' => 0, 'room_id' => 2],
                    (object) ['meal_plan' => 'Breakfast', 'is_rate_plan' => 0, 'room_id' => 2],
                    (object) ['meal_plan' => 'Room Only', 'is_rate_plan' => 0, 'room_id' => null],
                ]);
            }
        };

        $room = new class($relation) {
            public $relation;

            public function __construct($relation)
            {
                $this->relation = $relation;
            }

            public function rates()
            {
                return $this->relation;
            }
        };

        $mealPlans = $method->invoke($controller, $room);

        $this->assertContains('Half Board', $mealPlans);
        $this->assertContains('Breakfast', $mealPlans);
        $this->assertNotContains('Room Only', $mealPlans);
    }

    public function test_package_capacity_uses_multiple_sellable_rooms_for_larger_guest_counts(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'calculateMinimalRequiredRoomsForGuests');
        $method->setAccessible(true);

        $rooms = collect([
            (object) ['capacity' => 2, 'children_capacity' => 1, 'infant_capacity' => 1, 'max_person_capacity' => 4, 'allotment' => 1],
            (object) ['capacity' => 2, 'children_capacity' => 1, 'infant_capacity' => 1, 'max_person_capacity' => 4, 'allotment' => 1],
        ]);

        $this->assertSame(2, $method->invoke($controller, $rooms, 4, 2, 0));

        $matchMethod = new ReflectionMethod($controller, 'matchesPackageGuestCapacity');
        $matchMethod->setAccessible(true);
        $this->assertTrue($matchMethod->invoke($controller, $rooms, 4, 2, 2));
    }

    public function test_accommodation_room_catalog_matches_multi_room_guest_capacity(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'accommodationRoomCatalogMatchesGuestRequirements');
        $method->setAccessible(true);

        $roomCatalog = [
            ['capacity' => 2, 'children_capacity' => 0, 'infant_capacity' => 0, 'max_person_capacity' => 2, 'quantity' => 1],
            ['capacity' => 2, 'children_capacity' => 0, 'infant_capacity' => 0, 'max_person_capacity' => 2, 'quantity' => 1],
        ];

        $this->assertTrue($method->invoke($controller, $roomCatalog, 4, 0, 0, 2));
        $this->assertFalse($method->invoke($controller, $roomCatalog, 5, 0, 0, 2));
    }

    public function test_selected_room_count_is_used_when_validating_room_option_capacity(): void
    {
        $room = (object) [
            'capacity' => 2,
            'children_capacity' => 1,
            'infant_capacity' => 0,
            'max_person_capacity' => 4,
        ];

        $this->assertTrue(\App\Http\Controllers\Frontend\HomeController::roomMatchesSelectedGuestRequirements($room, 4, 2, 0, 2));
        $this->assertFalse(\App\Http\Controllers\Frontend\HomeController::roomMatchesSelectedGuestRequirements($room, 4, 2, 0, 1));
    }

    public function test_frontend_room_options_only_include_assigned_rate_plan_rows(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'buildAccommodationAvailability');
        $method->setAccessible(true);

        $accommodation = new \App\Models\Accommodation([
            'currency_code' => 'USD',
        ]);

        $rooms = collect([
            (object) [
                'id' => 12,
                'status' => 'Active',
                'room_name' => 'Standard',
                'room_type' => 'Double',
                'capacity' => 2,
                'children_capacity' => 1,
                'infant_capacity' => 0,
                'max_person_capacity' => 4,
                'allotment' => 3,
                'quantity' => 3,
                'base_price' => null,
            ],
        ]);

        $rates = collect([
            (object) [
                'id' => 1,
                'room_id' => 12,
                'rate_name' => 'Half Board',
                'meal_plan' => 'Breakfast',
                'pricing_setting' => 'Per Room/Night',
                'final_rate' => 100,
                'base_rate' => 100,
                'currency' => 'USD',
                'is_rate_plan' => true,
                'valid_from' => '2026-09-17',
                'valid_to' => '2026-09-18',
                'inclusions' => json_encode(['Welcome drink']),
                'is_default' => true,
            ],
            (object) [
                'id' => 2,
                'room_id' => 12,
                'rate_name' => 'Seasonal Rate',
                'meal_plan' => 'Breakfast',
                'pricing_setting' => 'Per Room/Night',
                'final_rate' => 80,
                'base_rate' => 80,
                'currency' => 'USD',
                'is_rate_plan' => false,
                'valid_from' => '2026-09-17',
                'valid_to' => '2026-09-18',
                'inclusions' => null,
                'is_default' => false,
            ],
        ]);

        $availability = $method->invoke($controller, $rooms, $rates, collect(), collect(), [
            'check_in' => '2026-09-17',
            'check_out' => '2026-09-18',
            'adults' => 2,
            'children' => 1,
            'infants' => 0,
            'nights' => 1,
        ], $accommodation);

        $this->assertCount(1, $availability);
        $this->assertSame('Half Board', $availability[0]['rate_name'] ?? '');
        $this->assertSame('Breakfast', $availability[0]['meal_plan'] ?? '');
    }

    public function test_zero_value_assigned_room_plans_still_keep_room_available_for_selection(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'buildAccommodationAvailability');
        $method->setAccessible(true);

        $accommodation = new \App\Models\Accommodation([
            'currency_code' => 'USD',
        ]);

        $rooms = collect([
            (object) [
                'id' => 21,
                'status' => 'Active',
                'room_name' => 'Standard yes',
                'room_type' => 'Double',
                'capacity' => 2,
                'children_capacity' => 1,
                'infant_capacity' => 1,
                'max_person_capacity' => 4,
                'allotment' => 3,
                'quantity' => 3,
                'base_price' => null,
            ],
        ]);

        $rates = collect([
            (object) [
                'id' => 200,
                'room_id' => 21,
                'rate_name' => 'Half Board',
                'meal_plan' => 'Breakfast',
                'pricing_setting' => 'Per Room/Night',
                'final_rate' => 0.00,
                'base_rate' => 0.00,
                'currency' => 'USD',
                'is_rate_plan' => true,
                'valid_from' => '2026-09-17',
                'valid_to' => '2026-09-18',
                'inclusions' => json_encode(['Breakfast']),
                'is_default' => true,
            ],
        ]);

        $availability = $method->invoke($controller, $rooms, $rates, collect(), collect(), [
            'check_in' => '2026-09-17',
            'check_out' => '2026-09-18',
            'adults' => 4,
            'children' => 0,
            'infants' => 0,
            'nights' => 1,
        ], $accommodation);

        $this->assertCount(1, $availability);
        $this->assertSame('Half Board', $availability[0]['rate_name'] ?? '');
        $this->assertNull($availability[0]['total_price'] ?? null);
    }

    public function test_assigned_plan_uses_default_pricing_row_when_calculating_frontend_room_price(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'buildAccommodationAvailability');
        $method->setAccessible(true);

        $accommodation = new \App\Models\Accommodation([
            'currency_code' => 'USD',
        ]);

        $rooms = collect([
            (object) [
                'id' => 25,
                'status' => 'Active',
                'room_name' => 'Garden View',
                'room_type' => 'Double',
                'capacity' => 2,
                'children_capacity' => 1,
                'infant_capacity' => 1,
                'max_person_capacity' => 4,
                'allotment' => 2,
                'quantity' => 2,
                'base_price' => null,
            ],
        ]);

        $rates = collect([
            (object) [
                'id' => 300,
                'room_id' => 25,
                'rate_name' => 'Group Rate Plan 12',
                'meal_plan' => 'Breakfast',
                'pricing_setting' => 'Per Room/Night',
                'base_rate' => 0,
                'final_rate' => 0,
                'extra_adult_rate' => 0,
                'children_rate' => 0,
                'infant_rate' => 0,
                'currency' => 'USD',
                'is_rate_plan' => true,
                'is_default' => false,
                'rate_type' => 'Rate Plan',
                'valid_from' => '2026-09-17',
                'valid_to' => '2026-09-18',
                'inclusions' => json_encode(['Breakfast']),
            ],
            (object) [
                'id' => 301,
                'room_id' => 25,
                'rate_name' => 'Group Rate Plan 12',
                'meal_plan' => 'Breakfast',
                'pricing_setting' => 'Per Room/Night',
                'base_rate' => 120,
                'final_rate' => 120,
                'extra_adult_rate' => 25,
                'children_rate' => 15,
                'infant_rate' => 0,
                'currency' => 'USD',
                'is_rate_plan' => false,
                'is_default' => true,
                'rate_type' => 'Standard',
                'valid_from' => '2026-09-17',
                'valid_to' => '2026-09-18',
                'inclusions' => json_encode(['Breakfast']),
            ],
        ]);

        $availability = $method->invoke($controller, $rooms, $rates, collect(), collect(), [
            'check_in' => '2026-09-17',
            'check_out' => '2026-09-18',
            'adults' => 2,
            'children' => 1,
            'infants' => 0,
            'nights' => 1,
        ], $accommodation);

        $this->assertCount(1, $availability);
        $this->assertSame('Group Rate Plan 12', $availability[0]['rate_name']);
        $this->assertSame(120.0, $availability[0]['total_price']);
    }

    public function test_detail_booking_context_keeps_requested_room_count(): void
    {
        $controller = new \App\Http\Controllers\Frontend\HomeController();
        $method = new ReflectionMethod($controller, 'buildDetailBookingContext');
        $method->setAccessible(true);

        $request = new \Illuminate\Http\Request([
            'check_in' => '2026-09-17',
            'check_out' => '2026-09-18',
            'adults' => 4,
            'children' => 2,
            'infants' => 0,
            'rooms' => 2,
        ]);

        $context = $method->invoke($controller, $request);

        $this->assertSame(2, (int) ($context['rooms'] ?? 0));
    }

    public function test_package_cart_item_keeps_room_count_and_room_name_for_guest_capacity(): void
    {
        $controller = new \App\Http\Controllers\Frontend\BookingController();
        $method = new ReflectionMethod($controller, 'buildPackageCartItem');
        $method->setAccessible(true);

        $request = new \Illuminate\Http\Request([
            'package_id' => 1,
            'package_name' => 'Amit test',
            'package_total_price' => 8468.00,
            'currency' => 'USD',
            'package_image' => 'https://example.com/image.jpg',
            'nights' => 2,
            'days' => 2,
            'package_start_date' => '2026-09-07',
            'adults' => 4,
            'children' => 2,
            'infants' => 0,
            'rooms' => 2,
            'rooms_required' => 2,
        ]);

        $item = $method->invoke($controller, $request);

        $this->assertArrayHasKey('room_name', $item);
        $this->assertArrayHasKey('rooms', $item);
        $this->assertGreaterThanOrEqual(2, (int) $item['rooms']);
        $this->assertNotSame('', trim((string) $item['room_name']));
    }

    public function test_package_cart_item_prefers_explicit_room_selection_when_it_is_valid(): void
    {
        $controller = new \App\Http\Controllers\Frontend\BookingController();
        $method = new ReflectionMethod($controller, 'buildPackageCartItem');
        $method->setAccessible(true);

        $request = new \Illuminate\Http\Request([
            'package_id' => 99,
            'package_name' => 'Capacity test package',
            'package_total_price' => 1200.00,
            'currency' => 'USD',
            'package_image' => 'https://example.com/image.jpg',
            'nights' => 2,
            'days' => 3,
            'package_start_date' => '2026-09-07',
            'adults' => 4,
            'children' => 2,
            'infants' => 0,
            'rooms' => 2,
            'rooms_required' => 2,
        ]);

        $item = $method->invoke($controller, $request);

        $this->assertSame(2, (int) ($item['rooms'] ?? 0));
    }

    public function test_room_matching_allows_two_rooms_for_four_adults_one_child_when_each_room_fits_two_adults_one_child(): void
    {
        $room = (object) [
            'capacity' => 2,
            'children_capacity' => 1,
            'infant_capacity' => 1,
            'max_person_capacity' => 4,
            'allotment' => 1,
        ];

        $this->assertTrue(\App\Http\Controllers\Frontend\HomeController::roomMatchesSelectedGuestRequirements($room, 4, 1, 0, 2));
        $this->assertFalse(\App\Http\Controllers\Frontend\HomeController::roomMatchesSelectedGuestRequirements($room, 4, 1, 0, 1));
    }

    public function test_package_show_uses_full_room_capacity_when_computing_required_rooms(): void
    {
        $html = view('frontend.package-show', [
            'package' => [
                'id' => 99,
                'name' => 'Capacity test package',
                'price' => 1200,
                'no_of_nights' => 2,
                'no_of_days' => 3,
                'image' => 'https://example.com/image.jpg',
                'location' => 'Mauritius',
                'days_label' => '3 Day Plan',
            ],
            'itineraryDays' => [[
                'accommodation' => [
                    'rooms' => [[
                        'id' => 1,
                        'room_name' => 'Standard Room',
                        'capacity' => 2,
                        'children_capacity' => 1,
                        'infant_capacity' => 1,
                        'max_person_capacity' => 4,
                        'allotment' => 1,
                        'quantity' => 1,
                    ]],
                    'meal_plans' => ['Breakfast'],
                ],
            ]],
            'content' => [],
            'summary' => [],
        ])->render();

        $this->assertStringContainsString('name="rooms" value="2"', $html);
        $this->assertStringContainsString('name="rooms_required" value="2"', $html);
    }

    public function test_activity_discount_uses_selected_per_person_rate_not_equipment_rate_for_same_variant(): void
    {
        Schema::dropIfExists('activity_rates');
        Schema::dropIfExists('activity_variants');
        Schema::dropIfExists('activities');

        Schema::create('activities', function ($table) {
            $table->id();
            $table->string('service_id');
            $table->string('activity_name');
            $table->string('status')->nullable();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_variants', function ($table) {
            $table->id('variant_id');
            $table->unsignedBigInteger('activity_id');
            $table->string('service_id');
            $table->string('variant_name');
            $table->string('variant_equipment_id')->nullable();
            $table->integer('max_pax')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_rates', function ($table) {
            $table->id('rate_id');
            $table->string('service_id');
            $table->unsignedBigInteger('activity_id');
            $table->string('variant_id');
            $table->string('variant_name')->nullable();
            $table->string('season')->nullable();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->string('rate_specificity');
            $table->decimal('adult_rate', 10, 2)->nullable();
            $table->decimal('children_rate', 10, 2)->nullable();
            $table->decimal('infant_rate', 10, 2)->nullable();
            $table->decimal('equipment_rate', 10, 2)->nullable();
            $table->decimal('private_exclusive_rate', 10, 2)->nullable();
            $table->timestamps();
        });

        $activity = \App\Models\Activity::create([
            'service_id' => 'SVC-ACT-PRICE',
            'activity_name' => 'Test Activity',
            'status' => 'Active',
            'operator_id' => 1,
        ]);

        $variant = \App\Models\ActivityVariant::create([
            'activity_id' => $activity->id,
            'service_id' => $activity->service_id,
            'variant_id' => 'VAR-LOW-01',
            'variant_name' => 'Low',
            'max_pax' => 10,
        ]);

        \App\Models\ActivityRate::create([
            'activity_id' => $activity->id,
            'service_id' => $activity->service_id,
            'variant_id' => $variant->variant_id,
            'variant_name' => $variant->variant_name,
            'season' => 'Low',
            'rate_specificity' => 'Per Equipment',
            'adult_rate' => null,
            'equipment_rate' => 5000.00,
            'valid_from' => '2025-01-01',
            'valid_to' => '2025-12-31',
        ]);

        \App\Models\ActivityRate::create([
            'activity_id' => $activity->id,
            'service_id' => $activity->service_id,
            'variant_id' => $variant->variant_id,
            'variant_name' => $variant->variant_name,
            'season' => 'Low',
            'rate_specificity' => 'Per Person',
            'adult_rate' => 1000.00,
            'children_rate' => 450.00,
            'infant_rate' => 0.00,
            'valid_from' => '2025-01-01',
            'valid_to' => '2025-12-31',
        ]);

        $package = new \App\Models\Package([
            'itinerary' => [
                'pricing_modes' => ['activity' => 'discount_offer'],
                'discounts' => ['activity' => 10],
            ],
        ]);

        $service = new \App\Services\PackagePricingService();
        $method = new ReflectionMethod($service, 'resolvePackageActivityAmount');
        $method->setAccessible(true);

        $amount = $method->invoke($service, $activity, ['activity_selection' => [$variant->variant_id . '|Per Person']], 4, $package, 2, 1, 1);

        $this->assertSame(2205.0, $amount);
    }
}
