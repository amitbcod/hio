<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

        $this->assertDatabaseHas('operators', [
            'email' => 'agreements@example.com',
            'agreement_type' => 'OTO',
        ]);
    }

    public function test_save_step5_updates_operator_agreement_type()
    {
        // create operator and login
        $operator = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'step5@example.com',
            'phone' => '+441234567891',
            'full_name' => 'Step Five',
            'business_legal_name' => 'Step Five Ltd',
            'agreement_type' => 'Listing Only',
            'account_status' => 'pending_verification',
            'password_hash' => bcrypt('Password123!'),
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

        $this->assertDatabaseHas('operators', [
            'email' => 'step5@example.com',
            'agreement_type' => 'OTO',
        ]);

        $this->assertDatabaseHas('operator_collaboration_agreements', [
            'operator_id' => $operator->operator_id,
            'agreement_type' => 'OTO',
        ]);
    }
}
