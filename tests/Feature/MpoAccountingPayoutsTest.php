<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Country;
use App\Models\MpoAccountingConfiguration;
use App\Models\MpoCollaborationAgreement;
use App\Models\MpoPayoutConfiguration;
use App\Models\MpoStaffUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MpoAccountingPayoutsTest extends TestCase
{
    use RefreshDatabase;

    public function test_mpo_can_save_own_schedule_and_bank_method_with_server_generated_reference(): void
    {
        $mpo = $this->mpo('first@example.test', 'MPO One');
        $this->accounting($mpo);

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.accounting-payouts'))
            ->assertOk()
            ->assertSee('Not configured — no MPO gateway fee rules are available')
            ->assertSee('Unavailable. The application has no MPO credit facility');

        $this->put(route('mpo.accounting-payouts.update'), [
            'payment_schedule' => 'Monthly',
            'payout_method' => 'Bank',
        ])->assertRedirect(route('mpo.accounting-payouts'))
            ->assertSessionHas('success');

        $configuration = MpoPayoutConfiguration::query()->where('admin_user_id', $mpo->id)->firstOrFail();
        $this->assertSame('MPO-PAY-'.str_pad((string) $configuration->id, 6, '0', STR_PAD_LEFT), $configuration->payout_id);
        $this->assertSame('Monthly', $configuration->payment_schedule);
        $this->assertSame('Bank', $configuration->payout_method);
        $this->assertDatabaseHas('mpo_payout_configuration_events', [
            'admin_user_id' => $mpo->id,
            'event_type' => 'configuration_updated',
        ]);
        $this->assertDatabaseCount('operator_payouts', 0);
    }

    public function test_payout_configuration_is_tenant_scoped_and_client_cannot_override_trusted_values(): void
    {
        $mpo = $this->mpo('first@example.test', 'MPO One');
        $other = $this->mpo('other@example.test', 'Other MPO');
        $this->accounting($mpo);
        $this->actingAs($mpo, 'mpo')->put(route('mpo.accounting-payouts.update'), [
            'payment_schedule' => 'On Request',
        ]);
        $reference = $mpo->payoutConfiguration()->firstOrFail()->payout_id;

        $this->actingAs($other, 'mpo')->get(route('mpo.accounting-payouts'))
            ->assertOk()
            ->assertDontSee($reference)
            ->assertSee('Other MPO');

        $this->put(route('mpo.accounting-payouts.update'), [
            'payment_schedule' => 'Monthly',
            'payout_method' => 'Wallet',
        ])->assertSessionHasErrors('payout_method');

        $this->put(route('mpo.accounting-payouts.update'), [
            'payment_schedule' => 'Monthly',
            'payout_id' => $reference,
        ])->assertSessionHasErrors('payout_id');

        $this->assertSame($mpo->id, MpoPayoutConfiguration::query()->where('payout_id', $reference)->value('admin_user_id'));
    }

    public function test_bank_method_requires_valid_existing_bank_details_and_unsupported_fields_are_rejected(): void
    {
        $mpo = $this->mpo('bankless@example.test', 'Bankless MPO');
        $this->actingAs($mpo, 'mpo')->put(route('mpo.accounting-payouts.update'), [
            'payment_schedule' => 'Service Provided',
            'payout_method' => 'Bank',
        ])->assertSessionHasErrors('payout_method');

        $this->assertDatabaseMissing('mpo_payout_configurations', ['admin_user_id' => $mpo->id]);

        $this->put(route('mpo.accounting-payouts.update'), [
            'payment_schedule' => 'Quarterly',
        ])->assertSessionHasErrors('payment_schedule');

        $this->put(route('mpo.accounting-payouts.update'), [
            'payment_schedule' => 'Monthly',
            'commission_value' => '99.0000',
        ])->assertSessionHasErrors('commission_value');

        $this->assertDatabaseMissing('mpo_payout_configurations', ['admin_user_id' => $mpo->id]);
    }

    public function test_mpo_payout_onboarding_completion_requires_active_agreement_accounting_and_saved_preferences(): void
    {
        $mpo = $this->mpo('ready@example.test', 'Ready MPO');
        $this->accounting($mpo);
        $agreement = MpoCollaborationAgreement::query()->create([
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
        $this->assertTrue($agreement->isOnboardingComplete());
        $this->actingAs($mpo, 'mpo')->put(route('mpo.accounting-payouts.update'), [
            'payment_schedule' => 'Monthly',
            'payout_method' => 'Bank',
        ]);

        $this->get(route('mpo.accounting-payouts'))
            ->assertOk()
            ->assertSee('Payout configuration is complete');
    }

    public function test_staff_without_accounting_permission_cannot_view_or_change_payout_configuration(): void
    {
        $mpo = $this->mpo('staff-owner@example.test', 'Staff Owner');
        $staff = MpoStaffUser::query()->create([
            'admin_user_id' => $mpo->id,
            'full_name' => 'No Accounting Access',
            'email' => 'no-accounting@example.test',
            'password_hash' => Hash::make('StaffPassword1!'),
            'role' => 'Support',
            'status' => 'Active',
        ]);

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.accounting-payouts'))
            ->assertForbidden();

        $this->put(route('mpo.accounting-payouts.update'), [
            'payment_schedule' => 'Monthly',
        ])->assertForbidden();
        $this->assertDatabaseMissing('mpo_payout_configurations', ['admin_user_id' => $mpo->id]);
    }

    public function test_global_admin_can_review_masked_payout_configuration_without_full_bank_data(): void
    {
        $mpo = $this->mpo('admin-review@example.test', 'Review MPO');
        $this->accounting($mpo);
        $this->actingAs($mpo, 'mpo')->put(route('mpo.accounting-payouts.update'), [
            'payment_schedule' => 'Monthly',
            'payout_method' => 'Bank',
        ]);
        $globalAdmin = AdminUser::query()->create([
            'name' => 'Global Admin',
            'email' => 'global@example.test',
            'password_hash' => Hash::make('AdminPassword1!'),
            'role' => 'super_admin',
            'admin_type' => 'global',
            'status' => 'active',
        ]);

        $this->withSession(['admin_id' => $globalAdmin->id])
            ->get(route('admin.country-admins.accounting-payouts.show', $mpo))
            ->assertOk()
            ->assertSee('IBAN ending **** 5432')
            ->assertDontSee('GB82WEST12345698765432')
            ->assertDontSee('Save Changes');
    }

    private function accounting(AdminUser $mpo): void
    {
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
    }

    private function mpo(string $email, string $name): AdminUser
    {
        static $counter = 0;
        $counter++;
        $country = Country::query()->create([
            'name' => 'Country '.$counter,
            'iso_code' => str_pad((string) $counter, 2, '0', STR_PAD_LEFT),
            'currency_code' => 'MUR',
            'time_zone' => 'Indian/Mauritius',
            'vat_percentage' => '15.0000',
            'is_active' => true,
        ]);

        return AdminUser::query()->create([
            'name' => $name,
            'registered_business_name' => $name,
            'email' => $email,
            'password_hash' => Hash::make('OwnerPassword1!'),
            'role' => 'admin',
            'admin_type' => 'mpo',
            'country_id' => $country->id,
            'status' => 'active',
        ]);
    }
}
