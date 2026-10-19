<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('agent_id', 50)->unique();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->string('agency_name');
            $table->string('contact_person');
            $table->string('email')->unique();
            $table->string('office_phone', 20);
            $table->string('emergency_mobile', 20);
            $table->string('password_hash');
            $table->string('account_status', 24)->default('pending');
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_account_id')->unique()->constrained('agent_accounts')->cascadeOnDelete();
            $table->string('address');
            $table->string('city');
            $table->string('agency_type')->nullable();
            $table->json('focus_segments')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('description', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('agent_compliance_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_account_id')->constrained('agent_accounts')->cascadeOnDelete();
            $table->string('business_registration_number');
            $table->string('iata_number')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('document_type')->nullable();
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->string('file_path');
            $table->date('renewal_reminder_date')->nullable();
            $table->string('status', 24)->default('pending');
            $table->timestamps();
            $table->index(['agent_account_id', 'expiry_date']);
        });

        Schema::create('agent_setup_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_account_id')->unique()->constrained('agent_accounts')->cascadeOnDelete();
            $table->boolean('profile_complete')->default(false);
            $table->boolean('compliance_complete')->default(false);
            $table->boolean('users_complete')->default(false);
            $table->boolean('operations_complete')->default(false);
            $table->unsignedTinyInteger('current_step')->default(2);
            $table->timestamps();
        });

        Schema::create('agent_staff_users', function (Blueprint $table): void {
            $table->id();
            $table->string('user_id', 50)->unique();
            $table->foreignId('agent_account_id')->constrained('agent_accounts')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('mobile', 20)->nullable();
            $table->string('role', 32);
            $table->string('status', 16)->default('Active');
            $table->dateTime('last_login')->nullable();
            $table->boolean('account_reset_required')->default(true);
            $table->timestamps();
            $table->index(['agent_account_id', 'status']);
        });

        Schema::create('agent_staff_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_staff_user_id')->constrained('agent_staff_users')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->boolean('can_read')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_approve')->default(false);
            $table->boolean('can_publish')->default(false);
            $table->string('capacity_level')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['agent_staff_user_id', 'module_id'], 'agent_staff_module_unique');
        });

        Schema::create('agent_operations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_account_id')->unique()->constrained('agent_accounts')->cascadeOnDelete();
            $table->string('preferred_language_ui', 2)->nullable();
            $table->string('preferred_language_communication', 2)->nullable();
            $table->string('time_zone', 64)->nullable();
            $table->string('primary_currency', 3)->nullable();
            $table->json('bank_details')->nullable();
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_mobile', 20);
            $table->string('commission_type', 24)->nullable();
            $table->decimal('commission_value', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_operations');
        Schema::dropIfExists('agent_staff_permissions');
        Schema::dropIfExists('agent_staff_users');
        Schema::dropIfExists('agent_setup_progress');
        Schema::dropIfExists('agent_compliance_documents');
        Schema::dropIfExists('agent_profiles');
        Schema::dropIfExists('agent_accounts');
    }
};
