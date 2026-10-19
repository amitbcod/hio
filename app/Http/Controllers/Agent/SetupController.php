<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentAccount;
use App\Models\AgentComplianceDocument;
use App\Models\AgentSetupProgress;
use App\Models\AgentStaffUser;
use App\Services\AgentPermissionService;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SetupController extends Controller
{
    public function dashboard(Request $request)
    {
        $agent = $this->agent($request);
        $progress = $agent->setupProgress()->firstOrCreate([]);
        if ($request->user('agent_staff')) {
            return view('agent.dashboard', compact('agent', 'progress'));
        }

        foreach ([
            'profile_complete' => 'agent.profile',
            'compliance_complete' => 'agent.compliance',
            'users_complete' => 'agent.users',
            'operations_complete' => 'agent.operations',
        ] as $field => $route) {
            if (! $progress->{$field}) {
                return redirect()->route($route);
            }
        }

        return view('agent.dashboard', compact('agent', 'progress'));
    }

    public function profile(Request $request)
    {
        $agent = $this->agent($request);

        return view('agent.setup.profile', ['agent' => $agent, 'profile' => $agent->profile]);
    }

    public function saveProfile(Request $request)
    {
        $agent = $this->agent($request);
        $data = $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:191'],
            'agency_type' => ['nullable', Rule::in(['Travel Agency', 'Tour Operator', 'Corporate Travel Desk'])],
            'focus_segments' => ['nullable', 'array'],
            'focus_segments.*' => ['string', Rule::in(['Accommodation', 'Transport', 'Activities', 'Packages'])],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $profile = $agent->profile()->firstOrNew();
        $profile->fill([
            'address' => $data['address'],
            'city' => $data['city'],
            'agency_type' => $data['agency_type'] ?? null,
            'focus_segments' => $data['focus_segments'] ?? [],
            'description' => $data['description'] ?? null,
        ]);
        if ($request->hasFile('logo')) {
            $profile->logo_path = $request->file('logo')->store('agent-logos', 'public');
        }
        $profile->save();

        $this->progress($agent)->update(['profile_complete' => true, 'current_step' => 3]);

        return redirect()->route('agent.compliance')->with('success', 'Profile saved.');
    }

    public function compliance(Request $request)
    {
        $agent = $this->agent($request);
        if ($redirect = $this->requireEarlierSteps($agent, ['profile_complete'])) {
            return $redirect;
        }

        return view('agent.setup.compliance', [
            'agent' => $agent,
            'document' => $agent->complianceDocuments()->latest('id')->first(),
        ]);
    }

    public function saveCompliance(Request $request)
    {
        $agent = $this->agent($request);
        if ($redirect = $this->requireEarlierSteps($agent, ['profile_complete'])) {
            return $redirect;
        }
        $existing = $agent->complianceDocuments()->latest('id')->first();
        $data = $request->validate([
            'business_registration_number' => ['required', 'string', 'max:100'],
            'iata_number' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'document_type' => ['nullable', Rule::in(['Trade Licence', 'Insurance', 'Contract ID Proof'])],
            'issue_date' => ['required', 'date', 'before_or_equal:today'],
            'expiry_date' => ['required', 'date', 'after:issue_date'],
            'document' => [$existing ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $filePath = $existing?->file_path;
        if ($request->hasFile('document')) {
            $filePath = $request->file('document')->store('agent-compliance', 'local');
        }

        $record = $existing ?? new AgentComplianceDocument;
        $record->fill([
            'business_registration_number' => $data['business_registration_number'],
            'iata_number' => $data['iata_number'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'document_type' => $data['document_type'] ?? null,
            'issue_date' => $data['issue_date'],
            'expiry_date' => $data['expiry_date'],
            'file_path' => $filePath,
            'renewal_reminder_date' => \Illuminate\Support\Carbon::parse($data['expiry_date'])->subDays(30)->toDateString(),
            'status' => \Illuminate\Support\Carbon::parse($data['expiry_date'])->startOfDay()->lt(now()->startOfDay())
                ? 'expired'
                : 'pending',
        ]);
        $record->agent_account_id = $agent->id;
        $record->save();
        if ($existing && $request->hasFile('document') && $existing->file_path !== $filePath) {
            Storage::disk('local')->delete($existing->file_path);
        }

        $this->progress($agent)->update(['compliance_complete' => true, 'current_step' => 4]);

        return redirect()->route('agent.users')->with('success', 'Compliance details saved.');
    }

    public function downloadCompliance(Request $request, AgentComplianceDocument $document)
    {
        abort_unless((int) $document->agent_account_id === (int) $this->agent($request)->id, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path);
    }

    public function users(Request $request)
    {
        $agent = $this->agent($request);
        if ($redirect = $this->requireEarlierSteps($agent, ['profile_complete', 'compliance_complete'])) {
            return $redirect;
        }
        $users = $agent->staffUsers()->with('permissions.module')->orderBy('full_name')->get();
        $modules = \App\Models\Module::query()->orderBy('name')->get();

        return view('agent.setup.users', compact('agent', 'users', 'modules'));
    }

    public function saveUser(Request $request, AgentPermissionService $permissions)
    {
        $agent = $this->agent($request);
        if ($redirect = $this->requireEarlierSteps($agent, ['profile_complete', 'compliance_complete'])) {
            return $redirect;
        }
        abort_if($request->user('agent_staff') && $request->user('agent_staff')->role !== 'Manager', 403);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:191',
                function (string $attribute, mixed $value, \Closure $fail) use ($agent): void {
                    $email = mb_strtolower(trim((string) $value));
                    if (\Illuminate\Support\Facades\DB::table('agent_staff_users')->whereRaw('LOWER(email) = ?', [$email])->exists()
                        || \Illuminate\Support\Facades\DB::table('agent_accounts')->whereRaw('LOWER(email) = ?', [$email])->exists()
                        || \Illuminate\Support\Facades\DB::table('operators')->whereRaw('LOWER(email) = ?', [$email])->exists()
                        || \Illuminate\Support\Facades\DB::table('mpos')->whereRaw('LOWER(email) = ?', [$email])->exists()
                        || \Illuminate\Support\Facades\DB::table('operator_users')->whereRaw('LOWER(email) = ?', [$email])->exists()
                        || \Illuminate\Support\Facades\DB::table('traveler_accounts')->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                        $fail('This email is already in use. Please use another email address.');
                    }
                },
            ],
            'mobile' => ['nullable', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[@#%!*^$&]/'],
            'role' => ['required', Rule::in(['Manager', 'Consultant', 'Accountant', 'Support'])],
            'module_actions' => ['nullable', 'array'],
            'module_actions.*' => ['array'],
            'module_actions.*.*' => ['string', Rule::in(['read', 'create', 'update', 'approve', 'publish'])],
        ], ['mobile.regex' => 'Enter the mobile number in E.164 format, including the +country code.']);

        foreach (array_keys($data['module_actions'] ?? []) as $moduleSlug) {
            abort_unless(\App\Models\Module::query()->where('slug', $moduleSlug)->exists(), 422, 'Unknown permission module.');
        }
        if ($request->user('agent_staff') && $data['role'] === 'Manager') {
            abort(403);
        }

        $staff = $agent->staffUsers()->create([
            'user_id' => 'AGU-'.strtoupper(bin2hex(random_bytes(6))),
            'full_name' => $data['full_name'],
            'email' => mb_strtolower(trim($data['email'])),
            'password_hash' => Hash::make($data['password']),
            'mobile' => $data['mobile'] ?? null,
            'role' => $data['role'],
            'status' => 'Active',
            'account_reset_required' => false,
        ]);
        $permissions->applyRoleDefaults($staff);
        if (! empty($data['module_actions'])) {
            $permissions->applyOverrides($staff, $data['module_actions']);
        }

        $this->progress($agent)->update(['users_complete' => true, 'current_step' => 5]);

        return redirect()->route('agent.operations')->with('success', 'Agent user created.');
    }

    public function resetUserPassword(Request $request, AgentStaffUser $user)
    {
        $agent = $this->agent($request);
        abort_unless((int) $user->agent_account_id === (int) $agent->id, 404);
        abort_if($request->user('agent_staff') && $request->user('agent_staff')->role !== 'Manager', 403);

        $data = $request->validate([
            'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[@#%!*^$&]/'],
        ]);
        $user->update([
            'password_hash' => Hash::make($data['password']),
            'account_reset_required' => false,
        ]);

        return redirect()->route('agent.users')->with('success', 'Agent user password reset.');
    }

    public function updateUserStatus(Request $request, AgentStaffUser $user)
    {
        $agent = $this->agent($request);
        abort_unless((int) $user->agent_account_id === (int) $agent->id, 404);
        abort_if($request->user('agent_staff') && $request->user('agent_staff')->role !== 'Manager', 403);
        $data = $request->validate(['status' => ['required', Rule::in(['Active', 'Disabled'])]]);
        $user->update(['status' => $data['status']]);

        return redirect()->route('agent.users')->with('success', 'Agent user status updated.');
    }

    public function operations(Request $request)
    {
        $agent = $this->agent($request);
        if ($redirect = $this->requireEarlierSteps($agent, ['profile_complete', 'compliance_complete', 'users_complete'])) {
            return $redirect;
        }

        return view('agent.setup.operations', [
            'agent' => $agent,
            'operations' => $agent->operations,
            'timeZones' => DateTimeZone::listIdentifiers(),
            'currencyCode' => $agent->country?->currency_code,
        ]);
    }

    public function saveOperations(Request $request)
    {
        $agent = $this->agent($request);
        if ($redirect = $this->requireEarlierSteps($agent, ['profile_complete', 'compliance_complete', 'users_complete'])) {
            return $redirect;
        }
        $currency = $agent->country?->currency_code;
        $data = $request->validate([
            'preferred_language_ui' => ['nullable', Rule::in(['EN', 'FR'])],
            'preferred_language_communication' => ['nullable', Rule::in(['EN', 'FR'])],
            'time_zone' => ['nullable', 'timezone'],
            'primary_currency' => ['nullable', 'string', 'size:3', Rule::in(array_filter([$currency]))],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'max:64'],
            'bank_swift' => ['nullable', 'string', 'max:32'],
            'bank_currency' => ['nullable', 'string', 'size:3', Rule::in(array_filter([$currency]))],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_mobile' => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
        ]);

        $agent->operations()->updateOrCreate(
            ['agent_account_id' => $agent->id],
            [
                'preferred_language_ui' => $data['preferred_language_ui'] ?? null,
                'preferred_language_communication' => $data['preferred_language_communication'] ?? null,
                'time_zone' => $data['time_zone'] ?? null,
                'primary_currency' => $data['primary_currency'] ?? null,
                'bank_details' => [
                    'account_holder' => $data['bank_account_holder'] ?? null,
                    'iban' => $data['bank_iban'] ?? null,
                    'swift' => $data['bank_swift'] ?? null,
                    'currency' => $data['bank_currency'] ?? null,
                ],
                'emergency_contact_name' => $data['emergency_contact_name'],
                'emergency_contact_mobile' => $data['emergency_contact_mobile'],
            ]
        );

        $this->progress($agent)->update(['operations_complete' => true, 'current_step' => 6]);

        return redirect()->route('agent.dashboard')->with('success', 'Operations settings saved.');
    }

    private function agent(Request $request): AgentAccount
    {
        $agent = $request->user('agent');
        if (! $agent) {
            $agent = $request->user('agent_staff')?->agent()->with('country')->first();
        }

        abort_unless($agent instanceof AgentAccount, 403);

        return $agent;
    }

    private function progress(AgentAccount $agent): AgentSetupProgress
    {
        return $agent->setupProgress()->firstOrCreate([]);
    }

    private function requireEarlierSteps(AgentAccount $agent, array $fields)
    {
        $progress = $this->progress($agent);
        foreach ($fields as $field) {
            if (! $progress->{$field}) {
                $route = [
                    'profile_complete' => 'agent.profile',
                    'compliance_complete' => 'agent.compliance',
                    'users_complete' => 'agent.users',
                ][$field];

                return redirect()->route($route)->with('error', 'Please complete the previous setup step first.');
            }
        }

        return null;
    }
}
