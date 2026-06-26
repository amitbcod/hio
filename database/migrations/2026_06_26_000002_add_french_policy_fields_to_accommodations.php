<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->longText('checkin_checkout_rules_fr')->nullable()->after('checkin_checkout_rules');
            $table->longText('booking_window_rules_fr')->nullable()->after('booking_window_rules');
            $table->longText('amendment_policy_fr')->nullable()->after('amendment_policy');
            $table->longText('cancellation_policy_fr')->nullable()->after('cancellation_policy');
            $table->longText('security_deposit_policy_fr')->nullable()->after('security_deposit_policy');
            $table->longText('house_rules_fr')->nullable()->after('house_rules');
        });
    }

    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn([
                'checkin_checkout_rules_fr',
                'booking_window_rules_fr',
                'amendment_policy_fr',
                'cancellation_policy_fr',
                'security_deposit_policy_fr',
                'house_rules_fr',
            ]);
        });
    }
};
