<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            DB::statement("ALTER TABLE `transports` MODIFY `approval_status` ENUM('Draft', 'Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Draft'");
        });

        DB::table('transports')
            ->where('approval_status', 'Pending')
            ->whereNull('submitted_for_approval_at')
            ->update(['approval_status' => 'Draft']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            DB::statement("ALTER TABLE `transports` MODIFY `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending'");
        });

        DB::table('transports')
            ->where('approval_status', 'Draft')
            ->whereNull('submitted_for_approval_at')
            ->update(['approval_status' => 'Pending']);
    }
};
