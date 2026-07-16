<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('transports')) {
            Schema::table('transports', function (Blueprint $table) {
                if (!Schema::hasColumn('transports', 'car_rental_prices')) {
                    $table->json('car_rental_prices')->nullable()->after('routes_pricing');
                }
                if (!Schema::hasColumn('transports', 'step2_car_rental')) {
                    $table->tinyInteger('step2_car_rental')->default(0)->after('step2_routes_pricing');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('transports')) {
            Schema::table('transports', function (Blueprint $table) {
                if (Schema::hasColumn('transports', 'car_rental_prices')) {
                    $table->dropColumn('car_rental_prices');
                }
                if (Schema::hasColumn('transports', 'step2_car_rental')) {
                    $table->dropColumn('step2_car_rental');
                }
            });
        }
    }
};
