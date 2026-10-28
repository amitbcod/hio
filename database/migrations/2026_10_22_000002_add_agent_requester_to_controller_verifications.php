<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('controller_verifications', function (Blueprint $table): void {
            if (! Schema::hasColumn('controller_verifications', 'requester_agent_account_id')) {
                $table->foreignId('requester_agent_account_id')
                    ->nullable()
                    ->after('requester_operator_id')
                    ->constrained('agent_accounts')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('controller_verifications', 'requester_agent_staff_user_id')) {
                $table->foreignId('requester_agent_staff_user_id')
                    ->nullable()
                    ->after('requester_agent_account_id')
                    ->constrained('agent_staff_users')
                    ->nullOnDelete();
            }
        });

        if (DB::getDriverName() === 'mysql') {
            foreach ([
                'requester_agent_account_id' => 'agent_accounts',
                'requester_agent_staff_user_id' => 'agent_staff_users',
            ] as $column => $referencedTable) {
                $foreignKeyExists = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
                    ->where('TABLE_NAME', 'controller_verifications')
                    ->where('COLUMN_NAME', $column)
                    ->where('REFERENCED_TABLE_NAME', $referencedTable)
                    ->exists();

                if (! $foreignKeyExists) {
                    $constraint = "controller_verifications_{$column}_foreign";
                    DB::statement(
                        "ALTER TABLE `controller_verifications` ADD CONSTRAINT `{$constraint}` FOREIGN KEY (`{$column}`) REFERENCES `{$referencedTable}` (`id`) ON DELETE SET NULL"
                    );
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('controller_verifications', function (Blueprint $table): void {
            foreach (['requester_agent_staff_user_id', 'requester_agent_account_id'] as $column) {
                if (Schema::hasColumn('controller_verifications', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });
    }
};
