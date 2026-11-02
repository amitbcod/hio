<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class ProfileController extends Controller
{
    private const SOCIAL_PLATFORMS = ['facebook', 'instagram', 'x', 'linkedin'];

    public function show(Request $request)
    {
        return view('mpo.profile.form', $this->formData($request->attributes->get('mpo_admin'), false));
    }

    public function save(Request $request)
    {
        $mpoAdmin = $request->attributes->get('mpo_admin');
        $data = $this->validateProfile($request, $mpoAdmin);
        $this->saveProfile($request, $mpoAdmin, $data);

        return redirect()->route('mpo.profile')->with(
            'success',
            $mpoAdmin->hasCompleteMpoProfile()
                ? 'Your MPO profile is complete and has been saved.'
                : 'Your MPO profile draft has been saved. Complete the required fields to finish this step.',
        );
    }

    public function editAdmin(AdminUser $administrator)
    {
        abort_unless($administrator->admin_type === 'mpo', 404);

        return view('mpo.profile.form', $this->formData($administrator->load('country'), true));
    }

    public function updateAdmin(Request $request, AdminUser $administrator)
    {
        abort_unless($administrator->admin_type === 'mpo', 404);

        $data = $this->validateProfile($request, $administrator);
        $this->saveProfile($request, $administrator, $data);

        return redirect()->route('admin.country-admins.profile.edit', $administrator)
            ->with('success', 'MPO profile updated.');
    }

    public function compliances()
    {
        return $this->placeholder('Compliances');
    }

    public function users()
    {
        return $this->placeholder('Users');
    }

    public function capacityPermissions()
    {
        return $this->placeholder('Capacity Permissions and Roles');
    }

    public function collaborationAgreements()
    {
        return app(CollaborationAgreementController::class)->show(request());
    }

    public function accountingCommission()
    {
        return app(AccountingCommissionController::class)->show(request());
    }

    private function placeholder(string $section)
    {
        return view('mpo.profile.placeholder', compact('section'));
    }

    private function formData(AdminUser $mpoAdmin, bool $isAdminEdit): array
    {
        $mpoAdmin->loadMissing('country');

        return [
            'mpoAdmin' => $mpoAdmin,
            'isAdminEdit' => $isAdminEdit,
            'regions' => Region::query()
                ->where('country_id', $mpoAdmin->country_id)
                ->orderBy('name')
                ->get(),
            'languages' => config('mpo.languages', []),
            'timezones' => \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC),
        ];
    }

    private function validateProfile(Request $request, AdminUser $mpoAdmin): array
    {
        foreach ([
            'registered_business_name',
            'registration_number',
            'address_line1',
            'address_line2',
            'official_email',
            'office_phone',
            'emergency_mobile',
            'operating_timezone',
            'brand_name',
            'description',
            'public_contact_email',
            'public_contact_phone',
            'office_hours',
            'website',
        ] as $field) {
            if (is_string($request->input($field))) {
                $request->merge([$field => trim($request->input($field))]);
            }
        }

        if (is_array($request->input('social_links'))) {
            $request->merge([
                'social_links' => array_map(
                    static fn ($value) => is_string($value) ? trim($value) : $value,
                    $request->input('social_links'),
                ),
            ]);
        }

        $requiredWhenCompleting = Rule::requiredIf($request->boolean('mark_complete'));
        $phoneRule = ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'];
        $httpUrlRule = ['nullable', 'url', 'starts_with:http://,https://'];

        return $request->validate([
            'mark_complete' => ['nullable', 'boolean'],
            'registered_business_name' => [$requiredWhenCompleting, 'nullable', 'string', 'max:191'],
            'registration_number' => [$requiredWhenCompleting, 'nullable', 'string', 'max:100'],
            'address_line1' => [$requiredWhenCompleting, 'nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'official_email' => [$requiredWhenCompleting, 'nullable', 'email', 'max:191'],
            'office_phone' => [$requiredWhenCompleting, ...$phoneRule],
            'emergency_mobile' => [$requiredWhenCompleting, ...$phoneRule],
            'operating_timezone' => [
                $requiredWhenCompleting,
                'nullable',
                Rule::in(\DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC)),
            ],
            'supported_languages' => [$requiredWhenCompleting, 'nullable', 'array', 'min:1'],
            'supported_languages.*' => ['string', Rule::in(array_keys(config('mpo.languages', [])))],
            'brand_name' => [$requiredWhenCompleting, 'nullable', 'string', 'max:191'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'regions_covered' => ['nullable', 'array'],
            'regions_covered.*' => [
                'integer',
                Rule::exists('regions', 'id')->where('country_id', $mpoAdmin->country_id),
            ],
            'public_contact_email' => ['nullable', 'email', 'max:191'],
            'public_contact_phone' => $phoneRule,
            'social_links' => ['nullable', 'array'],
            'social_links.facebook' => $httpUrlRule,
            'social_links.instagram' => $httpUrlRule,
            'social_links.x' => $httpUrlRule,
            'social_links.linkedin' => $httpUrlRule,
            'office_hours' => ['nullable', 'string', 'max:500'],
            'website' => [...$httpUrlRule, 'max:2048'],
        ]);
    }

    private function saveProfile(Request $request, AdminUser $mpoAdmin, array $data): void
    {
        $oldLogoPath = $mpoAdmin->logo_path;
        $newLogoPath = null;
        $legalInformationBefore = [
            $mpoAdmin->registered_business_name,
            $mpoAdmin->brn_number,
            $mpoAdmin->business_address,
            $mpoAdmin->business_address_line2,
        ];

        if ($request->hasFile('logo')) {
            $newLogoPath = $request->file('logo')->store('mpo-logos', 'public');
            if (! is_string($newLogoPath) || $newLogoPath === '') {
                throw new RuntimeException('The MPO logo could not be stored.');
            }
        }

        $mpoAdmin->fill([
            'registered_business_name' => $data['registered_business_name'] ?? null,
            'brn_number' => $data['registration_number'] ?? null,
            'business_address' => $data['address_line1'] ?? null,
            'business_address_line2' => $data['address_line2'] ?? null,
            'official_business_email' => $data['official_email'] ?? null,
            'phone_number' => $data['office_phone'] ?? null,
            'emergency_mobile' => $data['emergency_mobile'] ?? null,
            'operating_timezone' => $data['operating_timezone'] ?? null,
            'supported_languages' => $data['supported_languages'] ?? [],
            'brand_name' => $data['brand_name'] ?? null,
            'brand_description' => $data['description'] ?? null,
            'covered_region_ids' => $data['regions_covered'] ?? [],
            'public_contact_email' => $data['public_contact_email'] ?? null,
            'public_contact_phone' => $data['public_contact_phone'] ?? null,
            'social_links' => array_intersect_key(
                $data['social_links'] ?? [],
                array_flip(self::SOCIAL_PLATFORMS),
            ),
            'office_hours' => $data['office_hours'] ?? null,
            'website_url' => $data['website'] ?? null,
        ]);

        if ($newLogoPath !== null) {
            $mpoAdmin->logo_path = $newLogoPath;
        }

        $legalInformationChanged = $legalInformationBefore !== [
            $mpoAdmin->registered_business_name,
            $mpoAdmin->brn_number,
            $mpoAdmin->business_address,
            $mpoAdmin->business_address_line2,
        ];

        try {
            DB::transaction(function () use ($mpoAdmin, $legalInformationChanged): void {
                if (! $mpoAdmin->save()) {
                    throw new RuntimeException('The MPO profile could not be saved.');
                }

                if ($legalInformationChanged) {
                    $mpoAdmin->compliance?->requireReverification();
                }
            });
        } catch (Throwable $exception) {
            if ($newLogoPath !== null) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $exception;
        }

        if ($newLogoPath !== null && $oldLogoPath && $oldLogoPath !== $newLogoPath) {
            Storage::disk('public')->delete($oldLogoPath);
        }
    }
}
