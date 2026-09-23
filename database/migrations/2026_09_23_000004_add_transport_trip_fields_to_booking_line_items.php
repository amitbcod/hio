<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_line_items', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_line_items', 'transport_booking_id')) {
                $table->unsignedBigInteger('transport_booking_id')->nullable()->after('service_id');
                $table->index('transport_booking_id');
            }
            if (!Schema::hasColumn('booking_line_items', 'trip_type')) {
                $table->string('trip_type', 20)->nullable()->after('transport_booking_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_line_items', function (Blueprint $table) {
            if (Schema::hasColumn('booking_line_items', 'trip_type')) {
                $table->dropColumn('trip_type');
            }
            if (Schema::hasColumn('booking_line_items', 'transport_booking_id')) {
                $table->dropColumn('transport_booking_id');
            }
        });
    }
};
