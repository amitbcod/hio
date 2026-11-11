<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OperatorPermissionMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'operator_permission_migration_test',
            'database.connections.operator_permission_migration_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('operator_permission_migration_test');
        DB::setDefaultConnection('operator_permission_migration_test');

        Schema::create('countries', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unique(['name', 'guard_name']);
        });
        Schema::create('operators', function (Blueprint $table): void {
            $table->id();
            $table->string('operator_id');
            $table->unsignedBigInteger('business_id')->nullable();
        });
        Schema::create('operator_users', function (Blueprint $table): void {
            $table->id();
            $table->string('operator_id');
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('role');
            $table->timestamps();
        });
        Schema::create('modules', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
        });
        Schema::create('role_module_permissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('business_id')->nullable();
            $table->boolean('can_read')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_approve')->default(false);
            $table->boolean('can_publish')->default(false);
            $table->timestamps();
        });
    }

    public function test_migration_adds_scopes_and_preserves_legacy_operator_permissions_without_delegation(): void
    {
        DB::table('roles')->insert([
            'id' => 1,
            'name' => 'Finance Manager',
            'guard_name' => 'web',
            'business_id' => 20,
        ]);
        DB::table('operators')->insert([
            'id' => 10,
            'operator_id' => 'OP-1',
            'business_id' => 20,
        ]);
        DB::table('operator_users')->insert([
            'id' => 100,
            'operator_id' => 'OP-1',
            'business_id' => 20,
            'role' => 'Finance Manager',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('modules')->insert(['id' => 5, 'slug' => 'transport']);
        DB::table('role_module_permissions')->insert([
            'role_id' => 1,
            'module_id' => 5,
            'business_id' => 20,
            'can_read' => true,
            'can_create' => false,
            'can_update' => false,
            'can_approve' => false,
            'can_publish' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_11_11_000002_scope_operator_roles_and_permissions.php');
        $migration->up();
        $migration->up();

        $this->assertSame('business', DB::table('roles')->where('id', 1)->value('scope'));
        $this->assertSame('business:20', DB::table('roles')->where('id', 1)->value('role_scope_key'));
        $this->assertDatabaseHas('operator_role_module_permissions', [
            'operator_id' => 10,
            'role_name' => 'Finance Manager',
            'module_id' => 5,
            'action' => 'read',
            'is_allowed' => 1,
            'can_delegate' => 0,
        ]);
        $this->assertDatabaseHas('operator_staff_permission_overrides', [
            'operator_user_id' => 100,
            'module_id' => 5,
            'action' => 'read',
            'effect' => 'allow',
            'can_delegate' => 0,
        ]);
        $this->assertTrue(Schema::hasColumn('operator_users', 'created_by_operator_user_id'));
        $this->assertCount(3, Schema::getForeignKeys('operator_staff_permission_overrides'));
        $this->assertTrue(
            collect(Schema::getForeignKeys('operator_staff_permission_overrides'))
                ->contains(fn (array $foreignKey): bool => $foreignKey['columns'] === ['granted_by_operator_user_id']
                    && $foreignKey['foreign_table'] === 'operator_users'),
        );
    }
}
