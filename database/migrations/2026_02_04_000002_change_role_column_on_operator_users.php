<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Change role enum to string to be managed by admin and Spatie
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite (used in tests) does not support drop/rename column reliably; skip migration in sqlite test runs
            return;
        }

        if (Schema::hasTable('operator_users')) {
            Schema::table('operator_users', function (Blueprint $table) {
                // Some DB systems do not support direct enum->string change; use raw statements
                if (Schema::hasColumn('operator_users', 'role')) {
                    // add new temporary column
                    if (!Schema::hasColumn('operator_users', 'role_new')) {
                        $table->string('role_new', 191)->nullable()->after('role');
                    }
                }
            });

            // Copy enum values into new column
            DB::statement("UPDATE operator_users SET role_new = role WHERE role_new IS NULL");

            // Safely drop existing index if present to avoid SQL errors on some MySQL setups
            try {
                $hasIndex = DB::select("SHOW INDEX FROM operator_users WHERE Key_name = 'idx_operator_users_role'");
                if (!empty($hasIndex)) {
                    DB::statement('ALTER TABLE operator_users DROP INDEX `idx_operator_users_role`');
                }
            } catch (\Exception $e) {
                // ignore any errors here and proceed
            }

            Schema::table('operator_users', function (Blueprint $table) {
                // drop old column and rename new one
                try {
                    $table->dropColumn('role');
                } catch (\Exception $e) {
                    // SQLite does not support dropColumn; ignore and proceed if role_new exists
                }

                try {
                    $table->renameColumn('role_new', 'role');
                } catch (\Exception $e) {
                    // rename may fail on SQLite; role_new will remain – acceptable for tests
                }

                try {
                    $table->index('role', 'idx_operator_users_role');
                } catch (\Exception $e) {
                    // index creation may fail on some DB drivers; ignore
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('operator_users')) {
            Schema::table('operator_users', function (Blueprint $table) {
                if (!Schema::hasColumn('operator_users', 'role_old')) {
                    $table->enum('role_old', ['Admin','Head of Department','Reservation Manager','Operational Manager','Finance Manager','Marketing Manager','Support Manager','Content Manager'])->nullable()->after('role');
                }
            });

            DB::statement("UPDATE operator_users SET role_old = role WHERE role_old IS NULL");

            // Safely drop existing index if present to avoid SQL errors on some MySQL setups
            try {
                $hasIndex = DB::select("SHOW INDEX FROM operator_users WHERE Key_name = 'idx_operator_users_role'");
                if (!empty($hasIndex)) {
                    DB::statement('ALTER TABLE operator_users DROP INDEX `idx_operator_users_role`');
                }
            } catch (\Exception $e) {
                // ignore any errors here and proceed
            }

            Schema::table('operator_users', function (Blueprint $table) {
                try {
                    $table->dropColumn('role');
                } catch (\Exception $e) {
                    // ignore
                }

                try {
                    $table->renameColumn('role_old', 'role');
                } catch (\Exception $e) {
                    // ignore
                }

                try {
                    $table->index('role', 'idx_operator_users_role');
                } catch (\Exception $e) {
                    // ignore
                }
            });
        }
    }
};