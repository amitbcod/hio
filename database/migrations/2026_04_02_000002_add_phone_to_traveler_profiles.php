<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('traveler_profiles')) {
            return;
        }

        Schema::table('traveler_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('traveler_profiles', 'phone')) {
                $table->string('phone', 25)->nullable()->after('last_name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('traveler_profiles')) {
            return;
        }

        Schema::table('traveler_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('traveler_profiles', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
