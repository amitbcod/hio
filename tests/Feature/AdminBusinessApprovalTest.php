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

        $admin = \App\Models\AdminUser::create(['email' => 'admin@example.com']);

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
}
