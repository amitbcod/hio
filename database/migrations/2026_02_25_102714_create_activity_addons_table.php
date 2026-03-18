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
        Schema::create('activity_addons', function (Blueprint $table) {
            $table->id('addon_id');
            $table->string('service_id')->nullable(); // Link to parent activity/service
            $table->unsignedBigInteger('activity_id'); // FK to activities
            $table->string('addon_name'); // Name of extra (e.g., Hotel Pickup, BBQ Upgrade)
            $table->enum('pricing_type', ['Per Person', 'Per Booking']); // How add-on is priced
            $table->decimal('price', 10, 2); // Value of add-on
            $table->enum('addon_type', ['Optional', 'Compulsory'])->default('Optional'); // Optional or Compulsory
            $table->unsignedBigInteger('variant_id')->nullable(); // Link to specific variant if applicable
            $table->string('variant_name')->nullable(); // Variant name for reference
            $table->text('availability_rules')->nullable(); // If limited to certain slots/dates
            $table->timestamps();
            
            // Indexes
            $table->index('activity_id');
            $table->index('variant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_addons');
    }
};
