<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('packages', 'itinerary')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->json('itinerary')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('packages', 'itinerary')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropColumn('itinerary');
            });
        }
    }
};
