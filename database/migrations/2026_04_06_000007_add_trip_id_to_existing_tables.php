<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodations', 'trip_id')) {
                $table->foreignId('trip_id')->nullable()->constrained('trips')->onDelete('set null')->after('id');
            }
        });

        Schema::table('activity_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_bookings', 'trip_id')) {
                $table->foreignId('trip_id')->nullable()->constrained('trips')->onDelete('set null')->after('id');
            }
        });

        // Add to other relevant tables if needed
    }

    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (Schema::hasColumn('accommodations', 'trip_id')) {
                $table->dropForeign(['trip_id']);
                $table->dropColumn('trip_id');
            }
        });

        Schema::table('activity_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('activity_bookings', 'trip_id')) {
                $table->dropForeign(['trip_id']);
                $table->dropColumn('trip_id');
            }
        });
    }
};
