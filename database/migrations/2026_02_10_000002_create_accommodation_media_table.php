<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accommodation_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accommodation_id')->index();
            $table->enum('media_type', ['hero','gallery','room','logo','video'])->default('gallery');
            $table->string('path', 1024)->nullable();
            $table->string('original_name', 255)->nullable();
            $table->string('mime', 100)->nullable();
            $table->integer('size')->nullable();
            $table->unsignedBigInteger('room_id')->nullable()->index();
            $table->integer('order')->default(0);
            $table->boolean('is_approved')->default(false);
            $table->enum('approval_status', ['pending','approved','rejected'])->default('pending');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->foreign('accommodation_id')->references('id')->on('accommodations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accommodation_media');
    }
};
