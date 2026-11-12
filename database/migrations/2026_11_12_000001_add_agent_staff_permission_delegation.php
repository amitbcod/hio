<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agent_staff_users')
            && ! Schema::hasColumn('agent_staff_users', 'created_by_agent_staff_user_id')) {
            Schema::table('agent_staff_users', function (Blueprint $table): void {
                $table->unsignedBigInteger('created_by_agent_staff_user_id')->nullable();
                $table->index('created_by_agent_staff_user_id', 'agent_staff_creator_index');
                $table->foreign('created_by_agent_staff_user_id', 'agent_staff_creator_fk')
                    ->references('id')
                    ->on('agent_staff_users');
            });
        }

        if (Schema::hasTable('agent_staff_permissions')
            && ! Schema::hasColumn('agent_staff_permissions', 'can_delegate')) {
            Schema::table('agent_staff_permissions', function (Blueprint $table): void {
                $table->boolean('can_delegate')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('agent_staff_permissions')
            && Schema::hasColumn('agent_staff_permissions', 'can_delegate')) {
            Schema::table('agent_staff_permissions', function (Blueprint $table): void {
                $table->dropColumn('can_delegate');
            });
        }

        if (Schema::hasTable('agent_staff_users')
            && Schema::hasColumn('agent_staff_users', 'created_by_agent_staff_user_id')) {
            $hasForeignKey = collect(Schema::getForeignKeys('agent_staff_users'))
                ->contains(fn (array $foreignKey): bool => $foreignKey['name'] === 'agent_staff_creator_fk');
            $hasIndex = Schema::hasIndex('agent_staff_users', 'agent_staff_creator_index');

            Schema::table('agent_staff_users', function (Blueprint $table) use ($hasForeignKey, $hasIndex): void {
                if ($hasForeignKey) {
                    $table->dropForeign('agent_staff_creator_fk');
                }
                if ($hasIndex) {
                    $table->dropIndex('agent_staff_creator_index');
                }
                $table->dropColumn('created_by_agent_staff_user_id');
            });
        }
    }
};
