<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpo_role_module_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users')->cascadeOnDelete();
            $table->string('role', 32);
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->string('capacity_level', 100)->nullable();
            $table->string('data_scope', 32)->default('all');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['admin_user_id', 'role', 'module_id'], 'mpo_role_module_settings_unique');
        });

        Schema::table('mpo_role_module_permissions', function (Blueprint $table): void {
            $table->decimal('approval_limit', 14, 2)->nullable();
        });

        Schema::table('mpo_staff_permission_overrides', function (Blueprint $table): void {
            $table->string('grantor_role', 32)->nullable();
            $table->foreignId('granted_by_staff_user_id')->nullable()
                ->constrained('mpo_staff_users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mpo_role_module_permissions', function (Blueprint $table): void {
            $table->dropColumn('approval_limit');
        });

        Schema::table('mpo_staff_permission_overrides', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('granted_by_staff_user_id');
            $table->dropColumn('grantor_role');
        });

        Schema::dropIfExists('mpo_role_module_settings');
    }
};
