<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bli_traveller_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bli_id')->constrained('booking_line_items')->onDelete('cascade');
            $table->foreignId('traveller_id')->constrained('travellers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bli_traveller_allocations');
    }
};
