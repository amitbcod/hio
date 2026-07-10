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

        if (!Schema::hasColumn('operator_drivers', 'full_name')) {
            return;
        }

        // Use raw SQL to modify the column to be nullable with a default empty string.
        // Use VARCHAR(191) to avoid MySQL index length limits on older MySQL versions.
        DB::statement("ALTER TABLE `operator_drivers` MODIFY COLUMN `full_name` VARCHAR(191) NULL DEFAULT ''");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('operator_drivers')) {
            return;
        }

        if (!Schema::hasColumn('operator_drivers', 'full_name')) {
            return;
        }

        // Revert to NOT NULL without default (best-effort). If this fails, manual revert may be required.
        try {
            DB::statement("ALTER TABLE `operator_drivers` MODIFY COLUMN `full_name` VARCHAR(191) NOT NULL");
        } catch (\Exception $e) {
            // ignore
        }
    }
};
