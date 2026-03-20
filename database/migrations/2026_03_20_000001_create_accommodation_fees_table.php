<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodation_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accommodation_id');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->decimal('cleaning_fee', 12, 2)->nullable();
            $table->decimal('resort_fee', 12, 2)->nullable();
            $table->enum('early_checkin_type', ['percent', 'fixed'])->nullable();
            $table->decimal('early_checkin_value', 12, 2)->nullable();
            $table->enum('late_checkout_type', ['percent', 'fixed'])->nullable();
            $table->decimal('late_checkout_value', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('accommodation_id')
                ->references('id')->on('accommodations')
                ->onDelete('cascade');

            $table->foreign('room_id')
                ->references('id')->on('accommodation_rooms')
                ->onDelete('cascade');

            $table->unique(['accommodation_id', 'room_id'], 'accommodation_fees_unique');
            $table->index('accommodation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodation_fees');
    }
};
