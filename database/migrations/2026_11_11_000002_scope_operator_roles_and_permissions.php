<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table): void {
                if (! Schema::hasColumn('roles', 'scope')) {
                    $table->string('scope', 16)->default('global');
                }
                if (! Schema::hasColumn('roles', 'country_id')) {
                    $table->foreignId('country_id')->nullable();
                }
                if (! Schema::hasColumn('roles', 'role_scope_key')) {
                    $table->string('role_scope_key', 32)->nullable();
                }
            });
            if (! Schema::hasIndex('roles', 'roles_scope_country_index')) {
                Schema::table('roles', function (Blueprint $table): void {
                    $table->index(['scope', 'country_id'], 'roles_scope_country_index');
                });
            }

            if (Schema::hasColumn('roles', 'business_id')) {
                DB::table('roles')->whereNull('business_id')->update(['scope' => 'global']);
                DB::table('roles')->whereNotNull('business_id')->update(['scope' => 'business']);
                DB::table('roles')->whereNull('business_id')->update(['role_scope_key' => 'global:0']);
                foreach (DB::table('roles')->whereNotNull('business_id')->get(['id', 'business_id']) as $legacyRole) {
                    DB::table('roles')->where('id', $legacyRole->id)->update([
                        'role_scope_key' => 'business:'.$legacyRole->business_id,
                    ]);
                }
            } else {
                DB::table('roles')->update(['role_scope_key' => 'global:0']);
            }

            if (! Schema::hasIndex('roles', 'roles_scope_name_guard_unique')) {
                Schema::table('roles', function (Blueprint $table): void {
                    $table->unique(['role_scope_key', 'name', 'guard_name'], 'roles_scope_name_guard_unique');
                });
            }
            $this->addForeignKeyIfMissing('roles', 'country_id', 'countries', 'roles_country_fk', 'set null');
        }

        if (Schema::hasTable('operator_users')) {
            Schema::table('operator_users', function (Blueprint $table): void {
                if (! Schema::hasColumn('operator_users', 'created_by_operator_user_id')) {
                    $table->foreignId('created_by_operator_user_id')->nullable();
                }
            });
            $this->addForeignKeyIfMissing(
                'operator_users',
                'created_by_operator_user_id',
                'operator_users',
                'op_users_creator_fk',
                'set null',
            );
        }

        if (Schema::hasTable('operators')
            && Schema::hasTable('operator_users')
            && Schema::hasTable('roles')
            && Schema::hasTable('modules')) {
            if (! Schema::hasTable('operator_role_module_permissions')) {
                Schema::create('operator_role_module_permissions', function (Blueprint $table): void {
                    $table->id();
                    $table->foreignId('operator_id');
                    $table->string('role_name', 191);
                    $table->foreignId('module_id');
                    $table->string('action', 32);
                    $table->boolean('is_allowed')->default(false);
                    $table->boolean('can_delegate')->default(false);
                    $table->timestamps();
                    $table->unique(
                        ['operator_id', 'role_name', 'module_id', 'action'],
                        'operator_role_module_action_unique',
                    );
                });
            }

            if (! Schema::hasTable('operator_staff_permission_overrides')) {
                Schema::create('operator_staff_permission_overrides', function (Blueprint $table): void {
                    $table->id();
                    $table->foreignId('operator_user_id');
                    $table->foreignId('module_id');
                    $table->string('action', 32);
                    $table->string('effect', 8);
                    $table->boolean('can_delegate')->default(false);
                    $table->foreignId('granted_by_operator_user_id')->nullable();
                    $table->timestamps();
                    $table->unique(
                        ['operator_user_id', 'module_id', 'action'],
                        'operator_staff_module_action_unique',
                    );
                });
            }

            $this->addForeignKeyIfMissing(
                'operator_role_module_permissions',
                'operator_id',
                'operators',
                'op_role_perm_operator_fk',
                'cascade',
            );
            $this->addForeignKeyIfMissing(
                'operator_role_module_permissions',
                'module_id',
                'modules',
                'op_role_perm_module_fk',
                'cascade',
            );
            $this->addForeignKeyIfMissing(
                'operator_staff_permission_overrides',
                'operator_user_id',
                'operator_users',
                'op_staff_perm_user_fk',
                'cascade',
            );
            $this->addForeignKeyIfMissing(
                'operator_staff_permission_overrides',
                'module_id',
                'modules',
                'op_staff_perm_module_fk',
                'cascade',
            );
            $this->addForeignKeyIfMissing(
                'operator_staff_permission_overrides',
                'granted_by_operator_user_id',
                'operator_users',
                'op_staff_perm_granted_fk',
                'set null',
            );

            if (Schema::hasTable('role_module_permissions')
                && Schema::hasTable('operator_users')
                && Schema::hasColumn('roles', 'business_id')) {
                $legacyPermissions = DB::table('role_module_permissions')
                    ->join('roles', 'roles.id', '=', 'role_module_permissions.role_id')
                    ->join('modules', 'modules.id', '=', 'role_module_permissions.module_id')
                    ->select('role_module_permissions.*', 'roles.name as role_name', 'modules.slug as module_slug')
                    ->get();

                foreach ($legacyPermissions as $legacy) {
                    if (! $legacy->business_id) {
                        continue;
                    }

                    $operators = DB::table('operators')
                        ->where('business_id', $legacy->business_id)
                        ->get(['id', 'operator_id', 'business_id']);
                    foreach ($operators as $operator) {
                        foreach ([
                            'read' => 'can_read',
                            'create' => 'can_create',
                            'update' => 'can_update',
                            'approve' => 'can_approve',
                            'publish' => 'can_publish',
                        ] as $action => $column) {
                            if (! (bool) $legacy->{$column}) {
                                continue;
                            }

                            DB::table('operator_role_module_permissions')->updateOrInsert(
                                [
                                    'operator_id' => $operator->id,
                                    'role_name' => $legacy->role_name,
                                    'module_id' => $legacy->module_id,
                                    'action' => $action,
                                ],
                                [
                                    'is_allowed' => true,
                                    'can_delegate' => false,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ],
                            );

                            $staffUsers = DB::table('operator_users')
                                ->where('operator_id', $operator->operator_id)
                                ->where('business_id', $operator->business_id)
                                ->where('role', $legacy->role_name)
                                ->get(['id']);
                            foreach ($staffUsers as $staffUser) {
                                DB::table('operator_staff_permission_overrides')->updateOrInsert(
                                    [
                                        'operator_user_id' => $staffUser->id,
                                        'module_id' => $legacy->module_id,
                                        'action' => $action,
                                    ],
                                    [
                                        'effect' => 'allow',
                                        'can_delegate' => false,
                                        'granted_by_operator_user_id' => null,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ],
                                );
                            }
                        }
                    }
                }
            }

        }
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_staff_permission_overrides');
        Schema::dropIfExists('operator_role_module_permissions');

        if (Schema::hasTable('operator_users') && Schema::hasColumn('operator_users', 'created_by_operator_user_id')) {
            Schema::table('operator_users', function (Blueprint $table): void {
                $table->dropForeign('op_users_creator_fk');
                $table->dropColumn('created_by_operator_user_id');
            });
        }

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table): void {
                if (Schema::hasColumn('roles', 'country_id')) {
                    $table->dropIndex('roles_scope_country_index');
                    $table->dropForeign('roles_country_fk');
                    $table->dropColumn('country_id');
                }
                if (Schema::hasColumn('roles', 'role_scope_key')) {
                    $table->dropUnique('roles_scope_name_guard_unique');
                    $table->dropColumn('role_scope_key');
                }
                if (Schema::hasColumn('roles', 'scope')) {
                    $table->dropColumn('scope');
                }
            });
        }
    }

    private function addForeignKeyIfMissing(
        string $tableName,
        string $column,
        string $referencedTable,
        string $constraintName,
        string $onDelete,
    ): void {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $column)) {
            return;
        }

        $exists = collect(Schema::getForeignKeys($tableName))->contains(
            fn (array $foreignKey): bool => in_array($column, $foreignKey['columns'], true)
                && $foreignKey['foreign_table'] === $referencedTable,
        );
        if ($exists) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column, $referencedTable, $constraintName, $onDelete): void {
            $foreign = $table->foreign($column, $constraintName)
                ->references('id')
                ->on($referencedTable);
            $onDelete === 'cascade' ? $foreign->cascadeOnDelete() : $foreign->nullOnDelete();
        });
    }
};
