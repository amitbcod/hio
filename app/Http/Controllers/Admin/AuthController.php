<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = AdminUser::where('email', $request->email)->first();
        if ($admin && \Illuminate\Support\Facades\Hash::check($request->password, $admin->password_hash)) {
            if ($admin->status !== 'active'
                || ($admin->admin_type === 'mpo'
                    && (! $admin->country_id || ! $admin->country?->is_active))) {
                return back()->withErrors(['email' => 'This administrator account is inactive or has no active country assignment.']);
            }

            Auth::guard('mpo')->logout();
            Auth::guard('mpo_staff')->logout();
            if ($admin->admin_type === 'mpo') {
                session()->forget(['admin_id', 'admin_country_id']);
                session()->regenerate();
                Auth::guard('mpo')->login($admin);

                return redirect()->route(app(\App\Services\MpoRegistrationCompletionService::class)->nextRoute($admin));
            }

            session()->regenerate();
            session([
                'admin_id' => $admin->id,
                'admin_country_id' => null,
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    public function logout()
    {
        Auth::guard('mpo')->logout();
        Auth::guard('mpo_staff')->logout();
        session()->forget(['admin_id', 'admin_country_id']);

        return redirect()->route('admin.login');
    }
}
