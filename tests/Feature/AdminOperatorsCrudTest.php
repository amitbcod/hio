<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminOperatorsCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_operators_index_and_can_create_operator()
    {
        // create admin and set session
        $admin = \App\Models\AdminUser::create(['name' => 'Admin','email' => 'admin@example.com','password_hash' => bcrypt('Admin123!')]);
        session(['admin_id' => $admin->id]);

        $resp = $this->get(route('admin.operators.index'));
        $resp->assertStatus(200);
        $resp->assertSee('Create Operator');

        // Create a business to attach operator
        $business = \App\Models\Business::create(['business_id' => \App\Models\Business::generateBusinessId(), 'legal_name' => 'BizTest']);

        $response = $this->post(route('admin.operators.store'), [
            'full_name' => 'New Op',
            'email' => 'newop@example.com',
            'business_id' => $business->id,
            'is_owner' => 'no',
            'account_status' => 'active',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ]);

        $response->assertRedirect(route('admin.operators.index'));
        $this->assertDatabaseHas('operators', ['email' => 'newop@example.com', 'full_name' => 'New Op']);
    }
}
