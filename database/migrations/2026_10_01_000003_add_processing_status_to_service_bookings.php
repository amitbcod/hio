<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('accommodation_bookings') && Schema::hasColumn('accommodation_bookings', 'booking_status')) {
            DB::statement("ALTER TABLE `accommodation_bookings` MODIFY `booking_status` ENUM('Pending', 'Processing', 'Confirmed', 'Cancelled') NOT NULL DEFAULT 'Pending'");
        }

        if (Schema::hasTable('activity_bookings') && Schema::hasColumn('activity_bookings', 'booking_status')) {
            DB::statement("ALTER TABLE `activity_bookings` MODIFY `booking_status` ENUM('Pending', 'Processing', 'Confirmed', 'Cancelled') NOT NULL DEFAULT 'Pending'");
        }
    }

    public function down(): void
    {
        // Retain Processing in both enums so existing settled booking data remains valid.
    }
};