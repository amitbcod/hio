<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE booking_line_items MODIFY service_type ENUM('accommodation', 'activity', 'transport', 'package') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE booking_line_items MODIFY service_type ENUM('accommodation', 'activity', 'transport') NOT NULL");
    }
};
