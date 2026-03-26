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
        if (Schema::hasTable('accommodation_promotions')) {
            return;
        }

        Schema::create('accommodation_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_id')->constrained('accommodations')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('accommodation_rooms')->onDelete('cascade');
            $table->foreignId('rate_plan_id')->constrained('accommodation_rates')->onDelete('cascade');
            
            // Campaign Information
            $table->string('campaign_name')->nullable();
            $table->text('campaign_description')->nullable();
            
            // Promotion Details
            $table->enum('promotion_type', ['Early-bird', 'Last-minute', 'Stay X Pay Y', 'Seasonal'])->nullable();
            $table->enum('discount_type', ['Amount/Night', 'Percentage'])->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            
            // Validity Period
            $table->date('promo_valid_from')->nullable();
            $table->date('promo_valid_to')->nullable();
            
            // Non-Refundable
            $table->boolean('non_refundable')->default(false);
            
            // Status
            $table->enum('approval_status', ['Draft', 'Pending Approval', 'Published'])->default('Draft');
            
            // Metadata
            $table->timestamps();
            
            // Indexes
            $table->index('accommodation_id');
            $table->index('room_id');
            $table->index('rate_plan_id');
            $table->index('approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_promotions');
    }
};
