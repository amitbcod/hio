<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accommodation_inventory', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('accommodation_inventory', 'sellable_units')) {
                $table->integer('sellable_units')->default(0)->after('room_id');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'sold_units')) {
                $table->integer('sold_units')->default(0)->after('sellable_units');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'minimum_nights')) {
                $table->integer('minimum_nights')->nullable()->after('sold_units');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'days_before_release')) {
                $table->integer('days_before_release')->nullable()->after('minimum_nights');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'period_enabled')) {
                $table->boolean('period_enabled')->default(false)->after('days_before_release');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'period_start_date')) {
                $table->date('period_start_date')->nullable()->after('period_enabled');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'period_end_date')) {
                $table->date('period_end_date')->nullable()->after('period_start_date');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'sell_and_report')) {
                $table->boolean('sell_and_report')->default(false)->after('period_end_date');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'stop_sell')) {
                $table->boolean('stop_sell')->default(false)->after('sell_and_report');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'blackout_period_enabled')) {
                $table->boolean('blackout_period_enabled')->default(false)->after('stop_sell');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'blackout_dates')) {
                $table->json('blackout_dates')->nullable()->after('blackout_period_enabled');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'block_arrivals')) {
                $table->boolean('block_arrivals')->default(false)->after('blackout_dates');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'block_days')) {
                $table->integer('block_days')->default(1)->after('block_arrivals');
            }
            if (!Schema::hasColumn('accommodation_inventory', 'instant_on_request')) {
                $table->enum('instant_on_request', ['Instant', 'On Request'])->default('Instant')->after('block_days');
            }
        });
    }

    public function down()
    {
        Schema::table('accommodation_inventory', function (Blueprint $table) {
            $columns = [
                'sellable_units', 'sold_units', 'minimum_nights', 'days_before_release',
                'period_enabled', 'period_start_date', 'period_end_date', 'sell_and_report',
                'stop_sell', 'blackout_period_enabled', 'blackout_dates', 'block_arrivals',
                'block_days', 'instant_on_request'
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('accommodation_inventory', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
