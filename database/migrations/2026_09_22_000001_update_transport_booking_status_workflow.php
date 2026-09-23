<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First include Processing so MySQL can accept it during data cleanup.
        DB::statement("ALTER TABLE `transport_bookings` MODIFY `booking_status` ENUM('Pending','Processing','Confirmed','Scheduled','Cancelled','Completed') NULL DEFAULT 'Processing'");

        // Existing test rows may still contain the retired Pending value.
        DB::table('transport_bookings')
            ->whereNotIn('booking_status', ['Processing', 'Confirmed', 'Scheduled', 'Cancelled', 'Completed'])
            ->update(['booking_status' => 'Processing']);

        DB::statement("ALTER TABLE `transport_bookings` MODIFY `booking_status` ENUM('Processing','Confirmed','Scheduled','Cancelled','Completed') NOT NULL DEFAULT 'Processing'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `transport_bookings` MODIFY `booking_status` ENUM('Pending','Confirmed','Cancelled','Completed') NOT NULL DEFAULT 'Pending'");
    }
};