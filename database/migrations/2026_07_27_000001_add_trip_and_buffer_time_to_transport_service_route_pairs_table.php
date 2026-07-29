<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_service_route_pairs', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_service_route_pairs', 'trip_time_minutes')) {
                $table->integer('trip_time_minutes')->default(60)->after('route_to');
            }
            if (!Schema::hasColumn('transport_service_route_pairs', 'buffer_time_minutes')) {
                $table->integer('buffer_time_minutes')->default(30)->after('trip_time_minutes');
            }
        });

        DB::table('transport_service_route_pairs')
            ->whereNull('trip_time_minutes')
            ->update(['trip_time_minutes' => 60]);

        DB::table('transport_service_route_pairs')
            ->whereNull('buffer_time_minutes')
            ->update(['buffer_time_minutes' => 30]);
    }

    public function down(): void
    {
        Schema::table('transport_service_route_pairs', function (Blueprint $table) {
            if (Schema::hasColumn('transport_service_route_pairs', 'trip_time_minutes')) {
                $table->dropColumn('trip_time_minutes');
            }
            if (Schema::hasColumn('transport_service_route_pairs', 'buffer_time_minutes')) {
                $table->dropColumn('buffer_time_minutes');
            }
        });
    }
};
