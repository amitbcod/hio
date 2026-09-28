<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_vehicle_names', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('transport_vehicle_type_id')
                ->constrained('transport_vehicle_types')
                ->restrictOnDelete();
            $table->unsignedSmallInteger('seat_capacity');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['name', 'transport_vehicle_type_id'], 'transport_vehicle_names_name_type_unique');
        });

        if (Schema::hasColumn('transport_vehicle_types', 'seat_capacity')) {
            DB::table('transport_vehicle_types')
                ->whereNotNull('seat_capacity')
                ->orderBy('id')
                ->get(['id', 'name', 'seat_capacity', 'is_active', 'created_at', 'updated_at'])
                ->each(function ($vehicleType): void {
                    DB::table('transport_vehicle_names')->insert([
                        'name' => $vehicleType->name,
                        'transport_vehicle_type_id' => $vehicleType->id,
                        'seat_capacity' => $vehicleType->seat_capacity,
                        'is_active' => $vehicleType->is_active,
                        'created_at' => $vehicleType->created_at ?? now(),
                        'updated_at' => $vehicleType->updated_at ?? now(),
                    ]);
                });

            if (Schema::hasTable('transports')) {
                DB::table('transports')
                    ->whereNotNull('vehicle_name')
                    ->whereNotNull('vehicle_type')
                    ->orderBy('id')
                    ->get(['vehicle_name', 'vehicle_type', 'seating_capacity'])
                    ->each(function ($transport): void {
                        $vehicleType = DB::table('transport_vehicle_types')
                            ->where('name', $transport->vehicle_type)
                            ->first(['id', 'seat_capacity']);

                        if (!$vehicleType) {
                            return;
                        }

                        DB::table('transport_vehicle_names')->updateOrInsert(
                            [
                                'name' => $transport->vehicle_name,
                                'transport_vehicle_type_id' => $vehicleType->id,
                            ],
                            [
                                'seat_capacity' => max(1, (int) ($transport->seating_capacity ?: $vehicleType->seat_capacity ?: 1)),
                                'is_active' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    });
            }

            Schema::table('transport_vehicle_types', function (Blueprint $table) {
                $table->dropColumn('seat_capacity');
            });
        }
    }

    public function down(): void
    {
        Schema::table('transport_vehicle_types', function (Blueprint $table) {
            $table->unsignedSmallInteger('seat_capacity')->nullable();
        });

        DB::table('transport_vehicle_names')
            ->orderBy('id')
            ->get(['transport_vehicle_type_id', 'seat_capacity'])
            ->each(function ($vehicleName): void {
                DB::table('transport_vehicle_types')
                    ->where('id', $vehicleName->transport_vehicle_type_id)
                    ->whereNull('seat_capacity')
                    ->update(['seat_capacity' => $vehicleName->seat_capacity]);
            });

        Schema::dropIfExists('transport_vehicle_names');
    }
};