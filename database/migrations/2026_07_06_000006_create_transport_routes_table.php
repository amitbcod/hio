<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_id');
            $table->string('route_id')->unique();
            $table->enum('route_type', ['Airport', 'Route', 'Hourly']);
            $table->enum('pickup_type', ['Airport', 'Address', 'Hotel', 'Location zone']);
            $table->string('pickup_value');
            $table->enum('dropoff_type', ['Airport', 'Address', 'Hotel', 'Location zone']);
            $table->string('dropoff_value');
            $table->integer('duration_estimate')->nullable();
            $table->json('pricing')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('transport_id')->references('id')->on('transports')->onDelete('cascade');
            $table->index('transport_id');
            $table->index('route_type');
            $table->index('pickup_type');
            $table->index('dropoff_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_routes');
    }
};
