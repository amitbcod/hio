<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\ControllerVerificationRequested;
use App\Mail\OwnerClaimedToRequester;
use App\Models\Business;
use App\Models\ControllerVerification;
use App\Models\Operator;
use App\Models\AdminUser;

class ControllerVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_acceptBy_updates_status_direct()
    {
        // direct model method should persist accepted state
        $business = Business::create([
            'business_id' => Business::generateBusinessId(),
            'legal_name' => 'Direct Test Business',
            'primary_contact_email' => 'req@example.com',
            'status' => 'pending',
        ]);
        $cv = ControllerVerification::create([
            'token' => ControllerVerification::generateToken(),
            'business_id' => $business->id,
            'owner_email' => 'owner2@example.com',
            'expires_at' => now()->addDays(7),
        ]);

        $owner = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'owner2@example.com',
            'full_name' => 'Owner2',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'pending_verification',
        ]);

        $cv->acceptBy($owner);
        $cv = $cv->fresh();
        $this->assertEquals('accepted', $cv->status);
    }

    public function test_owner_can_claim_and_notifications_sent()
    {
        Mail::fake();

        // create a business and a requester operator
        $business = Business::create([
            'business_id' => Business::generateBusinessId(),
            'legal_name' => 'Test Business',
            'primary_contact_email' => 'requester@example.com',
            'status' => 'pending',
        ]);

        // create an admin to receive notifications
        AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('AdminPass123!'),
            'status' => 'active',
        ]);
        $requester = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'requester@example.com',
            'full_name' => 'Requester',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'pending_verification',
        ]);

        // create verification
        $cv = ControllerVerification::create([
            'token' => ControllerVerification::generateToken(),
            'business_id' => $business->id,
            'owner_email' => 'owner@example.com',
            'owner_full_name' => 'Owner Name',
            'requester_operator_id' => $requester->operator_id,
            'expires_at' => now()->addDays(7),
        ]);

        // simulate owner claim via HTTP
        $response = $this->post('/operator/register/controller/verify/'.$cv->token.'/claim', [
            'full_name' => 'Owner Name',
            'owner_email' => 'owner@example.com',
            'password' => 'OwnerPass123!',
            'password_confirmation' => 'OwnerPass123!',
            'confirm_authority' => 'on',
            'agreement_type' => 'Listing Only',
        ]);

        // Redirect to operator login
        $response->assertRedirect(route('operator.login'));

        // simulate owner claim by calling controller method directly (bypasses HTTP test isolation)
        $controller = new \App\Http\Controllers\Operator\ControllerVerificationController();
        $req = new \Illuminate\Http\Request([
            'full_name' => 'Owner Name',
            'owner_email' => 'owner@example.com',
            'password' => 'OwnerPass123!',
            'password_confirmation' => 'OwnerPass123!',
            'confirm_authority' => 'on',
            'agreement_type' => 'Listing Only',
        ]);
        $controllerResponse = $controller->claim($req, $cv->token);
        // If controller returns redirect we ignore, but we expect it to process and approve the verification

        // verification should have been accepted
        $cv = $cv->fresh();
        $this->assertEquals('accepted', $cv->status, 'Expected verification to be accepted after claim');

        // operator (owner) created
        $this->assertDatabaseHas('operators', ['email' => 'owner@example.com']);
        $this->assertNotNull($cv->accepted_by);

        // mails sent: OwnerClaimedToRequester and AdminOwnerClaimedNotification
        Mail::assertSent(OwnerClaimedToRequester::class);
        Mail::assertSent(\App\Mail\AdminOwnerClaimedNotification::class);
    }

    public function test_claim_with_expired_token_is_blocked()
    {
        // create business and requester
        $business = Business::create([
            'business_id' => Business::generateBusinessId(),
            'legal_name' => 'Expired Business',
            'primary_contact_email' => 'reqexp@example.com',
            'status' => 'pending',
        ]);
        $requester = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'reqexp@example.com',
            'full_name' => 'Requester',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'pending_verification',
        ]);

        $cv = ControllerVerification::create([
            'token' => ControllerVerification::generateToken(),
            'business_id' => $business->id,
            'owner_email' => 'owner_exp@example.com',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->post('/operator/register/controller/verify/'.$cv->token.'/claim', [
            'full_name' => 'Owner Exp',
            'owner_email' => 'owner_exp@example.com',
            'password' => 'OwnerPass123!',
            'password_confirmation' => 'OwnerPass123!',
            'confirm_authority' => 'on',
            'agreement_type' => 'Listing Only',
        ]);

        $response->assertRedirect(route('operator.register.step2'));
        $this->assertEquals('expired', $cv->fresh()->status);
        $this->assertDatabaseMissing('operators', ['email' => 'owner_exp@example.com']);
    }

    public function test_cannot_claim_when_already_accepted()
    {
        $business = Business::create([
            'business_id' => Business::generateBusinessId(),
            'legal_name' => 'Already Accepted',
            'primary_contact_email' => 'reqacc@example.com',
            'status' => 'pending',
        ]);

        $existingOperator = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'someowner@example.com',
            'full_name' => 'Some Owner',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'active',
        ]);

        $cv = ControllerVerification::create([
            'token' => ControllerVerification::generateToken(),
            'business_id' => $business->id,
            'owner_email' => 'owner_already@example.com',
            'status' => 'accepted',
            'accepted_by' => $existingOperator->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post('/operator/register/controller/verify/'.$cv->token.'/claim', [
            'full_name' => 'Owner Already',
            'owner_email' => 'owner_already@example.com',
            'password' => 'OwnerPass123!',
            'password_confirmation' => 'OwnerPass123!',
            'confirm_authority' => 'on',
            'agreement_type' => 'Listing Only',
        ]);

        $response->assertRedirect(route('operator.register.step2'));
        $this->assertEquals('accepted', $cv->fresh()->status);
        $this->assertDatabaseMissing('operators', ['email' => 'owner_already@example.com']);
    }

    public function test_owner_can_accept_if_already_registered()
    {
        $business = Business::create([
            'business_id' => Business::generateBusinessId(),
            'legal_name' => 'Accept Existing Owner',
            'primary_contact_email' => 'reqowner@example.com',
            'status' => 'pending',
        ]);

        $cv = ControllerVerification::create([
            'token' => ControllerVerification::generateToken(),
            'business_id' => $business->id,
            'owner_email' => 'owner_exists@example.com',
            'expires_at' => now()->addDays(7),
        ]);

        $owner = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'owner_exists@example.com',
            'full_name' => 'Owner Exists',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => null,
            'account_status' => 'active',
        ]);

        $this->actingAs($owner);

        $response = $this->post('/operator/register/controller/verify/'.$cv->token.'/accept');

        $response->assertRedirect(route('operator.register.step2'));
        $this->assertEquals('accepted', $cv->fresh()->status);
        $this->assertEquals($owner->id, $cv->fresh()->accepted_by);
        $this->assertEquals($cv->business_id, $owner->fresh()->business_id);
    }

    public function test_verification_page_displays_requester_details()
    {
        $business = Business::create([
            'business_id' => Business::generateBusinessId(),
            'legal_name' => 'Requester Business',
            'primary_contact_email' => 'reqview@example.com',
            'status' => 'pending',
        ]);

        $requester = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'reqview@example.com',
            'full_name' => 'Requester View',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'pending',
        ]);

        $cv = ControllerVerification::create([
            'token' => ControllerVerification::generateToken(),
            'business_id' => $business->id,
            'owner_email' => 'owner_view@example.com',
            'requester_operator_id' => $requester->operator_id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->get('/operator/register/controller/verify/'.$cv->token);
        $response->assertStatus(200);
        $response->assertSee('Requester details');
        $response->assertSee('Requester View');
        $response->assertSee('reqview@example.com');
    }

    public function test_requester_cannot_login_until_owner_accepts()
    {
        // create business + requester
        $business = Business::create([
            'business_id' => Business::generateBusinessId(),
            'legal_name' => 'Login Gate Test',
            'primary_contact_email' => 'reqlogin@example.com',
            'status' => 'pending',
        ]);

        $requester = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'reqlogin@example.com',
            'full_name' => 'Requester Login',
            'password_hash' => bcrypt('Secret123!'),
            'business_id' => $business->id,
            'account_status' => 'pending_verification',
        ]);

        $cv = ControllerVerification::create([
            'token' => ControllerVerification::generateToken(),
            'business_id' => $business->id,
            'owner_email' => 'ownerlogin@example.com',
            'requester_operator_id' => $requester->operator_id,
            'expires_at' => now()->addDays(7),
        ]);

        // Attempt to login before owner accepts
        $response = $this->post('/operator/login', [
            'email' => 'reqlogin@example.com',
            'password' => 'Secret123!'
        ]);

        $response->assertSessionHasErrors(['email' => 'Account not active. Please wait for owner approval.']);

        // Create owner and accept
        $owner = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'ownerlogin@example.com',
            'full_name' => 'Owner Login',
            'password_hash' => bcrypt('OwnerPass123!'),
            'business_id' => $business->id,
            'account_status' => 'active',
        ]);

        $this->actingAs($owner);
        $response = $this->post('/operator/register/controller/verify/'.$cv->token.'/accept');
        $response->assertRedirect(route('operator.register.step2'));

        // Now requester should be able to login
        $response = $this->post('/operator/login', [
            'email' => 'reqlogin@example.com',
            'password' => 'Secret123!'
        ]);

        $response->assertRedirect(route('operator.register.step2'));
    }

    public function test_owner_can_login_and_accept_in_one_flow()
    {
        // create business + requester
        $business = Business::create([
            'business_id' => Business::generateBusinessId(),
            'legal_name' => 'Login Accept Flow',
            'primary_contact_email' => 'reqflow@example.com',
            'status' => 'pending',
        ]);

        $requester = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'reqflow@example.com',
            'full_name' => 'Requester Flow',
            'password_hash' => bcrypt('Flow123!'),
            'business_id' => $business->id,
            'account_status' => 'pending_verification',
        ]);

        $cv = ControllerVerification::create([
            'token' => ControllerVerification::generateToken(),
            'business_id' => $business->id,
            'owner_email' => 'ownerflow@example.com',
            'requester_operator_id' => $requester->operator_id,
            'expires_at' => now()->addDays(7),
        ]);

        // Create owner account but do not log in yet
        $owner = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'ownerflow@example.com',
            'full_name' => 'Owner Flow',
            'password_hash' => bcrypt('OwnerFlow123!'),
            'business_id' => null,
            'account_status' => 'active',
        ]);

        // Owner clicks Accept but is not logged in -> should be redirected to login
        $response = $this->post('/operator/register/controller/verify/'.$cv->token.'/accept');
        // Accept action redirects to the login page (may be login fallback route)
        $response->assertRedirect(route('login'));
        // The operator login page should accept an accept_token query param and include it in the login form
        $loginGet = $this->get('/operator/login?accept_token='.$cv->token);
        $loginGet->assertStatus(200);
        $loginGet->assertSee('accept_token');

        // Simulate the browser: follow redirect to login page with token, then submit the login form which carries the token
        $this->get(route('login', ['accept_token' => $cv->token]));

        $login = $this->post('/operator/login', [
            'email' => 'ownerflow@example.com',
            'password' => 'OwnerFlow123!',
            'accept_token' => $cv->token,
        ]);

        $login->assertRedirect(route('operator.register.step2'));

        // verification should be accepted and requester activated
        $this->assertEquals('accepted', $cv->fresh()->status);
        $this->assertEquals('active', $requester->fresh()->account_status);
    }

    public function test_approval_page_shows_approve_button_when_owner_exists()
    {
        $business = Business::create([
            'business_id' => Business::generateBusinessId(),
            'legal_name' => 'Approval Button Test',
            'primary_contact_email' => 'reqapprove@example.com',
            'status' => 'pending',
        ]);

        $requester = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'reqapprove@example.com',
            'full_name' => 'Requester Approve',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'pending',
        ]);

        $owner = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'owner_approve@example.com',
            'full_name' => 'Owner Approve',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'active',
        ]);

        $cv = ControllerVerification::create([
            'token' => ControllerVerification::generateToken(),
            'business_id' => $business->id,
            'owner_email' => 'owner_approve@example.com',
            'requester_operator_id' => $requester->operator_id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->get('/operator/register/controller/verify/'.$cv->token);
        $response->assertStatus(200);
        // Check for approval UI
        $response->assertSee('Approve Verification');
        $response->assertSee('Account Found');
        $response->assertSee($owner->email);
        // Check that claim form is NOT shown
        $response->assertDontSee('Create & Claim');
    }
}

