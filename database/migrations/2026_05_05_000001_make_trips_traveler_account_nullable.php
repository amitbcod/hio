<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('trips') && Schema::hasColumn('trips', 'traveler_account_id')) {
            DB::statement('ALTER TABLE `trips` MODIFY `traveler_account_id` BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('trips') && Schema::hasColumn('trips', 'traveler_account_id')) {
            DB::statement('ALTER TABLE `trips` MODIFY `traveler_account_id` BIGINT UNSIGNED NOT NULL');
        }
    }
};
