<?php

namespace Tests\Feature;

use App\Models\AgentAccount;
use App\Models\AgentStaffUser;
use App\Models\Country;
use App\Services\AgentPermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgentActionVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_only_staff_see_no_unauthorized_actions_and_forms_are_read_only(): void
    {
        [$agent, $staff] = $this->createAgentAndStaff();
        $child = $agent->staffUsers()->create([
            'user_id' => 'AGU-VISIBILITY-READ-ONLY-CHILD',
            'full_name' => 'Read Only Child',
            'email' => 'read-only-child@visibility.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Manager',
            'created_by_agent_staff_user_id' => $staff->id,
            'status' => 'Active',
        ]);
        $permissions = app(AgentPermissionService::class);
        $permissions->syncPermissions($staff, [
            'profile' => ['read'],
            'compliance' => ['read'],
            'users' => ['read'],
            'operations' => ['read'],
            'reservation' => ['read'],
        ], $agent, 'Manager');

        $this->actingAs($staff, 'agent_staff');

        $this->get(route('agent.profile'))
            ->assertOk()
            ->assertSee('read-only access to this profile')
            ->assertSee('<fieldset disabled>', false)
            ->assertDontSee('Save and Continue');

        $this->get(route('agent.compliance'))
            ->assertOk()
            ->assertSee('read-only access to compliance information')
            ->assertSee('<fieldset disabled>', false)
            ->assertDontSee('Save and Continue');

        $this->get(route('agent.operations'))
            ->assertOk()
            ->assertSee('read-only access to operations information')
            ->assertSee('<fieldset disabled>', false)
            ->assertDontSee('Save and Finish');

        $this->get(route('agent.users'))
            ->assertOk()
            ->assertSee('read-only-child@visibility.test')
            ->assertDontSee('Add Agent User')
            ->assertDontSee('Create User')
            ->assertDontSee('Reset Password')
            ->assertDontSee(route('agent.users.edit', $child), false)
            ->assertDontSee('Disable');

        $this->get(route('agent.closed-groups.index'))
            ->assertOk()
            ->assertDontSee('Create Group Booking');

        $this->get(route('agent.dashboard'))
            ->assertOk()
            ->assertSee('Total Group Bookings')
            ->assertDontSee('Create Group Booking');

        $permissions->syncPermissions($staff, [
            'profile' => ['read'],
            'compliance' => ['read'],
            'users' => ['read'],
            'operations' => ['read'],
        ], $agent, 'Manager');
        $this->get(route('agent.dashboard'))
            ->assertOk()
            ->assertDontSee('Total Group Bookings');
        $this->get(route('agent.closed-groups.index'))->assertForbidden();

        $this->get(route('agent.closed-groups.book'))->assertForbidden();
        $this->post(route('agent.closed-groups.bank-transfer-proof', 1))->assertForbidden();
        $this->post(route('agent.profile.save'))->assertForbidden();
        $this->post(route('agent.compliance.save'))->assertForbidden();
        $this->post(route('agent.operations.save'))->assertForbidden();
        $this->post(route('agent.users.save'))->assertForbidden();
        $this->put(route('agent.users.update', $child))->assertForbidden();
        $this->post(route('agent.users.status', $child), ['status' => 'Disabled'])->assertForbidden();
        $this->post(route('agent.users.reset-password', $child), ['password' => 'Password123!'])->assertForbidden();
    }

    public function test_create_and_update_controls_follow_their_independent_permissions(): void
    {
        [$agent, $staff] = $this->createAgentAndStaff();
        $permissions = app(AgentPermissionService::class);
        $permissions->syncPermissions($staff, [
            'users' => ['read', 'create'],
            'reservation' => ['read', 'create'],
        ], $agent, 'Manager');
        $child = $agent->staffUsers()->create([
            'user_id' => 'AGU-VISIBILITY-CHILD',
            'full_name' => 'Child Staff',
            'email' => 'child@visibility.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Manager',
            'created_by_agent_staff_user_id' => $staff->id,
            'status' => 'Active',
        ]);

        $this->actingAs($staff, 'agent_staff')
            ->get(route('agent.users'))
            ->assertOk()
            ->assertSee('Create User')
            ->assertDontSee(route('agent.users.edit', $child), false)
            ->assertDontSee('Reset Password');

        $this->get(route('agent.closed-groups.index'))
            ->assertOk()
            ->assertSee('Create Group Booking');
        $this->post(route('agent.closed-groups.bank-transfer-proof', 1))->assertForbidden();

        $permissions->syncPermissions($staff, [
            'users' => ['read', 'update'],
            'reservation' => ['read', 'update'],
        ], $agent, 'Manager');

        $this->get(route('agent.users'))
            ->assertOk()
            ->assertDontSee('Create User')
            ->assertSee(route('agent.users.edit', $child), false)
            ->assertSee('Reset Password');
        $this->get(route('agent.users.edit', $child))
            ->assertOk()
            ->assertSee('Save User and Access Rights');

        $this->get(route('agent.closed-groups.index'))
            ->assertOk()
            ->assertDontSee('Create Group Booking');
        $this->get(route('agent.closed-groups.book'))->assertForbidden();
    }

    public function test_main_agent_retains_its_existing_action_access(): void
    {
        [$agent] = $this->createAgentAndStaff();

        $this->actingAs($agent, 'agent')
            ->get(route('agent.users'))
            ->assertOk()
            ->assertSee('Create User');

        $this->get(route('agent.closed-groups.index'))
            ->assertOk()
            ->assertSee('Create Group Booking');

        $this->get(route('agent.profile'))
            ->assertOk()
            ->assertSee('Save and Continue');
    }

    /**
     * @return array{AgentAccount, AgentStaffUser}
     */
    private function createAgentAndStaff(): array
    {
        $this->seed(\Database\Seeders\ModuleSeeder::class);
        $country = Country::query()->create([
            'name' => 'Action Visibility Country',
            'iso_code' => 'AV',
            'currency_code' => 'AVC',
            'is_active' => true,
        ]);
        $agent = AgentAccount::query()->create([
            'agent_id' => 'AGT-ACTION-VISIBILITY',
            'country_id' => $country->id,
            'agency_name' => 'Visibility Agency',
            'contact_person' => 'Agency Contact',
            'email' => 'owner@visibility.test',
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
        $ownerStaff = $agent->staffUsers()->create([
            'user_id' => 'AGU-ACTION-OWNER',
            'full_name' => 'Agent Owner',
            'email' => 'owner@visibility.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Manager',
            'status' => 'Active',
        ]);
        $staff = $agent->staffUsers()->create([
            'user_id' => 'AGU-ACTION-READER',
            'full_name' => 'Read Only Staff',
            'email' => 'reader@visibility.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Manager',
            'created_by_agent_staff_user_id' => $ownerStaff->id,
            'status' => 'Active',
        ]);

        return [$agent, $staff];
    }
}
