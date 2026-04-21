<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_bookings', function (Blueprint $table) {
            // Store time slot selections per participant as JSON
            // Format: { "participant_1": "08:00-11:30", "participant_2": "13:00-16:30", ... }
            $table->json('participant_time_slots')->nullable()->after('special_requests');
        });
    }

    public function down(): void
    {
        Schema::table('activity_bookings', function (Blueprint $table) {
            $table->dropColumn('participant_time_slots');
        });
    }
};
