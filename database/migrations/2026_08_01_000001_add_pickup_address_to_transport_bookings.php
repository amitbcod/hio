<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_bookings', 'pickup_address')) {
                $table->text('pickup_address')->nullable()->after('dropoff_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('transport_bookings', 'pickup_address')) {
                $table->dropColumn('pickup_address');
            }
        });
    }
};
