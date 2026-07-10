<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            // Add driver_id field if it doesn't exist
            if (!Schema::hasColumn('transport_bookings', 'driver_id')) {
                $table->unsignedBigInteger('driver_id')->nullable()->after('transport_id');
                $table->foreign('driver_id')->references('id')->on('operator_drivers')->onDelete('set null');
                $table->index('driver_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('transport_bookings', 'driver_id')) {
                $table->dropForeign(['driver_id']);
                $table->dropColumn('driver_id');
            }
        });
    }
};
