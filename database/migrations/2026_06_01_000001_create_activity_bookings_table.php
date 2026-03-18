<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference', 50)->unique();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('variant_name', 150)->nullable();

            $table->string('guest_name', 150);
            $table->string('guest_email', 150)->nullable();
            $table->string('guest_phone', 30)->nullable();

            $table->date('activity_date');
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);

            $table->enum('booking_status', ['Pending', 'Confirmed', 'Cancelled'])->default('Pending');
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('payment_method', 30)->default('COD');
            $table->string('source_channel', 50)->default('Direct');
            $table->text('special_requests')->nullable();

            $table->timestamp('booked_at')->nullable()->useCurrent();
            $table->timestamps();

            $table->foreign('activity_id')
                ->references('id')
                ->on('activities')
                ->onDelete('cascade');

            $table->index(['activity_id', 'activity_date']);
            $table->index(['activity_id', 'booking_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_bookings');
    }
};
