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
        Schema::create('booking_guests', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('booking_id');
            $table->enum('booking_type', ['accommodation', 'activity']);
            $table->integer('guest_number'); // 1,2,3,... for ordering
            $table->string('relation')->nullable(); // self, spouse, etc.
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('dob');
            $table->enum('gender', ['male', 'female', 'non_binary', 'other'])->nullable();
            $table->string('nationality');
            $table->string('passport_number')->nullable();
            $table->text('notes')->nullable();
            $table->index(['booking_id', 'booking_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_guests');
    }
};
