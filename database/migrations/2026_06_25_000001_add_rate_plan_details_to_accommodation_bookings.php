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
                if (!Schema::hasColumn('accommodation_bookings', 'rate_plan_id')) {
                    $table->unsignedBigInteger('rate_plan_id')->nullable()->after('meal_plan');
                }
                if (!Schema::hasColumn('accommodation_bookings', 'rate_name')) {
                    $table->string('rate_name', 255)->nullable()->after('rate_plan_id');
                }
                if (!Schema::hasColumn('accommodation_bookings', 'pricing_setting')) {
                    $table->string('pricing_setting', 100)->nullable()->after('rate_name');
                }
                if (!Schema::hasColumn('accommodation_bookings', 'plan_label')) {
                    $table->string('plan_label', 255)->nullable()->after('pricing_setting');
                }
                if (!Schema::hasColumn('accommodation_bookings', 'plan_inclusions')) {
                    $table->text('plan_inclusions')->nullable()->after('plan_label');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('accommodation_bookings')) {
            Schema::table('accommodation_bookings', function (Blueprint $table) {
                if (Schema::hasColumn('accommodation_bookings', 'plan_inclusions')) {
                    $table->dropColumn('plan_inclusions');
                }
                if (Schema::hasColumn('accommodation_bookings', 'plan_label')) {
                    $table->dropColumn('plan_label');
                }
                if (Schema::hasColumn('accommodation_bookings', 'pricing_setting')) {
                    $table->dropColumn('pricing_setting');
                }
                if (Schema::hasColumn('accommodation_bookings', 'rate_name')) {
                    $table->dropColumn('rate_name');
                }
                if (Schema::hasColumn('accommodation_bookings', 'rate_plan_id')) {
                    $table->dropColumn('rate_plan_id');
                }
            });
        }
    }
};
