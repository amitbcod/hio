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
use Illuminate\Support\Facades\Storage;
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
        $country = Country::query()->findOrFail($data['country_id']);
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

        $document = null;
        if ($request->hasFile('responsibilities_document')) {
            $file = $request->file('responsibilities_document');
            $path = $file->store('mpo-agreements', 'local');
            if (! is_string($path) || $path === '') {
                throw new \RuntimeException('The responsibilities document could not be stored.');
            }
            $originalName = basename(str_replace('\\', '/', $file->getClientOriginalName()));
            $document = [
                'path' => $path,
                'name' => preg_replace('/[^A-Za-z0-9._ -]/', '_', $originalName) ?: 'responsibilities-document',
                'mime' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => (int) $file->getSize(),
            ];
        }

        try {
            DB::transaction(function () use ($administrator, $country, $data, $document): void {
                $country->vat_percentage = $data['vat_percentage'] ?? 0;
                $country->save();
                $administrator->save();

                $agreement = $administrator->collaborationAgreements()->create([
                    'agreement_type' => $data['agreement_type'],
                    'onboarding_date' => $administrator->mpo_onboarded_at?->toDateString(),
                    'start_date' => $data['start_date'],
                    'commission_model' => $data['commission_model'],
                    'commission_value' => $data['commission_value'],
                    'marketing_contribution_percent' => $data['marketing_contribution_percent'] ?? null,
                    'marketing_contribution_payer' => ($data['marketing_contribution_percent'] ?? null) !== null
                        ? $data['marketing_contribution_payer']
                        : null,
                    'status' => 'Draft',
                ]);
                $agreement->agreement_id = 'MPO-AGR-'.str_pad((string) $agreement->id, 6, '0', STR_PAD_LEFT);
                $agreement->saveOrFail();

                $actor = AdminUser::query()
                    ->whereKey(session('admin_id'))
                    ->where('admin_type', 'global')
                    ->first();
                $terms = [
                    'agreement_type' => $agreement->agreement_type,
                    'start_date' => $agreement->start_date?->toDateString(),
                    'commission_model' => $agreement->commission_model,
                    'commission_value' => (string) $agreement->commission_value,
                    'marketing_contribution_percent' => $agreement->marketing_contribution_percent === null
                        ? null
                        : (string) $agreement->marketing_contribution_percent,
                    'marketing_contribution_payer' => $agreement->marketing_contribution_payer,
                ];
                $agreement->events()->create([
                    'event_type' => 'created',
                    'changes' => ['terms' => $terms],
                    'actor_type' => $actor?->admin_type ?? 'system',
                    'actor_id' => $actor?->id,
                ]);

                if ($document) {
                    $agreement->documents()->create([
                        'storage_path' => $document['path'],
                        'original_name' => $document['name'],
                        'mime_type' => $document['mime'],
                        'size_bytes' => $document['size'],
                        'uploaded_by_admin_id' => $actor?->id,
                        'is_current' => true,
                    ]);
                    $agreement->events()->create([
                        'event_type' => 'document_uploaded',
                        'changes' => [
                            'name' => $document['name'],
                            'size_bytes' => $document['size'],
                        ],
                        'actor_type' => $actor?->admin_type ?? 'system',
                        'actor_id' => $actor?->id,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if ($document && ! Storage::disk('local')->delete($document['path'])) {
                Log::error('MPO agreement document cleanup failed after MPO creation rollback.', [
                    'storage_path' => $document['path'],
                    'exception' => $exception->getMessage(),
                ]);
            }
            throw $exception;
        }

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
        $countries = Country::query()->where(function ($query) use ($administrator): void {
            $query->where('is_active', true)
                ->orWhere('id', $administrator->country_id);
        })
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
        $country = Country::query()->findOrFail($data['country_id']);
        DB::transaction(function () use ($administrator, $country, $data, $countryChanged, $wasActive): void {
            $country->vat_percentage = $data['vat_percentage'] ?? 0;
            $country->save();

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
        $rules = [
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
            'vat_percentage' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,4'],
        ];

        if (! $administrator) {
            $rules += [
                'agreement_type' => ['required', Rule::in(['mpo'])],
                'start_date' => [
                    'required',
                    'date',
                    ...($request->input('status') === 'active' ? ['after_or_equal:'.now()->toDateString()] : []),
                ],
                'commission_model' => ['required', Rule::in(['percentage'])],
                'commission_value' => ['required', 'numeric', 'between:0,100', 'decimal:0,4'],
                'marketing_contribution_percent' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,4'],
                'marketing_contribution_payer' => [
                    'nullable',
                    'required_with:marketing_contribution_percent',
                    Rule::in(['mpo', 'operator']),
                ],
                'responsibilities_document' => ['nullable', 'file', 'mimes:pdf,docx', 'max:10240'],
            ];
        }

        return $request->validate($rules, [
            'country_id.unique' => 'This country is already assigned to an MPO.',
        ]);
    }
}
