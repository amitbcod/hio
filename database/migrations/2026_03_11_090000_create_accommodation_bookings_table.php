<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accommodation_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference', 50)->unique();
            $table->unsignedBigInteger('accommodation_id');
            $table->unsignedBigInteger('room_id')->nullable();

            $table->string('guest_name', 150);
            $table->string('guest_email', 150)->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');

            $table->unsignedInteger('rooms_booked')->default(1);
            $table->unsignedSmallInteger('adults')->default(2);
            $table->unsignedSmallInteger('children')->default(0);

            $table->enum('booking_status', ['Pending', 'Confirmed', 'Cancelled'])->default('Pending');
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('source_channel', 50)->default('Direct');

            $table->timestamp('booked_at')->nullable()->useCurrent();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();

            $table->foreign('accommodation_id')
                ->references('id')
                ->on('accommodations')
                ->onDelete('cascade');

            $table->foreign('room_id')
                ->references('id')
                ->on('accommodation_rooms')
                ->onDelete('set null');

            $table->index(['accommodation_id', 'check_in_date']);
            $table->index(['accommodation_id', 'check_out_date']);
            $table->index(['accommodation_id', 'booking_status']);
            $table->index('room_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accommodation_bookings');
    }
};
