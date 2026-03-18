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
        Schema::create('activity_promotions', function (Blueprint $table) {
            $table->bigIncrements('promotion_id');
            $table->unsignedBigInteger('activity_id');
            $table->string('service_id');
            $table->string('campaign_id')->unique();
            $table->string('campaign_name');
            $table->string('campaign_description')->nullable();
            $table->longText('specifications');
            $table->longText('inclusions')->nullable();
            $table->longText('exclusions')->nullable();
            $table->enum('discount_type', ['Amount', 'Percentage']);
            $table->decimal('discount_value', 10, 2);
            $table->date('promo_valid_from');
            $table->date('promo_valid_to');
            $table->enum('non_refundable', ['Yes', 'No']);
            $table->enum('approval_status', ['Draft', 'Pending Approval', 'Published'])->default('Draft');
            $table->json('variant_ids')->nullable();
            $table->timestamps();
            
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->index('activity_id');
            $table->index('campaign_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_promotions');
    }
};
