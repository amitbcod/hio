<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodations', 'state')) {
                $table->string('state', 100)->nullable()->after('city');
            }
            if (!Schema::hasColumn('accommodations', 'region')) {
                $table->string('region', 100)->nullable()->after('state');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (Schema::hasColumn('accommodations', 'state')) {
                $table->dropColumn('state');
            }
            if (Schema::hasColumn('accommodations', 'region')) {
                $table->dropColumn('region');
            }
        });
    }
};
