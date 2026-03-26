<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('activities')) {
            return;
        }

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id');
            $table->string('service_id')->unique();
            $table->enum('service_type', ['Activity', 'Tour', 'Park', 'Place of Interest', 'Rental'])->nullable();
            $table->string('activity_name')->nullable(); // 5-120 chars (nullable until Step 1)
            $table->string('short_title')->nullable(); // ≤60 chars
            $table->json('team_categories')->nullable(); // Multi-select: Family, Romantic, Eco, Corporate, Sport, Adventure
            $table->enum('physical_level', ['Easy', 'Moderate', 'Challenging'])->nullable(); // Filled in Step 1
            $table->enum('price_range', ['$', '$$', '$$$'])->nullable(); // Filled in Step 1
            $table->json('primary_themes')->nullable(); // Multi-select: Ocean, Culture, Nature, Adventure, Group
            
            // Location fields
            $table->string('destination')->nullable(); // Dropdown
            $table->string('region')->nullable(); // Dropdown
            $table->string('town')->nullable(); // Dropdown
            $table->decimal('latitude', 10, 8)->nullable(); // GPS coordinate
            $table->decimal('longitude', 11, 8)->nullable(); // GPS coordinate
            $table->text('meeting_point_details')->nullable(); // Text Editor (filled in Step 1)
            
            // Content fields
            $table->longText('overview')->nullable(); // Text Editor (filled in Step 1)
            $table->longText('whats_included')->nullable(); // Text Editor (filled in Step 1)
            $table->longText('itinerary')->nullable(); // Text Editor (filled in Step 1)
            $table->string('duration')->nullable(); // e.g., "7h 30m" (filled in Step 1)
            $table->string('suitable_for_age')->nullable(); // e.g., "5-65"
            $table->json('languages_offered')->nullable(); // Multi-select
            
            // Booking & Add-ons
            $table->enum('booking_confirmation_type', ['Instant', 'On Request'])->nullable(); // Filled in Step 1
            $table->boolean('add_ons_available')->default(false); // Yes/No
            $table->boolean('private_exclusive_option')->default(false); // Yes/No
            
            // Status & Step tracking
            $table->string('status')->default('Draft'); // Draft, In Review, Active, Inactive
            $table->tinyInteger('step1_basic')->default(0);
            $table->softDeletes();
            $table->timestamps();
            
            // Foreign key
            $table->foreign('operator_id')->references('id')->on('operators')->onDelete('cascade');
            $table->index('operator_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activities');
    }
};
