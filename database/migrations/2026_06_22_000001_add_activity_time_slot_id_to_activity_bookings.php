<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_bookings', function (Blueprint $table) {
            // Store single activity time slot ID for the entire booking
            if (!Schema::hasColumn('activity_bookings', 'activity_time_slot_id')) {
                $table->unsignedBigInteger('activity_time_slot_id')->nullable()->after('special_requests');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activity_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('activity_bookings', 'activity_time_slot_id')) {
                $table->dropColumn('activity_time_slot_id');
            }
        });
    }
};
