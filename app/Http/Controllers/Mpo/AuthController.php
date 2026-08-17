<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mpo;
use App\Models\Business;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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

        $mpo = Mpo::where('email', $request->email)->first();
        if ($mpo && Hash::check($request->password, $mpo->password_hash)) {
            $isApprovedByAdmin = (bool) ($mpo->admin_approve_flag ?? 0);
            if (($mpo->account_status ?? '') !== 'active' || !$isApprovedByAdmin) {
                return back()->withErrors(['email' => 'Account is pending admin approval.']);
            }
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

    public function logout()
    {
        Auth::guard('mpo')->logout();
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
            if (!$business) {
                $business = Business::create([
                    'business_id' => Business::generateBusinessId(),
                    'legal_name' => $request->business_legal_name,
                    'country' => $request->country_of_operation,
                    'primary_contact_email' => $request->owner_email ?? $request->email,
                    'status' => 'pending',
                ]);

                if (!empty($request->owner_email)) {
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
                            'owner_email' => 'The owner verification email could not be sent. Please check the owner email or SMTP configuration.'
                        ])->withInput();
                    }
                }
            } else {
                if (!empty($request->owner_email)) {
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
                            'owner_email' => 'The owner verification email could not be sent. Please check the owner email or SMTP configuration.'
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
