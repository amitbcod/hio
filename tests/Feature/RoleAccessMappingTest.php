<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Operator;
use App\Models\OperatorUser;
use App\Models\Business;
use App\Models\Module;
use App\Models\OperatorRoleAccessMapping;

class RoleAccessMappingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_assign_module_permissions_to_user()
    {
        // create business and owner operator
        $business = Business::create([
            'business_id' => Business::generateBusinessId(),
            'legal_name' => 'OwnerCo',
            'status' => 'active',
        ]);

        $owner = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'owner@example.com',
            'full_name' => 'Owner',
            'password_hash' => bcrypt('Password123!'),
            'account_status' => 'active',
            'business_id' => $business->id,
        ]);

        // create a staff user under business
        $user = OperatorUser::create([
            'user_id' => uniqid('OPU'),
            'operator_id' => $owner->operator_id,
            'business_id' => $business->id,
            'full_name' => 'Staff Member',
            'email' => 'staff@example.com',
            'mobile' => '+1234567890',
            'status' => 'Active',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'Staff',
        ]);

        // ensure module exists
        $module = Module::create(['name' => 'Account', 'slug' => 'account']);

        // perform request as owner
        $this->actingAs($owner, 'operator');

        $response = $this->post('/operator/register/step6-role-access', [
            'user_id' => $user->id,
            'role' => 'Admin',
            'module' => $module->slug,
            'capacity_level' => 'Section',
            'permissions' => ['Read','Create','Publish'],
        ]);

        $response->assertRedirect(route('operator.register.step6'));

        $this->assertDatabaseHas('operator_role_access_mapping', [
            'user_id' => $user->id,
            'module' => $module->slug,
            'business_id' => $business->id,
            'can_read' => 1,
            'can_create' => 1,
            'can_update' => 0,
            'can_approve' => 0,
            'can_publish' => 1,
        ]);
    }
}
