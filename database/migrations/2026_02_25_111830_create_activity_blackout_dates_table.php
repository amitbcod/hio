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
        Schema::create('activity_blackout_dates', function (Blueprint $table) {
            $table->id('blackout_id');
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('season')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->index('activity_id');
            $table->index('variant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_blackout_dates');
    }
};
