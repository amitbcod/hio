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
        Schema::create('activity_seo_social', function (Blueprint $table) {
            $table->bigIncrements('seo_id');
            $table->unsignedBigInteger('activity_id');
            $table->string('service_id');
            $table->text('short_description');
            $table->longText('full_description');
            $table->longText('highlights')->nullable();
            $table->string('seo_title', 60)->nullable();
            $table->string('seo_description', 160)->nullable();
            $table->json('keywords_tags')->nullable();
            $table->string('og_title', 60)->nullable();
            $table->string('og_description', 200)->nullable();
            $table->string('og_image_path')->nullable();
            $table->timestamps();
            
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->index('activity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_seo_social');
    }
};
