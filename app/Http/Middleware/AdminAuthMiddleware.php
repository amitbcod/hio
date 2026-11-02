<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use App\Support\CountryAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        CountryAccess::clearCache();
        if (! session('admin_id')) {
            $mpo = Auth::guard('mpo')->user();
            if (Auth::guard('mpo_staff')->check()
                || ($request->routeIs('admin.dashboard') && $mpo instanceof AdminUser && $mpo->admin_type === 'mpo')) {
                return redirect()->route('mpo.profile');
            }
        }

        $admin = session('admin_id')
            ? AdminUser::query()->find(session('admin_id'))
            : null;

        if (! $admin || $admin->status !== 'active') {
            session()->forget('admin_id');

            return redirect()->route('admin.login');
        }

        if ($admin->admin_type === 'mpo') {
            if (! $admin->country_id || ! $admin->country?->is_active) {
                session()->forget(['admin_id', 'admin_country_id']);

                return redirect()->route('admin.login')
                    ->withErrors(['email' => 'This MPO account has no active country assignment.']);
            }

            session()->forget(['admin_id', 'admin_country_id']);
            Auth::guard('mpo')->login($admin);

            return redirect()->route('mpo.profile');
        }

        return $next($request);
    }
}
