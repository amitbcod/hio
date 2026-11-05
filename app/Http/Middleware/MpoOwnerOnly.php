<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;

class MpoOwnerOnly
{
    public function handle(Request $request, Closure $next)
    {
        $mpoAdmin = $request->attributes->get('mpo_admin');

        abort_unless(
            $mpoAdmin instanceof AdminUser
                && $mpoAdmin->admin_type === 'mpo'
                && ! $request->attributes->has('mpo_staff_user'),
            403,
        );

        return $next($request);
    }
}
