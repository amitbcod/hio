<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OperatorUser;
use App\Models\Business;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('operator.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $operator = \App\Models\Operator::where('email', $request->email)->first();
        if ($operator && \Illuminate\Support\Facades\Hash::check($request->password, $operator->password_hash)) {
            \Illuminate\Support\Facades\Auth::guard('operator')->login($operator);
            $operatorId = $operator->operator_id;
            $progress = \App\Models\OperatorRegistrationProgress::where('operator_id', $operatorId)->first();
            if ($progress) {
                $step = $progress->current_step ?? 2;
                switch ($step) {
                    case 2:
                        return redirect()->route('operator.register.step2');
                    case 3:
                        return redirect()->route('operator.register.step3');
                    case 4:
                        return redirect()->route('operator.register.step4');
                    case 5:
                        return redirect()->route('operator.register.step5');
                    case 6:
                        return redirect()->route('operator.register.step6');
                    case 7:
                        return redirect()->route('operator.register.step7');
                    case 8:
                        return redirect()->route('operator.register.step8');
                    case 9:
                        return redirect()->route('operator.register.step9');
                    default:
                        return redirect()->route('operator.register.step2');
                }
            }
            // If no progress, start at step 2
            return redirect()->route('operator.register.step2');
        }
        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('operator.login');
    }

    public function showRegistrationForm()
    {
        return view('operator.auth.register');
    }

    public function register(Request $request)
    {
        $rules = [
            'user_type' => 'required|in:Operator,MPO,Agent',
            'business_legal_name' => 'required',
            'country_of_operation' => 'required',
            'agreement_type' => 'required|in:Listing Only,OTO,Widget Only,OTO + Widget,Full Service',
            'is_owner' => 'required|in:yes,no',
            'email' => 'required|email|unique:operator_users,email',
            'phone' => 'required',
            'full_name' => 'required',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@#%!*^$&]/'
            ],
            'terms' => 'accepted',
        ];
        if ($request->is_owner === 'no') {
            $rules['owner_full_name'] = 'required';
            $rules['owner_email'] = 'required|email';
            $rules['owner_phone'] = 'required';
        }
        $request->validate($rules, [
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.'
        ]);
        $operatorId = uniqid('OP');
        $operator = \App\Models\Operator::create([
            'operator_id' => $operatorId,
            'user_type' => $request->user_type,
            'is_owner' => $request->is_owner,
            'email' => $request->email,
            'phone' => $request->phone,
            'full_name' => $request->full_name,
            'business_legal_name' => $request->business_legal_name,
            'agreement_type' => $request->agreement_type,
            'account_status' => 'pending_verification',
            'owner_full_name' => $request->is_owner === 'no' ? $request->owner_full_name : null,
            'owner_email' => $request->is_owner === 'no' ? $request->owner_email : null,
            'owner_phone' => $request->is_owner === 'no' ? $request->owner_phone : null,
            'password_hash' => bcrypt($request->password),
        ]);

        // Create or link a Business record
        if ($request->is_owner === 'yes') {
            $business = Business::create([
                'business_id' => Business::generateBusinessId(),
                'legal_name' => $request->business_legal_name,
                'country' => $request->country_of_operation,
                'primary_contact_email' => $request->email,
                'status' => 'pending',
            ]);
        } else {
            // Try to find an existing business by legal name + country (simple lookup)
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

                // Create controller verification to notify owner for claiming
                if (!empty($request->owner_email)) {
                    $cv = \App\Models\ControllerVerification::create([
                        'token' => \App\Models\ControllerVerification::generateToken(),
                        'business_id' => $business->id,
                        'owner_email' => $request->owner_email,
                        'owner_full_name' => $request->owner_full_name,
                        'requester_operator_id' => $operator->operator_id,
                        'status' => 'pending',
                        'expires_at' => now()->addDays(7),
                    ]);
                    // send email
                    try {
                        \Mail::to($request->owner_email)->send(new \App\Mail\ControllerVerificationRequested($cv));
                    } catch (\Exception $e) {
                        // swallow email errors in dev
                    }
                }
            } else {
                // If existing business exists and non-owner specified owner_email, create verification if needed
                if (!empty($request->owner_email)) {
                    $cv = \App\Models\ControllerVerification::create([
                        'token' => \App\Models\ControllerVerification::generateToken(),
                        'business_id' => $business->id,
                        'owner_email' => $request->owner_email,
                        'owner_full_name' => $request->owner_full_name,
                        'requester_operator_id' => $operator->operator_id,
                        'status' => 'pending',
                        'expires_at' => now()->addDays(7),
                    ]);
                    try {
                        \Mail::to($request->owner_email)->send(new \App\Mail\ControllerVerificationRequested($cv));
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        // Link operator to business
        $operator->business_id = $business->id;
        $operator->save();

        // Optionally, log in the operator or redirect to login page
        // \Illuminate\Support\Facades\Auth::login($operator);
        return redirect()->route('operator.register.step2');
    }
}
