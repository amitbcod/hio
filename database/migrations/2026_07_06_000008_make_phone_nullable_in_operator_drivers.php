<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('operator_drivers')) {
            return;
        }

        if (!Schema::hasColumn('operator_drivers', 'phone')) {
            return;
        }

        try {
            DB::statement("ALTER TABLE `operator_drivers` MODIFY COLUMN `phone` VARCHAR(191) NULL DEFAULT ''");
        } catch (\Exception $e) {
            // best-effort
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('operator_drivers') || !Schema::hasColumn('operator_drivers', 'phone')) {
            return;
        }

        try {
            DB::statement("ALTER TABLE `operator_drivers` MODIFY COLUMN `phone` VARCHAR(191) NOT NULL");
        } catch (\Exception $e) {
            // ignore
        }
    }
};
