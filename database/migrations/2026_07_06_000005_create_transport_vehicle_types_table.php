<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transport_vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('seat_capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('transport_vehicle_types')->insert([
            ['name' => 'Bus', 'seat_capacity' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Coach', 'seat_capacity' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mini bus', 'seat_capacity' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Family car', 'seat_capacity' => 7, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Standard car', 'seat_capacity' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Taxi', 'seat_capacity' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Seat in Coach', 'seat_capacity' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Seat in Mini bus', 'seat_capacity' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('transport_vehicle_types');
    }
};
