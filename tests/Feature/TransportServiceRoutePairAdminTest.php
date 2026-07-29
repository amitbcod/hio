<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class TransportServiceRoutePairAdminTest extends TestCase
{
    public function test_admin_update_route_accepts_put_patch_and_post_methods()
    {
        $route = Route::getRoutes()->getByName('admin.transport-service-route-pairs.update');

        $this->assertNotNull($route);
        $this->assertTrue(in_array('PUT', $route->methods(), true));
        $this->assertTrue(in_array('PATCH', $route->methods(), true));
        $this->assertTrue(in_array('POST', $route->methods(), true));
    }
}
