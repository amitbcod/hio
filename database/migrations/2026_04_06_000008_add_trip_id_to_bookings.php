<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('accommodation_bookings')) {
            Schema::table('accommodation_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('accommodation_bookings', 'trip_id')) {
                    $table->foreignId('trip_id')->nullable()->constrained('trips')->onDelete('set null')->after('booking_reference');
                }
            });
        }

        if (Schema::hasTable('activity_bookings')) {
            Schema::table('activity_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('activity_bookings', 'trip_id')) {
                    $table->foreignId('trip_id')->nullable()->constrained('trips')->onDelete('set null')->after('booking_reference');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('accommodation_bookings')) {
            Schema::table('accommodation_bookings', function (Blueprint $table) {
                if (Schema::hasColumn('accommodation_bookings', 'trip_id')) {
                    $table->dropForeign(['trip_id']);
                    $table->dropColumn('trip_id');
                }
            });
        }

        if (Schema::hasTable('activity_bookings')) {
            Schema::table('activity_bookings', function (Blueprint $table) {
                if (Schema::hasColumn('activity_bookings', 'trip_id')) {
                    $table->dropForeign(['trip_id']);
                    $table->dropColumn('trip_id');
                }
            });
        }
    }
};
