<?php

namespace Tests\Feature;

use App\Models\AgentAccount;
use App\Models\AgentStaffUser;
use App\Models\Country;
use App\Services\AgentPermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgentStaffScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_staff_can_create_same_role_descendants_with_only_delegated_rights(): void
    {
        $this->seed(\Database\Seeders\ModuleSeeder::class);
        $country = Country::query()->create([
            'name' => 'Scoped Test Country',
            'iso_code' => 'ZZ',
            'currency_code' => 'ZZZ',
            'is_active' => true,
        ]);
        $agent = AgentAccount::query()->create([
            'agent_id' => 'AGT-STAFF-SCOPE',
            'country_id' => $country->id,
            'agency_name' => 'Scoped Agency',
            'contact_person' => 'Agency Contact',
            'email' => 'owner@scoped.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'password_hash' => Hash::make('Password123!'),
            'account_status' => 'approved',
        ]);
        $agent->setupProgress()->create([
            'profile_complete' => true,
            'compliance_complete' => true,
            'users_complete' => true,
            'operations_complete' => true,
        ]);
        $creator = $agent->staffUsers()->create([
            'user_id' => 'AGU-SCOPE-CREATOR',
            'full_name' => 'Manager A',
            'email' => 'manager-a@scoped.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Manager',
            'status' => 'Active',
        ]);
        $unrelated = $agent->staffUsers()->create([
            'user_id' => 'AGU-SCOPE-UNRELATED',
            'full_name' => 'Manager B',
            'email' => 'manager-b@scoped.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Manager',
            'status' => 'Active',
        ]);
        $otherAgent = AgentAccount::query()->create([
            'agent_id' => 'AGT-OTHER-SCOPE',
            'country_id' => $country->id,
            'agency_name' => 'Other Agency',
            'contact_person' => 'Other Contact',
            'email' => 'owner@other.test',
            'office_phone' => '+23012345679',
            'emergency_mobile' => '+23087654322',
            'password_hash' => Hash::make('Password123!'),
            'account_status' => 'approved',
        ]);
        $foreignStaff = $otherAgent->staffUsers()->create([
            'user_id' => 'AGU-FOREIGN-SCOPE',
            'full_name' => 'Other Agent Staff',
            'email' => 'staff@other.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Manager',
            'status' => 'Active',
        ]);

        app(AgentPermissionService::class)->syncPermissions(
            $creator,
            ['users' => ['read', 'create', 'update'], 'reservation' => ['read', 'create']],
            $agent,
            'Manager',
        );

        $this->actingAs($creator, 'agent_staff')
            ->get(route('agent.users'))
            ->assertOk()
            ->assertSee('Closed Group Bookings')
            ->assertDontSee('Operations')
            ->assertDontSee('manager-b@scoped.test');

        $this->post(route('agent.users.save'), [
            'full_name' => 'Manager C',
            'email' => 'manager-c@scoped.test',
            'mobile' => '+23057011234',
            'password' => 'Password123!',
            'agent_account_id' => $otherAgent->id,
            'created_by_agent_staff_user_id' => $unrelated->id,
            'module_actions' => [
                'users' => ['read'],
                'reservation' => ['read'],
            ],
        ])->assertRedirect(route('agent.users'));

        $child = AgentStaffUser::query()->where('email', 'manager-c@scoped.test')->firstOrFail();
        $this->assertSame('Manager', $child->role);
        $this->assertSame($agent->id, $child->agent_account_id);
        $this->assertSame($creator->id, $child->created_by_agent_staff_user_id);
        $permissions = app(AgentPermissionService::class);
        $this->assertTrue($permissions->can($child, 'users', 'read'));
        $this->assertFalse($permissions->can($child, 'users', 'create'));
        $this->assertTrue($permissions->can($child, 'reservation', 'read'));
        $this->assertFalse($permissions->can($child, 'reservation', 'create'));

        $this->get(route('agent.users'))
            ->assertOk()
            ->assertSee('manager-c@scoped.test')
            ->assertDontSee('manager-b@scoped.test')
            ->assertDontSee('Operations');

        $this->getJson(route('agent.users.edit', $unrelated))->assertNotFound();
        $this->putJson(route('agent.users.update', $unrelated), [
            'full_name' => 'Changed',
            'email' => 'changed@scoped.test',
            'role' => 'Manager',
            'module_actions' => [],
        ])->assertNotFound();
        $this->putJson(route('agent.users.update', $child), [
            'full_name' => 'Manager C',
            'email' => 'manager-c@scoped.test',
            'role' => 'Consultant',
            'module_actions' => ['reservation' => ['read']],
        ])->assertUnprocessable();
        $this->putJson(route('agent.users.update', $child), [
            'full_name' => 'Manager C',
            'email' => 'manager-c@scoped.test',
            'module_actions' => ['reservation' => ['update']],
        ])->assertForbidden();
        $this->postJson(route('agent.users.save'), [
            'full_name' => 'Escalated Manager',
            'email' => 'escalated@scoped.test',
            'password' => 'Password123!',
            'role' => 'Consultant',
            'module_actions' => ['reservation' => ['update']],
        ])->assertUnprocessable();
        $this->assertDatabaseMissing('agent_staff_users', ['email' => 'escalated@scoped.test']);
        $this->assertFalse($permissions->targetIsManageable($creator, $foreignStaff));
    }
}
