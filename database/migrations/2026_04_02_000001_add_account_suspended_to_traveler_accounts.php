<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('traveler_accounts')) {
            return;
        }

        Schema::table('traveler_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('traveler_accounts', 'account_suspended')) {
                $table->boolean('account_suspended')->default(false)->after('marketing_opt_in');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('traveler_accounts')) {
            return;
        }

        Schema::table('traveler_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('traveler_accounts', 'account_suspended')) {
                $table->dropColumn('account_suspended');
            }
        });
    }
};
