<?php

namespace Tests\Feature;

use App\Http\Controllers\Operator\TransportController;
use App\Models\Region;
use App\Models\Transport;
use App\Models\TransportServiceRoutePair;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TransportServiceRoutePairConfigTest extends TestCase
{
    public function test_step2_routes_pricing_uses_admin_configured_service_pairs(): void
    {
        if (!Schema::hasTable('regions')) {
            Schema::create('regions', function ($table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('transport_service_route_pairs')) {
            Schema::create('transport_service_route_pairs', function ($table) {
                $table->id();
                $table->string('service_type');
                $table->string('route_from');
                $table->string('route_to');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['service_type', 'route_from', 'route_to']);
            });
        }

        Region::query()->delete();
        TransportServiceRoutePair::query()->delete();

        Region::create(['name' => 'North']);
        Region::create(['name' => 'South']);

        TransportServiceRoutePair::create([
            'service_type' => 'activity_transfer',
            'route_from' => 'Airport',
            'route_to' => 'North',
            'is_active' => true,
        ]);

        $operator = new \App\Models\Operator(['id' => 99]);
        Auth::guard('operator')->setUser($operator);

        $transport = Transport::create([
            'operator_id' => 99,
            'service_id' => 'TRNTESTCONFIG',
            'vehicle_name' => 'Sedan',
            'vehicle_type' => 'Sedan',
            'seating_capacity' => 4,
            'status' => 'Draft',
            'approval_status' => 'Draft',
        ]);

        $controller = new TransportController();
        $view = $controller->step2RoutesPricing($transport);
        $data = $view->getData();

        $this->assertSame('Airport', $data['serviceGroups']['activity_transfer']['routes'][0]['route_from']);
        $this->assertSame('North', $data['serviceGroups']['activity_transfer']['routes'][0]['route_to']);
    }
}
