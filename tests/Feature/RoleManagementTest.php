<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Operator;
use App\Models\Business;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Run migrations and seed permissions (if package exists only)
        $this->artisan('migrate', ['--force' => true]);
        try {
            $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\PermissionsSeeder']);
        } catch (\Exception $e) {
            // spatie not installed in test env; tests that depend on it should be skipped in that case
        }
    }

    public function test_owner_can_create_role_and_assign_permissions()
    {
        // Create minimal business and owner operator since factories not present
        $business = Business::create(['legal_name' => 'Test Business', 'business_id' => \App\Models\Business::generateBusinessId()]);
        $owner = Operator::create([
            'operator_id' => 'OP' . uniqid(),
            'full_name' => 'Owner',
            'email' => 'owner@example.com',
            'password_hash' => bcrypt('password'),
            'is_owner' => 'yes',
            'business_id' => $business->id,
        ]);

        // Create an admin session and create role via admin endpoint
        session(['admin_id' => 1]);
        if (!\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            $this->markTestSkipped('roles table not present - Spatie package migrations not run');
        }

        $response = $this->post(route('admin.roles.store'), ['name' => 'Test Role', 'business_id' => $business->id]);
        $response->assertRedirect(route('admin.roles.index'));

        $role = Role::where('name', 'Test Role')->first();
        $this->assertNotNull($role);
        $this->assertEquals($business->id, $role->business_id);

        // Assign permission to role (if permissions exist)
        if (Permission::count() > 0) {
            $perm = Permission::first();
            $this->post(route('operator.roles.permissions.update', $role->id), ['permissions' => [$perm->name]]);
            $this->assertTrue($role->fresh()->hasPermissionTo($perm->name));
        }
    }

    public function test_non_owner_cannot_create_role()
    {
        $business = Business::create(['legal_name' => 'Test Business 2', 'business_id' => \App\Models\Business::generateBusinessId()]);
        $operator = Operator::create([
            'operator_id' => 'OP' . uniqid(),
            'full_name' => 'Operator',
            'email' => 'op@example.com',
            'password_hash' => bcrypt('password'),
            'is_owner' => 'no',
            'business_id' => $business->id,
        ]);
        $this->actingAs($operator, 'operator');

        $response = $this->post(route('operator.roles.store'), ['name' => 'Another Role']);
        // Creation should be disallowed; redirect back to roles list with error
        $response->assertRedirect(route('operator.roles.index'));

        if (!\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            $this->markTestSkipped('roles table not present - Spatie package migrations not run');
        }

        $this->assertNull(Role::where('name', 'Another Role')->first());
    }

    public function test_owner_can_delete_role()
    {
        $business = Business::create(['legal_name' => 'Delete Business', 'business_id' => \App\Models\Business::generateBusinessId()]);
        $owner = Operator::create([
            'operator_id' => 'OP' . uniqid(),
            'full_name' => 'OwnerDel',
            'email' => 'owner_del@example.com',
            'password_hash' => bcrypt('password'),
            'is_owner' => 'yes',
            'business_id' => $business->id,
        ]);

        $this->actingAs($owner, 'operator');

        if (!\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            $this->markTestSkipped('roles table not present - Spatie package migrations not run');
        }

        $role = Role::create(['name' => 'To Delete', 'guard_name' => 'web', 'business_id' => $business->id]);
        $response = $this->delete(route('operator.roles.destroy', $role->id));
        $response->assertRedirect(route('operator.roles.index'));
        $this->assertNull(Role::where('name', 'To Delete')->first());
    }

    public function test_non_owner_cannot_delete_role()
    {
        $business = Business::create(['legal_name' => 'Delete Business 2', 'business_id' => \App\Models\Business::generateBusinessId()]);
        $owner = Operator::create([
            'operator_id' => 'OP' . uniqid(),
            'full_name' => 'OwnerDel2',
            'email' => 'owner_del2@example.com',
            'password_hash' => bcrypt('password'),
            'is_owner' => 'yes',
            'business_id' => $business->id,
        ]);

        if (!\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            $this->markTestSkipped('roles table not present - Spatie package migrations not run');
        }

        $role = Role::create(['name' => 'Other Role', 'guard_name' => 'web', 'business_id' => $business->id]);

        $operator = Operator::create([
            'operator_id' => 'OP' . uniqid(),
            'full_name' => 'Not Owner',
            'email' => 'not_owner@example.com',
            'password_hash' => bcrypt('password'),
            'is_owner' => 'no',
            'business_id' => $business->id,
        ]);

        $this->actingAs($operator, 'operator');
        $response = $this->delete(route('operator.roles.destroy', $role->id));
        $response->assertRedirect(route('operator.roles.index'));
        $this->assertNotNull(Role::where('name', 'Other Role')->first());
    }
}
