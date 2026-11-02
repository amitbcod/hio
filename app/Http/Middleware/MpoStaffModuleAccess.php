<?php

namespace App\Http\Middleware;

use App\Models\MpoStaffUser;
use App\Services\MpoStaffPermissionService;
use Closure;
use Illuminate\Http\Request;

class MpoStaffModuleAccess
{
    public function __construct(private readonly MpoStaffPermissionService $permissions) {}

    public function handle(Request $request, Closure $next, string $module, string $action = 'read')
    {
        $staffUser = $request->attributes->get('mpo_staff_user');
        if ($staffUser instanceof MpoStaffUser) {
            abort_unless($this->permissions->can($staffUser, $module, $action), 403);
        }

        return $next($request);
    }
}
