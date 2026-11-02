<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            ['slug' => 'account', 'name' => 'Account', 'description' => 'Account management module'],
            ['slug' => 'profile', 'name' => 'Profile', 'description' => 'User profile module'],
            ['slug' => 'compliance', 'name' => 'Compliance', 'description' => 'Compliance and legal module'],
            ['slug' => 'users', 'name' => 'Users', 'description' => 'User management module'],
            ['slug' => 'capacity-permissions', 'name' => 'Capacity Permissions and Roles', 'description' => 'Role permissions and delegation configuration'],
            ['slug' => 'collaboration-agreements', 'name' => 'Collaboration Agreements', 'description' => 'Collaboration agreement management'],
            ['slug' => 'reservation', 'name' => 'Reservation', 'description' => 'Reservation and booking module'],
            ['slug' => 'accounting', 'name' => 'Accounting', 'description' => 'Accounting and billing module'],
            ['slug' => 'accommodations', 'name' => 'Accommodations', 'description' => 'Accommodation management module'],
            ['slug' => 'activities', 'name' => 'Activities', 'description' => 'Activity management module'],
            ['slug' => 'transport', 'name' => 'Transport', 'description' => 'Transport management module'],
            ['slug' => 'operations', 'name' => 'Operations', 'description' => 'Operations management module'],
            ['slug' => 'marketing', 'name' => 'Marketing', 'description' => 'Marketing tools and promotions'],
            ['slug' => 'content', 'name' => 'Content', 'description' => 'Content management module'],
            ['slug' => 'support', 'name' => 'Support', 'description' => 'Support ticketing and helpdesk'],
            ['slug' => 'feedback', 'name' => 'Feedback', 'description' => 'User feedback and reviews'],
            ['slug' => 'trips', 'name' => 'Trips', 'description' => 'Country-scoped trip records'],
            ['slug' => 'operators', 'name' => 'Operators', 'description' => 'Country-scoped operator records'],
            ['slug' => 'packages', 'name' => 'Packages', 'description' => 'Country-scoped package records'],
            ['slug' => 'groups', 'name' => 'Groups', 'description' => 'Country-scoped group records'],
            ['slug' => 'accommodation-bookings', 'name' => 'Accommodation Bookings', 'description' => 'Country-scoped accommodation bookings'],
            ['slug' => 'activity-bookings', 'name' => 'Activity Bookings', 'description' => 'Country-scoped activity bookings'],
            ['slug' => 'transport-bookings', 'name' => 'Transport Bookings', 'description' => 'Country-scoped transport bookings'],
            ['slug' => 'payment-transactions', 'name' => 'Payment Transactions', 'description' => 'Read-only country-scoped payment transaction summaries'],
            ['slug' => 'regions', 'name' => 'Regions', 'description' => 'Country-scoped regions'],
            ['slug' => 'transport-service-route-pairs', 'name' => 'Transport Service Route Pairs', 'description' => 'Country-scoped transport routes'],
            ['slug' => 'places', 'name' => 'Hotel / City Mapping', 'description' => 'Country-scoped places and city mappings'],
        ];

        foreach ($modules as $m) {
            Module::updateOrCreate(['slug' => $m['slug']], ['name' => $m['name'], 'description' => $m['description']]);
        }
    }
}
