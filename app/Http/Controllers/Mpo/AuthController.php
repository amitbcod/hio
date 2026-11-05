<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Business;
use App\Models\Mpo;
use App\Models\MpoStaffUser;
use App\Services\MpoStaffPermissionService;
use App\Services\MpoRegistrationCompletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showRegistrationForm()
    {
        return view('mpo.auth.register');
    }

    public function showLoginForm()
    {
        return view('mpo.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = mb_strtolower(trim($request->input('email')));
        $staffUser = MpoStaffUser::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($staffUser) {
            if (! Hash::check($request->password, $staffUser->password_hash)) {
                return back()->withErrors(['email' => 'Invalid credentials.']);
            }

            $owner = $staffUser->mpo;
            if ($staffUser->status !== 'Active'
                || ! $owner
                || $owner->admin_type !== 'mpo'
                || $owner->status !== 'active'
                || ! $owner->country_id
                || ! $owner->country?->is_active) {
                return back()->withErrors(['email' => 'This MPO staff account is inactive.']);
            }
            if ($staffUser->account_reset_required) {
                return back()->withErrors(['email' => 'Use the password reset link sent to your email before signing in.']);
            }
            $landingRoute = $this->staffLandingRoute($staffUser);
            if ($landingRoute === null) {
                return back()->withErrors(['email' => 'No access has been assigned to this staff account. Contact your MPO administrator.']);
            }

            Auth::guard('mpo')->logout();
            Auth::guard('mpo_staff')->logout();
            Auth::guard('mpo_staff')->login($staffUser);
            session()->forget(['admin_id', 'admin_country_id']);
            session()->regenerate();
            if (! $staffUser->forceFill(['last_login' => now()])->save()) {
                Auth::guard('mpo_staff')->logout();

                return back()->withErrors(['email' => 'Sign-in could not be completed. Please try again.']);
            }

            return redirect()->to($landingRoute);
        }

        $adminMpo = AdminUser::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($adminMpo) {
            if ($adminMpo->admin_type !== 'mpo' || ! Hash::check($request->password, $adminMpo->password_hash)) {
                return back()->withErrors(['email' => 'Invalid credentials.']);
            }

            if ($adminMpo->status !== 'active'
                || ! $adminMpo->country_id
                || ! $adminMpo->country?->is_active) {
                return back()->withErrors(['email' => 'This MPO account is inactive or has no active country assignment.']);
            }

            Auth::guard('mpo')->logout();
            Auth::guard('mpo_staff')->logout();
            session()->forget(['admin_id', 'admin_country_id']);
            Auth::guard('mpo')->login($adminMpo);
            session()->regenerate();

            return redirect()->route(app(MpoRegistrationCompletionService::class)->nextRoute($adminMpo));
        }

        $mpo = Mpo::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($mpo && Hash::check($request->password, $mpo->password_hash)) {
            $isApprovedByAdmin = (bool) ($mpo->admin_approve_flag ?? 0);
            if (($mpo->account_status ?? '') !== 'active' || ! $isApprovedByAdmin) {
                return back()->withErrors(['email' => 'Account is pending admin approval.']);
            }
            session()->forget(['admin_id', 'admin_country_id']);
            Auth::guard('mpo_staff')->logout();
            Auth::guard('mpo')->login($mpo);

            // Handle accept token flow (owner accepting a ControllerVerification)
            $acceptToken = session()->pull('accept_token', $request->input('accept_token'));
            if ($acceptToken) {
                try {
                    $token = $acceptToken;
                    $cv = \App\Models\ControllerVerification::where('token', $token)->first();
                    if ($cv && $cv->status === 'pending' && $mpo->email === $cv->owner_email) {
                        $cv->acceptBy($mpo);
                        if (empty($mpo->business_id)) {
                            $mpo->business_id = $cv->business_id;
                            $mpo->save();
                        }
                        if ($cv->requester) {
                            $rq = $cv->requester;
                            $rq->account_status = 'active';
                            $rq->save();
                        }

                        return redirect()->route('mpo.register.step2')->with('success', 'You have accepted the request. Business is pending admin approval.');
                    }
                } catch (\Exception $e) {
                    \Log::error('Mpo\AuthController::login - accept after login failed', ['err' => $e->getMessage()]);
                }
            }

            return redirect()->route('mpo.register.step2');
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    private function staffLandingRoute(MpoStaffUser $staffUser): ?string
    {
        $permissions = app(MpoStaffPermissionService::class);
        foreach ([
            ['users', 'mpo.users'],
        ] as [$module, $route]) {
            if ($permissions->can($staffUser, $module, 'read')) {
                return route($route);
            }
        }

        if ($permissions->can($staffUser, 'users', 'create')) {
            return route('mpo.users.create');
        }

        foreach (array_keys(config('mpo_staff.registry', [])) as $module) {
            if ($permissions->can($staffUser, $module, 'read')) {
                return route('mpo.operations.module', ['module' => $module]);
            }
        }

        return null;
    }

    public function logout()
    {
        Auth::guard('mpo')->logout();
        Auth::guard('mpo_staff')->logout();
        session()->forget(['admin_id', 'admin_country_id']);

        return redirect()->route('mpo.login');
    }

    public function register(Request $request)
    {
        $rules = [
            'business_legal_name' => 'required',
            'country_of_operation' => 'required',
            'is_owner' => 'required|in:yes,no',
            'email' => 'required|email|unique:mpos,email',
            'phone' => 'required',
            'full_name' => 'required',
            'password' => 'required|confirmed|min:8',
            'terms' => 'accepted',
        ];
        if ($request->is_owner === 'no') {
            $rules['owner_full_name'] = 'required';
            $rules['owner_email'] = 'required|email';
            $rules['owner_phone'] = 'required';
        }
        $request->validate($rules);

        $verification_status = 'pending_verification';
        $mpoId = uniqid('MP');
        $mpo = Mpo::create([
            'mpo_id' => $mpoId,
            'is_owner' => $request->is_owner,
            'email' => $request->email,
            'phone' => $request->phone,
            'full_name' => $request->full_name,
            'business_legal_name' => $request->business_legal_name,
            'account_status' => $verification_status,
            'admin_approve_flag' => 0,
            'owner_full_name' => $request->is_owner === 'no' ? $request->owner_full_name : null,
            'owner_email' => $request->is_owner === 'no' ? $request->owner_email : null,
            'owner_phone' => $request->is_owner === 'no' ? $request->owner_phone : null,
            'password_hash' => bcrypt($request->password),
        ]);

        // Create or link Business record similar to operator flow
        if ($request->is_owner === 'yes') {
            $business = Business::create([
                'business_id' => Business::generateBusinessId(),
                'legal_name' => $request->business_legal_name,
                'country' => $request->country_of_operation,
                'primary_contact_email' => $request->email,
                'status' => 'pending',
            ]);
        } else {
            $business = Business::where('legal_name', $request->business_legal_name)
                ->where('country', $request->country_of_operation)
                ->first();
            if (! $business) {
                $business = Business::create([
                    'business_id' => Business::generateBusinessId(),
                    'legal_name' => $request->business_legal_name,
                    'country' => $request->country_of_operation,
                    'primary_contact_email' => $request->owner_email ?? $request->email,
                    'status' => 'pending',
                ]);

                if (! empty($request->owner_email)) {
                    $cv = \App\Models\ControllerVerification::create([
                        'token' => \App\Models\ControllerVerification::generateToken(),
                        'business_id' => $business->id,
                        'owner_email' => $request->owner_email,
                        'owner_full_name' => $request->owner_full_name,
                        'requester_operator_id' => $mpo->mpo_id,
                        'status' => 'pending',
                        'expires_at' => now()->addDays(7),
                    ]);

                    try {
                        Mail::to($request->owner_email)->send(new \App\Mail\ControllerVerificationRequested($cv));
                    } catch (\Exception $e) {
                        \Log::error('MPO verification email failed for non-owner registration', [
                            'mpo_email' => $request->email,
                            'owner_email' => $request->owner_email,
                            'business_id' => $business->id,
                            'exception' => $e->getMessage(),
                        ]);

                        return back()->withErrors([
                            'owner_email' => 'The owner verification email could not be sent. Please check the owner email or SMTP configuration.',
                        ])->withInput();
                    }
                }
            } else {
                if (! empty($request->owner_email)) {
                    $cv = \App\Models\ControllerVerification::create([
                        'token' => \App\Models\ControllerVerification::generateToken(),
                        'business_id' => $business->id,
                        'owner_email' => $request->owner_email,
                        'owner_full_name' => $request->owner_full_name,
                        'requester_operator_id' => $mpo->mpo_id,
                        'status' => 'pending',
                        'expires_at' => now()->addDays(7),
                    ]);
                    try {
                        Mail::to($request->owner_email)->send(new \App\Mail\ControllerVerificationRequested($cv));
                    } catch (\Exception $e) {
                        \Log::error('MPO verification email failed for existing business registration', [
                            'mpo_email' => $request->email,
                            'owner_email' => $request->owner_email,
                            'business_id' => $business->id,
                            'exception' => $e->getMessage(),
                        ]);

                        return back()->withErrors([
                            'owner_email' => 'The owner verification email could not be sent. Please check the owner email or SMTP configuration.',
                        ])->withInput();
                    }
                }
            }
        }

        // Link mpo to business
        $mpo->business_id = $business->id;
        $mpo->save();

        return redirect()->route('mpo.register.step2');
    }
}
