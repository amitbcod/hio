<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\RoleController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminRoleSchemaCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'admin_role_schema_test',
            'database.connections.admin_role_schema_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('admin_role_schema_test');
        DB::setDefaultConnection('admin_role_schema_test');

        Schema::create('countries', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->string('scope', 16);
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('role_scope_key', 32);
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
            $table->unique(['role_scope_key', 'name', 'guard_name']);
        });
        DB::table('countries')->insert(['id' => 1, 'name' => 'Example Country']);

        session()->put('admin_id', 1);
    }

    public function test_global_admin_can_create_and_update_roles_without_legacy_business_column(): void
    {
        $controller = new RoleController();
        $create = Request::create('/admin/roles', 'POST', [
            'name' => 'Global Operator',
            'scope' => 'global',
        ]);
        $controller->store($create);

        $role = Role::query()->where('name', 'Global Operator')->firstOrFail();
        $this->assertSame('global', $role->scope);
        $this->assertNull($role->country_id);
        $this->assertSame('global:0', $role->role_scope_key);

        $update = Request::create('/admin/roles/'.$role->id, 'PUT', [
            'name' => 'Updated Global Operator',
            'scope' => 'global',
        ]);
        $controller->update($update, $role);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Updated Global Operator',
            'scope' => 'global',
            'role_scope_key' => 'global:0',
        ]);
    }
}
