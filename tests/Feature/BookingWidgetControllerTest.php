<?php

namespace Tests\Feature;

use App\Models\BookingWidget;
use App\Models\Region;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookingWidgetControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['booking_widgets', 'regions'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('booking_widgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id');
            $table->string('widget_token', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function test_widget_region_options_include_service_types_and_regions(): void
    {
        Region::create(['name' => 'North']);
        Region::create(['name' => 'South']);

        $response = $this->getJson('/widget/regions');

        $response->assertOk();
        $response->assertJsonPath('service_types.0.value', 'airport_transfer');
        $response->assertJsonPath('service_types.4.value', 'full_day_sightseeing');
        $response->assertJsonPath('regions.0.name', 'North');
    }

    public function test_widget_redirect_keeps_transport_service_type_and_regions(): void
    {
        $north = Region::create(['name' => 'North']);
        $south = Region::create(['name' => 'South']);
        BookingWidget::create([
            'operator_id' => 99,
            'widget_token' => 'widget-token-123',
            'is_active' => true,
        ]);

        $response = $this->get('/widget/track-redirect?token=widget-token-123&service=transport&service_type=airport_transfer&pickup_region_id=' . $north->id . '&dropoff_region_id=' . $south->id . '&pickup_date=2026-10-15&pickup_time=14:30&passengers=3');

        $response->assertStatus(302);
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/category-list?category=transport', $location);
        $this->assertStringContainsString('service_type=airport_transfer', $location);
        $this->assertStringContainsString('pickup_region_id=' . $north->id, $location);
        $this->assertStringContainsString('dropoff_region_id=' . $south->id, $location);
        $this->assertStringContainsString('arrival_date=2026-10-15', $location);
        $this->assertStringContainsString('arrival_time=14%3A30', $location);
        $this->assertStringContainsString('passengers=3', $location);
    }
}
