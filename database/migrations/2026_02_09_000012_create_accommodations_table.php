<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->string('accommodation_id', 50)->unique();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->string('property_name', 255)->required();
            $table->enum('property_type', ['Hotel', 'Lodge', 'Guesthouse', 'Apartment', 'Holiday Rental', 'Villa', 'Resort', 'Cottage', 'Other'])->required();
            $table->text('property_description')->nullable();
            $table->text('short_description', 250)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 10, 8)->nullable();
            $table->string('postal_code', 20)->nullable();
            
            // Legal holder (may differ from operator)
            $table->string('legal_holder_name', 255)->nullable();
            $table->string('legal_holder_id_type', 50)->nullable();
            $table->string('legal_holder_id_number', 100)->nullable();
            
            // Reservation contact
            $table->string('reservation_contact_name', 255)->nullable();
            $table->string('reservation_contact_email', 255)->nullable();
            $table->string('reservation_contact_phone', 20)->nullable();
            
            // Management contact
            $table->string('management_contact_name', 255)->nullable();
            $table->string('management_contact_email', 255)->nullable();
            $table->string('management_contact_phone', 20)->nullable();
            
            // Property Status
            $table->enum('status', ['Draft', 'In Setup', 'Pending Approval', 'Active', 'Suspended', 'Archived'])->default('Draft');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            
            // Compliance flags
            $table->boolean('compliance_documents_submitted')->default(false);
            $table->enum('compliance_status', ['Not Started', 'Submitted', 'Verified', 'Rejected'])->default('Not Started');
            $table->timestamp('compliance_verified_at')->nullable();
            $table->string('compliance_verified_by', 255)->nullable();
            
            // Setup completion tracking
            $table->tinyInteger('step1_basics')->default(0);
            $table->tinyInteger('step2_legal')->default(0);
            $table->tinyInteger('step3_media')->default(0);
            $table->tinyInteger('step4_rooms')->default(0);
            $table->tinyInteger('step5_rates')->default(0);
            $table->tinyInteger('step6_policies')->default(0);
            $table->tinyInteger('step7_compliance')->default(0);
            $table->tinyInteger('step8_communication')->default(0);
            $table->tinyInteger('step9_availability')->default(0);
            $table->tinyInteger('step10_banking')->default(0);
            $table->tinyInteger('step11_agents')->default(0);
            $table->tinyInteger('step12_review')->default(0);
            
            // Visibility
            $table->boolean('is_visible_to_travellers')->default(false);
            $table->boolean('is_visible_to_agents')->default(false);
            
            // Audit
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->softDeletes();
            
            // Indexes
            $table->index('business_id');
            $table->index('operator_id');
            $table->index('status');
            $table->index('is_published');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accommodations');
    }
};
