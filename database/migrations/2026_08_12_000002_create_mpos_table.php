<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mpos', function (Blueprint $table) {
            $table->id();
            $table->string('mpo_id', 50);
            $table->enum('user_type', ['Operator','MPO','Agent'])->default('MPO');
            $table->enum('is_owner', ['yes','no'])->default('yes');
            $table->string('email', 191);
            $table->string('phone', 20)->nullable();
            $table->string('full_name', 255)->nullable();
            $table->string('business_legal_name', 255)->nullable();
            $table->string('country_of_operation', 255)->nullable();
            $table->string('agreement_type', 255)->nullable();
            $table->string('booking_registration_type', 255)->nullable();
            $table->string('owner_full_name', 255)->nullable();
            $table->string('owner_email', 255)->nullable();
            $table->string('owner_phone', 20)->nullable();
            $table->string('password_hash', 255);
            $table->string('password_reset_token', 255)->nullable();
            $table->dateTime('password_reset_expiry')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->enum('account_status', ['pending_verification','active','suspended','archived'])->default('pending_verification');
            $table->tinyInteger('operator_approve_flag')->default(0);
            $table->enum('registration_status', ['in_progress','submitted','approved','rejected','under_review'])->default('in_progress');
            $table->integer('current_step')->default(1)->nullable();
            $table->json('steps_completed')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->integer('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->index('mpo_id', 'idx_mpos_mpo_id');
            $table->index('business_id', 'idx_mpos_business_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mpos');
    }
};
