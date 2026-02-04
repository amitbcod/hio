<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        // If Spatie package isn't installed or migrations not run, skip seeding
        if (!class_exists(\Spatie\Permission\Models\Permission::class) || !\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
            return;
        }

        $modules = ['Account','Profile','Compliance','Users','Reservation','Accounting','Operations','Marketing','Content','Support','Feedback'];
        $operations = ['read','create','update','approve','publish'];

        foreach ($modules as $module) {
            foreach ($operations as $op) {
                $name = strtolower($module) . '.' . $op;
                try {
                    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                } catch (\Exception $e) {
                    // ignore if permission table not present yet
                }
            }
        }
    }
}
