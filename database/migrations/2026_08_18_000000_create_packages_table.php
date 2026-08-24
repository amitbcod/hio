<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('no_of_days')->nullable();
            $table->integer('no_of_nights')->nullable();
            $table->integer('booking_cutoff_days')->nullable();
            $table->date('available_from')->nullable();
            $table->date('available_to')->nullable();
            $table->integer('minimum_pax')->nullable();
            $table->integer('maximum_pax')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('packages');
    }
};
