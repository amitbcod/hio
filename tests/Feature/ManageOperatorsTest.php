<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class ManageOperatorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_manage_operators_link()
    {
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

        $this->actingAs($owner);
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Settings');
        $response->assertSee('Manage Operators');
    }

    public function test_owner_can_update_operator_status_and_receives_email()
    {
        Mail::fake();

        $business = \App\Models\Business::create(['business_id' => \App\Models\Business::generateBusinessId(), 'legal_name' => 'Test Bus']);
        $owner = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'owner2@example.com',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'active',
        ]);

        $op = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'no',
            'email' => 'nonowner@example.com',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'pending_verification',
        ]);

        $this->actingAs($owner);

        $response = $this->post(route('operator.manage.operators.update_status', $op->id), ['status' => 'active']);
        $response->assertRedirect(route('operator.manage.operators.index'));

        $this->assertDatabaseHas('operators', ['id' => $op->id, 'account_status' => 'active']);

        Mail::assertSent(\App\Mail\OperatorStatusChanged::class, function ($mail) use ($op) {
            return $mail->hasTo($op->email) && $mail->newStatus === 'active';
        });
    }
}
