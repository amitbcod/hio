<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use App\Support\CountryAccess;
use Closure;
use Illuminate\Http\Request;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        CountryAccess::clearCache();
        $admin = session('admin_id')
            ? AdminUser::query()->find(session('admin_id'))
            : null;

        if (! $admin || $admin->status !== 'active') {
            session()->forget('admin_id');

            return redirect()->route('admin.login');
        }

        if (CountryAccess::isMpoAdmin()
            && (! $admin->country_id || ! $admin->country?->is_active)) {
            abort(403);
        }

        return $next($request);
    }
}