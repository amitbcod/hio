<?php

namespace Tests\Feature;

use App\Mail\MpoAccountActivation;
use App\Models\AdminUser;
use App\Models\Business;
use App\Models\Country;
use App\Models\MpoAccountingConfiguration;
use App\Models\MpoCollaborationAgreement;
use App\Models\MpoCompliance;
use App\Models\MpoPayoutConfiguration;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MpoOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_admin_creation_preserves_the_existing_form_and_emails_a_secure_setup_link(): void
    {
        Mail::fake();
        $country = $this->country('Mauritius', 'MU');
        $admin = $this->globalAdmin();

        $this->withSession(['admin_id' => $admin->id])
            ->post(route('admin.country-admins.store'), [
                'name' => 'Island MPO',
                'email' => 'mpo@example.test',
                'country_id' => $country->id,
                'status' => 'active',
                'password' => 'InitialPass123!',
                'password_confirmation' => 'InitialPass123!',
            ])
            ->assertRedirect(route('admin.country-admins.index'));

        $mpo = AdminUser::query()->where('email', 'mpo@example.test')->firstOrFail();
        $this->assertSame('mpo', $mpo->admin_type);
        $this->assertSame($country->id, $mpo->country_id);
        $this->assertTrue(Hash::check('InitialPass123!', $mpo->password_hash));
        $this->assertNotNull($mpo->mpo_password_setup_token_hash);

        Mail::assertSent(MpoAccountActivation::class, function (MpoAccountActivation $mail) use ($mpo): bool {
            $content = $mail->render();

            return $mail->hasTo($mpo->email)
                && str_contains($mail->activationUrl, '/mpo/activate/')
                && ! str_contains($content, 'InitialPass123!');
        });

        $this->get(route('mpo.activation.show', [$mpo, 'invalid-token']))->assertNotFound();
    }

    public function test_a_country_already_assigned_to_an_mpo_cannot_be_assigned_again(): void
    {
        Mail::fake();
        $country = $this->country('Mauritius', 'MU');
        $globalAdmin = $this->globalAdmin();

        $this->withSession(['admin_id' => $globalAdmin->id])
            ->post(route('admin.country-admins.store'), [
                'name' => 'First MPO',
                'email' => 'first-mpo@example.test',
                'country_id' => $country->id,
                'status' => 'active',
                'password' => 'InitialPass123!',
                'password_confirmation' => 'InitialPass123!',
            ])
            ->assertRedirect(route('admin.country-admins.index'));

        $this->withSession(['admin_id' => $globalAdmin->id])
            ->post(route('admin.country-admins.store'), [
                'name' => 'Second MPO',
                'email' => 'second-mpo@example.test',
                'country_id' => $country->id,
                'status' => 'active',
                'password' => 'InitialPass123!',
                'password_confirmation' => 'InitialPass123!',
            ])
            ->assertSessionHasErrors('country_id');

        $this->assertDatabaseMissing('admin_users', ['email' => 'second-mpo@example.test']);
    }

    public function test_global_admin_can_open_the_country_mpo_edit_form(): void
    {
        $country = $this->country('Mauritius', 'MU');
        $globalAdmin = $this->globalAdmin();
        $mpo = $this->mpo($country);

        $this->withSession(['admin_id' => $globalAdmin->id])
            ->get(route('admin.country-admins.edit', $mpo))
            ->assertOk()
            ->assertSee('Edit MPO')
            ->assertSee('value="' . $country->id . '" selected', false);
    }

    public function test_admin_created_mpo_can_set_password_sign_in_and_open_own_profile(): void
    {
        Mail::fake();
        $country = $this->country('Mauritius', 'MU');
        $globalAdmin = $this->globalAdmin();

        $this->withSession(['admin_id' => $globalAdmin->id])
            ->post(route('admin.country-admins.store'), [
                'name' => 'Island MPO',
                'email' => 'mpo@example.test',
                'country_id' => $country->id,
                'status' => 'active',
                'password' => 'InitialPass123!',
                'password_confirmation' => 'InitialPass123!',
            ]);

        $mpo = AdminUser::query()->where('email', 'mpo@example.test')->firstOrFail();
        $mail = Mail::sent(MpoAccountActivation::class)->first();
        $token = basename(parse_url($mail->activationUrl, PHP_URL_PATH));

        $this->post(route('mpo.activation.update', [$mpo, $token]), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertRedirect(route('mpo.login'));

        $this->post(route('mpo.login'), [
            'email' => $mpo->email,
            'password' => 'NewPassword123!',
        ])->assertRedirect(route('mpo.profile'));
        $this->assertAuthenticated('mpo');

        $this->get(route('mpo.profile'))
            ->assertOk()
            ->assertSee('MPO Profile')
            ->assertSee('class="admin-mpo-brand" href="'.route('mpo.profile').'"', false)
            ->assertSee('Mauritius')
            ->assertSee('Incomplete')
            ->assertDontSee('MPO ID')
            ->assertDontSee('Legal Entity Name')
            ->assertSee('Registered business name')
            ->assertSee('Compliances')
            ->assertSee('Accounting Payouts');
    }

    public function test_admin_login_routes_mpo_to_onboarding_and_prevents_admin_sidebar_access(): void
    {
        $mpo = $this->mpo($this->country('Mauritius', 'MU'));

        $this->post(route('admin.login'), [
            'email' => $mpo->email,
            'password' => 'InitialPass123!',
        ])->assertRedirect(route('mpo.profile'));
        $this->assertAuthenticated('mpo');

        $this->get(route('mpo.profile'))
            ->assertOk()
            ->assertSee('MPO navigation')
            ->assertSee('Compliances')
            ->assertDontSee('Dashboard');

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('mpo.profile'));
    }

    public function test_completed_mpo_registration_redirects_to_country_dashboard_without_payout_setup(): void
    {
        $country = $this->country('Mauritius', 'MU');
        $otherCountry = $this->country('France', 'FR');
        $country->vat_percentage = '15.0000';
        $country->save();
        $mpo = $this->mpo($country, 'complete-mpo@example.test');
        $ownedBusiness = Business::query()->create([
            'business_id' => 'BUS-MU-DASHBOARD',
            'legal_name' => 'Mauritius Pending Business',
            'country_id' => $country->id,
            'status' => 'pending',
        ]);
        $foreignBusiness = Business::query()->create([
            'business_id' => 'BUS-FR-DASHBOARD',
            'legal_name' => 'France Private Business',
            'country_id' => $otherCountry->id,
            'status' => 'pending',
        ]);

        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.profile.save'), $this->validProfile())
            ->assertRedirect(route('mpo.profile'));

        MpoCollaborationAgreement::query()->create([
            'admin_user_id' => $mpo->id,
            'agreement_id' => 'MPO-AGR-000001',
            'agreement_type' => 'mpo',
            'onboarding_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
            'commission_model' => 'percentage',
            'commission_value' => '10.0000',
            'status' => 'Active',
            'management_contact_name' => 'Manager',
            'management_contact_email' => 'manager@example.test',
            'management_contact_phone' => '+23012345678',
            'management_contact_mobile' => '+23012345678',
            'accounting_contact_name' => 'Accountant',
            'accounting_contact_email' => 'accounting@example.test',
            'accounting_contact_phone' => '+23012345678',
            'accounting_contact_mobile' => '+23012345678',
        ]);
        MpoAccountingConfiguration::query()->create([
            'admin_user_id' => $mpo->id,
            'vat_number' => 'VAT-123',
            'bank_details' => [
                'account_holder_name' => 'MPO Bank Holder',
                'iban' => 'GB82WEST12345698765432',
                'bank_name' => 'Example Bank',
                'swift_bic' => 'DEUTDEFF',
            ],
        ]);
        $mpo->compliance()->create([
            'business_license_number' => 'LIC-123',
            'license_type' => 'tour_operator',
            'license_expiry_date' => now()->addYear()->toDateString(),
            'proof_of_license_path' => 'compliance/license.pdf',
            'insurance_certificate_path' => 'compliance/insurance.pdf',
            'verification_status' => 'verified',
        ]);
        MpoPayoutConfiguration::query()->create([
            'admin_user_id' => $mpo->id,
            'payment_schedule' => null,
            'payout_method' => null,
        ]);

        \Illuminate\Support\Facades\Auth::guard('mpo')->logout();
        $this->post(route('mpo.login'), [
            'email' => $mpo->email,
            'password' => 'InitialPass123!',
        ])->assertRedirect(route('mpo.dashboard'));

        $this->get(route('mpo.dashboard'))
            ->assertOk()
            ->assertSee('<h1 class="h3 mb-4">Dashboard</h1>', false)
            ->assertSee('class="admin-mpo-brand" href="'.route('mpo.dashboard').'"', false)
            ->assertSee('Mauritius Pending Business')
            ->assertDontSee('France Private Business')
            ->assertSee('Pending MPO Accounts')
            ->assertSee('Pending Businesses')
            ->assertSee('Accommodation Approvals')
            ->assertSee('Vehicle Approvals')
            ->assertSee('Activity Approvals')
            ->assertSee('Feedback')
            ->assertSee('Operators')
            ->assertSee('Agents')
            ->assertSee('Travellers')
            ->assertSee('Accommodation Bookings')
            ->assertSee('Transport Bookings')
            ->assertSee('MPO Setup')
            ->assertSee('In Progress')
            ->assertSee('<details class="list-group-item p-0 mt-2">', false)
            ->assertDontSee('<details class="list-group-item p-0 mt-2" open', false);

        $this->post(route('mpo.dashboard.business.approve', $foreignBusiness))
            ->assertForbidden();
        $this->assertDatabaseHas('businesses', ['id' => $foreignBusiness->id, 'status' => 'pending']);

        $this->post(route('mpo.dashboard.business.approve', $ownedBusiness))
            ->assertRedirect(route('mpo.dashboard'));
        $this->assertDatabaseHas('businesses', ['id' => $ownedBusiness->id, 'status' => 'active']);
        $this->get(route('mpo.dashboard'))->assertOk();
        $this->get(route('mpo.accounting-payouts'))->assertOk();
        $this->get(route('admin.trips.index'))->assertOk();

        $this->post(route('mpo.logout'))->assertRedirect(route('mpo.login'));
        $this->post(route('mpo.login'), [
            'email' => $mpo->email,
            'password' => 'InitialPass123!',
        ])->assertRedirect(route('mpo.dashboard'));

        $this->post(route('mpo.logout'))->assertRedirect(route('mpo.login'));
        $this->post(route('admin.login'), [
            'email' => $mpo->email,
            'password' => 'InitialPass123!',
        ])->assertRedirect(route('mpo.dashboard'));
    }

    public function test_completed_payout_configuration_does_not_change_dashboard_authorization(): void
    {
        $country = $this->country('Mauritius', 'MU');
        $country->vat_percentage = '15.0000';
        $country->save();
        $mpo = $this->mpo($country, 'payout-complete-mpo@example.test');
        $this->completeMandatoryRegistration($mpo);
        MpoPayoutConfiguration::query()->create([
            'admin_user_id' => $mpo->id,
            'payout_id' => 'MPO-PAYOUT-000001',
            'payment_schedule' => 'Monthly',
            'payout_method' => 'Bank',
        ]);
        $this->assertSame([
            'complete' => true,
            'profile' => true,
            'compliance' => true,
            'agreement' => true,
            'accounting' => true,
        ], array_intersect_key(
            app(\App\Services\MpoRegistrationCompletionService::class)->state($mpo),
            array_flip(['complete', 'profile', 'compliance', 'agreement', 'accounting']),
        ));

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.dashboard'))
            ->assertOk()
            ->assertSee('<h1 class="h3 mb-4">Dashboard</h1>', false)
            ->assertSee('Operators')
            ->assertSee('MPO Setup')
            ->assertSee('Completed');
    }

    public function test_unverified_compliance_redirects_to_compliance_setup(): void
    {
        $country = $this->country('Mauritius', 'MU');
        $country->vat_percentage = '15.0000';
        $country->save();
        $mpo = $this->mpo($country, 'pending-compliance-mpo@example.test');
        $this->completeMandatoryRegistration($mpo, includeCompliance: false);
        $mpo->compliance()->create([
            'business_license_number' => 'LIC-123',
            'license_type' => 'tour_operator',
            'license_expiry_date' => now()->addYear()->toDateString(),
            'proof_of_license_path' => 'compliance/license.pdf',
            'insurance_certificate_path' => 'compliance/insurance.pdf',
            'verification_status' => 'pending_verification',
        ]);

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.dashboard'))
            ->assertRedirect(route('mpo.compliances'));
    }

    private function completeMandatoryRegistration(AdminUser $mpo, bool $includeCompliance = true): void
    {
        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.profile.save'), $this->validProfile())
            ->assertRedirect(route('mpo.profile'));

        MpoCollaborationAgreement::query()->create([
            'admin_user_id' => $mpo->id,
            'agreement_id' => 'MPO-AGR-'.$mpo->id,
            'agreement_type' => 'mpo',
            'onboarding_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
            'commission_model' => 'percentage',
            'commission_value' => '10.0000',
            'status' => 'Active',
            'management_contact_name' => 'Manager',
            'management_contact_email' => 'manager@example.test',
            'management_contact_phone' => '+23012345678',
            'management_contact_mobile' => '+23012345678',
            'accounting_contact_name' => 'Accountant',
            'accounting_contact_email' => 'accounting@example.test',
            'accounting_contact_phone' => '+23012345678',
            'accounting_contact_mobile' => '+23012345678',
        ]);
        MpoAccountingConfiguration::query()->create([
            'admin_user_id' => $mpo->id,
            'vat_number' => 'VAT-123',
            'bank_details' => [
                'account_holder_name' => 'MPO Bank Holder',
                'iban' => 'GB82WEST12345698765432',
                'bank_name' => 'Example Bank',
                'swift_bic' => 'DEUTDEFF',
            ],
        ]);
        if ($includeCompliance) {
            $mpo->compliance()->create([
                'business_license_number' => 'LIC-123',
                'license_type' => 'tour_operator',
                'license_expiry_date' => now()->addYear()->toDateString(),
                'proof_of_license_path' => 'compliance/license.pdf',
                'insurance_certificate_path' => 'compliance/insurance.pdf',
                'verification_status' => 'verified',
            ]);
        }
    }

    public function test_mpo_dashboard_is_blocked_until_mandatory_registration_steps_are_complete(): void
    {
        $mpo = $this->mpo($this->country('Mauritius', 'MU'));

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.dashboard'))
            ->assertRedirect(route('mpo.profile'))
            ->assertSessionHas('warning');
    }

    public function test_mpo_can_save_complete_profile_without_changing_country_or_another_account(): void
    {
        Storage::fake('public');
        $country = $this->country('Mauritius', 'MU');
        $otherCountry = $this->country('France', 'FR');
        $region = Region::query()->create(['name' => 'North', 'country_id' => $country->id]);
        $otherRegion = Region::query()->create(['name' => 'South France', 'country_id' => $otherCountry->id]);
        $mpo = $this->mpo($country);
        $otherMpo = $this->mpo($otherCountry, 'other-mpo@example.test');
        Storage::disk('public')->put('mpo-logos/old-logo.png', 'old logo');
        $mpo->logo_path = 'mpo-logos/old-logo.png';
        $mpo->save();

        $response = $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.profile.save'), $this->validProfile([
                'country_id' => $otherCountry->id,
                'regions_covered' => [$region->id],
                'mpo_admin_id' => $otherMpo->id,
                'logo' => UploadedFile::fake()->image('brand.png'),
            ]));

        $response->assertRedirect(route('mpo.profile'));
        $saved = $mpo->fresh();
        $this->assertSame($country->id, $saved->country_id);
        $this->assertSame('Island Legal Ltd', $saved->registered_business_name);
        $this->assertNull($saved->business_name);
        $this->assertSame('contact@island.example', $saved->official_business_email);
        $this->assertSame([$region->id], $saved->covered_region_ids);
        $this->assertSame('login-mpo@example.test', $saved->email);
        $this->assertTrue($saved->hasCompleteMpoProfile());
        $this->assertNotNull($saved->logo_path);
        Storage::disk('public')->assertExists($saved->logo_path);
        Storage::disk('public')->assertMissing('mpo-logos/old-logo.png');
        $this->assertNull($otherMpo->fresh()->business_name);
    }

    public function test_marking_profile_complete_requires_valid_fields_and_country_regions(): void
    {
        $country = $this->country('Mauritius', 'MU');
        $otherCountry = $this->country('France', 'FR');
        $otherRegion = Region::query()->create(['name' => 'Île-de-France', 'country_id' => $otherCountry->id]);
        $mpo = $this->mpo($country);

        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.profile.save'), [
                'mark_complete' => '1',
                'registered_business_name' => '  Example MPO  ',
                'registration_number' => '12345',
                'address_line1' => '1 Main Street',
                'official_email' => 'bad-email',
                'office_phone' => '12345',
                'emergency_mobile' => '+23012345678',
                'operating_timezone' => 'Not/A_Timezone',
                'supported_languages' => ['not-a-language'],
                'brand_name' => 'Example',
                'website' => 'javascript:alert(1)',
                'social_links' => ['facebook' => 'javascript:alert(1)'],
                'regions_covered' => [$otherRegion->id],
                'logo' => UploadedFile::fake()->create('logo.svg', 20, 'image/svg+xml'),
            ])
            ->assertSessionHasErrors([
                'official_email',
                'office_phone',
                'operating_timezone',
                'supported_languages.0',
                'website',
                'social_links.facebook',
                'regions_covered.0',
                'logo',
            ]);

        $this->post(route('mpo.profile.save'), ['mark_complete' => '1'])
            ->assertSessionHasErrors([
                'registered_business_name',
                'registration_number',
                'address_line1',
                'official_email',
                'office_phone',
                'emergency_mobile',
                'operating_timezone',
                'supported_languages',
                'brand_name',
            ]);
    }

    public function test_global_admin_can_edit_mpo_profile_and_mpo_cannot_access_admin_profile_route(): void
    {
        $country = $this->country('Mauritius', 'MU');
        $globalAdmin = $this->globalAdmin();
        $mpo = $this->mpo($country);

        $this->withSession(['admin_id' => $globalAdmin->id])
            ->get(route('admin.country-admins.profile.edit', $mpo))
            ->assertOk()
            ->assertSee('MPO ID')
            ->assertSee('Registered business name')
            ->assertSee('Editing the profile for Island MPO');

        $this->withSession(['admin_id' => $globalAdmin->id])
            ->put(route('admin.country-admins.profile.update', $mpo), $this->validProfile())
            ->assertRedirect(route('admin.country-admins.profile.edit', $mpo));
        $this->assertSame('Island Legal Ltd', $mpo->fresh()->registered_business_name);

        $this->withSession(['admin_id' => null])->actingAs($mpo, 'mpo')
            ->get(route('admin.country-admins.profile.edit', $mpo))
            ->assertRedirect(route('admin.login'));
    }

    public function test_compliance_section_is_available_before_profile_completion(): void
    {
        $mpo = $this->mpo($this->country('Mauritius', 'MU'));

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.compliances'))
            ->assertOk()
            ->assertSee('Complete your Profile first')
            ->assertSee('Submit Compliance');
    }

    private function globalAdmin(): AdminUser
    {
        return AdminUser::query()->create([
            'name' => 'Global Admin',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('AdminPass123!'),
            'role' => 'super_admin',
            'admin_type' => 'global',
            'status' => 'active',
        ]);
    }

    private function mpo(Country $country, string $email = 'login-mpo@example.test'): AdminUser
    {
        return AdminUser::query()->create([
            'name' => 'Island MPO',
            'email' => $email,
            'password_hash' => Hash::make('InitialPass123!'),
            'role' => 'admin',
            'admin_type' => 'mpo',
            'country_id' => $country->id,
            'status' => 'active',
        ]);
    }

    private function country(string $name, string $isoCode): Country
    {
        return Country::query()->firstOrCreate(
            ['name' => $name],
            [
                'iso_code' => $isoCode,
                'currency_code' => $isoCode === 'MU' ? 'MUR' : 'EUR',
                'time_zone' => $isoCode === 'MU' ? 'Indian/Mauritius' : 'Europe/Paris',
                'is_active' => true,
            ],
        );
    }

    private function validProfile(array $overrides = []): array
    {
        return array_merge([
            'registered_business_name' => 'Island Legal Ltd',
            'registration_number' => 'BRN123456',
            'address_line1' => '1 Main Street',
            'address_line2' => 'Port Louis',
            'official_email' => 'contact@island.example',
            'office_phone' => '+23057011234',
            'emergency_mobile' => '+23057011235',
            'operating_timezone' => 'Indian/Mauritius',
            'supported_languages' => ['en', 'fr'],
            'brand_name' => 'Island Holidays',
            'description' => 'A local destination management company.',
            'public_contact_email' => 'hello@island.example',
            'public_contact_phone' => '+23057011236',
            'social_links' => ['facebook' => 'https://facebook.com/island'],
            'office_hours' => 'Mon-Fri 09:00-17:00',
            'website' => 'https://island.example',
            'mark_complete' => '1',
        ], $overrides);
    }
}
