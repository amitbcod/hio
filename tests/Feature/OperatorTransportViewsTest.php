<?php

namespace Tests\Feature;

use App\Models\Transport;
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

        $view = view('operator.transport.step2-routes-pricing', compact('transport', 'routes', 'vehicleTypes'))
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
}
