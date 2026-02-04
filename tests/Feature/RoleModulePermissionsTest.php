<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Business;
use App\Models\Module;
use App\Models\Operator;
use Spatie\Permission\Models\Role;
use App\Models\RoleModulePermission;

class RoleModulePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_set_module_permissions_for_role()
    {
        // create business and owner
        $business = Business::create([
            'business_id' => Business::generateBusinessId(),
            'legal_name' => 'PermCo',
            'status' => 'active',
        ]);

        $owner = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'ownerperm@example.com',
            'full_name' => 'Owner Perm',
            'business_id' => $business->id,
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
        ]);

        // create a role for this business
        $role = Role::create(['name' => 'Business Admin', 'guard_name' => 'web']);
        // Note: some DBs may not have roles.business_id column; permissions will be saved scoped to the owner's business.

        // create modules
        $account = Module::create(['name' => 'Account', 'slug' => 'account']);
        $book = Module::create(['name' => 'Bookings', 'slug' => 'bookings']);

        $this->actingAs($owner, 'operator');

        $payload = [
            'permissions' => [
                'account' => ['Read','Create','Publish'],
                'bookings' => ['Read','Update']
            ]
        ];

        $response = $this->post(route('operator.roles.permissions.update', $role->id), $payload);

        $response->assertRedirect(route('operator.roles.index'));

        $this->assertDatabaseHas('role_module_permissions', [
            'role_id' => $role->id,
            'module_id' => $account->id,
            'business_id' => $business->id,
            'can_read' => 1,
            'can_create' => 1,
            'can_publish' => 1,
        ]);

        $this->assertDatabaseHas('role_module_permissions', [
            'role_id' => $role->id,
            'module_id' => $book->id,
            'business_id' => $business->id,
            'can_read' => 1,
            'can_update' => 1,
            'can_create' => 0,
        ]);
    }
}
