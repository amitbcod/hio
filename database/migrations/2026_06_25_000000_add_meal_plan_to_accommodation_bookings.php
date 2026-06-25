<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('accommodation_bookings')) {
            Schema::table('accommodation_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('accommodation_bookings', 'meal_plan')) {
                    $table->string('meal_plan', 255)->nullable()->after('room_id');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('accommodation_bookings')) {
            Schema::table('accommodation_bookings', function (Blueprint $table) {
                if (Schema::hasColumn('accommodation_bookings', 'meal_plan')) {
                    $table->dropColumn('meal_plan');
                }
            });
        }
    }
};
