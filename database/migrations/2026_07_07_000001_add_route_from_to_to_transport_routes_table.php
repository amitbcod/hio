<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            if (! Schema::hasColumn('transport_routes', 'route_from')) {
                $table->string('route_from', 60)->nullable()->after('dropoff_value');
            }
            if (! Schema::hasColumn('transport_routes', 'route_to')) {
                $table->string('route_to', 60)->nullable()->after('route_from');
            }
        });

        $indexName = 'transport_routes_route_from_route_to_index';
        if (DB::connection()->getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('transport_routes')");
            $indexExists = collect($indexes)->contains(fn (object $index): bool => $index->name === $indexName);

            if (! $indexExists) {
                Schema::table('transport_routes', function (Blueprint $table) use ($indexName): void {
                    $table->index(['route_from', 'route_to'], $indexName);
                });
            }
        } else {
            $indexExists = count(DB::select('SHOW INDEX FROM `transport_routes` WHERE Key_name = ?', [$indexName])) > 0;
            if (! $indexExists) {
            DB::statement("ALTER TABLE `transport_routes` ADD INDEX `{$indexName}` (`route_from`(100), `route_to`(100))");
            }
        }

        DB::table('transport_routes')
            ->whereNull('route_from')
            ->orWhereNull('route_to')
            ->update([
                'route_from' => DB::raw('COALESCE(route_from, pickup_value)'),
                'route_to' => DB::raw('COALESCE(route_to, dropoff_value)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->dropIndex(['route_from', 'route_to']);
            $table->dropColumn(['route_from', 'route_to']);
        });
    }
};
