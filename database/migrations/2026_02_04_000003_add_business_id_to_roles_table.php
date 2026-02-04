<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('roles')) {
            // Roles table provided by Spatie package not present in this environment; skip
            return;
        }

        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'business_id')) {
                $table->unsignedBigInteger('business_id')->nullable()->after('guard_name')->index();
                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'business_id')) {
                $table->dropForeign(['business_id']);
                $table->dropColumn('business_id');
            }
        });
    }
};
