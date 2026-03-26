<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('traveler_accounts', function (Blueprint $table) {
            // 2FA settings
            if (!Schema::hasColumn('traveler_accounts', '2fa_enabled')) {
                $table->boolean('2fa_enabled')->default(false)->after('marketing_opt_in');
            }

            if (!Schema::hasColumn('traveler_accounts', '2fa_method')) {
                $table->enum('2fa_method', ['email', 'sms', 'auth_app'])->nullable()->after('2fa_enabled');
            }

            // Communication preferences (JSON: email, sms, whatsapp)
            if (!Schema::hasColumn('traveler_accounts', 'communication_preference')) {
                $table->json('communication_preference')->nullable()->after('2fa_method');
            }

            // Account suspension
            if (!Schema::hasColumn('traveler_accounts', 'account_suspended')) {
                $table->boolean('account_suspended')->default(false)->after('communication_preference');
            }

            // Last password reset
            if (!Schema::hasColumn('traveler_accounts', 'password_reset_requested_at')) {
                $table->timestamp('password_reset_requested_at')->nullable()->after('account_suspended');
            }
        });
    }

    public function down(): void
    {
        Schema::table('traveler_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('traveler_accounts', '2fa_enabled')) {
                $table->dropColumn('2fa_enabled');
            }

            if (Schema::hasColumn('traveler_accounts', '2fa_method')) {
                $table->dropColumn('2fa_method');
            }

            if (Schema::hasColumn('traveler_accounts', 'communication_preference')) {
                $table->dropColumn('communication_preference');
            }

            if (Schema::hasColumn('traveler_accounts', 'account_suspended')) {
                $table->dropColumn('account_suspended');
            }

            if (Schema::hasColumn('traveler_accounts', 'password_reset_requested_at')) {
                $table->dropColumn('password_reset_requested_at');
            }
        });
    }
};
