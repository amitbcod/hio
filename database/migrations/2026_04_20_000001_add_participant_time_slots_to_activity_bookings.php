<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_bookings') && ! Schema::hasColumn('activity_bookings', 'participant_time_slots')) {
            Schema::table('activity_bookings', function (Blueprint $table) {
                $table->json('participant_time_slots')->nullable()->after('special_requests');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('activity_bookings') && Schema::hasColumn('activity_bookings', 'participant_time_slots')) {
            Schema::table('activity_bookings', function (Blueprint $table) {
                $table->dropColumn('participant_time_slots');
            });
        }
    }
};
