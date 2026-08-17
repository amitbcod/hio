<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('activities')) {
            return;
        }

        if (!Schema::hasColumn('activities', 'regions')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->string('regions')->nullable()->after('address');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('activities')) {
            return;
        }

        if (Schema::hasColumn('activities', 'regions')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->dropColumn('regions');
            });
        }
    }
};
