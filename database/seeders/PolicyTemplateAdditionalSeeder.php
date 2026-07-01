<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PolicyTemplate;

class PolicyTemplateAdditionalSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [

            // Accommodation - Amendment Policy
            [
                'title' => 'Flexible Amendment Policy',
                'slug' => 'flexible-amendment-policy',
                'service_type' => 'accommodation',
                'policy_type' => 'Amendment Policy',
                'content' => 'Flexible Amendment Policy',
            ],
            [
                'title' => 'Non-Refundable Amendment Policy',
                'slug' => 'non-refundable-amendment-policy',
                'service_type' => 'accommodation',
                'policy_type' => 'Amendment Policy',
                'content' => 'Non-Refundable Amendment Policy',
            ],

            // Accommodation - Cancellation Policy
            [
                'title' => 'Moderate Cancellation (Free up to 14 days)',
                'slug' => 'moderate-cancellation',
                'service_type' => 'accommodation',
                'policy_type' => 'Cancellation Policy',
                'content' => 'Moderate Cancellation (Free up to 14 days)',
            ],
            [
                'title' => 'Strict Cancellation (Non-refundable)',
                'slug' => 'strict-cancellation',
                'service_type' => 'accommodation',
                'policy_type' => 'Cancellation Policy',
                'content' => 'Strict Cancellation (Non-refundable)',
            ],
            [
                'title' => 'Non-Refundable',
                'slug' => 'non-refundable',
                'service_type' => 'accommodation',
                'policy_type' => 'Cancellation Policy',
                'content' => 'Non-Refundable',
            ],

            // Accommodation - Security Deposit Policy
            [
                'title' => 'Pet-Friendly Deposit Policy',
                'slug' => 'pet-friendly-deposit-policy',
                'service_type' => 'accommodation',
                'policy_type' => 'Security Deposit Policy',
                'content' => 'Pet-Friendly Deposit Policy',
            ],
            [
                'title' => 'No Deposit Policy',
                'slug' => 'no-deposit-policy',
                'service_type' => 'accommodation',
                'policy_type' => 'Security Deposit Policy',
                'content' => 'No Deposit Policy',
            ],

            // Accommodation - House Rules
            [
                'title' => 'Pet-Friendly House Rules',
                'slug' => 'pet-friendly-house-rules',
                'service_type' => 'accommodation',
                'policy_type' => 'House Rules',
                'content' => 'Pet-Friendly House Rules',
            ],
            [
                'title' => 'Shared Property Rules',
                'slug' => 'shared-property-house-rules',
                'service_type' => 'accommodation',
                'policy_type' => 'House Rules',
                'content' => 'Shared Property Rules',
            ],
            [
                'title' => 'Luxury Property Rules',
                'slug' => 'luxury-property-house-rules',
                'service_type' => 'accommodation',
                'policy_type' => 'House Rules',
                'content' => 'Luxury Property Rules',
            ],

            // Activity - Amendment Policy
            [
                'title' => 'Flexible Amendment Policy',
                'slug' => 'activity-flexible-amendment-policy',
                'service_type' => 'activity',
                'policy_type' => 'Amendment Policy',
                'content' => 'Flexible Amendment Policy',
            ],
            [
                'title' => 'Standard Amendment Policy',
                'slug' => 'activity-standard-amendment-policy',
                'service_type' => 'activity',
                'policy_type' => 'Amendment Policy',
                'content' => 'Standard Amendment Policy',
            ],

            // Activity - Cancellation Policy
            [
                'title' => 'Moderate Cancellation (Free up to 14 days)',
                'slug' => 'activity-moderate-cancellation',
                'service_type' => 'activity',
                'policy_type' => 'Cancellation Policy',
                'content' => 'Moderate Cancellation (Free up to 14 days)',
            ],
            [
                'title' => 'Strict Cancellation (24 hours notice)',
                'slug' => 'activity-strict-cancellation',
                'service_type' => 'activity',
                'policy_type' => 'Cancellation Policy',
                'content' => 'Strict Cancellation (24 hours notice)',
            ],
            [
                'title' => 'Non-Refundable',
                'slug' => 'activity-non-refundable',
                'service_type' => 'activity',
                'policy_type' => 'Cancellation Policy',
                'content' => 'Non-Refundable',
            ],
        ];

        foreach ($templates as $template) {
            PolicyTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                array_merge($template, [
                    'is_active' => true,
                    'created_by' => null,
                ])
            );
        }
    }
}