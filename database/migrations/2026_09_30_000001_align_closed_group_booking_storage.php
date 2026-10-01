<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $transactionalTables = [
        'trips',
        'travellers',
        'bookings',
        'booking_line_items',
        'booking_refs',
        'booking_guests',
        'accommodation_bookings',
        'activity_bookings',
        'transport_bookings',
        'bli_traveller_allocations',
    ];

    public function up(): void
    {
        foreach ($this->transactionalTables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB");
            }
        }

        if (Schema::hasTable('transport_bookings')) {
            DB::statement("ALTER TABLE `transport_bookings` MODIFY `booking_status` ENUM('Pending', 'Processing', 'Confirmed', 'Scheduled', 'Cancelled', 'Completed') NOT NULL DEFAULT 'Processing'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transport_bookings')) {
            DB::table('transport_bookings')
                ->where('booking_status', 'Pending')
                ->update(['booking_status' => 'Processing']);
            DB::statement("ALTER TABLE `transport_bookings` MODIFY `booking_status` ENUM('Processing', 'Confirmed', 'Scheduled', 'Cancelled', 'Completed') NOT NULL DEFAULT 'Processing'");
        }

        foreach (array_reverse($this->transactionalTables) as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `{$table}` ENGINE=MyISAM");
            }
        }
    }
};