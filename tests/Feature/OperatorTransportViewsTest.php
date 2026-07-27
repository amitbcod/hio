<?php

namespace Tests\Feature;

use App\Models\Operator;
use App\Models\Transport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OperatorTransportViewsTest extends TestCase
{
    public function test_edit_transport_view_shows_steps_sidebar(): void
    {
        $transport = new Transport([
            'id' => 2,
            'operator_id' => 10,
            'vehicle_name' => 'Sedan',
            'vehicle_type' => 'Sedan',
        ]);

        $view = view('operator.transport.edit', compact('transport'))
            ->with('errors', new \Illuminate\Support\ViewErrorBag());
        $html = $view->render();

        $this->assertStringContainsString('Transport Steps', $html);
        $this->assertStringContainsString('Step 1: Basics', $html);
    }

    public function test_routing_step_view_hides_route_id_field(): void
    {
        $transport = new Transport(['id' => 2, 'operator_id' => 10]);
        $routes = collect();
        $vehicleTypes = ['sedan' => 'Sedan'];
        $serviceGroups = [];
        $regionOptions = ['Airport', 'North', 'South'];

        $view = view('operator.transport.step2-routes-pricing', compact('transport', 'routes', 'vehicleTypes', 'serviceGroups', 'regionOptions'))
            ->with('errors', new \Illuminate\Support\ViewErrorBag());
        $html = $view->render();

        $this->assertStringNotContainsString('Route ID', $html);
    }

    public function test_basic_details_step_view_renders_first_step_heading(): void
    {
        $view = view('operator.transport.basic-details', [
            'step' => 1,
            'title' => 'Transport Basic',
            'description' => 'Enter the basic transport details.',
            'transportSettings' => [],
        ])->with('errors', new \Illuminate\Support\ViewErrorBag());
        $html = $view->render();

        $this->assertStringContainsString('Step 1: Transport Basic', $html);
        $this->assertStringContainsString('Basic details', $html);
    }

    public function test_operator_step2_routes_pricing_resolves_region_model(): void
    {
        if (!Schema::hasTable('regions')) {
            Schema::create('regions', function ($table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('transport_vehicle_types')) {
            Schema::create('transport_vehicle_types', function ($table) {
                $table->id();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->integer('seat_capacity')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('transport_routes')) {
            Schema::create('transport_routes', function ($table) {
                $table->id();
                $table->foreignId('transport_id')->constrained('transports')->cascadeOnDelete();
                $table->string('route_from')->nullable();
                $table->string('route_to')->nullable();
                $table->string('service_type')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('transports')) {
            Schema::create('transports', function ($table) {
                $table->id();
                $table->foreignId('operator_id')->nullable();
                $table->string('service_id')->nullable();
                $table->string('vehicle_name')->nullable();
                $table->string('vehicle_type')->nullable();
                $table->integer('seating_capacity')->nullable();
                $table->string('registration_number')->nullable();
                $table->string('status')->nullable();
                $table->string('approval_status')->nullable();
                $table->timestamps();
            });
        }

        $operator = new Operator(['id' => 10]);
        Auth::guard('operator')->setUser($operator);

        $transport = Transport::create([
            'operator_id' => 10,
            'service_id' => 'TRNTEST01',
            'vehicle_name' => 'Sedan',
            'vehicle_type' => 'Sedan',
            'seating_capacity' => 4,
            'status' => 'Draft',
            'approval_status' => 'Draft',
        ]);

        $controller = new \App\Http\Controllers\Operator\TransportController();
        $response = $controller->step2RoutesPricing($transport);

        $this->assertNotNull($response);
        $this->assertStringContainsString('Airport Transfer', $response->render());
    }
}
