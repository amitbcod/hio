<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpo_compliances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_user_id')
                ->unique()
                ->constrained('admin_users')
                ->cascadeOnDelete();
            $table->string('business_license_number', 100);
            $table->string('license_type', 64);
            $table->string('other_license_type_description', 191)->nullable();
            $table->date('license_expiry_date');
            $table->string('proof_of_license_path', 255);
            $table->string('proof_of_license_filename', 255)->nullable();
            $table->string('insurance_certificate_path', 255);
            $table->string('insurance_certificate_filename', 255)->nullable();
            $table->string('verification_status', 32)->default('pending_verification');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by_admin_id')
                ->nullable()
                ->constrained('admin_users')
                ->nullOnDelete();
            $table->timestamps();
            $table->index('license_expiry_date');
            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpo_compliances');
    }
};
