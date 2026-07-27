<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\TransportServiceRoutePair;

return new class extends Migration
{
    public function up(): void
    {
        $pairs = [
            // Airport Transfer
            ['service_type' => 'airport_transfer', 'route_from' => 'Airport', 'route_to' => 'North', 'is_active' => true],
            ['service_type' => 'airport_transfer', 'route_from' => 'Airport', 'route_to' => 'South', 'is_active' => true],
            ['service_type' => 'airport_transfer', 'route_from' => 'Airport', 'route_to' => 'East', 'is_active' => true],
            ['service_type' => 'airport_transfer', 'route_from' => 'Airport', 'route_to' => 'West', 'is_active' => true],

            // Activity Transfer
            ['service_type' => 'activity_transfer', 'route_from' => 'Airport', 'route_to' => 'North', 'is_active' => true],
            ['service_type' => 'activity_transfer', 'route_from' => 'Airport', 'route_to' => 'South', 'is_active' => true],
            ['service_type' => 'activity_transfer', 'route_from' => 'Airport', 'route_to' => 'East', 'is_active' => true],
            ['service_type' => 'activity_transfer', 'route_from' => 'Airport', 'route_to' => 'West', 'is_active' => true],
            ['service_type' => 'activity_transfer', 'route_from' => 'North', 'route_to' => 'South', 'is_active' => true],
            ['service_type' => 'activity_transfer', 'route_from' => 'South', 'route_to' => 'North', 'is_active' => true],
            ['service_type' => 'activity_transfer', 'route_from' => 'North', 'route_to' => 'East', 'is_active' => true],
            ['service_type' => 'activity_transfer', 'route_from' => 'North', 'route_to' => 'West', 'is_active' => true],
            ['service_type' => 'activity_transfer', 'route_from' => 'South', 'route_to' => 'East', 'is_active' => true],
            ['service_type' => 'activity_transfer', 'route_from' => 'South', 'route_to' => 'West', 'is_active' => true],

            // Full Day Sightseeing
            ['service_type' => 'full_day_sightseeing', 'route_from' => 'Airport', 'route_to' => 'North', 'is_active' => true],
            ['service_type' => 'full_day_sightseeing', 'route_from' => 'Airport', 'route_to' => 'South', 'is_active' => true],
            ['service_type' => 'full_day_sightseeing', 'route_from' => 'Airport', 'route_to' => 'East', 'is_active' => true],
            ['service_type' => 'full_day_sightseeing', 'route_from' => 'Airport', 'route_to' => 'West', 'is_active' => true],
            ['service_type' => 'full_day_sightseeing', 'route_from' => 'North', 'route_to' => 'South', 'is_active' => true],
            ['service_type' => 'full_day_sightseeing', 'route_from' => 'South', 'route_to' => 'North', 'is_active' => true],
            ['service_type' => 'full_day_sightseeing', 'route_from' => 'North', 'route_to' => 'East', 'is_active' => true],
            ['service_type' => 'full_day_sightseeing', 'route_from' => 'North', 'route_to' => 'West', 'is_active' => true],
            ['service_type' => 'full_day_sightseeing', 'route_from' => 'South', 'route_to' => 'East', 'is_active' => true],
            ['service_type' => 'full_day_sightseeing', 'route_from' => 'South', 'route_to' => 'West', 'is_active' => true],
        ];

        foreach ($pairs as $pair) {
            TransportServiceRoutePair::firstOrCreate(
                [
                    'service_type' => $pair['service_type'],
                    'route_from' => $pair['route_from'],
                    'route_to' => $pair['route_to'],
                ],
                ['is_active' => $pair['is_active']]
            );
        }
    }

    public function down(): void
    {
        TransportServiceRoutePair::query()->delete();
    }
};
