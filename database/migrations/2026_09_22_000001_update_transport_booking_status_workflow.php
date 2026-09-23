<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `transport_bookings` MODIFY `booking_status` ENUM('Processing','Confirmed','Scheduled','Cancelled','Completed') NOT NULL DEFAULT 'Processing'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `transport_bookings` MODIFY `booking_status` ENUM('Pending','Confirmed','Cancelled','Completed') NOT NULL DEFAULT 'Pending'");
    }
};