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
        Schema::create('saved_guests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('first_name', 150);
            $table->string('middle_name', 150)->nullable();
            $table->string('last_name', 150);
            $table->date('dob');
            $table->enum('gender', ['male', 'female', 'non_binary', 'other'])->nullable();
            $table->string('nationality', 100);
            $table->string('passport_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('traveler_accounts')->onDelete('cascade');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_guests');
    }
};
