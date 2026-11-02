<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Country;
use App\Models\MpoCompliance;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MpoComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_mpo_compliance_page_reads_profile_values_and_shows_profile_dependency(): void
    {
        $mpo = $this->mpo();

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.compliances'))
            ->assertOk()
            ->assertSee('Business Legal Name')
            ->assertSee('Registered Legal Entity Ltd')
            ->assertSee('Registration 001')
            ->assertSee('12 Main Street')
            ->assertDontSee('Complete your Profile first')
            ->assertSee('Compliances');

        $incomplete = $this->mpo('incomplete@example.test', false);
        $this->actingAs($incomplete, 'mpo')
            ->get(route('mpo.compliances'))
            ->assertOk()
            ->assertSee('Complete Profile')
            ->assertSee('Registration Number');
    }

    public function test_mpo_submission_requires_valid_values_and_both_documents(): void
    {
        $mpo = $this->mpo();

        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.compliances.save'), [])
            ->assertSessionHasErrors([
                'business_license_number',
                'license_type',
                'license_expiry_date',
                'proof_of_license',
                'insurance_certificate',
            ]);

        $this->post(route('mpo.compliances.save'), $this->validSubmission([
            'license_type' => 'fake-type',
        ]))->assertSessionHasErrors('license_type');

        $this->post(route('mpo.compliances.save'), $this->validSubmission([
            'license_type' => 'other',
            'other_license_type_description' => '',
        ]))->assertSessionHasErrors('other_license_type_description');
    }

    public function test_valid_submission_uses_authenticated_profile_and_stores_private_documents_pending_review(): void
    {
        Storage::fake('local');
        $mpo = $this->mpo();
        $otherMpo = $this->mpo('other@example.test');

        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.compliances.save'), $this->validSubmission([
                'registered_business_name' => 'Forged Legal Name',
                'brn_number' => 'Forged Number',
                'business_address' => 'Forged Address',
                'admin_user_id' => $otherMpo->id,
                'verification_status' => 'verified',
                'reviewed_by_admin_id' => $otherMpo->id,
                'proof_of_license_path' => 'forged/path.pdf',
            ]))
            ->assertRedirect(route('mpo.compliances'))
            ->assertSessionHas('success');

        $compliance = MpoCompliance::query()->where('admin_user_id', $mpo->id)->firstOrFail();
        $this->assertSame('pending_verification', $compliance->verification_status);
        $this->assertSame('Tour Operator License / A-001', $compliance->business_license_number);
        $this->assertNull($compliance->reviewed_by_admin_id);
        $this->assertNotSame('forged/path.pdf', $compliance->proof_of_license_path);
        $this->assertStringStartsWith('docs/legal/', $compliance->proof_of_license_path);
        $this->assertStringStartsWith('docs/legal/', $compliance->insurance_certificate_path);
        Storage::disk('local')->assertExists($compliance->proof_of_license_path);
        Storage::disk('local')->assertExists($compliance->insurance_certificate_path);

        $this->actingAs($otherMpo, 'mpo')
            ->get(route('mpo.compliances.download', [$compliance, 'proof-of-license']))
            ->assertNotFound();
    }

    public function test_missing_profile_data_blocks_submission_without_accepting_compliance_overrides(): void
    {
        $mpo = $this->mpo('incomplete@example.test', false);

        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.compliances.save'), $this->validSubmission([
                'registered_business_name' => 'Request Override',
                'brn_number' => 'Request Override',
                'business_address' => 'Request Override',
            ]))
            ->assertSessionHasErrors('profile');

        $this->assertDatabaseMissing('mpo_compliances', ['admin_user_id' => $mpo->id]);
    }

    public function test_global_admin_can_edit_review_and_download_only_server_resolved_documents(): void
    {
        Storage::fake('local');
        $mpo = $this->mpo();
        $compliance = $this->submitAsMpo($mpo);
        $admin = $this->globalAdmin();

        $this->withSession(['admin_id' => $admin->id])
            ->get(route('admin.country-admins.compliance.show', $mpo))
            ->assertOk()
            ->assertSee($compliance->business_license_number)
            ->assertSee('Proof of License')
            ->assertSee('Insurance Certificate');

        $this->withSession(['admin_id' => $admin->id])
            ->get(route('admin.mpo-compliance.download', [$compliance, 'proof-of-license']))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Type', 'application/octet-stream');

        $this->withSession(['admin_id' => $admin->id])
            ->patch(route('admin.country-admins.compliance.review', $mpo), [
                'verification_status' => 'verified',
            ])
            ->assertRedirect(route('admin.country-admins.compliance.show', $mpo));

        $this->assertSame('verified', $compliance->fresh()->verification_status);
        $this->assertSame($admin->id, $compliance->fresh()->reviewed_by_admin_id);
        $this->assertNotNull($compliance->fresh()->reviewed_at);
    }

    public function test_admin_rejection_requires_reason_and_corrected_submission_returns_to_pending(): void
    {
        Storage::fake('local');
        $mpo = $this->mpo();
        $admin = $this->globalAdmin();
        $compliance = $this->submitAsMpo($mpo);

        $this->withSession(['admin_id' => $admin->id])
            ->patch(route('admin.country-admins.compliance.review', $mpo), [
                'verification_status' => 'rejected',
            ])
            ->assertSessionHasErrors('rejection_reason');

        $this->withSession(['admin_id' => $admin->id])
            ->patch(route('admin.country-admins.compliance.review', $mpo), [
                'verification_status' => 'rejected',
                'rejection_reason' => 'Upload a clear copy.',
            ])
            ->assertRedirect(route('admin.country-admins.compliance.show', $mpo));

        $this->assertSame('rejected', $compliance->fresh()->verification_status);
        $this->assertSame('Upload a clear copy.', $compliance->fresh()->rejection_reason);

        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.compliances.save'), $this->validSubmission([
                'business_license_number' => 'Updated-License-02',
                'proof_of_license' => null,
                'insurance_certificate' => null,
            ]))
            ->assertRedirect(route('mpo.compliances'));

        $this->assertSame('pending_verification', $compliance->fresh()->verification_status);
        $this->assertNull($compliance->fresh()->rejection_reason);
        Storage::disk('local')->assertExists($compliance->fresh()->proof_of_license_path);
    }

    public function test_changes_to_verified_license_require_reverification_and_failed_replacement_preserves_files(): void
    {
        Storage::fake('local');
        $mpo = $this->mpo();
        $admin = $this->globalAdmin();
        $compliance = $this->submitAsMpo($mpo);
        $compliance->update(['verification_status' => 'verified']);
        $oldProof = $compliance->proof_of_license_path;
        $oldInsurance = $compliance->insurance_certificate_path;

        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.compliances.save'), $this->validSubmission([
                'proof_of_license' => UploadedFile::fake()->create('script.svg', 10, 'image/svg+xml'),
                'insurance_certificate' => null,
            ]))
            ->assertSessionHasErrors('proof_of_license');

        Storage::disk('local')->assertExists($oldProof);
        Storage::disk('local')->assertExists($oldInsurance);

        $this->withSession(['admin_id' => $admin->id])
            ->patch(route('admin.country-admins.compliance.save', $mpo), $this->validSubmission([
                'business_license_number' => 'Updated License 2',
                'proof_of_license' => null,
                'insurance_certificate' => null,
            ]))
            ->assertRedirect(route('admin.country-admins.compliance.show', $mpo));

        $this->assertSame('pending_verification', $compliance->fresh()->verification_status);
        Storage::disk('local')->assertExists($compliance->fresh()->proof_of_license_path);
        Storage::disk('local')->assertMissing($oldProof);
    }

    public function test_changes_to_verified_profile_legal_information_require_reverification(): void
    {
        Storage::fake('local');
        $mpo = $this->mpo();
        $admin = $this->globalAdmin();
        $compliance = $this->submitAsMpo($mpo);
        $compliance->update([
            'verification_status' => 'verified',
            'reviewed_by_admin_id' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.profile.save'), [
                'registered_business_name' => 'Updated Legal Entity Ltd',
                'registration_number' => 'Registration 001',
                'address_line1' => '12 Main Street',
                'address_line2' => 'Port Louis',
            ])
            ->assertRedirect(route('mpo.profile'));

        $this->assertSame('Updated Legal Entity Ltd', $mpo->fresh()->registered_business_name);
        $this->assertSame('pending_verification', $compliance->fresh()->verification_status);
        $this->assertNull($compliance->fresh()->reviewed_by_admin_id);
        $this->assertNull($compliance->fresh()->reviewed_at);
    }

    public function test_expiry_status_uses_current_date_and_is_independent_of_verification(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-28 12:00:00', 'Indian/Mauritius'));
        $compliance = new MpoCompliance(['verification_status' => 'verified']);

        foreach ([
            ['2026-10-27', 'expired'],
            ['2026-10-28', 'expiring_soon'],
            ['2026-11-27', 'expiring_soon'],
            ['2026-11-28', 'valid'],
        ] as [$expiry, $status]) {
            $compliance->license_expiry_date = $expiry;
            $this->assertSame($status, $compliance->expiryStatus('Indian/Mauritius'));
        }

        $compliance->license_expiry_date = '2026-10-27';
        $this->assertSame('verified', $compliance->verification_status);
        $this->assertSame('Verified', $compliance->verificationStatusLabel());
        CarbonImmutable::setTestNow();
    }

    public function test_country_scoped_mpo_admin_cannot_review_compliance_as_global_admin(): void
    {
        $mpo = $this->mpo();
        $countryAdmin = $this->globalAdmin();
        $countryAdmin->admin_type = 'mpo';
        $countryAdmin->country_id = $mpo->country_id;
        $countryAdmin->save();
        $compliance = $this->submitAsMpo($mpo);

        $this->withSession(['admin_id' => $countryAdmin->id])
            ->get(route('admin.country-admins.compliance.show', $mpo))
            ->assertRedirect(route('mpo.profile'));

        $this->assertSame('pending_verification', $compliance->fresh()->verification_status);
    }

    private function submitAsMpo(AdminUser $mpo): MpoCompliance
    {
        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.compliances.save'), $this->validSubmission())
            ->assertRedirect(route('mpo.compliances'));

        return MpoCompliance::query()->where('admin_user_id', $mpo->id)->firstOrFail();
    }

    private function validSubmission(array $overrides = []): array
    {
        return array_merge([
            'business_license_number' => 'Tour Operator License / A-001',
            'license_type' => 'tour_operator',
            'license_expiry_date' => now()->addYear()->toDateString(),
            'proof_of_license' => UploadedFile::fake()->createWithContent('license.pdf', "%PDF-1.4\nLicense document\n"),
            'insurance_certificate' => UploadedFile::fake()->createWithContent('insurance.pdf', "%PDF-1.4\nInsurance certificate\n"),
        ], $overrides);
    }

    private function mpo(string $email = 'mpo@example.test', bool $withProfile = true): AdminUser
    {
        $country = Country::query()->firstOrCreate(
            ['name' => 'Mauritius'],
            ['iso_code' => 'MU', 'currency_code' => 'MUR', 'time_zone' => 'Indian/Mauritius', 'is_active' => true],
        );

        return AdminUser::query()->create(array_merge([
            'name' => 'MPO Contact',
            'email' => $email,
            'password_hash' => Hash::make('Password123!'),
            'role' => 'admin',
            'admin_type' => 'mpo',
            'country_id' => $country->id,
            'status' => 'active',
        ], $withProfile ? [
            'registered_business_name' => 'Registered Legal Entity Ltd',
            'brn_number' => 'Registration 001',
            'business_address' => '12 Main Street',
            'business_address_line2' => 'Port Louis',
        ] : []));
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
}
