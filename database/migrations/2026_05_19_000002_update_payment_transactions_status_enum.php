<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE `payment_transactions` MODIFY `status` ENUM('pending','paid','refunded','failed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE `payment_transactions` MODIFY `status` ENUM('pending','paid','refunded') NOT NULL DEFAULT 'pending'");
    }
};
