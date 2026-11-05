<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Country;
use App\Models\Module;
use App\Models\MpoAccountingConfiguration;
use App\Models\MpoCollaborationAgreement;
use App\Models\MpoRoleModulePermission;
use App\Models\MpoStaffUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MpoAccountingCommissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_mpo_view_resolves_its_country_vat_rate_and_canonical_agreement_terms(): void
    {
        $mpo = $this->mpo('owner@example.test', '19.5000');
        $agreement = $this->agreement($mpo);
        $other = $this->mpo('other@example.test', '7.0000');
        $this->agreement($other, '99.0000');

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.accounting-commission'))
            ->assertOk()
            ->assertSee('19.5000%')
            ->assertSee('10.0000%')
            ->assertSee($agreement->agreement_id)
            ->assertDontSee('99.0000%')
            ->assertSee('Country VAT % (read-only)');
    }

    public function test_vat_number_is_required_unless_exempt_and_country_rate_cannot_be_submitted(): void
    {
        $mpo = $this->mpo();

        $this->actingAs($mpo, 'mpo')
            ->put(route('mpo.accounting-commission.update'), $this->validPayload([
                'vat_number' => '',
            ]))->assertSessionHasErrors('vat_number');

        $this->put(route('mpo.accounting-commission.update'), $this->validPayload([
            'vat_exempted' => '1',
            'vat_number' => '',
        ]))->assertRedirect(route('mpo.accounting-commission'));

        $this->assertDatabaseHas('mpo_accounting_configurations', [
            'admin_user_id' => $mpo->id,
            'vat_exempted' => 1,
        ]);
        $this->assertDatabaseMissing('mpo_accounting_configurations', [
            'admin_user_id' => $mpo->id,
            'vat_number' => 'VAT-123',
        ]);

        $this->put(route('mpo.accounting-commission.update'), $this->validPayload([
            'vat_percentage' => '99.0000',
        ]))->assertSessionHasErrors('vat_percentage');

        $this->put(route('mpo.accounting-commission.update'), $this->validPayload([
            'same_as_operator' => '1',
        ]))->assertSessionHasErrors('same_as_operator');
    }

    public function test_bank_details_are_encrypted_and_audits_never_include_full_iban(): void
    {
        $mpo = $this->mpo();

        $this->actingAs($mpo, 'mpo')
            ->put(route('mpo.accounting-commission.update'), $this->validPayload())
            ->assertRedirect(route('mpo.accounting-commission'))
            ->assertSessionHas('success');

        $configuration = MpoAccountingConfiguration::query()->where('admin_user_id', $mpo->id)->firstOrFail();
        $rawCiphertext = \Illuminate\Support\Facades\DB::table('mpo_accounting_configurations')
            ->where('id', $configuration->id)
            ->value('bank_details');
        $this->assertNotSame('GB82WEST12345698765432', $rawCiphertext);
        $this->assertSame('GB82WEST12345698765432', $configuration->bank_details['iban']);
        $this->assertSame('DEUTDEFF', $configuration->bank_details['swift_bic']);
        $this->assertTrue(Crypt::decryptString($rawCiphertext) !== 'GB82WEST12345698765432');
        $this->assertDatabaseHas('mpo_accounting_configuration_events', [
            'admin_user_id' => $mpo->id,
            'event_type' => 'configuration_updated',
        ]);
        $event = $configuration->mpo->accountingConfiguration
            ->mpo
            ->getConnection()
            ->table('mpo_accounting_configuration_events')
            ->where('admin_user_id', $mpo->id)
            ->first();
        $this->assertStringNotContainsString('GB82WEST12345698765432', $event->changes);
        $this->assertStringContainsString('5432', $event->changes);

        $this->put(route('mpo.accounting-commission.update'), $this->validPayload([
            'vat_exempted' => '1',
            'vat_number' => null,
        ]))->assertRedirect();
        $this->assertSame('VAT-123', $configuration->fresh()->vat_number);
    }

    public function test_invalid_iban_swift_vat_and_accountant_values_are_rejected(): void
    {
        $mpo = $this->mpo();

        $this->actingAs($mpo, 'mpo')
            ->put(route('mpo.accounting-commission.update'), $this->validPayload([
                'bank' => [
                    'account_holder_name' => 'MPO Ltd',
                    'iban' => 'GB00WEST12345698765432',
                    'bank_name' => 'Bank',
                    'swift_bic' => 'BAD',
                ],
                'vat_number' => 'INVALID VAT!',
                'responsible_accountant_user_id' => 99999,
            ]))
            ->assertSessionHasErrors(['bank.iban', 'bank.swift_bic', 'vat_number', 'responsible_accountant_user_id']);
    }

    public function test_staff_cannot_access_or_change_mpo_accounting_setup_even_with_accounting_permissions(): void
    {
        $mpo = $this->mpo();
        $other = $this->mpo('other@example.test');
        $staff = $this->staff($mpo, 'Accountant', 'accountant@example.test');
        $otherStaff = $this->staff($other, 'Other Accountant', 'other-accountant@example.test');
        $accounting = Module::query()->create(['slug' => 'accounting', 'name' => 'Accounting']);
        MpoRoleModulePermission::query()->create([
            'admin_user_id' => $mpo->id,
            'role' => 'Finance',
            'module_id' => $accounting->id,
            'action' => 'read',
            'is_allowed' => true,
            'can_delegate' => false,
        ]);
        MpoRoleModulePermission::query()->create([
            'admin_user_id' => $mpo->id,
            'role' => 'Finance',
            'module_id' => $accounting->id,
            'action' => 'update',
            'is_allowed' => true,
            'can_delegate' => false,
        ]);

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.accounting-commission'))
            ->assertForbidden();

        $this->put(route('mpo.accounting-commission.update'), $this->validPayload([
            'responsible_accountant_user_id' => $otherStaff->id,
        ]))->assertForbidden();
        $this->assertDatabaseMissing('mpo_accounting_configurations', ['admin_user_id' => $mpo->id]);
    }

    public function test_only_global_admin_can_open_admin_review_and_configured_country_rate_is_global_admin_controlled(): void
    {
        $mpo = $this->mpo();
        $admin = $this->globalAdmin();

        $this->actingAs($mpo, 'mpo')
            ->get(route('admin.country-admins.accounting-commission.show', $mpo))
            ->assertRedirect(route('admin.login'));

        $this->withSession(['admin_id' => $admin->id])
            ->get(route('admin.country-admins.accounting-commission.show', $mpo))
            ->assertOk()
            ->assertSee('Bank Account Details')
            ->assertSee('No bank details are configured.');

        $this->withSession(['admin_id' => $admin->id])
            ->put(route('admin.countries.update', $mpo->country_id), [
                'name' => $mpo->country->name,
                'iso_code' => $mpo->country->iso_code,
                'currency_code' => 'MUR',
                'time_zone' => 'Indian/Mauritius',
                'vat_percentage' => '17.5000',
                'is_active' => 1,
            ])->assertRedirect(route('admin.countries.index'));
        $this->assertSame('17.5000', $mpo->country->fresh()->vat_percentage);
    }

    public function test_disabled_responsible_accountant_is_unassigned_and_missing_country_rate_blocks_completion(): void
    {
        $mpo = $this->mpo('owner@example.test', null);
        $staff = $this->staff($mpo, 'Accountant', 'accountant@example.test');
        $configuration = MpoAccountingConfiguration::query()->create([
            'admin_user_id' => $mpo->id,
            'vat_number' => 'VAT-1',
            'vat_exempted' => false,
            'bank_details' => [
                'account_holder_name' => 'MPO Ltd',
                'iban' => 'GB82WEST12345698765432',
                'bank_name' => 'Bank',
                'swift_bic' => 'DEUTDEFF',
            ],
            'responsible_accountant_user_id' => $staff->id,
        ]);
        $users = Module::query()->create(['slug' => 'users', 'name' => 'Users']);
        MpoRoleModulePermission::query()->create([
            'admin_user_id' => $mpo->id,
            'role' => 'Admin',
            'module_id' => $users->id,
            'action' => 'disable',
            'is_allowed' => true,
            'can_delegate' => false,
        ]);

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.accounting-commission'))
            ->assertOk()
            ->assertSee('does not have a VAT percentage configured')
            ->assertSee('In Progress');

        $this->post(route('mpo.users.status', $staff->id), ['status' => 'Disabled'])
            ->assertRedirect();
        $this->assertNull($configuration->fresh()->responsible_accountant_user_id);
        $this->assertDatabaseHas('mpo_accounting_configuration_events', [
            'admin_user_id' => $mpo->id,
            'event_type' => 'accountant_unassigned',
        ]);
    }

    public function test_staff_losing_accounting_permission_is_removed_as_responsible_accountant(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Accountant', 'accountant@example.test');
        $accounting = Module::query()->create(['slug' => 'accounting', 'name' => 'Accounting']);
        MpoRoleModulePermission::query()->create([
            'admin_user_id' => $mpo->id,
            'role' => 'Finance',
            'module_id' => $accounting->id,
            'action' => 'read',
            'is_allowed' => true,
            'can_delegate' => false,
        ]);
        MpoAccountingConfiguration::query()->create([
            'admin_user_id' => $mpo->id,
            'responsible_accountant_user_id' => $staff->id,
        ]);

        $this->actingAs($mpo, 'mpo')
            ->put(route('mpo.users.update', $staff->id), [
                'full_name' => $staff->full_name,
                'email' => $staff->email,
                'mobile' => '',
                'role' => 'Support',
            ])->assertRedirect();

        $this->assertNull(MpoAccountingConfiguration::query()->where('admin_user_id', $mpo->id)->value('responsible_accountant_user_id'));
        $this->assertDatabaseHas('mpo_accounting_configuration_events', [
            'admin_user_id' => $mpo->id,
            'event_type' => 'accountant_unassigned',
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'tax_id' => 'TAX-12345',
            'vat_number' => 'VAT-123',
            'vat_exempted' => '0',
            'bank' => [
                'account_holder_name' => 'MPO Ltd',
                'iban' => 'GB82 WEST 1234 5698 7654 32',
                'bank_name' => 'Example Bank',
                'swift_bic' => 'DEUTDEFF',
            ],
        ], $overrides);
    }

    private function mpo(string $email = 'owner@example.test', ?string $vatPercentage = '15.0000'): AdminUser
    {
        static $counter = 0;
        $counter++;
        $country = Country::query()->create([
            'name' => 'Country '.$counter,
            'iso_code' => str_pad((string) $counter, 2, '0', STR_PAD_LEFT),
            'currency_code' => 'MUR',
            'time_zone' => 'Indian/Mauritius',
            'vat_percentage' => $vatPercentage,
            'is_active' => true,
        ]);

        return AdminUser::query()->create([
            'name' => 'MPO Owner '.$counter,
            'email' => $email,
            'password_hash' => Hash::make('OwnerPassword1!'),
            'role' => 'admin',
            'admin_type' => 'mpo',
            'country_id' => $country->id,
            'status' => 'active',
        ]);
    }

    private function staff(AdminUser $mpo, string $name, string $email, string $role = 'Finance', string $status = 'Active'): MpoStaffUser
    {
        return MpoStaffUser::query()->create([
            'admin_user_id' => $mpo->id,
            'full_name' => $name,
            'email' => $email,
            'password_hash' => Hash::make('StaffPassword1!'),
            'role' => $role,
            'status' => $status,
        ]);
    }

    private function agreement(AdminUser $mpo, string $commissionValue = '10.0000'): MpoCollaborationAgreement
    {
        $agreement = MpoCollaborationAgreement::query()->create([
            'admin_user_id' => $mpo->id,
            'agreement_type' => 'mpo',
            'commission_model' => 'percentage',
            'commission_value' => $commissionValue,
            'status' => 'Draft',
        ]);
        $agreement->agreement_id = 'MPO-AGR-'.str_pad((string) $agreement->id, 6, '0', STR_PAD_LEFT);
        $agreement->save();

        return $agreement;
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
}
