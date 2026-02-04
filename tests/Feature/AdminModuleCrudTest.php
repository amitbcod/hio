<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Module;
use App\Models\AdminUser;

class AdminModuleCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_crud_modules()
    {
        // create admin session (we use simple session guard in app)
        $admin = AdminUser::create([ 'email' => 'admin@example.com', 'name' => 'Admin', 'password_hash' => bcrypt('Password123!') ]);
        $this->withSession(['admin_id' => $admin->id]);

        // create
        $response = $this->post(route('admin.modules.store'), [
            'name' => 'Reporting',
            'slug' => 'reporting',
            'description' => 'Reports',
        ]);
        $response->assertRedirect(route('admin.modules.index'));
        $this->assertDatabaseHas('modules', ['slug' => 'reporting']);

        $module = Module::where('slug', 'reporting')->first();

        // edit
        $response = $this->post(route('admin.modules.update', $module->id), [
            'name' => 'Reporting Updated',
            'slug' => 'reporting',
            'description' => 'Reports v2',
        ]);
        $response->assertRedirect(route('admin.modules.index'));
        $this->assertDatabaseHas('modules', ['name' => 'Reporting Updated']);

        // delete
        $response = $this->delete(route('admin.modules.destroy', $module->id));
        $response->assertRedirect(route('admin.modules.index'));
        $this->assertDatabaseMissing('modules', ['slug' => 'reporting']);
    }
}
