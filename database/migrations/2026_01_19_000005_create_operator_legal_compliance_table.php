<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_legal_compliance', function (Blueprint $table) {
            $table->id();
            $table->string('operator_id', 50);
            $table->string('business_legal_name', 255)->nullable();
            $table->string('business_registration_number', 100)->nullable();
            $table->text('registered_address')->nullable();
            $table->string('business_license_number', 100);
            $table->enum('license_type', ['Accommodation','Tour Operator','Car Rental','Guide','Other']);
            $table->date('license_expiry_date');
            $table->string('proof_of_license', 255)->nullable();
            $table->string('insurance_certificate', 255)->nullable();
            $table->string('signed_agreement', 255)->nullable();
            $table->enum('service_package', ['HIO Listing Only','HIO Partner Standard','HIO Partner Pro','HIO Partner Elite','HIO Full Service'])->default('HIO Listing Only');
            $table->enum('compliance_status', ['pending_verification','verified','expired','needs_renewal'])->default('pending_verification');
            $table->dateTime('verification_date')->nullable();
            $table->integer('verified_by')->nullable();
            $table->tinyInteger('renewal_alert_sent')->default(0);
            $table->dateTime('renewal_alert_date')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->unique('operator_id');
            $table->index('operator_id', 'idx_operator_legal_compliance_operator_id');
            $table->index('license_expiry_date', 'idx_license_expiry');
            $table->index('license_expiry_date', 'idx_compliance_expiry');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_legal_compliance');
    }
};
