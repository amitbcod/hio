<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Country;
use App\Models\MpoCollaborationAgreement;
use App\Models\MpoStaffUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MpoCollaborationAgreementTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_admin_creates_unique_draft_agreement_and_private_document(): void
    {
        Storage::fake('local');
        $mpo = $this->mpo();
        $admin = $this->globalAdmin();

        $this->withSession(['admin_id' => $admin->id])
            ->post(route('admin.country-admins.collaboration-agreements.store', $mpo), $this->terms([
                'responsibilities_document' => UploadedFile::fake()->create('responsibilities.pdf', 20, 'application/pdf'),
            ]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $agreement = MpoCollaborationAgreement::query()->where('admin_user_id', $mpo->id)->firstOrFail();
        $this->assertSame('MPO-AGR-'.str_pad((string) $agreement->id, 6, '0', STR_PAD_LEFT), $agreement->agreement_id);
        $this->assertSame('Draft', $agreement->status);
        $this->assertSame($mpo->mpo_onboarded_at->toDateString(), $agreement->onboarding_date->toDateString());
        $this->assertDatabaseHas('mpo_collaboration_agreement_events', [
            'agreement_id' => $agreement->id,
            'event_type' => 'created',
            'actor_type' => 'global',
        ]);
        $document = $agreement->currentDocument;
        $this->assertNotNull($document);
        $this->assertStringStartsWith('mpo-agreements/', $document->storage_path);
        Storage::disk('local')->assertExists($document->storage_path);

        $this->withSession(['admin_id' => $admin->id])
            ->put(route('admin.country-admins.collaboration-agreements.update', [$mpo, $agreement]), $this->terms([
                'responsibilities_document' => UploadedFile::fake()->create('revised.pdf', 24, 'application/pdf'),
            ]))->assertSessionHasNoErrors()
            ->assertSessionHas('success');
        $this->assertCount(2, $agreement->fresh()->documents);
        $this->assertFalse($document->fresh()->is_current);
        $this->assertTrue($agreement->fresh()->currentDocument->is_current);
        $this->assertDatabaseHas('mpo_collaboration_agreement_events', [
            'agreement_id' => $agreement->id,
            'event_type' => 'document_uploaded',
        ]);
    }

    public function test_mpo_sees_only_its_agreement_and_cannot_change_terms_or_download_another_document(): void
    {
        Storage::fake('local');
        $mpo = $this->mpo();
        $other = $this->mpo('other@example.test');
        $agreement = $this->agreement($mpo);
        $otherAgreement = $this->agreement($other);
        $otherDocument = $otherAgreement->documents()->create([
            'storage_path' => 'mpo-agreements/other.pdf',
            'original_name' => 'other.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 4,
            'is_current' => true,
        ]);
        $ownDocument = $agreement->documents()->create([
            'storage_path' => 'mpo-agreements/own.pdf',
            'original_name' => 'own.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 4,
            'is_current' => true,
        ]);
        Storage::disk('local')->put('mpo-agreements/own.pdf', 'own!');
        Storage::disk('local')->put('mpo-agreements/other.pdf', 'test');

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.collaboration-agreements'))
            ->assertOk()
            ->assertSee($agreement->agreement_id)
            ->assertDontSee($otherAgreement->agreement_id);

        $this->get(route('mpo.collaboration-agreements.documents.download', $ownDocument))
            ->assertOk()
            ->assertHeader('content-disposition');
        $this->get(route('mpo.collaboration-agreements.documents.download', $otherDocument))
            ->assertNotFound();

        $this->put(route('mpo.collaboration-agreements.contacts.update', 'management'), [
            'name' => 'Updated',
            'email' => 'valid@example.test',
            'phone' => '+23051234567',
            'mobile' => '+23051234568',
            'agreement_id' => 'FORGED',
            'admin_user_id' => $other->id,
            'commission_value' => 99,
            'status' => 'Active',
        ])->assertSessionHasErrors(['agreement_id', 'admin_user_id', 'commission_value', 'status']);

        $this->assertSame('MPO-AGR-'.str_pad((string) $agreement->id, 6, '0', STR_PAD_LEFT), $agreement->fresh()->agreement_id);
        $this->assertSame('10.0000', $agreement->fresh()->commission_value);
        $this->assertSame('Draft', $agreement->fresh()->status);
        $this->assertNull($agreement->fresh()->management_contact_name);
        $this->assertNull($agreement->fresh()->accounting_contact_name);
    }

    public function test_management_and_accounting_contacts_are_independent_and_validate_international_phones(): void
    {
        $mpo = $this->mpo();
        $agreement = $this->agreement($mpo);

        $this->actingAs($mpo, 'mpo')->put(route('mpo.collaboration-agreements.contacts.update', 'management'), [
            'name' => 'Manager',
            'email' => 'manager@example.test',
            'phone' => '+14155552671',
            'mobile' => '+442071838750',
        ])->assertRedirect();

        $this->put(route('mpo.collaboration-agreements.contacts.update', 'accounting'), [
            'name' => 'Accountant',
            'email' => 'accounts@example.test',
            'phone' => '+61412345678',
            'mobile' => '+33123456789',
        ])->assertRedirect();

        $saved = $agreement->fresh();
        $this->assertSame('Manager', $saved->management_contact_name);
        $this->assertSame('manager@example.test', $saved->management_contact_email);
        $this->assertSame('Accountant', $saved->accounting_contact_name);
        $this->assertSame('accounts@example.test', $saved->accounting_contact_email);

        $this->put(route('mpo.collaboration-agreements.contacts.update', 'accounting'), [
            'name' => 'Bad contact',
            'email' => 'not-an-email',
            'phone' => '12345',
            'mobile' => '+23051234567',
        ])->assertSessionHasErrors(['email', 'phone']);
    }

    public function test_onboarding_completion_is_independent_of_agreement_activation(): void
    {
        $mpo = $this->mpo();
        $agreement = $this->agreement($mpo);
        $agreement->forceFill([
            'management_contact_name' => 'Manager',
            'management_contact_email' => 'manager@example.test',
            'management_contact_phone' => '+14155552671',
            'management_contact_mobile' => '+442071838750',
            'accounting_contact_name' => 'Accountant',
            'accounting_contact_email' => 'accounts@example.test',
            'accounting_contact_phone' => '+61412345678',
            'accounting_contact_mobile' => '+33123456789',
        ])->save();

        $this->assertSame('Draft', $agreement->fresh()->status);
        $this->assertTrue($agreement->fresh()->isOnboardingComplete());
    }

    public function test_staff_contacts_require_update_permission_and_only_global_admin_can_manage_agreement_lifecycle(): void
    {
        $mpo = $this->mpo();
        $agreement = $this->agreement($mpo);
        $staff = MpoStaffUser::query()->create([
            'admin_user_id' => $mpo->id,
            'full_name' => 'Reader',
            'email' => 'reader@example.test',
            'password_hash' => Hash::make('StaffPassword1!'),
            'role' => 'Support',
            'status' => 'Active',
        ]);

        $this->actingAs($staff, 'mpo_staff')
            ->put(route('mpo.collaboration-agreements.contacts.update', 'management'), [
                'name' => 'No access',
                'email' => 'no@example.test',
                'phone' => '+23051234567',
                'mobile' => '+23051234568',
            ])->assertForbidden();

        $this->actingAs($mpo, 'mpo')
            ->post(route('admin.country-admins.collaboration-agreements.status', [$mpo, $agreement]), [
                'status' => 'Active',
            ])->assertRedirect(route('mpo.profile'));
    }

    public function test_admin_validates_terms_enforces_transitions_and_keeps_history(): void
    {
        $mpo = $this->mpo();
        $admin = $this->globalAdmin();
        $agreement = $this->agreement($mpo);

        $this->withSession(['admin_id' => $admin->id])
            ->post(route('admin.country-admins.collaboration-agreements.store', $mpo), $this->terms([
                'start_date' => $mpo->mpo_onboarded_at->subDay()->toDateString(),
                'marketing_contribution_percent' => '101',
                'marketing_contribution_payer' => 'invalid',
                'responsibilities_document' => UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload'),
            ]))->assertSessionHasErrors([
                'start_date',
                'marketing_contribution_percent',
                'marketing_contribution_payer',
                'responsibilities_document',
            ]);

        $this->withSession(['admin_id' => $admin->id])
            ->put(route('admin.country-admins.collaboration-agreements.update', [$mpo, $agreement]), $this->terms([
                'commission_value' => '100.0001',
            ]))->assertSessionHasErrors('commission_value');

        $this->withSession(['admin_id' => $admin->id])
            ->post(route('admin.country-admins.collaboration-agreements.status', [$mpo, $agreement]), [
                'status' => 'Terminated',
                'reason' => 'Premature',
            ])->assertStatus(422);

        $this->withSession(['admin_id' => $admin->id])
            ->post(route('admin.country-admins.collaboration-agreements.status', [$mpo, $agreement]), [
                'status' => 'Active',
            ])->assertRedirect();
        $this->put(route('admin.country-admins.collaboration-agreements.update', [$mpo, $agreement]), $this->terms([
            'commission_value' => '11.0000',
        ]))->assertStatus(422);
        $this->assertDatabaseHas('mpo_collaboration_agreement_events', [
            'agreement_id' => $agreement->id,
            'event_type' => 'status_changed',
            'from_status' => 'Draft',
            'to_status' => 'Active',
        ]);

        $this->post(route('admin.country-admins.collaboration-agreements.status', [$mpo, $agreement]), [
            'status' => 'Suspended',
        ])->assertSessionHasErrors('reason');
        $this->post(route('admin.country-admins.collaboration-agreements.status', [$mpo, $agreement]), [
            'status' => 'Suspended',
            'reason' => 'Temporary review',
        ])->assertRedirect();
        $this->assertSame('Active', $agreement->fresh()->events()->where('event_type', 'status_changed')->first()->from_status);
        $this->assertSame('Temporary review', $agreement->fresh()->events()->where('event_type', 'status_changed')->first()->reason);

        $this->post(route('admin.country-admins.collaboration-agreements.status', [$mpo, $agreement]), [
            'status' => 'Active',
        ])->assertRedirect();
        $this->post(route('admin.country-admins.collaboration-agreements.status', [$mpo, $agreement]), [
            'status' => 'Terminated',
            'reason' => 'Agreement ended by administration.',
        ])->assertRedirect();
        $this->assertSame('Terminated', $agreement->fresh()->status);
        $this->post(route('admin.country-admins.collaboration-agreements.status', [$mpo, $agreement]), [
            'status' => 'Active',
        ])->assertStatus(422);
    }

    private function terms(array $overrides = []): array
    {
        return array_merge([
            'agreement_type' => 'mpo',
            'start_date' => now()->toDateString(),
            'commission_model' => 'percentage',
            'commission_value' => '10.0000',
            'marketing_contribution_percent' => null,
            'marketing_contribution_payer' => null,
        ], $overrides);
    }

    private function mpo(string $email = 'owner@example.test'): AdminUser
    {
        $country = Country::query()->create([
            'name' => 'Country '.str_replace(['@', '.'], '-', $email),
            'iso_code' => null,
            'currency_code' => 'MUR',
            'time_zone' => 'Indian/Mauritius',
            'is_active' => true,
        ]);

        return AdminUser::query()->create([
            'name' => 'MPO Owner',
            'email' => $email,
            'password_hash' => Hash::make('OwnerPassword1!'),
            'role' => 'admin',
            'admin_type' => 'mpo',
            'country_id' => $country->id,
            'status' => 'active',
            'mpo_onboarded_at' => now()->subDay(),
        ]);
    }

    private function globalAdmin(): AdminUser
    {
        return AdminUser::query()->create([
            'name' => 'Global Admin',
            'email' => 'global-admin@example.test',
            'password_hash' => Hash::make('GlobalPassword1!'),
            'role' => 'super_admin',
            'admin_type' => 'global',
            'status' => 'active',
        ]);
    }

    private function agreement(AdminUser $mpo): MpoCollaborationAgreement
    {
        $agreement = MpoCollaborationAgreement::query()->create([
            'admin_user_id' => $mpo->id,
            'agreement_type' => 'mpo',
            'onboarding_date' => $mpo->mpo_onboarded_at->toDateString(),
            'start_date' => now()->toDateString(),
            'commission_model' => 'percentage',
            'commission_value' => '10.0000',
            'status' => 'Draft',
        ]);
        $agreement->agreement_id = 'MPO-AGR-'.str_pad((string) $agreement->id, 6, '0', STR_PAD_LEFT);
        $agreement->save();

        return $agreement;
    }
}
