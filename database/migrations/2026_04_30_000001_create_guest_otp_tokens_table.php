<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('guest_otp_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150)->index();
            $table->string('otp_code', 10)->index();
            $table->unsignedBigInteger('booking_id')->nullable()->index(); // Links to first accommodation/activity booking
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            // Compound index for email + OTP lookups
            $table->index(['email', 'otp_code']);

            // Foreign key to accommodation_bookings
            $table->foreign('booking_id')
                ->references('id')
                ->on('accommodation_bookings')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('guest_otp_tokens');
    }
};
