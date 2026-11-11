<?php

namespace App\Http\Middleware;

use App\Models\Operator;
use App\Models\OperatorUser;
use App\Services\OperatorStaffPermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeOperatorStaffPermissions
{
    public function __construct(private readonly OperatorStaffPermissionService $permissions) {}

    public function handle(Request $request, Closure $next): Response
    {
        $actor = $request->user('operator') ?? $request->user('operator_staff');
        if (! $actor instanceof OperatorUser) {
            return $next($request);
        }

        $routeName = (string) $request->route()?->getName();
        $routeUri = $this->routeUri($request);
        if (str_starts_with($routeName, 'operator.roles.')
            || $routeUri === 'operator/roles'
            || str_starts_with($routeUri, 'operator/roles/')) {
            abort(403, 'Only the main Operator can configure roles.');
        }
        if ((str_starts_with($routeName, 'operator.register.')
                || str_starts_with($routeUri, 'operator/register/'))
            && ! str_starts_with($routeUri, 'operator/register/step6-users')) {
            abort(403, 'Operator staff cannot access the main account registration steps.');
        }
        if (in_array($routeUri, [
            'operator/manage/operators',
            'operator/pending-approval',
            'operator/status-submit',
        ], true)) {
            abort(403, 'Only the main Operator can access this account-management page.');
        }

        $permission = $this->permissions->permissionForRoute(
            $routeName,
            $request->method(),
            $request->route()?->parameters() ?? [],
            $routeUri,
        );
        if ($permission === null) {
            abort(403, 'No Operator permission is configured for this route.');
        }

        abort_unless($this->permissions->can($actor, $permission[0], $permission[1]), 403);

        return $next($request);
    }

    private function routeUri(Request $request): string
    {
        return trim((string) $request->route()?->uri(), '/');
    }
}
