<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mpos', function (Blueprint $table) {
            if (!Schema::hasColumn('mpos', 'admin_approve_flag')) {
                $table->tinyInteger('admin_approve_flag')->default(0)->after('operator_approve_flag');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mpos', function (Blueprint $table) {
            if (Schema::hasColumn('mpos', 'admin_approve_flag')) {
                $table->dropColumn('admin_approve_flag');
            }
        });
    }
};
