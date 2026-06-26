<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_policies', function (Blueprint $table) {
            $table->longText('booking_window_rules_fr')->nullable()->after('booking_window_rules');
            $table->longText('no_show_policy_fr')->nullable()->after('no_show_policy');
            $table->longText('amendment_policy_fr')->nullable()->after('amendment_policy');
            $table->longText('cancellation_policy_fr')->nullable()->after('cancellation_policy');
            $table->longText('safety_requirements_fr')->nullable()->after('safety_requirements');
        });
    }

    public function down(): void
    {
        Schema::table('activity_policies', function (Blueprint $table) {
            $table->dropColumn([
                'booking_window_rules_fr',
                'no_show_policy_fr',
                'amendment_policy_fr',
                'cancellation_policy_fr',
                'safety_requirements_fr',
            ]);
        });
    }
};
