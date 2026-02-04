<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Ensure roles table exists before attempting to seed (Spatie may not be installed in all environments)
        if (!Schema::hasTable('roles')) {
            return;
        }

        $roles = [
            'Admin',
            'Head of Department',
            'Reservation Manager',
            'Operational Manager',
            'Finance Manager',
            'Marketing Manager',
            'Support Manager',
            'Content Manager',
        ];

        foreach ($roles as $roleName) {
            // Use updateOrCreate to be idempotent. Handle environments where the roles table does not have business_id column.
            $attributes = ['name' => $roleName, 'guard_name' => 'web'];
            $values = ['name' => $roleName, 'guard_name' => 'web'];

            if (Schema::hasColumn('roles', 'business_id')) {
                $attributes['business_id'] = null;
                $values['business_id'] = null;
            }

            \Spatie\Permission\Models\Role::updateOrCreate($attributes, $values);
        }
    }
}
