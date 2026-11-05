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
        if (! session('admin_id') && Auth::guard('mpo_staff')->check()) {
            return redirect()->route('mpo.profile');
        }

        $admin = session('admin_id')
            ? AdminUser::query()->find(session('admin_id'))
            : Auth::guard('mpo')->user();

        if (! $admin instanceof AdminUser || $admin->status !== 'active') {
            session()->forget('admin_id');

            return redirect()->route('admin.login');
        }

        if ($admin->admin_type === 'mpo') {
            if (! $admin->country_id || ! $admin->country?->is_active) {
                session()->forget(['admin_id', 'admin_country_id']);

                return redirect()->route('admin.login')
                    ->withErrors(['email' => 'This MPO account has no active country assignment.']);
            }

            $request->attributes->set('mpo_admin', $admin);
            Auth::guard('mpo')->login($admin);

            $completion = app(\App\Services\MpoRegistrationCompletionService::class);
            if ($request->routeIs('admin.dashboard')) {
                return redirect()->route($completion->nextRoute($admin));
            }

            if (! \App\Support\MpoAdminRouteAccess::allows($request)) {
                return redirect()->route('admin.login');
            }

            if (! $completion->state($admin)['complete']) {
                return redirect()->route($completion->nextRoute($admin))
                    ->with('warning', 'Complete all mandatory MPO onboarding steps before accessing country administration.');
            }

            session(['admin_id' => $admin->id]);

            return $next($request);
        }

        return $next($request);
    }
}
