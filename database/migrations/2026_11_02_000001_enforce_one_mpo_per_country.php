<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admin_users')
            || ! Schema::hasColumn('admin_users', 'admin_type')
            || ! Schema::hasColumn('admin_users', 'country_id')
            || Schema::hasColumn('admin_users', 'mpo_country_id')) {
            return;
        }

        $duplicateCountry = DB::table('admin_users')
            ->select('country_id')
            ->where('admin_type', 'mpo')
            ->whereNotNull('country_id')
            ->groupBy('country_id')
            ->havingRaw('COUNT(*) > 1')
            ->value('country_id');

        if ($duplicateCountry !== null) {
            throw new RuntimeException(
                'Cannot enforce one MPO per country: multiple MPO accounts are assigned to country ID '.$duplicateCountry.'. Resolve the duplicate assignment before migrating.',
            );
        }

        Schema::table('admin_users', function (Blueprint $table): void {
            $table->unsignedBigInteger('mpo_country_id')
                ->nullable()
                ->storedAs("CASE WHEN admin_type = 'mpo' THEN country_id ELSE NULL END");
            $table->unique('mpo_country_id', 'admin_users_one_mpo_per_country_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('admin_users') || ! Schema::hasColumn('admin_users', 'mpo_country_id')) {
            return;
        }

        Schema::table('admin_users', function (Blueprint $table): void {
            $table->dropUnique('admin_users_one_mpo_per_country_unique');
            $table->dropColumn('mpo_country_id');
        });
    }
};
