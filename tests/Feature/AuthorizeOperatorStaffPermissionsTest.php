<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthorizeOperatorStaffPermissions;
use App\Models\OperatorUser;
use App\Services\OperatorStaffPermissionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AuthorizeOperatorStaffPermissionsTest extends TestCase
{
    public function test_every_protected_operator_route_is_mapped_or_explicitly_owner_only(): void
    {
        $permissionService = \Mockery::mock(OperatorStaffPermissionService::class)->makePartial();
        $permissionService->shouldReceive('can')->andReturn(true);
        $middleware = new AuthorizeOperatorStaffPermissions($permissionService);
        $actor = new OperatorUser(['id' => 1]);
        $protectedRoutes = collect(app('router')->getRoutes())->filter(
            fn ($route): bool => in_array(
                AuthorizeOperatorStaffPermissions::class,
                $route->gatherMiddleware(),
                true,
            )
        );

        $this->assertGreaterThan(100, $protectedRoutes->count());

        foreach ($protectedRoutes as $route) {
            $methods = $route->methods();
            $method = in_array('GET', $methods, true) ? 'GET' : $methods[0];
            $request = Request::create('/'.ltrim($route->uri(), '/'), $method);
            $request->setUserResolver(fn (?string $guard = null) => $actor);
            $route->bind($request);
            foreach ($route->parameterNames() as $parameter) {
                $route->setParameter($parameter, $parameter === 'serviceType' ? 'activity' : 1);
            }
            $request->setRouteResolver(fn () => $route);
            $routeUri = trim($route->uri(), '/');
            $isOwnerOnly = str_starts_with($routeUri, 'operator/roles')
                || ($routeUri === 'operator/register')
                || (str_starts_with($routeUri, 'operator/register/')
                    && ! str_starts_with($routeUri, 'operator/register/step6-users'))
                || in_array($routeUri, [
                    'operator/manage/operators',
                    'operator/pending-approval',
                    'operator/status-submit',
                ], true);

            try {
                $response = $middleware->handle($request, fn () => response()->noContent());
                $this->assertFalse($isOwnerOnly, "Owner-only route was available to staff: {$routeUri}");
                $this->assertSame(204, $response->getStatusCode(), "Route failed: {$routeUri}");
            } catch (HttpException $exception) {
                $this->assertTrue($isOwnerOnly, "Unmapped route was rejected: {$routeUri}");
                $this->assertSame(403, $exception->getStatusCode(), "Unexpected denial: {$routeUri}");
            }
        }
    }
}
