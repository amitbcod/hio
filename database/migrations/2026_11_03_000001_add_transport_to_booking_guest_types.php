<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('booking_guests')
            || ! Schema::hasColumn('booking_guests', 'booking_type')
            || DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE `booking_guests` MODIFY `booking_type` ENUM('accommodation', 'activity', 'transport') NOT NULL",
        );
    }

    public function down(): void
    {
        // Keep the transport value valid so rollback cannot make existing guest rows invalid.
    }
};
