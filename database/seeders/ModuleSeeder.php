<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            ['slug' => 'account', 'name' => 'Account', 'description' => 'Account management module'],
            ['slug' => 'profile', 'name' => 'Profile', 'description' => 'User profile module'],
            ['slug' => 'compliance', 'name' => 'Compliance', 'description' => 'Compliance and legal module'],
            ['slug' => 'users', 'name' => 'Users', 'description' => 'User management module'],
            ['slug' => 'reservation', 'name' => 'Reservation', 'description' => 'Reservation and booking module'],
            ['slug' => 'accounting', 'name' => 'Accounting', 'description' => 'Accounting and billing module'],
            ['slug' => 'operations', 'name' => 'Operations', 'description' => 'Operations management module'],
            ['slug' => 'marketing', 'name' => 'Marketing', 'description' => 'Marketing tools and promotions'],
            ['slug' => 'content', 'name' => 'Content', 'description' => 'Content management module'],
            ['slug' => 'support', 'name' => 'Support', 'description' => 'Support ticketing and helpdesk'],
            ['slug' => 'feedback', 'name' => 'Feedback', 'description' => 'User feedback and reviews'],
        ];

        foreach ($modules as $m) {
            Module::updateOrCreate(['slug' => $m['slug']], ['name' => $m['name'], 'description' => $m['description']]);
        }
    }
}
