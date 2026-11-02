<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class CountryAdminController extends Controller
{
    public function index()
    {
        $administrators = AdminUser::query()
            ->with(['country', 'collaborationAgreements' => fn ($query) => $query->orderByDesc('id')])
            ->where('admin_type', 'mpo')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.country-admins.index', compact('administrators'));
    }

    public function create()
    {
        $assignedCountryIds = AdminUser::query()
            ->where('admin_type', 'mpo')
            ->whereNotNull('country_id')
            ->pluck('country_id');
        $countries = Country::query()
            ->where('is_active', true)
            ->whereNotIn('id', $assignedCountryIds)
            ->orderBy('name')
            ->get();

        return view('admin.country-admins.form', [
            'administrator' => new AdminUser,
            'countries' => $countries,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $administrator = new AdminUser;
        $administrator->name = $data['name'];
        $administrator->email = $data['email'];
        $administrator->password_hash = Hash::make($data['password']);
        $administrator->role = 'admin';
        $administrator->admin_type = 'mpo';
        $administrator->country_id = $data['country_id'];
        $administrator->status = $data['status'];
        if ($administrator->status === 'active') {
            $administrator->mpo_onboarded_at = now();
        }
        $token = Str::random(64);
        $administrator->mpo_password_setup_token_hash = hash('sha256', $token);
        $administrator->mpo_password_setup_expires_at = now()->addHours(24);
        $administrator->save();

        try {
            Mail::to($administrator->email)->send(new \App\Mail\MpoAccountActivation(
                $administrator,
                route('mpo.activation.show', [$administrator, $token]),
            ));

            return redirect()->route('admin.country-admins.index')->with('success', 'MPO administrator created. Password setup instructions were emailed.');
        } catch (Throwable $exception) {
            Log::error('MPO account activation email could not be sent.', [
                'admin_user_id' => $administrator->id,
                'email' => $administrator->email,
                'exception' => $exception->getMessage(),
            ]);

            return redirect()->route('admin.country-admins.index')
                ->with('warning', 'MPO administrator created, but the password setup email could not be sent. The account was not rolled back.');
        }
    }

    public function edit(AdminUser $administrator)
    {
        abort_unless($administrator->admin_type === 'mpo', 404);
        $countries = Country::query()->where('is_active', true)
            ->orWhereKey($administrator->country_id)
            ->orderBy('name')
            ->get();

        return view('admin.country-admins.form', compact('administrator', 'countries'));
    }

    public function update(Request $request, AdminUser $administrator)
    {
        abort_unless($administrator->admin_type === 'mpo', 404);
        $data = $this->validated($request, $administrator);
        $countryChanged = (int) $administrator->country_id !== (int) $data['country_id'];
        $wasActive = $administrator->status === 'active';
        $administrator->name = $data['name'];
        $administrator->email = $data['email'];
        $administrator->country_id = $data['country_id'];
        $administrator->status = $data['status'];
        if (! $wasActive && $administrator->status === 'active' && ! $administrator->mpo_onboarded_at) {
            $administrator->mpo_onboarded_at = now();
        }
        if (filled($data['password'] ?? null)) {
            $administrator->password_hash = Hash::make($data['password']);
        }
        DB::transaction(function () use ($administrator, $countryChanged, $wasActive): void {
            if (! $administrator->save()) {
                throw new \RuntimeException('The MPO account could not be saved.');
            }

            if (! $wasActive && $administrator->status === 'active' && $administrator->mpo_onboarded_at) {
                $administrator->collaborationAgreements()
                    ->whereNull('onboarding_date')
                    ->update(['onboarding_date' => $administrator->mpo_onboarded_at->toDateString()]);
            }

            if ($countryChanged) {
                $administrator->compliance?->requireReverification();
            }
        });

        return redirect()->route('admin.country-admins.index')->with('success', 'MPO administrator updated.');
    }

    private function validated(Request $request, ?AdminUser $administrator = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('admin_users', 'email')->ignore($administrator?->id)],
            'country_id' => [
                'required',
                'integer',
                Rule::exists('countries', 'id')->where('is_active', true),
                Rule::unique('admin_users', 'country_id')
                    ->where('admin_type', 'mpo')
                    ->ignore($administrator?->id),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => [$administrator ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ], [
            'country_id.unique' => 'This country is already assigned to an MPO.',
        ]);
    }
}
