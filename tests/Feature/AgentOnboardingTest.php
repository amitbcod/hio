<?php

namespace Tests\Feature;

use App\Models\AgentAccount;
use App\Models\AgentStaffUser;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgentOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_registration_uses_a_single_country_and_a_hashed_password(): void
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
            'email' => 'agent@example.test',
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
        $this->assertTrue(Hash::check('Password123!', $agent->password_hash));
        $this->assertDatabaseHas('agent_setup_progress', [
            'agent_account_id' => $agent->id,
            'current_step' => 2,
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
