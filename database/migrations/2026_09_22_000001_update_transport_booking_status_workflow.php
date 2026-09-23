<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL rejects the enum change when existing rows contain values that
        // are not present in the new enum, such as the retired Pending value.
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