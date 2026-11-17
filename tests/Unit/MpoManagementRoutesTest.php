<?php

namespace Tests\Unit;

use App\Support\MpoManagementRoutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\GlobalAdminMiddleware;
use Tests\TestCase;

class MpoManagementRoutesTest extends TestCase
{
    public function test_route_names_switch_only_inside_the_mpo_group_and_package_routes(): void
    {
        $originalRequest = app('request');
        $mpoRoute = Route::getRoutes()->getByName('mpo.groups.index');
        $request = Request::create('/mpo/groups', 'GET');
        $request->setRouteResolver(fn () => $mpoRoute);
        app()->instance('request', $request);

        try {
            $this->assertSame('mpo.groups.step2', MpoManagementRoutes::name('admin.groups.step2'));
            $this->assertSame('mpo.packages.index', MpoManagementRoutes::name('admin.packages.index'));

            $request->setRouteResolver(fn () => Route::getRoutes()->getByName('mpo.dashboard'));
            $this->assertSame('admin.groups.index', MpoManagementRoutes::name('admin.groups.index'));
        } finally {
            app()->instance('request', $originalRequest);
        }
    }

    public function test_mpo_management_routes_use_mpo_auth_and_module_permissions(): void
    {
        $routes = Route::getRoutes();
        $this->assertRouteMiddleware($routes->getByName('mpo.groups.index'), 'groups,read');
        $this->assertRouteMiddleware($routes->getByName('mpo.groups.create'), 'groups,create');
        $this->assertRouteMiddleware($routes->getByName('mpo.groups.update'), 'groups,update');
        $this->assertRouteMiddleware($routes->getByName('mpo.packages.index'), 'packages,read');
        $this->assertRouteMiddleware($routes->getByName('mpo.packages.create'), 'packages,create');
        $this->assertRouteMiddleware($routes->getByName('mpo.packages.update'), 'packages,update');

        foreach (['admin.groups.create', 'admin.packages.create'] as $adminRouteName) {
            $adminRoute = $routes->getByName($adminRouteName);
            $this->assertNotNull($adminRoute);
            $this->assertContains(GlobalAdminMiddleware::class, $adminRoute->gatherMiddleware());
            $this->assertNotContains(\App\Http\Middleware\MpoPortalAuthMiddleware::class, $adminRoute->gatherMiddleware());
        }
    }

    private function assertRouteMiddleware(?\Illuminate\Routing\Route $route, string $moduleAction): void
    {
        $this->assertNotNull($route);
        $middleware = $route->gatherMiddleware();
        $this->assertContains(\App\Http\Middleware\MpoPortalAuthMiddleware::class, $middleware);
        $this->assertContains('App\\Http\\Middleware\\MpoStaffModuleAccess:'.$moduleAction, $middleware);
    }
}
