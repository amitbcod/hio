<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PolicyTemplate;
use Illuminate\Support\Str;

class PolicyTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'title' => 'Standard Amendment Policy',
                'service_type' => 'accommodation',
                'policy_type' => 'Amendment Policy',
                'content' => '<p>Guests may amend their booking up to 7 days before arrival without charge. Amendments within 7 days may incur fees.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Flexible Cancellation (Free up to 7 days)',
                'service_type' => 'accommodation',
                'policy_type' => 'Cancellation Policy',
                'content' => '<p>Free cancellation up to 7 days before check-in. Cancellations within 7 days are non-refundable or charged at the owner\'s discretion.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Standard Security Deposit Policy',
                'service_type' => 'accommodation',
                'policy_type' => 'Security Deposit Policy',
                'content' => '<p>A refundable security deposit may be held and returned within 14 days after checkout subject to inspection for damages.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Standard House Rules',
                'service_type' => 'accommodation',
                'policy_type' => 'House Rules',
                'content' => '<ul><li>No smoking inside the property</li><li>No parties</li><li>Respect quiet hours from 10pm to 8am</li></ul>',
                'is_active' => true,
            ],
            [
                'title' => 'Standard Amendment Policy',
                'service_type' => 'activity',
                'policy_type' => 'Amendment Policy',
                'content' => '<p>Amendments allowed up to 48 hours before the activity. Amendments within 48 hours are subject to availability.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Flexible Cancellation (Free up to 7 days)',
                'service_type' => 'activity',
                'policy_type' => 'Cancellation Policy',
                'content' => '<p>Free cancellation up to 7 days before the activity. Later cancellations may incur partial charges.</p>',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $t) {
            $slug = Str::slug($t['service_type'] . ' ' . $t['policy_type'] . ' ' . $t['title']);
            PolicyTemplate::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $t['title'],
                    'slug' => $slug,
                    'service_type' => $t['service_type'],
                    'policy_type' => $t['policy_type'],
                    'content' => $t['content'],
                    'is_active' => $t['is_active'],
                    'created_by' => null,
                ]
            );
        }
    }
}
