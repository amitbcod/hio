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

        if (!Schema::hasColumn('operator_drivers', 'email')) {
            return;
        }

        // Make email nullable and set default empty string to avoid insert failures.
        try {
            DB::statement("ALTER TABLE `operator_drivers` MODIFY COLUMN `email` VARCHAR(191) NULL DEFAULT ''");
        } catch (\Exception $e) {
            // ignore failures; migration is best-effort
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('operator_drivers') || !Schema::hasColumn('operator_drivers', 'email')) {
            return;
        }

        try {
            DB::statement("ALTER TABLE `operator_drivers` MODIFY COLUMN `email` VARCHAR(191) NOT NULL");
        } catch (\Exception $e) {
            // ignore
        }
    }
};
