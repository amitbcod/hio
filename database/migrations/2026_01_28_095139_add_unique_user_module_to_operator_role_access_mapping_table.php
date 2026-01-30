<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operator_role_access_mapping', function (Blueprint $table) {

            // ✅ Ensure no duplicate module per user
            $table->unique(
                ['user_id', 'module'],
                'uniq_user_module'
            );

            /*
            |--------------------------------------------------------------------------
            | OPTIONAL IMPROVEMENTS (NOT REQUIRED)
            |--------------------------------------------------------------------------
            |
            | Uncomment ONLY if needed later
            |
            */

            // 🔹 If you want faster lookups
            // $table->index('user_id');
            // $table->index('module');

            // 🔹 If you plan to soft delete mappings
            // $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('operator_role_access_mapping', function (Blueprint $table) {

            // 🔁 Remove unique constraint on rollback
            $table->dropUnique('uniq_user_module');

            // 🔁 Optional rollback cleanup
            // $table->dropSoftDeletes();
        });
    }
};
