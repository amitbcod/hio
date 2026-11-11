<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mpo_staff_users', function (Blueprint $table): void {
            $table->foreignId('created_by_admin_user_id')
                ->nullable()
                ->after('admin_user_id')
                ->constrained('admin_users')
                ->nullOnDelete();
            $table->foreignId('created_by_staff_user_id')
                ->nullable()
                ->after('created_by_admin_user_id')
                ->constrained('mpo_staff_users')
                ->nullOnDelete();
            $table->index(['admin_user_id', 'created_by_staff_user_id'], 'mpo_staff_creator_scope_index');
        });
    }

    public function down(): void
    {
        Schema::table('mpo_staff_users', function (Blueprint $table): void {
            $table->dropIndex('mpo_staff_creator_scope_index');
            $table->dropConstrainedForeignId('created_by_staff_user_id');
            $table->dropConstrainedForeignId('created_by_admin_user_id');
        });
    }
};
