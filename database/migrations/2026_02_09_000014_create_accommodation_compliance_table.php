<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accommodation_compliance', function (Blueprint $table) {
            $table->id();
            $table->string('compliance_id', 50)->unique();
            $table->unsignedBigInteger('accommodation_id')->required();
            $table->string('tourism_permit_number', 100)->nullable();
            $table->string('insurance_provider', 255)->nullable();
            $table->string('insurance_policy_number', 100)->nullable();
            $table->timestamp('insurance_expiry_date')->nullable();
            $table->string('fire_safety_certificate', 255)->nullable();
            $table->timestamp('fire_safety_expiry')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by', 255)->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->onDelete('cascade');
            $table->index('accommodation_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accommodation_compliance');
    }
};
