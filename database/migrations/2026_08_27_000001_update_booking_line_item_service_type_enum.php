<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE booking_line_items MODIFY service_type ENUM('accommodation', 'activity', 'transport', 'package') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE booking_line_items MODIFY service_type ENUM('accommodation', 'activity', 'transport') NOT NULL");
        }
    }
};
