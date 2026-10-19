<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentAccount;
use App\Models\AgentSetupProgress;
use App\Models\AgentStaffUser;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
        $data = $request->validate([
            'agency_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:191',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $email = mb_strtolower(trim((string) $value));
                    if (DB::table('agent_accounts')->whereRaw('LOWER(email) = ?', [$email])->exists()
                        || DB::table('operators')->whereRaw('LOWER(email) = ?', [$email])->exists()
                        || DB::table('mpos')->whereRaw('LOWER(email) = ?', [$email])->exists()
                        || DB::table('operator_users')->whereRaw('LOWER(email) = ?', [$email])->exists()
                        || DB::table('agent_staff_users')->whereRaw('LOWER(email) = ?', [$email])->exists()
                        || DB::table('traveler_accounts')->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                        $fail('This email is already in use. Please use another email address.');
                    }
                },
            ],
            'office_phone' => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'emergency_mobile' => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')->where('is_active', true)],
            'password' => [
                'required', 'confirmed', 'min:8',
                'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[@#%!*^$&]/',
            ],
            'terms' => ['accepted'],
        ], [
            'office_phone.regex' => 'Enter the phone number in E.164 format, including the +country code.',
            'emergency_mobile.regex' => 'Enter the mobile number in E.164 format, including the +country code.',
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
        ]);

        $agent = DB::transaction(function () use ($data): AgentAccount {
            $agent = AgentAccount::query()->create([
                'agent_id' => 'AGT-'.strtoupper(bin2hex(random_bytes(6))),
                'country_id' => $data['country_id'],
                'agency_name' => $data['agency_name'],
                'contact_person' => $data['contact_person'],
                'email' => mb_strtolower(trim($data['email'])),
                'office_phone' => $data['office_phone'],
                'emergency_mobile' => $data['emergency_mobile'],
                'password_hash' => Hash::make($data['password']),
                'account_status' => 'pending',
            ]);

            AgentSetupProgress::query()->create(['agent_account_id' => $agent->id]);

            return $agent;
        });

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
        $agent = AgentAccount::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($agent && Hash::check($data['password'], $agent->password_hash)) {
            if ($agent->account_status === 'suspended') {
                return back()->withErrors(['email' => 'This Agent account is suspended.']);
            }
            Auth::guard('agent_staff')->logout();
            Auth::guard('agent')->login($agent);
            $agent->forceFill(['last_login' => now()])->save();

            return redirect()->route('agent.dashboard');
        }

        $staff = AgentStaffUser::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if ($staff && Hash::check($data['password'], $staff->password_hash) && $staff->status === 'Active') {
            if ($staff->agent?->account_status === 'suspended') {
                return back()->withErrors(['email' => 'This Agent account is suspended.']);
            }

            Auth::guard('agent')->logout();
            Auth::guard('agent_staff')->login($staff);
            $staff->forceFill(['last_login' => now()])->save();

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
