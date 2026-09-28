<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            $table->foreignId('vehicle_name_id')
                ->nullable()
                ->after('vehicle_type')
                ->constrained('transport_vehicle_names')
                ->nullOnDelete();
        });

        DB::table('transports')
            ->whereNotNull('vehicle_name')
            ->whereNotNull('vehicle_type')
            ->orderBy('id')
            ->get(['id', 'vehicle_name', 'vehicle_type'])
            ->each(function ($transport): void {
                $vehicleNameId = DB::table('transport_vehicle_names as names')
                    ->join('transport_vehicle_types as types', 'types.id', '=', 'names.transport_vehicle_type_id')
                    ->where('names.name', trim((string) $transport->vehicle_name))
                    ->where('types.name', trim((string) $transport->vehicle_type))
                    ->value('names.id');

                if ($vehicleNameId) {
                    DB::table('transports')
                        ->where('id', $transport->id)
                        ->update(['vehicle_name_id' => $vehicleNameId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_name_id');
        });
    }
};