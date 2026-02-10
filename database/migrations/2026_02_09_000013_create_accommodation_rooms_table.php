<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accommodation_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_id', 50)->unique();
            $table->unsignedBigInteger('accommodation_id')->required();
            $table->string('room_name', 255)->required();
            $table->enum('room_type', ['Single', 'Double', 'Twin', 'Suite', 'Deluxe', 'Family', 'Studio', 'Bungalow', 'Villa', 'Other'])->required();
            $table->text('room_description')->nullable();
            $table->integer('capacity')->default(1);
            $table->integer('quantity')->default(1);
            $table->boolean('is_accessible')->default(false);
            $table->text('amenities')->nullable();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->enum('status', ['Active', 'Inactive', 'Maintenance'])->default('Active');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->onDelete('cascade');
            $table->index('accommodation_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accommodation_rooms');
    }
};
