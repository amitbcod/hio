<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminUser;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        AdminUser::updateOrCreate([
            'email' => 'admin@example.com'
        ],[
            'name' => 'Super Admin',
            'password_hash' => bcrypt('AdminPass123!'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);
    }
}
