<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $businessIdExists = Schema::hasColumn('agent_accounts', 'business_id');
        Schema::table('agent_accounts', function (Blueprint $table): void {
            if (! Schema::hasColumn('agent_accounts', 'business_id')) {
                $table->foreignId('business_id')->nullable()->after('country_id')->constrained('businesses')->nullOnDelete();
            }
            if (! Schema::hasColumn('agent_accounts', 'is_owner')) {
                $table->string('is_owner', 3)->default('yes')->after('business_id');
            }
            if (! Schema::hasColumn('agent_accounts', 'owner_full_name')) {
                $table->string('owner_full_name')->nullable()->after('is_owner');
            }
            if (! Schema::hasColumn('agent_accounts', 'owner_email')) {
                $table->string('owner_email', 191)->nullable()->after('owner_full_name');
            }
            if (! Schema::hasColumn('agent_accounts', 'owner_phone')) {
                $table->string('owner_phone', 20)->nullable()->after('owner_email');
            }
            if (! Schema::hasColumn('agent_accounts', 'agency_email')) {
                $table->string('agency_email', 191)->nullable()->after('owner_phone');
            }
        });

        if ($businessIdExists && DB::getDriverName() === 'mysql') {
            $foreignKeyExists = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
                ->where('TABLE_NAME', 'agent_accounts')
                ->where('COLUMN_NAME', 'business_id')
                ->where('REFERENCED_TABLE_NAME', 'businesses')
                ->exists();

            if (! $foreignKeyExists) {
                DB::statement(
                    'ALTER TABLE `agent_accounts` ADD CONSTRAINT `agent_accounts_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL'
                );
            }
        }
    }

    public function down(): void
    {
        Schema::table('agent_accounts', function (Blueprint $table): void {
            if (Schema::hasColumn('agent_accounts', 'business_id')) {
                $table->dropConstrainedForeignId('business_id');
            }
            foreach (['is_owner', 'owner_full_name', 'owner_email', 'owner_phone', 'agency_email'] as $column) {
                if (Schema::hasColumn('agent_accounts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
