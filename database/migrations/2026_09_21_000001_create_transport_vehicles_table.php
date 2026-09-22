<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_id')->constrained('transports')->cascadeOnDelete();
            $table->string('license_number');
            $table->string('registration_number');
            $table->string('policy_path')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();

            $table->unique('license_number');
            $table->unique('registration_number');
            $table->index(['transport_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_vehicles');
    }
};