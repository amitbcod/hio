<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('place_name', 100)->unique();
            $table->enum('route_region', ['North', 'South', 'Airport']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('places')->insertOrIgnore([
            ['place_name' => 'Sir Seewoosagur Ramgoolam Airport', 'route_region' => 'Airport', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['place_name' => 'Grand Baie', 'route_region' => 'North', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['place_name' => 'Trou aux Biches', 'route_region' => 'North', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['place_name' => 'Pereybere', 'route_region' => 'North', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['place_name' => 'Flic en Flac', 'route_region' => 'South', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['place_name' => 'Le Morne', 'route_region' => 'South', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['place_name' => 'Mahebourg', 'route_region' => 'South', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['place_name' => 'Belle Mare', 'route_region' => 'South', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
