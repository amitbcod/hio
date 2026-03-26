<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('activity_compliance')) {
            return;
        }

        Schema::create('activity_compliance', function (Blueprint $table) {
            $table->id();
            $table->string('compliance_id', 50)->unique();
            $table->unsignedBigInteger('activity_id')->required();
            
            // Service ID (Parent service ID - managed by operator)
            $table->string('parent_service_id')->nullable();
            
            // Business Registration Number
            $table->string('business_registration_number', 100)->nullable();
            
            // Tourism Activity Permit
            $table->string('tourism_activity_permit', 100)->nullable();
            
            // Permits/Authorisations (file paths stored in JSON)
            $table->json('permits_authorisations_files')->nullable();
            
            // Public Liability Insurance
            $table->string('public_liability_insurance', 100)->nullable();
            $table->date('insurance_expiration')->nullable();
            
            // File uploads (stored as paths)
            $table->string('tourism_permit_file')->nullable();
            $table->string('insurance_file')->nullable();
            $table->json('other_permit_files')->nullable();
            
            // Verification fields
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by', 255)->nullable();
            $table->text('verification_notes')->nullable();
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->index('activity_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_compliance');
    }
};
