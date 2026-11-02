<?php

namespace App\Http\Middleware;

use App\Models\MpoStaffUser;
use App\Services\MpoStaffPermissionService;
use Closure;
use Illuminate\Http\Request;

class MpoStaffOperationalAccess
{
    public function __construct(private readonly MpoStaffPermissionService $permissions) {}

    public function handle(Request $request, Closure $next)
    {
        $staffUser = $request->attributes->get('mpo_staff_user');
        $module = (string) $request->route('module');
        abort_unless($staffUser instanceof MpoStaffUser, 403);
        abort_unless(array_key_exists($module, config('mpo_staff.registry', [])), 404);
        abort_unless(in_array($request->method(), ['GET', 'HEAD'], true), 403);
        abort_unless($this->permissions->can($staffUser, $module, 'read'), 403);

        return $next($request);
    }
}
