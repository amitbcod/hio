<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql' && ! DB::connection()->pretending()) {
            foreach (['agent_accounts', 'agent_staff_users'] as $referencedTable) {
                if (! Schema::hasTable($referencedTable)) {
                    throw new RuntimeException("Required table [{$referencedTable}] does not exist.");
                }

                $engine = DB::selectOne(
                    'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                    [$referencedTable]
                )?->ENGINE;

                if (strtoupper((string) $engine) !== 'INNODB') {
                    DB::statement("ALTER TABLE `{$referencedTable}` ENGINE=InnoDB");
                }
            }
        }

        Schema::table('bookings', function (Blueprint $table): void {
            if (! Schema::hasColumn('bookings', 'agent_account_id')) {
                $table->foreignId('agent_account_id')->nullable()->constrained('agent_accounts')->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'agent_staff_user_id')) {
                $table->foreignId('agent_staff_user_id')->nullable()->constrained('agent_staff_users')->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'agent_verification_status')) {
                $table->string('agent_verification_status', 40)->nullable()->index();
            }
            if (! Schema::hasColumn('bookings', 'agent_verified_by')) {
                $table->unsignedBigInteger('agent_verified_by')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'agent_verified_at')) {
                $table->dateTime('agent_verified_at')->nullable();
            }
        });

        Schema::table('travellers', function (Blueprint $table): void {
            if (! Schema::hasColumn('travellers', 'passport_number')) {
                $table->string('passport_number', 100)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('travellers', function (Blueprint $table): void {
            if (Schema::hasColumn('travellers', 'passport_number')) {
                $table->dropColumn('passport_number');
            }
        });

        Schema::table('bookings', function (Blueprint $table): void {
            if (Schema::hasColumn('bookings', 'agent_account_id')) {
                $table->dropForeign(['agent_account_id']);
            }
            if (Schema::hasColumn('bookings', 'agent_staff_user_id')) {
                $table->dropForeign(['agent_staff_user_id']);
            }
            foreach (['agent_account_id', 'agent_staff_user_id', 'agent_verification_status', 'agent_verified_by', 'agent_verified_at'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
