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
        if (Schema::hasTable('transport_bookings')) {
            Schema::table('transport_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('transport_bookings', 'pickup_driver_id')) {
                    $table->unsignedBigInteger('pickup_driver_id')->nullable()->after('driver_id');
                    $table->index('pickup_driver_id');
                }

                if (!Schema::hasColumn('transport_bookings', 'return_driver_id')) {
                    $table->unsignedBigInteger('return_driver_id')->nullable()->after('pickup_driver_id');
                    $table->index('return_driver_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transport_bookings')) {
            Schema::table('transport_bookings', function (Blueprint $table) {
                if (Schema::hasColumn('transport_bookings', 'return_driver_id')) {
                    $table->dropColumn('return_driver_id');
                }

                if (Schema::hasColumn('transport_bookings', 'pickup_driver_id')) {
                    $table->dropColumn('pickup_driver_id');
                }
            });
        }
    }
};
