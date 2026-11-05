<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\BusinessApproved;

class AdminBusinessApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_business_sends_notifications_to_owner_and_active_operators()
    {
        Mail::fake();

        $business = \App\Models\Business::create(['business_id' => \App\Models\Business::generateBusinessId(), 'legal_name' => 'Test Bus']);
        $owner = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'owner@example.com',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'active',
        ]);

        $activeOp = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'active@example.com',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'active',
        ]);

        $pendingOp = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'pending@example.com',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'pending_verification',
        ]);

        $admin = \App\Models\AdminUser::create([
            'name' => 'Global Admin',
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'super_admin',
            'admin_type' => 'global',
            'status' => 'active',
        ]);

        $response = $this->withSession(['admin_id' => $admin->id])
                         ->post(route('admin.business.approve', $business));

        $response->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('businesses', ['id' => $business->id, 'status' => 'active']);

        Mail::assertSent(BusinessApproved::class, function ($mail) use ($owner) {
            return $mail->hasTo($owner->email) && $mail->business->id === $mail->business->id;
        });

        Mail::assertSent(BusinessApproved::class, function ($mail) use ($activeOp) {
            return $mail->hasTo($activeOp->email);
        });

        Mail::assertNotSent(BusinessApproved::class, function ($mail) use ($pendingOp) {
            return $mail->hasTo($pendingOp->email);
        });
    }

    public function test_global_dashboard_shows_summary_cards_matching_pending_listings()
    {
        $business = \App\Models\Business::create([
            'business_id' => \App\Models\Business::generateBusinessId(),
            'legal_name' => 'Pending Dashboard Business',
            'status' => 'pending',
        ]);
        $admin = \App\Models\AdminUser::create([
            'name' => 'Global Admin',
            'email' => 'dashboard-admin@example.com',
            'password_hash' => bcrypt('Password123!'),
            'role' => 'super_admin',
            'admin_type' => 'global',
            'status' => 'active',
        ]);

        $response = $this->withSession(['admin_id' => $admin->id])
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Pending MPO Accounts')
            ->assertSee('Pending Businesses')
            ->assertSee('Pending Accommodation Approvals')
            ->assertSee('Pending Vehicle Approvals')
            ->assertSee('Pending Activity Approvals')
            ->assertSee('Feedback Submitted')
            ->assertSee('href="#pending-mpo-accounts"', false)
            ->assertSee('href="#pending-businesses"', false)
            ->assertSee('href="#pending-accommodation-approvals"', false)
            ->assertSee('href="#pending-vehicle-approvals"', false)
            ->assertSee('href="#pending-activity-approvals"', false)
            ->assertSee('href="#feedback"', false)
            ->assertSee('id="pending-mpo-accounts"', false)
            ->assertSee('id="pending-businesses"', false)
            ->assertSee('id="pending-accommodation-approvals"', false)
            ->assertSee('id="pending-vehicle-approvals"', false)
            ->assertSee('id="pending-activity-approvals"', false)
            ->assertSee('id="feedback"', false)
            ->assertSee($business->legal_name);

        $response->assertViewHas('summaryCounts', function (array $counts): bool {
            return $counts['businesses'] === 1
                && $counts['pendingMpos'] === 0
                && $counts['pendingAccommodations'] === 0
                && $counts['pendingTransports'] === 0
                && $counts['pendingActivities'] === 0
                && $counts['feedbacks'] === 0;
        });
    }
}
