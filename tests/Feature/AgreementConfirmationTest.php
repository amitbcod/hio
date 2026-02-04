<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgreementConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_collaboration_and_update_business_agreement_type()
    {
        $business = \App\Models\Business::create([
            'business_id' => \App\Models\Business::generateBusinessId(),
            'legal_name' => 'Agree Co',
            'primary_contact_email' => 'owner@agree.com',
            'agreement_type' => 'Listing Only',
        ]);

        $owner = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'owner@agree.com',
            'full_name' => 'Owner Name',
            'business_id' => $business->id,
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
        ]);

        $this->actingAs($owner);

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
            'business_id' => $business->id,
            'agreement_type' => 'OTO',
        ]);
    }
}
