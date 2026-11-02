<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transport_bookings') && ! Schema::hasColumn('transport_bookings', 'transport_vehicle_id')) {
            Schema::table('transport_bookings', function (Blueprint $table) {
                $table->foreignId('transport_vehicle_id')
                    ->nullable()
                    ->after('transport_id')
                    ->constrained('transport_vehicles')
                    ->nullOnDelete();
            });
        }

        $indexName = 'tb_transport_vehicle_status_idx';
        $indexExists = $this->hasIndex($indexName);

        if (!$indexExists) {
            Schema::table('transport_bookings', function (Blueprint $table) use ($indexName) {
                $table->index(
                    ['transport_id', 'transport_vehicle_id', 'booking_status'],
                    $indexName
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('transport_bookings') || ! Schema::hasColumn('transport_bookings', 'transport_vehicle_id')) {
            return;
        }

        $indexName = 'tb_transport_vehicle_status_idx';
        $indexExists = $this->hasIndex($indexName);

        Schema::table('transport_bookings', function (Blueprint $table) use ($indexName, $indexExists) {
            if ($indexExists) {
                $table->dropIndex($indexName);
            }

            $table->dropForeign(['transport_vehicle_id']);
            $table->dropColumn('transport_vehicle_id');
        });
    }

    private function hasIndex(string $indexName): bool
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('transport_bookings')"))
                ->contains(fn (object $index): bool => $index->name === $indexName);
        }

        return collect(DB::select('SHOW INDEX FROM `transport_bookings`'))
            ->contains(fn (object $index): bool => ($index->Key_name ?? null) === $indexName);
    }
};