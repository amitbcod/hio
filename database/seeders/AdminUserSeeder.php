<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminUser;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        AdminUser::updateOrCreate([
            'email' => 'amit29592@gmail.com'
        ],[
            'name' => 'Super Admin',
            'password_hash' => bcrypt('Admin@999999'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);
    }
}
