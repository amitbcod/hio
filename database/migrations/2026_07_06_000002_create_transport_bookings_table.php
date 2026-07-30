<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_id');
            $table->unsignedBigInteger('traveler_account_id')->nullable();
            $table->string('booking_reference')->unique();
            $table->string('guest_name')->nullable();
            $table->string('guest_email');
            $table->string('guest_phone')->nullable();
            
            // Route & Date Details
            $table->string('route_from');
            $table->string('route_to');
            $table->date('pickup_date');
            $table->time('pickup_time')->nullable();
            $table->date('return_date')->nullable();
            $table->time('return_time')->nullable();
            $table->text('dropoff_address')->nullable();
            
            // Passengers
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->integer('total_passengers')->default(1);
            
            // Primary Traveler Details
            $table->string('traveler_first_name')->nullable();
            $table->string('traveler_middle_name')->nullable();
            $table->string('traveler_last_name')->nullable();
            $table->string('traveler_relation')->nullable();
            $table->date('traveler_dob')->nullable();
            $table->string('traveler_gender')->nullable();
            $table->string('traveler_nationality')->nullable();
            $table->string('traveler_passport_number')->nullable();
            $table->text('traveler_notes')->nullable();
            
            // Pricing
            $table->decimal('price_per_person', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->string('currency')->default('USD');
            
            // Status & Payment
            $table->enum('booking_status', ['Pending', 'Confirmed', 'Cancelled', 'Completed'])->default('Pending');
            $table->string('payment_method')->nullable(); // COD, Againgency
            $table->string('source_channel')->default('Direct');
            
            // Special Requirements
            $table->text('special_requests')->nullable();
            
            // Guest OTP
            $table->unsignedBigInteger('guest_otp_token_id')->nullable();
            $table->boolean('is_guest')->default(false);
            
            // Trip Assignment
            $table->unsignedBigInteger('trip_id')->nullable();
            
            $table->timestamp('booked_at')->nullable();
            $table->timestamps();
            
            $table->foreign('transport_id')->references('id')->on('transports')->onDelete('cascade');
            $table->foreign('traveler_account_id')->references('id')->on('traveler_accounts')->onDelete('set null');
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('set null');
            $table->index(['guest_email', 'is_guest']);
            $table->index(['transport_id', 'booking_status']);
            $table->index('booking_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_bookings');
    }
};
