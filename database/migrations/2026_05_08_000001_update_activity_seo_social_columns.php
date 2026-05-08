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
        Schema::table('activity_seo_social', function (Blueprint $table) {
            $table->string('seo_title', 255)->nullable()->change();
            $table->text('seo_description')->nullable()->change();
            $table->string('og_title', 255)->nullable()->change();
            $table->text('og_description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_seo_social', function (Blueprint $table) {
            $table->string('seo_title', 60)->change();
            $table->string('seo_description', 160)->change();
            $table->string('og_title', 60)->change();
            $table->string('og_description', 200)->change();
        });
    }
};
