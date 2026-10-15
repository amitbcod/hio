<?php

namespace App\Http\Middleware;

use App\Support\CountryAccess;
use Closure;
use Illuminate\Http\Request;

class GlobalAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(CountryAccess::isGlobalAdmin(), 403);

        return $next($request);
    }
}
