<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (!Schema::hasColumn('roles', 'business_id')) {
                    $table->unsignedBigInteger('business_id')->nullable()->after('id');
                    $table->index('business_id', 'idx_roles_business_id');
                    // Drop unique on name+guard_name if exists and create composite unique per business
                    try {
                        $table->unique(['business_id', 'name', 'guard_name'], 'uniq_business_role_name_guard');
                    } catch (\Exception $e) {
                        // ignore if exists
                    }
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (Schema::hasColumn('roles', 'business_id')) {
                    $table->dropIndex('idx_roles_business_id');
                    try {
                        $table->dropUnique('uniq_business_role_name_guard');
                    } catch (\Exception $e) {}
                    $table->dropColumn('business_id');
                }
            });
        }
    }
};