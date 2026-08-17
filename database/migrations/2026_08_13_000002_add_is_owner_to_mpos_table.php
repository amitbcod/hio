<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mpos', function (Blueprint $table) {
            if (!Schema::hasColumn('mpos', 'is_owner')) {
                $table->enum('is_owner', ['yes', 'no'])->default('yes')->after('user_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mpos', function (Blueprint $table) {
            if (Schema::hasColumn('mpos', 'is_owner')) {
                $table->dropColumn('is_owner');
            }
        });
    }
};
