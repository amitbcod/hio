<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class OperatorRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_saves_agreement_type()
    {
        $response = $this->post('/operator/register', [
            'user_type' => 'Operator',
            'business_legal_name' => 'Agreement Test Ltd',
            'country_of_operation' => 'GB',
            'agreement_type' => 'OTO',
            'is_owner' => 'yes',
            'email' => 'agreements@example.com',
            'phone' => '+441234567890',
            'full_name' => 'Agreement Person',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => 'on',
        ]);

        $response->assertRedirect(route('operator.register.step2'));

        $this->assertDatabaseHas('businesses', [
            'legal_name' => 'Agreement Test Ltd',
            'agreement_type' => 'OTO',
        ]);
    }

    public function test_non_owner_registration_does_not_require_agreement_type()
    {
        $response = $this->post('/operator/register', [
            'user_type' => 'Operator',
            'business_legal_name' => 'NonOwner Ltd',
            'country_of_operation' => 'GB',
            'is_owner' => 'no',
            'owner_email' => 'owner@example.com',
            'email' => 'nonowner@example.com',
            'phone' => '+441234567899',
            'full_name' => 'Non Owner',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => 'on',
        ]);

        $response->assertRedirect(route('operator.register.step2'));

        $this->assertDatabaseHas('operators', [
            'email' => 'nonowner@example.com',
            'owner_email' => 'owner@example.com',
        ]);
    }

    public function test_save_step5_updates_operator_agreement_type()
    {
        // create operator and login
        $business = \App\Models\Business::create([
            'business_id' => \App\Models\Business::generateBusinessId(),
            'legal_name' => 'Step Five Ltd',
            'agreement_type' => 'Listing Only',
        ]);

        $operator = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'step5@example.com',
            'phone' => '+441234567891',
            'full_name' => 'Step Five',
            'business_legal_name' => 'Step Five Ltd',
            'account_status' => 'pending_verification',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
        ]);

        $this->actingAs($operator);

        $response = $this->post('/operator/register/step5-collaboration', [
            'agreement_type' => 'OTO',
            'contact_management_name' => 'Manager',
            'contact_management_email' => 'man@example.com',
            'contact_management_phone' => '01234',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
            'renewal_date' => now()->addMonths(11)->format('Y-m-d'),
            'commission_model' => 'percentage',
            'commission_value' => 10,
            'marketing_contribution_percent' => 5,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('operator.register.step6'));

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'agreement_type' => 'OTO',
        ]);

        $this->assertDatabaseHas('operator_collaboration_agreements', [
            'business_id' => $operator->business_id,
            'agreement_type' => 'OTO',
        ]);
    }

    public function test_submit_for_approval_sends_admin_email()
    {
        Mail::fake();

        // create an admin
        \App\Models\AdminUser::create([
            'name' => 'Admin',
            'email' => 'adminnotify@example.com',
            'password_hash' => bcrypt('Admin123!'),
            'status' => 'active',
        ]);

        // create operator and status review
        $operator = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'submitforapprove@example.com',
            'phone' => '+441234567892',
            'full_name' => 'Submit Approve',
            'business_legal_name' => 'Submit Approve Ltd',
            'account_status' => 'pending_verification',
            'password_hash' => bcrypt('Password123!'),
        ]);

        \App\Models\OperatorStatusReview::create([
            'operator_id' => $operator->operator_id,
        ]);

        $this->actingAs($operator);

        $response = $this->post(route('operator.status.submit'));
        $response->assertRedirect(route('operator.pending.approval'));

        Mail::assertSent(\App\Mail\AdminApprovalRequested::class);

        $this->assertDatabaseHas('operator_registration_progress', [
            'operator_id' => $operator->operator_id,
            'step9_review' => 1,
        ]);
    }

    public function test_payouts_listed_by_business_for_linked_operators()
    {
        // create business
        $business = \App\Models\Business::create([
            'business_id' => \App\Models\Business::generateBusinessId(),
            'legal_name' => 'Business Payouts Ltd',
            'agreement_type' => 'Listing Only',
        ]);

        // create two operators linked to the same business
        $op1 = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'op1@example.com',
            'phone' => '+441234567900',
            'full_name' => 'Op One',
            'business_legal_name' => 'Business Payouts Ltd',
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
        ]);

        $op2 = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'op2@example.com',
            'phone' => '+441234567901',
            'full_name' => 'Op Two',
            'business_legal_name' => 'Business Payouts Ltd',
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
        ]);

        // create a payout associated with the business
        \App\Models\OperatorPayout::create([
            'payout_id' => 'PAYOUT-TEST-1',
            'beneficiary_id' => $op1->operator_id,
            'business_id' => $business->id,
            'beneficiary' => $business->legal_name,
            'period_covered' => '2026-01',
            'total_commission' => 100,
            'payout_amount' => 90,
            'currency' => 'USD',
            'status' => 'Pending',
        ]);

        // acting as second operator, ensure the payout appears in the listing
        $this->actingAs($op2);
        $response = $this->get(route('operator.register.step7'));
        $response->assertViewHas('payouts', function ($payouts) {
            return $payouts->contains('payout_id', 'PAYOUT-TEST-1');
        });
    }

    public function test_payouts_listed_by_beneficiary_for_unlinked_operator()
    {
        $op = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'op-local@example.com',
            'phone' => '+441234567902',
            'full_name' => 'Op Local',
            'business_legal_name' => 'Local Ltd',
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
        ]);

        \App\Models\OperatorPayout::create([
            'payout_id' => 'PAYOUT-TEST-2',
            'beneficiary_id' => $op->operator_id,
            'beneficiary' => $op->full_name,
            'period_covered' => '2026-01',
            'total_commission' => 50,
            'payout_amount' => 45,
            'currency' => 'USD',
            'status' => 'Pending',
        ]);

        $this->actingAs($op);
        $response = $this->get(route('operator.register.step7'));
        $response->assertViewHas('payouts', function ($payouts) {
            return $payouts->contains('payout_id', 'PAYOUT-TEST-2');
        });
    }

    public function test_operator_users_listed_by_business_for_linked_operators()
    {
        // create business
        $business = \App\Models\Business::create([
            'business_id' => \App\Models\Business::generateBusinessId(),
            'legal_name' => 'Business Users Ltd',
            'agreement_type' => 'Listing Only',
        ]);

        // create two operators linked to the same business
        $op1 = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'opu1@example.com',
            'phone' => '+441234567910',
            'full_name' => 'Op User One',
            'business_legal_name' => 'Business Users Ltd',
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
        ]);

        $op2 = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'opu2@example.com',
            'phone' => '+441234567911',
            'full_name' => 'Op User Two',
            'business_legal_name' => 'Business Users Ltd',
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
        ]);

        // create a user associated with the business
        \App\Models\OperatorUser::create([
            'user_id' => uniqid('OPU'),
            'operator_id' => $op1->operator_id,
            'business_id' => $business->id,
            'full_name' => 'Business User',
            'email' => 'businessuser@example.com',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'Support Manager',
            'status' => 'Active',
        ]);

        // acting as second operator, ensure the user appears in the listing
        $this->actingAs($op2);
        $response = $this->get(route('operator.register.step6'));
        $response->assertViewHas('users', function ($users) {
            return $users->contains('email', 'businessuser@example.com');
        });
    }

    public function test_operator_users_listed_by_operator_for_unlinked_operator()
    {
        $op = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'op-local2@example.com',
            'phone' => '+441234567912',
            'full_name' => 'Op Local 2',
            'business_legal_name' => 'Local Two Ltd',
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
        ]);

        \App\Models\OperatorUser::create([
            'user_id' => uniqid('OPU'),
            'operator_id' => $op->operator_id,
            'full_name' => 'Local User',
            'email' => 'localuser@example.com',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'Support Manager',
            'status' => 'Active',
        ]);

        $this->actingAs($op);
        $response = $this->get(route('operator.register.step6'));
        $response->assertViewHas('users', function ($users) {
            return $users->contains('email', 'localuser@example.com');
        });
    }

    public function test_system_processes_listed_by_business_for_linked_operators()
    {
        // create business
        $business = \App\Models\Business::create([
            'business_id' => \App\Models\Business::generateBusinessId(),
            'legal_name' => 'Business Systems Ltd',
        ]);

        // create two operators linked to the same business
        $op1 = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'ops1@example.com',
            'phone' => '+441234567913',
            'full_name' => 'Op Sys One',
            'business_legal_name' => 'Business Systems Ltd',
            'agreement_type' => 'Listing Only',
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
        ]);

        $op2 = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'ops2@example.com',
            'phone' => '+441234567914',
            'full_name' => 'Op Sys Two',
            'business_legal_name' => 'Business Systems Ltd',
            'agreement_type' => 'Listing Only',
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
        ]);

        // create a system process associated with the business
        \App\Models\OperatorSystemProcess::create([
            'operator_id' => $op1->operator_id,
            'business_id' => $business->id,
            'service_category' => 'Accommodation',
            'communication_preference' => 'Email',
            'assigned_operator_name' => 'Sys Admin',
            'assigned_operator_role' => 'Primary Operator',
            'status' => 'active',
        ]);

        // acting as second operator, ensure the system process appears in the listing
        $this->actingAs($op2);
        $response = $this->get(route('operator.register.step4'));
        $response->assertViewHas('system', function ($system) {
            return $system !== null && $system->service_category === 'Accommodation';
        });
    }

    public function test_system_processes_listed_by_operator_for_unlinked_operator()
    {
        $op = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'op-local3@example.com',
            'phone' => '+441234567915',
            'full_name' => 'Op Local 3',
            'business_legal_name' => 'Local Three Ltd',
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
        ]);

        \App\Models\OperatorSystemProcess::create([
            'operator_id' => $op->operator_id,
            'service_category' => 'Activities',
            'communication_preference' => 'Email',
            'assigned_operator_name' => 'Local Admin',
            'assigned_operator_role' => 'Primary Operator',
            'status' => 'active',
        ]);

        $this->actingAs($op);
        $response = $this->get(route('operator.register.step4'));
        $response->assertViewHas('system', function ($system) {
            return $system !== null && $system->service_category === 'Activities';
        });
    }

    public function test_business_scoped_data_visible_to_both_owner_and_authorized_operators()
    {
        // Create a business
        $business = \App\Models\Business::create([
            'business_id' => 'BIZ-' . uniqid(),
            'legal_name' => 'Shared Business Ltd',
            'country' => 'GB',
            'registration_number' => 'REG123456',
            'status' => 'active',
            'agreement_type' => 'Listing Only',
        ]);

        // Create owner operator linked to business
        $owner = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'owner@example.com',
            'phone' => '+441234567920',
            'full_name' => 'Owner Name',
            'business_legal_name' => 'Shared Business Ltd',
            'account_status' => 'active',
            'business_id' => $business->id,
            'password_hash' => bcrypt('Password123!'),
        ]);

        // Set up owner's progress to allow accessing step 4
        \App\Models\OperatorRegistrationProgress::create([
            'operator_id' => $owner->operator_id,
            'business_id' => $business->id,
            'step2_profile' => 1,
            'step3_legal' => 1,
            'current_step' => 4,
        ]);

        // Owner fills system configuration
        $this->actingAs($owner);
        $response = $this->post('/operator/register/step4-system-process', [
            'service_category' => 'Accommodation',
            'communication_preference' => 'SMS',
            'assigned_operator_name' => 'Shared Admin',
            'assigned_operator_role' => 'Manager',
            'status' => 'active',
        ]);
        $response->assertRedirect(route('operator.register.step5'));

        // Verify system process is saved with business_id
        $this->assertDatabaseHas('operator_system_processes', [
            'business_id' => $business->id,
            'communication_preference' => 'SMS',
            'assigned_operator_name' => 'Shared Admin',
        ]);

        // Create authorized (non-owner) operator linked to same business
        $authorized = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'authorized@example.com',
            'phone' => '+441234567921',
            'full_name' => 'Authorized Op',
            'business_legal_name' => 'Shared Business Ltd',
            'account_status' => 'active',
            'business_id' => $business->id,
            'password_hash' => bcrypt('Password123!'),
        ]);

        // Set up authorized operator's progress to allow accessing step 4
        \App\Models\OperatorRegistrationProgress::create([
            'operator_id' => $authorized->operator_id,
            'business_id' => $business->id,
            'step2_profile' => 1,
            'step3_legal' => 1,
            'current_step' => 4,
        ]);

        // Authorized operator views system configuration and sees owner's data
        $this->actingAs($authorized);
        $response = $this->get(route('operator.register.step4'));
        $response->assertStatus(200);
        $response->assertViewHas('system', function ($system) {
            return $system !== null && 
                   $system->communication_preference === 'SMS' && 
                   $system->assigned_operator_name === 'Shared Admin';
        });
    }


}
