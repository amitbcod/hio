<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('activity_name_fr')->nullable()->after('activity_name');
            $table->string('short_title_fr')->nullable()->after('short_title');
            $table->longText('overview_fr')->nullable()->after('overview');
            $table->longText('whats_included_fr')->nullable()->after('whats_included');
            $table->longText('itinerary_fr')->nullable()->after('itinerary');
        });
    }

    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['activity_name_fr','short_title_fr','overview_fr','whats_included_fr','itinerary_fr']);
        });
    }
};
