<?php

namespace Tests\Feature;

use App\Models\AgentAccount;
use App\Models\AgentStaffUser;
use App\Models\AdminUser;
use App\Models\Country;
use App\Models\Operator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AgentOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_registration_creates_one_role_based_agent_login_and_uses_a_single_country(): void
    {
        $country = $this->country();
        $otherCountry = Country::query()->create([
            'name' => 'France',
            'iso_code' => 'FR',
            'currency_code' => 'EUR',
            'is_active' => true,
        ]);

        $response = $this->post(route('agent.register.store'), [
            'agency_name' => 'Island Travel',
            'contact_person' => 'A. Agent',
            'is_owner' => 'yes',
            'owner_approved' => '1',
            'status' => 'approved',
            'account_status' => 'approved',
            'applicant_email' => 'agent@example.test',
            'applicant_mobile' => '+230 57011234',
            'applicant_full_name' => 'A. Agent',
            'applicant_role' => 'Consultant',
            'email' => 'agency@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'country_id' => $country->id,
            'operating_countries' => [$country->id, $otherCountry->id],
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('agent.login'));
        $agent = AgentAccount::query()->where('email', 'agent@example.test')->firstOrFail();
        $this->assertSame($country->id, $agent->country_id);
        $this->assertSame('agency@example.test', $agent->agency_email);
        $this->assertSame('A. Agent', $agent->owner_full_name);
        $this->assertSame('agent@example.test', $agent->owner_email);
        $applicant = $agent->staffUsers()->firstOrFail();
        $this->assertSame('Consultant', $applicant->role);
        $this->assertSame('+23057011234', $applicant->mobile);
        $this->assertTrue(Hash::check('Password123!', $applicant->password_hash));
        $this->assertDatabaseHas('agent_setup_progress', [
            'agent_account_id' => $agent->id,
            'current_step' => 2,
        ]);
        $this->assertSame('yes', $agent->is_owner);
        $this->assertSame('pending', $agent->account_status);
        $this->assertNotNull($agent->business_id);
        $this->assertDatabaseMissing('controller_verifications', [
            'requester_agent_account_id' => $agent->id,
        ]);
        $this->post(route('agent.login.store'), [
            'email' => 'agent@example.test',
            'password' => 'Password123!',
        ])->assertRedirect(route('agent.dashboard'));
        $this->assertAuthenticatedAs($applicant, 'agent_staff');
        $this->get(route('agent.users'))->assertForbidden();
    }

    public function test_non_owner_registration_requires_owner_details_and_retains_them_after_validation_errors(): void
    {
        $country = $this->country();
        $response = $this->from(route('agent.register'))->post(route('agent.register.store'), [
            'agency_name' => 'Island Travel',
            'contact_person' => 'A. Agent',
            'is_owner' => 'no',
            'applicant_email' => 'applicant@example.test',
            'applicant_mobile' => '+230 57011234',
            'applicant_full_name' => 'John Smith',
            'applicant_role' => 'Consultant',
            'email' => 'agency@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'country_id' => $country->id,
            'owner_full_name' => '',
            'owner_email' => 'not-an-email',
            'owner_phone' => '',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['owner_full_name', 'owner_email', 'owner_phone']);
        $this->get(route('agent.register'))
            ->assertSee('id="is_owner_no" type="radio" name="is_owner" value="no" checked', false)
            ->assertSee('Your Information')
            ->assertSee('Owner Information')
            ->assertSee('value="applicant@example.test"', false)
            ->assertSee('value="John Smith"', false);
    }

    public function test_registration_always_shows_applicant_fields_and_hides_owner_fields_by_default(): void
    {
        $this->get(route('agent.register'))
            ->assertOk()
            ->assertSee('<div id="agent-applicant-fields" class="mb-3">', false)
            ->assertSee('name="applicant_email"', false)
            ->assertSee('name="applicant_mobile"', false)
            ->assertSee('name="applicant_full_name"', false)
            ->assertSee('name="applicant_role"', false)
            ->assertSee('id="agent-owner-fields" class="mb-3" hidden', false);
    }

    public function test_non_owner_registration_requires_applicant_identity_and_role(): void
    {
        $country = $this->country();
        $response = $this->from(route('agent.register'))->post(route('agent.register.store'), [
            'agency_name' => 'Island Travel',
            'contact_person' => 'Agency Contact',
            'is_owner' => 'no',
            'email' => 'agency@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'country_id' => $country->id,
            'owner_full_name' => 'Business Owner',
            'owner_email' => 'owner@example.test',
            'owner_phone' => '+23011112222',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors([
            'applicant_email',
            'applicant_mobile',
            'applicant_full_name',
            'applicant_role',
        ]);
        $this->assertDatabaseCount('agent_accounts', 0);
        $this->assertDatabaseCount('controller_verifications', 0);

        $this->post(route('agent.register.store'), [
            'agency_name' => 'Island Travel',
            'contact_person' => 'Agency Contact',
            'is_owner' => 'yes',
            'email' => 'agency@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'country_id' => $country->id,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertSessionHasErrors([
            'applicant_email',
            'applicant_mobile',
            'applicant_full_name',
            'applicant_role',
        ]);

        $this->post(route('agent.register.store'), [
            'agency_name' => 'Island Travel',
            'contact_person' => 'Agency Contact',
            'is_owner' => 'yes',
            'applicant_email' => 'applicant@example.test',
            'applicant_mobile' => '+23057011234',
            'applicant_full_name' => 'Applicant Name',
            'applicant_role' => 'Admin',
            'email' => 'agency@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'country_id' => $country->id,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertSessionHasErrors('applicant_role');
    }

    public function test_non_owner_registration_stays_blocked_until_owner_approval_then_returns_to_agent_setup(): void
    {
        Mail::fake();
        $country = $this->country();
        $this->post(route('agent.register.store'), [
            'agency_name' => 'Island Travel',
            'contact_person' => 'Agency Contact',
            'is_owner' => 'no',
            'applicant_email' => 'john@example.test',
            'applicant_mobile' => '+230 57011234',
            'applicant_full_name' => 'John Smith',
            'applicant_role' => 'Consultant',
            'email' => 'agency@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'country_id' => $country->id,
            'owner_full_name' => 'Business Owner',
            'owner_email' => 'owner@example.test',
            'owner_phone' => '+23011112222',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertRedirect(route('agent.login'));

        $agent = AgentAccount::query()->where('email', 'john@example.test')->firstOrFail();
        $verification = $agent->ownerVerifications()->firstOrFail();
        $this->assertSame('owner_approval_pending', $agent->account_status);
        $this->assertSame('agency@example.test', $agent->agency_email);
        $this->assertSame('Business Owner', $agent->owner_full_name);
        $this->assertSame('owner@example.test', $agent->owner_email);
        $this->assertSame('+23011112222', $agent->owner_phone);
        $applicant = $agent->staffUsers()->firstOrFail();
        $this->assertSame('John Smith', $applicant->full_name);
        $this->assertSame('john@example.test', $applicant->email);
        $this->assertSame('+23057011234', $applicant->mobile);
        $this->assertSame('Consultant', $applicant->role);
        $this->assertTrue(app(\App\Services\AgentPermissionService::class)->roleAllows('Consultant', 'profile', 'read'));
        $this->assertFalse(app(\App\Services\AgentPermissionService::class)->roleAllows('Consultant', 'users', 'create'));
        $this->post(route('agent.login.store'), [
            'email' => 'john@example.test',
            'password' => 'Password123!',
        ])->assertSessionHasErrors('email');
        Mail::assertSent(\App\Mail\ControllerVerificationRequested::class);
        Mail::assertSent(\App\Mail\ControllerVerificationRequested::class, function ($mail): bool {
            return $mail->requester?->full_name === 'John Smith';
        });

        $owner = Operator::query()->create([
            'operator_id' => 'OP-AGENT-OWNER-1',
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'owner@example.test',
            'full_name' => 'Business Owner',
            'password_hash' => Hash::make('Password123!'),
            'account_status' => 'active',
        ]);
        $this->actingAs($owner, 'operator')
            ->post(route('operator.register.controller.verify.accept', $verification->token))
            ->assertRedirect(route('agent.login'));

        $this->assertDatabaseHas('controller_verifications', [
            'id' => $verification->id,
            'status' => 'accepted',
        ]);
        $this->assertDatabaseHas('agent_accounts', [
            'id' => $agent->id,
            'account_status' => 'pending',
        ]);
        $this->post(route('agent.login.store'), [
            'email' => 'john@example.test',
            'password' => 'Password123!',
        ])->assertRedirect(route('agent.dashboard'));
        $this->assertAuthenticatedAs($applicant, 'agent_staff');
    }

    public function test_owner_rejection_blocks_agent_login(): void
    {
        Mail::fake();
        $country = $this->country();
        $this->post(route('agent.register.store'), [
            'agency_name' => 'Island Travel',
            'contact_person' => 'Agency Contact',
            'is_owner' => 'no',
            'applicant_email' => 'john@example.test',
            'applicant_mobile' => '+230 57011234',
            'applicant_full_name' => 'John Smith',
            'applicant_role' => 'Consultant',
            'email' => 'agency@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'country_id' => $country->id,
            'owner_full_name' => 'Business Owner',
            'owner_email' => 'owner@example.test',
            'owner_phone' => '+23011112222',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $agent = AgentAccount::query()->where('email', 'john@example.test')->firstOrFail();
        $verification = $agent->ownerVerifications()->firstOrFail();
        $owner = Operator::query()->create([
            'operator_id' => 'OP-AGENT-OWNER-2',
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'owner@example.test',
            'full_name' => 'Business Owner',
            'password_hash' => Hash::make('Password123!'),
            'account_status' => 'active',
        ]);

        $this->actingAs($owner, 'operator')
            ->post(route('operator.register.controller.verify.reject', $verification->token))
            ->assertRedirect(route('agent.login'));

        $this->assertDatabaseHas('agent_accounts', [
            'id' => $agent->id,
            'account_status' => 'owner_rejected',
        ]);
        $this->assertDatabaseHas('controller_verifications', [
            'id' => $verification->id,
            'status' => 'rejected',
        ]);
        $this->post(route('agent.login.store'), [
            'email' => 'john@example.test',
            'password' => 'Password123!',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_cannot_approve_a_non_owner_agent_before_owner_approval(): void
    {
        $country = $this->country();
        $admin = AdminUser::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('Password123!'),
            'status' => 'active',
            'role' => 'super_admin',
        ]);
        $agent = AgentAccount::query()->create([
            'agent_id' => 'AGT-OWNER-PENDING',
            'country_id' => $country->id,
            'agency_name' => 'Island Travel',
            'contact_person' => 'A. Agent',
            'email' => 'agent@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'password_hash' => Hash::make('Password123!'),
            'account_status' => 'owner_approval_pending',
            'is_owner' => 'no',
            'owner_full_name' => 'Business Owner',
            'owner_email' => 'owner@example.test',
            'owner_phone' => '+23011112222',
        ]);

        $this->withSession(['admin_id' => $admin->id])
            ->post(route('admin.agents.approve', $agent))
            ->assertUnprocessable();

        $this->assertDatabaseHas('agent_accounts', [
            'id' => $agent->id,
            'account_status' => 'owner_approval_pending',
        ]);
    }

    public function test_agent_login_redirects_to_the_first_incomplete_setup_step(): void
    {
        $country = $this->country();
        $agent = AgentAccount::query()->create([
            'agent_id' => 'AGT-TEST-0001',
            'country_id' => $country->id,
            'agency_name' => 'Island Travel',
            'contact_person' => 'A. Agent',
            'email' => 'agent@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'password_hash' => Hash::make('Password123!'),
            'account_status' => 'pending',
        ]);
        $agent->setupProgress()->create();

        $this->post(route('agent.login.store'), [
            'email' => 'agent@example.test',
            'password' => 'Password123!',
        ])->assertRedirect(route('agent.profile'));

        $this->assertAuthenticatedAs($agent, 'agent');
    }

    public function test_registration_applicant_can_reach_setup_steps_despite_role_permissions(): void
    {
        $country = $this->country();
        $agent = AgentAccount::query()->create([
            'agent_id' => 'AGT-SETUP-APPLICANT',
            'country_id' => $country->id,
            'agency_name' => 'Island Travel',
            'contact_person' => 'Agency Contact',
            'email' => 'accountant@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'password_hash' => Hash::make('Password123!'),
            'account_status' => 'pending',
        ]);
        $progress = $agent->setupProgress()->create();
        $applicant = $agent->staffUsers()->create([
            'user_id' => 'AGU-SETUP-APPLICANT',
            'full_name' => 'Agency Accountant',
            'email' => 'accountant@example.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Accountant',
            'status' => 'Active',
        ]);

        $this->actingAs($applicant, 'agent_staff')
            ->get(route('agent.dashboard'))
            ->assertOk()
            ->assertSee('Agency Profile')
            ->assertSee('Compliance')
            ->assertSee('Users')
            ->assertSee('Operations');
        $this->get(route('agent.profile'))->assertOk();
        $this->assertTrue(app(\App\Services\AgentPermissionService::class)->canAccessSetup($applicant, 'users', 'create'));

        $progress->update(['users_complete' => true]);
        $this->assertFalse(app(\App\Services\AgentPermissionService::class)->canAccessSetup($applicant, 'users', 'create'));
        $progress->update([
            'profile_complete' => true,
            'compliance_complete' => true,
            'users_complete' => true,
            'operations_complete' => true,
        ]);
        $this->assertTrue(app(\App\Services\AgentPermissionService::class)->canAccessSetup($applicant, 'profile', 'read'));
        $this->assertTrue(app(\App\Services\AgentPermissionService::class)->canAccessSetup($applicant, 'profile', 'update'));
        $this->assertTrue(app(\App\Services\AgentPermissionService::class)->canAccessSetup($applicant, 'users', 'read'));

        $this->get(route('agent.dashboard'))
            ->assertOk()
            ->assertSee('href="'.route('agent.profile').'"', false)
            ->assertSee('href="'.route('agent.compliance').'"', false)
            ->assertSee('href="'.route('agent.users').'"', false)
            ->assertSee('href="'.route('agent.operations').'"', false);
        $this->get(route('agent.profile'))->assertOk();
        $this->get(route('agent.compliance'))->assertOk();
        $this->get(route('agent.users'))->assertOk();
        $this->get(route('agent.operations'))->assertOk();
    }

    public function test_primary_applicant_can_edit_agent_users_and_save_explicit_access_rights(): void
    {
        $country = $this->country();
        $agent = AgentAccount::query()->create([
            'agent_id' => 'AGT-USER-ADMIN',
            'country_id' => $country->id,
            'agency_name' => 'Island Travel',
            'contact_person' => 'Agency Contact',
            'email' => 'primary@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'password_hash' => Hash::make('Password123!'),
            'account_status' => 'approved',
        ]);
        $agent->setupProgress()->create([
            'profile_complete' => true,
            'compliance_complete' => true,
            'users_complete' => true,
        ]);
        $primary = $agent->staffUsers()->create([
            'user_id' => 'AGU-PRIMARY-ADMIN',
            'full_name' => 'Primary Agent',
            'email' => 'primary@example.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Accountant',
            'status' => 'Active',
        ]);
        $user = $agent->staffUsers()->create([
            'user_id' => 'AGU-EDIT-TARGET',
            'full_name' => 'Agency Accountant',
            'email' => 'accountant@example.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Accountant',
            'status' => 'Active',
        ]);
        $module = \App\Models\Module::query()->create([
            'name' => 'Test Module',
            'slug' => 'test-module',
        ]);

        $this->actingAs($primary, 'agent_staff')
            ->get(route('agent.users'))
            ->assertOk()
            ->assertSee('Edit')
            ->assertSee('Access Rights');
        $this->get(route('agent.users.edit', $user))
            ->assertOk()
            ->assertSee('Edit Agent User')
            ->assertSee('Module Access Rights')
            ->assertSee('Save User and Access Rights');

        $this->put(route('agent.users.update', $user), [
            'full_name' => 'Updated Accountant',
            'email' => 'updated-accountant@example.test',
            'mobile' => '+23057011234',
            'role' => 'Accountant',
            'permission_modules' => \App\Models\Module::query()->pluck('slug')->all(),
            'module_actions' => [$module->slug => ['read', 'update']],
        ])->assertRedirect(route('agent.users'));

        $this->assertDatabaseHas('agent_staff_users', [
            'id' => $user->id,
            'full_name' => 'Updated Accountant',
            'email' => 'updated-accountant@example.test',
            'mobile' => '+23057011234',
            'role' => 'Accountant',
        ]);
        $this->assertDatabaseHas('agent_staff_permissions', [
            'agent_staff_user_id' => $user->id,
            'module_id' => $module->id,
            'can_read' => true,
            'can_update' => true,
            'can_create' => false,
        ]);
        $this->assertTrue(app(\App\Services\AgentPermissionService::class)->can($user, 'test-module', 'update'));
        $this->get(route('agent.users'))->assertSee('Test Module: Read, Update');
        $this->get(route('agent.users.edit', $user))
            ->assertSee('name="module_actions[test-module][]" value="read" checked', false)
            ->assertSee('name="module_actions[test-module][]" value="update" checked', false);

        $this->post(route('agent.users.save'), [
            'full_name' => 'New Accountant',
            'email' => 'new-accountant@example.test',
            'mobile' => '+23057011235',
            'password' => 'Password123!',
            'role' => 'Accountant',
            'permission_modules' => \App\Models\Module::query()->pluck('slug')->all(),
            'module_actions' => [$module->slug => ['read', 'create']],
        ])->assertRedirect(route('agent.operations'));

        $newUser = AgentStaffUser::query()->where('email', 'new-accountant@example.test')->firstOrFail();
        $this->assertTrue(app(\App\Services\AgentPermissionService::class)->can($newUser, 'test-module', 'create'));
        $this->get(route('agent.users'))->assertSee('Test Module: Read, Create');
    }

    public function test_agent_cannot_skip_an_incomplete_previous_setup_step(): void
    {
        $country = $this->country();
        $agent = AgentAccount::query()->create([
            'agent_id' => 'AGT-TEST-0002',
            'country_id' => $country->id,
            'agency_name' => 'Island Travel',
            'contact_person' => 'A. Agent',
            'email' => 'agent@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'password_hash' => Hash::make('Password123!'),
            'account_status' => 'pending',
        ]);
        $agent->setupProgress()->create();
        $this->actingAs($agent, 'agent');

        $this->get(route('agent.compliance'))->assertRedirect(route('agent.profile'));
    }

    public function test_suspended_agent_staff_cannot_login_or_use_an_existing_session(): void
    {
        $country = $this->country();
        $agent = AgentAccount::query()->create([
            'agent_id' => 'AGT-TEST-0003',
            'country_id' => $country->id,
            'agency_name' => 'Island Travel',
            'contact_person' => 'A. Agent',
            'email' => 'agent@example.test',
            'office_phone' => '+23012345678',
            'emergency_mobile' => '+23087654321',
            'password_hash' => Hash::make('Password123!'),
            'account_status' => 'suspended',
        ]);
        $staff = $agent->staffUsers()->create([
            'user_id' => 'AGU-TEST-0003',
            'full_name' => 'Agent Manager',
            'email' => 'manager@example.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Manager',
            'status' => 'Active',
        ]);

        $this->post(route('agent.login.store'), [
            'email' => 'manager@example.test',
            'password' => 'Password123!',
        ])->assertSessionHasErrors('email');

        $this->actingAs($staff, 'agent_staff')
            ->get(route('agent.dashboard'))
            ->assertForbidden();
    }

    private function country(): Country
    {
        $country = Country::query()->firstOrCreate(
            ['name' => 'Mauritius'],
            ['iso_code' => 'MU', 'currency_code' => 'MUR', 'is_active' => true]
        );
        $country->forceFill(['currency_code' => 'MUR', 'is_active' => true])->save();

        return $country;
    }
}
