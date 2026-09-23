<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_bookings', 'trip_type')) {
                $table->string('trip_type', 20)->nullable()->after('booking_reference');
            }
            if (!Schema::hasColumn('transport_bookings', 'transport_group_reference')) {
                $table->string('transport_group_reference', 100)->nullable()->after('trip_type');
                $table->index('transport_group_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('transport_bookings', 'transport_group_reference')) {
                $table->dropColumn('transport_group_reference');
            }
            if (Schema::hasColumn('transport_bookings', 'trip_type')) {
                $table->dropColumn('trip_type');
            }
        });
    }
};