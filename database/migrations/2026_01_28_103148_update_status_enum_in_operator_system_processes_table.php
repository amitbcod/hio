<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the 'status' column (MySQL only)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE `operator_system_processes` 
                CHANGE `status` `status` ENUM('draft','active','inactive') 
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci 
                NULL DEFAULT 'draft';
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to previous ENUM values if known (MySQL only)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE `operator_system_processes` 
                CHANGE `status` `status` ENUM('draft','active') 
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci 
                NULL DEFAULT 'draft';
            ");
        }
    }
};
