<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpo_staff_users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users')->cascadeOnDelete();
            $table->string('full_name', 191);
            $table->string('email', 191)->unique();
            $table->string('password_hash');
            $table->string('mobile', 20)->nullable();
            $table->string('role', 32);
            $table->string('status', 16)->default('Active');
            $table->timestamp('last_login')->nullable();
            $table->boolean('account_reset_required')->default(false);
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->index(['admin_user_id', 'status']);
        });

        Schema::create('mpo_role_module_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users')->cascadeOnDelete();
            $table->string('role', 32);
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->string('action', 32);
            $table->boolean('is_allowed')->default(false);
            $table->boolean('can_delegate')->default(false);
            $table->timestamps();
            $table->unique(['admin_user_id', 'role', 'module_id', 'action'], 'mpo_role_module_action_unique');
        });

        Schema::create('mpo_role_delegations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users')->cascadeOnDelete();
            $table->string('assigner_role', 32);
            $table->string('assignable_role', 32);
            $table->timestamps();
            $table->unique(['admin_user_id', 'assigner_role', 'assignable_role'], 'mpo_role_delegation_unique');
        });

        Schema::create('mpo_staff_permission_overrides', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mpo_staff_user_id')->constrained('mpo_staff_users')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->string('action', 32);
            $table->string('effect', 8);
            $table->boolean('can_delegate')->default(false);
            $table->timestamps();
            $table->unique(['mpo_staff_user_id', 'module_id', 'action'], 'mpo_staff_module_action_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpo_staff_permission_overrides');
        Schema::dropIfExists('mpo_role_delegations');
        Schema::dropIfExists('mpo_role_module_permissions');
        Schema::dropIfExists('mpo_staff_users');
    }
};
