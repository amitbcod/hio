<?php

namespace App\Http\Middleware;

use App\Models\AgentStaffUser;
use App\Services\AgentPermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentModuleAccess
{
    public function __construct(private readonly AgentPermissionService $permissions) {}

    public function handle(Request $request, Closure $next, string $module, string $action = 'read'): Response
    {
        $staff = $request->user('agent_staff');
        if (! $staff instanceof AgentStaffUser || $this->permissions->can($staff, $module, $action)) {
            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}
