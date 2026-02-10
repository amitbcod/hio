<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accommodation_inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accommodation_id')->required();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->date('date')->required();
            $table->integer('available_units')->default(0);
            $table->integer('booked_units')->default(0);
            $table->integer('blocked_units')->default(0);
            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason', 255)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->onDelete('cascade');
            $table->unique(['accommodation_id', 'room_id', 'date']);
            $table->index(['accommodation_id', 'date']);
            $table->index('accommodation_id');
            $table->index('room_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accommodation_inventory');
    }
};
