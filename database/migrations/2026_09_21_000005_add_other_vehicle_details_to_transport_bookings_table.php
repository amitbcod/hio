<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            $table->string('other_vehicle_name')->nullable()->after('transport_vehicle_id');
            $table->string('other_vehicle_license_number')->nullable()->after('other_vehicle_name');
        });
    }

    public function down(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            $table->dropColumn(['other_vehicle_name', 'other_vehicle_license_number']);
        });
    }
};
