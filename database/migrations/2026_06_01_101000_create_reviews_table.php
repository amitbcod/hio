<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('traveler_account_id')->nullable();
            $table->tinyInteger('overall_rating')->nullable();
            $table->text('overall_review')->nullable();
            $table->timestamps();

            $table->index('trip_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};
