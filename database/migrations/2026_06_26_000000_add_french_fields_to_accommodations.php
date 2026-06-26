<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->string('property_name_fr')->nullable()->after('property_name');
            $table->text('short_description_fr')->nullable()->after('short_description');
            $table->longText('property_description_fr')->nullable()->after('property_description');
        });
    }

    public function down()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn(['property_name_fr', 'short_description_fr', 'property_description_fr']);
        });
    }
};
