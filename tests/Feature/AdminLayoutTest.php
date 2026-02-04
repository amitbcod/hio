<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_does_not_show_operator_header_when_operator_authenticated()
    {
        $business = \App\Models\Business::create(['business_id' => \App\Models\Business::generateBusinessId(), 'legal_name' => 'Test Bus']);
        $operator = \App\Models\Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'owner@example.com',
            'password_hash' => bcrypt('Password123!'),
            'business_id' => $business->id,
            'account_status' => 'active',
        ]);

        $this->actingAs($operator);

        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertDontSee('Operator Portal');
        $response->assertDontSee($operator->email);
        $response->assertSee('Admin Login');
    }
}