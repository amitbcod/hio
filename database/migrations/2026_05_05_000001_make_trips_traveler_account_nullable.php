<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite'
            && Schema::hasTable('trips')
            && Schema::hasColumn('trips', 'traveler_account_id')) {
            DB::statement('ALTER TABLE `trips` MODIFY `traveler_account_id` BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite'
            && Schema::hasTable('trips')
            && Schema::hasColumn('trips', 'traveler_account_id')) {
            DB::statement('ALTER TABLE `trips` MODIFY `traveler_account_id` BIGINT UNSIGNED NOT NULL');
        }
    }
};
