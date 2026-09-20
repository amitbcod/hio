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
                if (!Schema::hasColumn('accommodation_bookings', 'payment_method')) {
                    $table->string('payment_method', 50)->nullable()->after('source_channel');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('accommodation_bookings')) {
            Schema::table('accommodation_bookings', function (Blueprint $table) {
                if (Schema::hasColumn('accommodation_bookings', 'payment_method')) {
                    $table->dropColumn('payment_method');
                }
            });
        }
    }
};
