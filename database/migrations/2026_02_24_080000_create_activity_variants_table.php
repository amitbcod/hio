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
        Schema::create('activity_variants', function (Blueprint $table) {
            $table->id('variant_id');
            $table->unsignedBigInteger('activity_id');
            
            // IDs
            $table->string('service_id')->nullable();
            $table->string('variant_equipment_id')->unique();
            
            // Basic Info
            $table->string('variant_name');
            $table->enum('quality_tier', ['Standard', 'Premium', 'Luxury'])->default('Standard');
            
            // Amenities & Safety
            $table->json('amenities')->nullable(); // ['WC', 'Shade', 'Music', 'Snorkel gear']
            $table->json('safety_equipment')->nullable(); // ['Lifewest', 'Helmet', 'Harness', 'First Aid kit']
            
            // Capacity & Participants
            $table->integer('max_pax')->comment('Maximum capacity');
            $table->integer('min_participants')->default(1);
            $table->integer('max_participants');
            
            // Options
            $table->enum('private_exclusive', ['Yes', 'No'])->default('No');
            
            // Media
            $table->string('equipment_image')->nullable();
            
            $table->timestamps();
            
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->index('service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_variants');
    }
};
