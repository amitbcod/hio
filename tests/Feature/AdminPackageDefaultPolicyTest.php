<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Operator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPackageDefaultPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_operator_inherits_admin_default_package_policy(): void
    {
        AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('Password123!'),
            'package_policy' => [
                'cancellation' => ['type' => 'Moderate', 'before_deadline' => '50% Refund'],
                'booking_notes' => 'Default admin package note',
            ],
        ]);

        $operator = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'operator@example.com',
            'phone' => '123456',
            'full_name' => 'Test Operator',
            'business_legal_name' => 'Test Business',
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
        ]);

        $this->assertSame('Moderate', $operator->package_policy['cancellation']['type']);
        $this->assertSame('Default admin package note', $operator->package_policy['booking_notes']);
    }

    public function test_existing_operator_values_are_not_overwritten_by_admin_default_policy(): void
    {
        AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin2@example.com',
            'password_hash' => bcrypt('Password123!'),
            'package_policy' => [
                'cancellation' => ['type' => 'Strict'],
            ],
        ]);

        $operator = Operator::create([
            'operator_id' => uniqid('OP'),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'custom@example.com',
            'phone' => '123456',
            'full_name' => 'Custom Operator',
            'business_legal_name' => 'Custom Business',
            'account_status' => 'active',
            'password_hash' => bcrypt('Password123!'),
            'package_policy' => [
                'cancellation' => ['type' => 'Flexible', 'before_deadline' => '100% Refund'],
            ],
        ]);

        $this->assertSame('Flexible', $operator->package_policy['cancellation']['type']);
    }
}
