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

        // Try first as OperatorUser (from operator_users table) with role "Head of Department"
        $operatorUser = OperatorUser::where('email', $request->email)->first();
        if ($operatorUser && \Illuminate\Support\Facades\Hash::check($request->password, $operatorUser->password_hash)) {
            // Only allow Head of Department role to login
            if (($operatorUser->role ?? '') !== 'Head of Department') {
                return back()->withErrors(['email' => 'Only Head of Department role can login via this interface.']);
            }

            // Check if user's business exists (no strict status requirement)
            if (!empty($operatorUser->business_id)) {
                $business = Business::find($operatorUser->business_id);
                if (!$business) {
                    return back()->withErrors(['email' => 'Business not found. Please contact support.']);
                }
            } else {
                return back()->withErrors(['email' => 'User is not linked to a business. Please contact support.']);
            }

            // Verify that the Head of Department role has at least read permission on the Users module
            try {
                $roleModel = \Spatie\Permission\Models\Role::where('name', 'Head of Department')
                    ->where('business_id', $operatorUser->business_id)
                    ->first();
                
                if ($roleModel && \Illuminate\Support\Facades\Schema::hasTable('role_module_permissions')) {
                    // Find the Users module and check if role has at least read permission
                    $usersModule = \App\Models\Module::where('slug', 'users')->orWhere('name', 'Users')->first();
                    if ($usersModule) {
                        $hasPermission = \App\Models\RoleModulePermission::where('role_id', $roleModel->id)
                            ->where('module_id', $usersModule->id)
                            ->where('can_read', true)
                            ->exists();
                        
                        if (!$hasPermission) {
                            return back()->withErrors(['email' => 'Your role does not have permission to access required modules.']);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('AuthController::login - permission check failed', ['error' => $e->getMessage()]);
                // Don't block login if permission check fails; log and continue
            }

            // Login using the operator_staff guard for OperatorUser
            \Illuminate\Support\Facades\Auth::guard('operator_staff')->login($operatorUser);

            // Redirect Head of Department users to Users & Staff page
            return redirect()->route('operator.register.step6');
        }

        // Fall back: try to authenticate as Operator (from operators table)
        $operator = \App\Models\Operator::where('email', $request->email)->first();
        if ($operator && \Illuminate\Support\Facades\Hash::check($request->password, $operator->password_hash)) {
            // Block login until account is active (owner must approve non-owner operators)
            if (($operator->account_status ?? '') !== 'active') {
                return back()->withErrors(['email' => 'Account not active. Please wait for owner approval.']);
            }

            \Illuminate\Support\Facades\Auth::guard('operator')->login($operator);

            // If an owner accept flow was started before login, complete it now
            // Support accept token passed either as flashed session or as query parameter
            $acceptToken = session()->pull('accept_token', $request->input('accept_token'));
            if ($acceptToken) {
                try {
                    $token = $acceptToken;
                    $cv = \App\Models\ControllerVerification::where('token', $token)->first();
                    if ($cv && $cv->status === 'pending' && $operator->email === $cv->owner_email) {
                        // complete acceptance
                        $cv->acceptBy($operator);
                        // ensure owner has business_id
                        if (empty($operator->business_id)) {
                            $operator->business_id = $cv->business_id;
                            $operator->save();
                        }
                        // activate requester
                        if ($cv->requester) {
                            $rq = $cv->requester;
                            $rq->account_status = 'active';
                            $rq->save();
                        }
                        return redirect()->route('operator.register.step2')->with('success', 'You have accepted the request. Business is pending admin approval.');
                    }
                } catch (\Exception $e) {
                    \Log::error('AuthController::login - accept after login failed', ['err' => $e->getMessage()]);
                }
            }

            $operatorId = $operator->operator_id;
            // Prefer business-scoped progress when linked
            if (!empty($operator->business_id)) {
                $progress = \App\Models\OperatorRegistrationProgress::where('business_id', $operator->business_id)->first();
            } else {
                $progress = \App\Models\OperatorRegistrationProgress::where('operator_id', $operatorId)->first();
            }
            if ($progress) {
                $step = $progress->current_step ?? 2;
                switch ($step) {
                    case 2:
                        return redirect()->route('operator.register.step2');
                    case 3:
                        // Step 3 (Legal) is now handled in step 2 modal, redirect to step 5 (Collaboration)
                        return redirect()->route('operator.register.step5');
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
        // Logout from both guards
        Auth::guard('operator')->logout();
        Auth::guard('operator_staff')->logout();
        return redirect()->route('operator.login');
    }

    public function showRegistrationForm()
    {
        // Provide available roles to the registration form when permissions are installed
        $roles = collect();

        if (class_exists(\Spatie\Permission\Models\Role::class) && \Illuminate\Support\Facades\Schema::hasTable('roles')) {
            // If `business_id` column is present we want global roles only; otherwise return all roles
            if (\Illuminate\Support\Facades\Schema::hasColumn('roles', 'business_id')) {
                $roles = \Spatie\Permission\Models\Role::whereNull('business_id')->orderBy('name')->get();
            } else {
                $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
            }
        }

        return view('operator.auth.register', compact('roles'));
    }

    public function register(Request $request)
    {
        $rules = [
            'user_type' => 'required|in:Operator,MPO,Agent',
            'business_legal_name' => 'required',
            'country_of_operation' => 'required',
            'is_owner' => 'required|in:yes,no',
            'email' => 'required|email|unique:operator_users,email',
            'phone' => 'required',
            'full_name' => 'required',
            // Role is optional at registration time; if provided it should be a string (we assign roles later in the onboarding flow)
            'role' => 'nullable|string',
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
        if ($request->is_owner === 'yes') {
            $rules['agreement_type'] = 'required|in:Listing Only,OTO,Widget Only,OTO + Widget,Full Service';
        }
        $request->validate($rules, [
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.'
        ]);
        
        if ($request->is_owner === 'no') {
            $verification_status = 'pending_verification';
        }else{
            $verification_status = 'active';
        }
        $operatorId = uniqid('OP');
        $operator = \App\Models\Operator::create([
            'operator_id' => $operatorId,
            'user_type' => $request->user_type,
            'is_owner' => $request->is_owner,
            'email' => $request->email,
            'phone' => $request->phone,
            'full_name' => $request->full_name,
            'business_legal_name' => $request->business_legal_name,
            'account_status' => $verification_status,
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
                'agreement_type' => $request->agreement_type,
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
