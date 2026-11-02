<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use App\Models\Mpo;
use App\Models\MpoStaffUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MpoPortalAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $staffUser = Auth::guard('mpo_staff')->user();
        if ($staffUser instanceof MpoStaffUser) {
            $mpoAdmin = $staffUser->mpo;
            if ($staffUser->status !== 'Active'
                || $staffUser->account_reset_required
                || ! $mpoAdmin
                || $mpoAdmin->admin_type !== 'mpo'
                || $mpoAdmin->status !== 'active'
                || ! $mpoAdmin->country_id
                || ! $mpoAdmin->country?->is_active) {
                Auth::guard('mpo_staff')->logout();
                Auth::guard('mpo')->logout();

                return redirect()->route('mpo.login')
                    ->withErrors(['email' => 'This MPO staff account is inactive or requires a password reset.']);
            }

            $request->attributes->set('mpo_admin', $mpoAdmin->loadMissing(['country', 'compliance']));
            $request->attributes->set('mpo_staff_user', $staffUser);

            return $next($request);
        }

        $mpoUser = Auth::guard('mpo')->user();

        if ($mpoUser instanceof Mpo) {
            return redirect()->route('mpo.register.step2');
        }

        if (! ($mpoUser instanceof AdminUser)
            || $mpoUser->admin_type !== 'mpo'
            || $mpoUser->status !== 'active'
            || ! $mpoUser->country_id
            || ! $mpoUser->country?->is_active) {
            Auth::guard('mpo')->logout();

            return redirect()->route('mpo.login');
        }

        $mpoAdmin = $mpoUser->loadMissing(['country', 'compliance']);

        $request->attributes->set('mpo_admin', $mpoAdmin);

        return $next($request);
    }
}
