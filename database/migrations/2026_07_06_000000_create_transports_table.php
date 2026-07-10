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
        Schema::create('transports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id');
            $table->string('service_id')->unique()->nullable();
            
            // Basic Info
            $table->string('vehicle_name')->nullable();
            $table->string('vehicle_type')->nullable(); // Car, Van, Bus, etc.
            $table->integer('seating_capacity')->nullable();
            $table->string('registration_number')->nullable();
            
            // Service Details
            $table->string('service_description')->nullable();
            $table->text('overview')->nullable();
            $table->text('overview_fr')->nullable();
            $table->text('amenities')->nullable(); // JSON array
            $table->text('amenities_fr')->nullable(); // JSON array
            
            // Routes/Fixed Pricing
            $table->text('routes_pricing')->nullable(); // JSON: [{"from":"Airport","to":"North","price":50},...]
            
            // Media
            $table->string('hero_banner_image')->nullable();
            $table->json('gallery_images')->nullable();
            
            // Contact & Operations
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->integer('advance_booking_days')->default(1); // Min days to book
            $table->time('earliest_pickup_time')->nullable();
            $table->time('latest_pickup_time')->nullable();
            
            // Compliance & Legal
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->date('insurance_expiration')->nullable();
            $table->string('license_number')->nullable();
            $table->date('license_expiration')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('cancellation_policy')->nullable();
            
            // Tax & Accounting
            $table->string('tax_type')->default('VAT')->nullable();
            $table->decimal('tax_charges_value', 5, 2)->default(0);
            $table->string('tax_charges_type')->default('Percentage')->nullable();
            
            // SEO & Social
            $table->text('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->text('short_description')->nullable();
            $table->text('short_description_fr')->nullable();
            
            // Publishing & Approval
            $table->enum('approval_status', ['Draft', 'Pending', 'Approved', 'Rejected'])->default('Draft');
            $table->enum('status', ['Draft', 'In Review', 'Active', 'Inactive', 'Archived'])->default('Draft');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_visible_to_travellers')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('submitted_for_approval_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Setup Steps Tracking
            $table->boolean('step1_basics')->default(false);
            $table->boolean('step2_routes_pricing')->default(false);
            $table->boolean('step3_media')->default(false);
            $table->boolean('step4_compliance')->default(false);
            $table->boolean('step5_accounting')->default(false);
            $table->boolean('step6_seo_social')->default(false);
            $table->boolean('step7_publish')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->foreign('operator_id')->references('id')->on('operators')->onDelete('cascade');
            $table->index('approval_status');
            $table->index('status');
            $table->index('is_published');
            $table->index(['operator_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transports');
    }
};
