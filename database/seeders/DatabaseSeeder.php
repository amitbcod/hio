<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /*User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/

        // Admin user for system
        $this->call([\Database\Seeders\AdminUserSeeder::class]);

        // Seed Modules
        try {
            $this->call([\Database\Seeders\ModuleSeeder::class]);
        } catch (\Exception $e) {
            // modules table may not exist yet; run manually after migrate
        }

        // Seed global Roles (if Spatie is installed)
        try {
            $this->call([\Database\Seeders\RoleSeeder::class]);
        } catch (\Exception $e) {
            // roles table may not exist yet; run manually after vendor:publish and migrate
        }

        // Seed permissions for modules and operations (requires Spatie package)
        try {
            $this->call([\Database\Seeders\PermissionsSeeder::class]);
        } catch (\Exception $e) {
            // permissions table may not exist yet; run manually after vendor:publish and migrate
        }

        // Seed accommodation booking report demo data
        try {
            $this->call([\Database\Seeders\AccommodationBookingReportSeeder::class]);
        } catch (\Exception $e) {
            // booking tables may not exist yet; run manually after migrate
        }
    }
}
