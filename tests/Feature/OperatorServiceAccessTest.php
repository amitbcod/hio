<?php

namespace Tests\Feature;

use App\Http\Middleware\RestrictOperatorServiceModules;
use App\Models\Operator;
use App\Models\OperatorProfile;
use App\Models\OperatorUser;
use App\Services\OperatorServiceAccess;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class OperatorServiceAccessTest extends TestCase
{
    private OperatorServiceAccess $serviceAccess;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'operator_service_access_test',
            'database.connections.operator_service_access_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('operator_service_access_test');
        DB::setDefaultConnection('operator_service_access_test');

        Schema::create('operator_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('operator_id')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->text('service_types')->nullable();
            $table->timestamps();
        });

        $this->serviceAccess = new OperatorServiceAccess();
    }

    public function test_selected_services_are_matched_as_complete_case_insensitive_values(): void
    {
        OperatorProfile::create([
            'operator_id' => 'OP-1',
            'service_types' => ['accommodation', 'Activity', 'Transport Extra'],
        ]);
        $operator = new Operator(['operator_id' => 'OP-1']);

        $this->assertSame(['Accommodation', 'Activity'], $this->serviceAccess->selectedServices($operator));
        $this->assertTrue($this->serviceAccess->allows($operator, 'Accommodation'));
        $this->assertFalse($this->serviceAccess->allows($operator, 'Transport'));
    }

    public function test_double_encoded_service_types_are_read_for_existing_profiles(): void
    {
        DB::table('operator_profiles')->insert([
            'operator_id' => 'OP-DOUBLE-ENCODED',
            'service_types' => json_encode(json_encode(['Accommodation', 'Transport'])),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $operator = new Operator(['operator_id' => 'OP-DOUBLE-ENCODED']);

        $this->assertSame(
            ['Accommodation', 'Transport'],
            $this->serviceAccess->selectedServices($operator)
        );
    }

    public function test_missing_null_empty_and_malformed_profiles_grant_no_service_access(): void
    {
        $missingProfileOperator = new Operator(['operator_id' => 'MISSING']);
        $this->assertSame([], $this->serviceAccess->selectedServices($missingProfileOperator));

        OperatorProfile::create(['operator_id' => 'NULL', 'service_types' => null]);
        OperatorProfile::create(['operator_id' => 'EMPTY', 'service_types' => []]);
        DB::table('operator_profiles')->insert([
            'operator_id' => 'MALFORMED',
            'service_types' => '{invalid json',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame([], $this->serviceAccess->selectedServices(new Operator(['operator_id' => 'NULL'])));
        $this->assertSame([], $this->serviceAccess->selectedServices(new Operator(['operator_id' => 'EMPTY'])));
        $this->assertSame([], $this->serviceAccess->selectedServices(new Operator(['operator_id' => 'MALFORMED'])));
    }

    public function test_business_scoped_profile_is_used_for_owner_and_staff(): void
    {
        OperatorProfile::create([
            'operator_id' => 'OP-2',
            'service_types' => ['Accommodation'],
        ]);
        OperatorProfile::create([
            'operator_id' => 'OP-2',
            'business_id' => 20,
            'service_types' => ['Transport'],
        ]);
        $owner = new Operator(['operator_id' => 'OP-2', 'business_id' => 20]);
        $staff = new OperatorUser(['operator_id' => 'OP-2', 'business_id' => 20]);

        $this->assertSame(['Transport'], $this->serviceAccess->selectedServices($owner));
        $this->assertSame(['Transport'], $this->serviceAccess->selectedServices($staff));
    }

    public function test_middleware_denies_direct_access_to_an_unselected_module(): void
    {
        OperatorProfile::create([
            'operator_id' => 'OP-3',
            'service_types' => ['Accommodation'],
        ]);
        $operator = new Operator(['operator_id' => 'OP-3']);
        $request = Request::create('/operator/activity', 'GET');
        $request->setUserResolver(fn (?string $guard = null) => $operator);
        $route = new \Illuminate\Routing\Route(['GET'], '/operator/activity', fn () => response()->noContent());
        $route->name('operator.activity.index');
        $route->bind($request);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });
        $middleware = new RestrictOperatorServiceModules($this->serviceAccess);

        try {
            $middleware->handle($request, fn () => response()->noContent());
            $this->fail('Direct access to an unselected service module must be denied.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_middleware_allows_selected_service_routes_and_leaves_unrelated_routes_alone(): void
    {
        OperatorProfile::create([
            'operator_id' => 'OP-4',
            'service_types' => ['Activity', 'Transport'],
        ]);
        $operator = new Operator(['operator_id' => 'OP-4']);
        $middleware = new RestrictOperatorServiceModules($this->serviceAccess);

        foreach (['operator.activity.index', 'operator.transport.index', 'operator.feedback.index'] as $routeName) {
            $request = Request::create('/operator/module', 'GET');
            $request->setUserResolver(fn (?string $guard = null) => $operator);
            $route = new \Illuminate\Routing\Route(['GET'], '/operator/module', fn () => response()->noContent());
            $route->name($routeName);
            $route->bind($request);
            $request->setRouteResolver(function () use ($route) {

                return $route;
            });

            $response = $middleware->handle($request, fn () => response()->noContent());
            $this->assertSame(204, $response->getStatusCode());
        }
    }

    public function test_booking_postponement_and_driver_routes_are_classified_by_service(): void
    {
        $this->assertSame(
            'Activity',
            $this->serviceAccess->serviceForRouteName('operator.bookings.postpone', ['serviceType' => 'activity'])
        );
        $this->assertSame(
            'Activity',
            $this->serviceAccess->serviceForRouteName('operator.bookings.postpone.activity-slots')
        );
        $this->assertSame('Transport', $this->serviceAccess->serviceForRouteName('operator.drivers.index'));
    }

    public function test_sidebar_only_hides_the_unselected_management_sections(): void
    {
        $this->app['request']->attributes->set('operator.selected_services', ['Activity']);

        $sidebar = view('operator.management._sidebar')->render();

        $this->assertStringContainsString('Trips', $sidebar);
        $this->assertStringContainsString('Shared Trip Links', $sidebar);
        $this->assertStringContainsString('Activity Management', $sidebar);
        $this->assertStringNotContainsString('Property Management', $sidebar);
        $this->assertStringNotContainsString('Transport Management', $sidebar);
    }

    public function test_operator_navigation_links_prefer_trip_listing_over_accommodation_default(): void
    {
        $layout = view('layouts.app')->render();
        $frontendLayout = view('frontend.layout')->render();

        $this->assertStringContainsString(route('operator.trips.index'), $layout);
        $this->assertStringContainsString(route('operator.trips.index'), $frontendLayout);
        $this->assertStringNotContainsString('/operator/accommodation', $layout);
        $this->assertStringNotContainsString('/operator/accommodation', $frontendLayout);
    }
}
