<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\AgreementConfirmed;

class AgreementConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_confirm_agreement_generates_pdf_and_sends_email()
    {
        Mail::fake();
        Storage::fake('public');

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

        $response = $this->post('/operator/register/step5-collaboration/confirm', [
            'agreement_confirm_name' => 'Owner Name',
            'agreement_type' => 'Listing Only',
        ]);

        $response->assertRedirect(route('operator.register.step5'));

        $this->assertDatabaseHas('operator_collaboration_agreements', [
            'business_id' => $business->id,
            'agreement_type' => 'Listing Only',
        ]);

        $collab = \App\Models\OperatorCollaborationAgreement::where('business_id', $business->id)->first();
        $this->assertNotNull($collab->agreement_file);
        Storage::disk('public')->assertExists($collab->agreement_file);

        Mail::assertSent(AgreementConfirmed::class, function ($mail) use ($business) {
            return $mail->hasTo($business->primary_contact_email);
        });
    }
}
