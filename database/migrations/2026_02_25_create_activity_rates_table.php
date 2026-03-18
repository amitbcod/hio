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
        Schema::create('activity_rates', function (Blueprint $table) {
            $table->id('rate_id');
            $table->string('service_id', 50);  // Links to activities.service_id
            $table->unsignedBigInteger('activity_id');  // Links to activities.id
            $table->string('variant_id', 50);  // Links to activity_variants.variant_id
            $table->string('variant_name')->nullable();
            $table->string('season', 100)->nullable();  // e.g., 'One Season', 'High', 'Low', 'Peak'
            $table->date('valid_from');
            $table->date('valid_to');
            $table->enum('rate_specificity', ['Per Person', 'Per Equipment']);
            
            // Per Person Rates
            $table->decimal('adult_rate', 10, 2)->nullable();
            $table->decimal('teen_rate', 10, 2)->nullable();
            $table->decimal('children_rate', 10, 2)->nullable();
            $table->decimal('infant_rate', 10, 2)->nullable();
            
            // Per Equipment Rates
            $table->decimal('equipment_rate', 10, 2)->nullable();
            
            // Private/Exclusive Rate
            $table->decimal('private_exclusive_rate', 10, 2)->nullable();
            
            $table->timestamps();
            
            // Indexes for faster lookups
            $table->index('activity_id');
            $table->index('variant_id');
            $table->index('service_id');
            $table->index(['valid_from', 'valid_to']);
            
            // Foreign key
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_rates');
    }
};
