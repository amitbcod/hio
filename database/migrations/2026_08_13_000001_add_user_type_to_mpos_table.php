<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mpos', function (Blueprint $table) {
            if (!Schema::hasColumn('mpos', 'user_type')) {
                $table->enum('user_type', ['Operator', 'MPO', 'Agent'])->default('MPO')->after('mpo_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mpos', function (Blueprint $table) {
            if (Schema::hasColumn('mpos', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });
    }
};
