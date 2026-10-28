<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentAccount;
use App\Models\AgentSetupProgress;
use App\Models\AgentStaffUser;
use App\Models\Business;
use App\Models\Country;
use App\Services\AgentPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('agent.auth.register', [
            'countries' => Country::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function register(Request $request)
    {
        $emailIsAvailable = static function (string $email): bool {
            return !(
                DB::table('agent_accounts')->whereRaw('LOWER(email) = ?', [$email])->exists()
                || DB::table('operators')->whereRaw('LOWER(email) = ?', [$email])->exists()
                || DB::table('mpos')->whereRaw('LOWER(email) = ?', [$email])->exists()
                || DB::table('operator_users')->whereRaw('LOWER(email) = ?', [$email])->exists()
                || DB::table('agent_staff_users')->whereRaw('LOWER(email) = ?', [$email])->exists()
                || DB::table('traveler_accounts')->whereRaw('LOWER(email) = ?', [$email])->exists()
            );
        };
        $rules = [
            'agency_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'is_owner' => ['required', Rule::in(['yes', 'no'])],
            'email' => ['required', 'email', 'max:191'],
            'office_phone' => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'emergency_mobile' => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')->where('is_active', true)],
            'password' => [
                'required', 'confirmed', 'min:8',
                'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[@#%!*^$&]/',
            ],
            'terms' => ['accepted'],
            'applicant_email' => [
                'required', 'email', 'max:191',
                function (string $attribute, mixed $value, \Closure $fail) use ($emailIsAvailable): void {
                    if (!$emailIsAvailable(mb_strtolower(trim((string) $value)))) {
                        $fail('This email is already in use. Please use another email address.');
                    }
                },
            ],
            'applicant_mobile' => [
                'required', 'string', 'max:25',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $normalized = preg_replace('/[\s()\-]/', '', (string) $value);
                    if (!is_string($normalized) || !preg_match('/^\+[1-9]\d{7,14}$/', $normalized)) {
                        $fail('Enter the phone number in E.164 format, including the +country code.');
                    }
                },
            ],
            'applicant_full_name' => ['required', 'string', 'max:255'],
            'applicant_role' => ['required', Rule::in(['Manager', 'Consultant', 'Accountant', 'Support'])],
        ];

        if ($request->input('is_owner') === 'no') {
            $rules += [
                'owner_full_name' => ['required', 'string', 'max:255'],
                'owner_email' => ['required', 'email', 'max:191'],
                'owner_phone' => [
                    'required', 'string', 'max:25',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        $normalized = preg_replace('/[\s()\-]/', '', (string) $value);
                        if (!is_string($normalized) || !preg_match('/^\+[1-9]\d{7,14}$/', $normalized)) {
                            $fail('Enter the phone number in E.164 format, including the +country code.');
                        }
                    },
                ],
            ];
        }

        $data = $request->validate($rules, [
            'office_phone.regex' => 'Enter the phone number in E.164 format, including the +country code.',
            'emergency_mobile.regex' => 'Enter the mobile number in E.164 format, including the +country code.',
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
        ]);

        [$agent, $verification] = DB::transaction(function () use ($data): array {
            $country = Country::query()->findOrFail($data['country_id']);
            $loginEmail = mb_strtolower(trim($data['applicant_email']));
            $agencyEmail = mb_strtolower(trim($data['email']));
            $applicantMobile = preg_replace('/[\s()\-]/', '', $data['applicant_mobile']);
            $ownerFullName = $data['is_owner'] === 'yes' ? $data['applicant_full_name'] : $data['owner_full_name'];
            $ownerEmail = $data['is_owner'] === 'yes' ? $loginEmail : mb_strtolower(trim($data['owner_email']));
            $ownerPhone = $data['is_owner'] === 'yes'
                ? $applicantMobile
                : preg_replace('/[\s()\-]/', '', $data['owner_phone']);
            $business = $data['is_owner'] === 'yes'
                ? Business::query()->create([
                    'business_id' => Business::generateBusinessId(),
                    'legal_name' => $data['agency_name'],
                    'country' => $country->name,
                    'primary_contact_email' => $agencyEmail,
                    'status' => 'pending',
                ])
                : Business::query()->firstOrCreate(
                    ['legal_name' => $data['agency_name'], 'country' => $country->name],
                    [
                        'business_id' => Business::generateBusinessId(),
                        'primary_contact_email' => $agencyEmail,
                        'status' => 'pending',
                    ]
                );

            $agent = AgentAccount::query()->create([
                'agent_id' => 'AGT-'.strtoupper(bin2hex(random_bytes(6))),
                'country_id' => $data['country_id'],
                'business_id' => $business->id,
                'is_owner' => $data['is_owner'],
                'owner_full_name' => $ownerFullName,
                'owner_email' => $ownerEmail,
                'owner_phone' => $ownerPhone,
                'agency_email' => $agencyEmail,
                'agency_name' => $data['agency_name'],
                'contact_person' => $data['contact_person'],
                'email' => $loginEmail,
                'office_phone' => $data['office_phone'],
                'emergency_mobile' => $data['emergency_mobile'],
                'password_hash' => Hash::make(Str::random(64)),
                'account_status' => $data['is_owner'] === 'no' ? 'owner_approval_pending' : 'pending',
            ]);

            AgentSetupProgress::query()->create(['agent_account_id' => $agent->id]);

            $verification = null;
            $applicantUser = AgentStaffUser::query()->create([
                'user_id' => 'AGU-'.strtoupper(bin2hex(random_bytes(6))),
                'agent_account_id' => $agent->id,
                'full_name' => $data['applicant_full_name'],
                'email' => $loginEmail,
                'password_hash' => Hash::make($data['password']),
                'mobile' => $applicantMobile,
                'role' => $data['applicant_role'],
                'status' => 'Active',
                'account_reset_required' => false,
            ]);
            app(AgentPermissionService::class)->applyRoleDefaults($applicantUser);

            if ($data['is_owner'] === 'no') {
                $verification = \App\Models\ControllerVerification::query()->create([
                    'token' => \App\Models\ControllerVerification::generateToken(),
                    'business_id' => $business->id,
                    'owner_email' => $ownerEmail,
                    'owner_full_name' => $ownerFullName,
                    'requester_agent_account_id' => $agent->id,
                    'requester_agent_staff_user_id' => $applicantUser->id,
                    'status' => 'pending',
                    'expires_at' => now()->addDays(7),
                ]);
            }

            return [$agent, $verification];
        });

        if ($verification) {
            try {
                Mail::to($verification->owner_email)->send(new \App\Mail\ControllerVerificationRequested($verification));
            } catch (\Throwable $exception) {
                Log::error('Agent owner verification email could not be sent.', [
                    'agent_account_id' => $agent->id,
                    'verification_id' => $verification->id,
                    'error' => $exception->getMessage(),
                ]);

                return redirect()->route('agent.login')
                    ->with('error', 'Your registration was saved, but the owner approval email could not be sent. Please contact support before continuing.');
            }

            return redirect()->route('agent.login')
                ->with('success', 'Registration received. Sign-in and setup will be available after the business owner approves your request.');
        }

        return redirect()->route('agent.login')
            ->with('success', 'Registration received. Sign in to complete your Agent setup while your account is pending review.');
    }

    public function showLogin()
    {
        return view('agent.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $email = mb_strtolower(trim($data['email']));
        $staff = AgentStaffUser::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if ($staff && Hash::check($data['password'], $staff->password_hash) && $staff->status === 'Active') {
            if ($staff->agent?->account_status === 'suspended') {
                return back()->withErrors(['email' => 'This Agent account is suspended.']);
            }
            if (in_array($staff->agent?->account_status, ['owner_approval_pending', 'owner_rejected'], true)) {
                return back()->withErrors(['email' => 'The associated Agent account is not available until business owner approval is complete.']);
            }

            Auth::guard('agent')->logout();
            Auth::guard('agent_staff')->login($staff);
            $staff->forceFill(['last_login' => now()])->save();

            return redirect()->route('agent.dashboard');
        }

        $agent = AgentAccount::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if ($agent && Hash::check($data['password'], $agent->password_hash)) {
            if ($agent->account_status === 'suspended') {
                return back()->withErrors(['email' => 'This Agent account is suspended.']);
            }
            if ($agent->account_status === 'owner_approval_pending') {
                return back()->withErrors(['email' => 'Your account is pending business owner approval.']);
            }
            if ($agent->account_status === 'owner_rejected') {
                return back()->withErrors(['email' => 'The business owner rejected this registration. Please contact support if you believe this is an error.']);
            }
            Auth::guard('agent_staff')->logout();
            Auth::guard('agent')->login($agent);
            $agent->forceFill(['last_login' => now()])->save();

            return redirect()->route('agent.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout()
    {
        Auth::guard('agent')->logout();
        Auth::guard('agent_staff')->logout();

        return redirect()->route('agent.login');
    }
}
