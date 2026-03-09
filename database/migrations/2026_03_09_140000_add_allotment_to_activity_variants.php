<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('activity_variants', function (Blueprint $table) {
            // Allotment: Number of sellable seats/equipment
            $table->integer('allotment')->nullable()->after('max_participants')->comment('Number of sellable seats/equipment');
        });
    }

    public function down()
    {
        Schema::table('activity_variants', function (Blueprint $table) {
            $table->dropColumn('allotment');
        });
    }
};
