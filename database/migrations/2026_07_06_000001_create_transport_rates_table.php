<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_id');
            $table->string('route_from', 100)->nullable();
            $table->string('route_to', 100)->nullable();
            $table->decimal('price_per_person', 10, 2);
            $table->decimal('price_per_vehicle', 10, 2)->nullable();
            $table->integer('min_passengers')->default(1);
            $table->integer('max_passengers')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('transport_id')->references('id')->on('transports')->onDelete('cascade');
            $table->index(['transport_id', 'route_from', 'route_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_rates');
    }
};
