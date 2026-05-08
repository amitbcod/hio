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
        Schema::table('accommodations', function (Blueprint $table) {
            $table->string('seo_title', 255)->nullable()->change();
            $table->text('seo_description')->nullable()->change();
            $table->string('og_title', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->string('seo_title', 191)->change();
            $table->string('seo_description', 191)->change();
            $table->string('og_title', 191)->change();
        });
    }
};
