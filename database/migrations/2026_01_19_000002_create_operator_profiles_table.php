<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('operator_id', 50);
            $table->string('business_legal_name', 255);
            $table->string('business_registration_number', 100)->nullable();
            // Removed country_of_operation as per new requirements
            $table->text('registered_address')->nullable();
            $table->text('operational_address')->nullable();
            $table->json('service_types')->nullable();
            $table->integer('years_in_operation')->nullable();
            $table->json('departments')->nullable();
            $table->string('trading_name', 255)->nullable();
            $table->string('company_logo', 255)->nullable();
            $table->text('company_description')->nullable();
            $table->json('contact_details')->nullable();
            $table->json('social_media_links')->nullable();
            $table->tinyInteger('profile_verified')->default(0);
            $table->integer('profile_verified_by')->nullable();
            $table->dateTime('profile_verified_date')->nullable();
            $table->enum('status', ['draft','pending_approval','approved','rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->string('contact_name', 255)->nullable();
            $table->string('contact_email', 255)->nullable();
            $table->string('contact_phone', 255)->nullable();
            $table->string('facebook_link', 255)->nullable();
            $table->string('instagram_link', 255)->nullable();
            $table->string('linkedin_link', 255)->nullable();
            $table->index('status', 'idx_profiles_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('operator_profiles');
    }
};
