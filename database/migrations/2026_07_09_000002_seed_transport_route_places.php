<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE `transport_routes` SET `route_from` = COALESCE(NULLIF(`route_from`, ''), NULLIF(`pickup_value`, ''), 'Unknown Departure') WHERE `route_from` IS NULL OR `route_from` = ''");
        DB::statement("UPDATE `transport_routes` SET `route_to` = COALESCE(NULLIF(`route_to`, ''), NULLIF(`dropoff_value`, ''), 'Unknown Destination') WHERE `route_to` IS NULL OR `route_to` = ''");
    }

    public function down(): void
    {
        // Do not remove generated route place descriptions automatically.
    }
};
