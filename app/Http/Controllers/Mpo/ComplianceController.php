<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\MpoCompliance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class ComplianceController extends Controller
{
    private const MAX_DOCUMENT_KILOBYTES = 10240;

    public function show(Request $request)
    {
        $mpo = $request->attributes->get('mpo_admin');
        $compliance = $mpo->compliance;

        return view('mpo.compliance.form', $this->viewData($mpo, $compliance, false));
    }

    public function submit(Request $request)
    {
        $mpo = $request->attributes->get('mpo_admin');

        return $this->saveSubmission($request, $mpo, false);
    }

    public function download(Request $request, MpoCompliance $compliance, string $document)
    {
        $mpo = $request->attributes->get('mpo_admin');
        abort_unless((int) $compliance->admin_user_id === (int) $mpo->id, 404);

        return $this->downloadDocument($compliance, $document);
    }

    public function adminShow(AdminUser $administrator)
    {
        $this->ensureMpo($administrator);
        $administrator->loadMissing(['country', 'compliance.reviewedBy']);

        return view('mpo.compliance.form', $this->viewData($administrator, $administrator->compliance, true));
    }

    public function adminSubmit(Request $request, AdminUser $administrator)
    {
        $this->ensureMpo($administrator);

        return $this->saveSubmission($request, $administrator, true);
    }

    public function adminReview(Request $request, AdminUser $administrator)
    {
        $this->ensureMpo($administrator);
        $compliance = $administrator->compliance()->firstOrFail();
        $data = $request->validate([
            'verification_status' => ['required', Rule::in(['verified', 'rejected'])],
            'rejection_reason' => [
                Rule::requiredIf($request->input('verification_status') === 'rejected'),
                'nullable',
                'string',
                'max:5000',
            ],
        ]);
        $reviewerId = (int) session('admin_id');
        $reviewer = AdminUser::query()
            ->whereKey($reviewerId)
            ->where('admin_type', 'global')
            ->where('status', 'active')
            ->firstOrFail();

        $compliance->verification_status = $data['verification_status'];
        $compliance->rejection_reason = $data['verification_status'] === 'rejected'
            ? trim($data['rejection_reason'])
            : null;
        $compliance->reviewed_by_admin_id = $reviewer->id;
        $compliance->reviewed_at = now();
        $compliance->save();

        return redirect()->route('admin.country-admins.compliance.show', $administrator)
            ->with('success', $data['verification_status'] === 'verified'
                ? 'MPO compliance marked as verified.'
                : 'MPO compliance rejected.');
    }

    public function adminDownload(MpoCompliance $compliance, string $document)
    {
        $this->ensureMpo($compliance->mpo()->firstOrFail());

        return $this->downloadDocument($compliance, $document);
    }

    private function saveSubmission(Request $request, AdminUser $mpo, bool $isAdmin)
    {
        $mpo->loadMissing('country');
        $missingProfileFields = $this->missingProfileFields($mpo);
        if ($missingProfileFields !== []) {
            return back()->withErrors([
                'profile' => 'Complete these Profile fields before submitting compliance: '.implode(', ', $missingProfileFields).'.',
            ])->withInput();
        }

        $compliance = $mpo->compliance()->first();
        $data = $this->validateSubmission($request, $compliance);

        $newPaths = [];
        $newFilenames = [];
        try {
            foreach (['proof_of_license', 'insurance_certificate'] as $field) {
                if (! $request->hasFile($field)) {
                    continue;
                }

                $path = $request->file($field)->store('docs/legal', 'local');
                if (! is_string($path) || $path === '') {
                    throw new RuntimeException('Unable to store a compliance document.');
                }
                $newPaths[$field] = $path;
                $newFilenames[$field] = $this->safeOriginalFilename($request->file($field)->getClientOriginalName());
            }

            $oldPaths = [];
            $updatedCompliance = DB::transaction(function () use ($mpo, $compliance, $data, $newPaths, $newFilenames, &$oldPaths): MpoCompliance {
                $record = $compliance ?? $mpo->compliance()->make();
                $oldPaths = [
                    'proof_of_license' => $record->proof_of_license_path,
                    'insurance_certificate' => $record->insurance_certificate_path,
                ];

                $changed = $record->exists && (
                    $record->business_license_number !== $data['business_license_number']
                    || $record->license_type !== $data['license_type']
                    || $record->other_license_type_description !== ($data['other_license_type_description'] ?? null)
                    || $record->license_expiry_date?->toDateString() !== $data['license_expiry_date']
                    || isset($newPaths['proof_of_license'])
                    || isset($newPaths['insurance_certificate'])
                );

                $record->business_license_number = $data['business_license_number'];
                $record->license_type = $data['license_type'];
                $record->other_license_type_description = $data['license_type'] === 'other'
                    ? ($data['other_license_type_description'] ?? null)
                    : null;
                $record->license_expiry_date = $data['license_expiry_date'];
                $record->proof_of_license_path = $newPaths['proof_of_license'] ?? $record->proof_of_license_path;
                $record->proof_of_license_filename = $newFilenames['proof_of_license'] ?? $record->proof_of_license_filename;
                $record->insurance_certificate_path = $newPaths['insurance_certificate'] ?? $record->insurance_certificate_path;
                $record->insurance_certificate_filename = $newFilenames['insurance_certificate'] ?? $record->insurance_certificate_filename;

                $needsReview = ! $record->exists
                    || $record->verification_status === 'rejected'
                    || ($record->verification_status === 'verified' && $changed);

                if ($needsReview) {
                    $record->verification_status = 'pending_verification';
                    $record->rejection_reason = null;
                    $record->reviewed_at = null;
                    $record->reviewed_by_admin_id = null;
                } elseif (! $record->verification_status) {
                    $record->verification_status = 'pending_verification';
                }

                $record->submitted_at = now();
                if (! $record->save()) {
                    throw new RuntimeException('Unable to save compliance record.');
                }

                return $record;
            });
        } catch (Throwable $exception) {
            foreach ($newPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            Log::error('MPO compliance submission could not be saved.', [
                'mpo_admin_id' => $mpo->id,
                'actor_admin' => $isAdmin,
                'exception_class' => $exception::class,
            ]);

            return back()->withErrors([
                'documents' => 'The compliance submission could not be saved. Your previous submission and documents were preserved. Please try again.',
            ])->withInput();
        }

        foreach ([
            'proof_of_license' => $updatedCompliance->proof_of_license_path,
            'insurance_certificate' => $updatedCompliance->insurance_certificate_path,
        ] as $field => $newPath) {
            $oldPath = $oldPaths[$field] ?? null;
            if ($oldPath && $oldPath !== $newPath) {
                $this->deleteIfUnreferenced($oldPath, $updatedCompliance);
            }
        }

        $route = $isAdmin
            ? route('admin.country-admins.compliance.show', $mpo)
            : route('mpo.compliances');

        return redirect($route)->with(
            'success',
            'Compliance details saved. Verification status: '.$updatedCompliance->verificationStatusLabel().'.',
        );
    }

    private function validateSubmission(Request $request, ?MpoCompliance $existing): array
    {
        foreach (['business_license_number', 'other_license_type_description'] as $field) {
            if (is_string($request->input($field))) {
                $request->merge([$field => trim($request->input($field))]);
            }
        }

        return $request->validate([
            'business_license_number' => ['required', 'string', 'max:100'],
            'license_type' => ['required', Rule::in(array_keys(config('mpo.compliance_license_types', [])))],
            'other_license_type_description' => [
                Rule::requiredIf($request->input('license_type') === 'other'),
                'nullable',
                'string',
                'max:191',
            ],
            'license_expiry_date' => ['required', 'date_format:Y-m-d'],
            'proof_of_license' => [
                Rule::requiredIf(! $existing?->proof_of_license_path),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg',
                'extensions:pdf,jpg,jpeg',
                'mimetypes:application/pdf,image/jpeg',
                'max:'.self::MAX_DOCUMENT_KILOBYTES,
            ],
            'insurance_certificate' => [
                Rule::requiredIf(! $existing?->insurance_certificate_path),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg',
                'extensions:pdf,jpg,jpeg',
                'mimetypes:application/pdf,image/jpeg',
                'max:'.self::MAX_DOCUMENT_KILOBYTES,
            ],
        ], [
            'proof_of_license.max' => 'The proof of license must not exceed 10 MB.',
            'insurance_certificate.max' => 'The insurance certificate must not exceed 10 MB.',
        ]);
    }

    private function viewData(AdminUser $mpo, ?MpoCompliance $compliance, bool $isAdmin): array
    {
        $mpo->loadMissing(['country', 'compliance.reviewedBy']);

        return [
            'mpoAdmin' => $mpo,
            'compliance' => $compliance,
            'isAdminView' => $isAdmin,
            'missingProfileFields' => $this->missingProfileFields($mpo),
            'licenseTypes' => config('mpo.compliance_license_types', []),
            'expiryStatus' => $compliance?->expiryStatus($mpo->operating_timezone),
        ];
    }

    private function missingProfileFields(AdminUser $mpo): array
    {
        $missing = [];
        foreach ([
            'registered_business_name' => 'Legal Entity Name',
            'brn_number' => 'Registration Number',
            'business_address' => 'Business Address Line 1',
        ] as $field => $label) {
            if (! is_string($mpo->{$field}) || trim($mpo->{$field}) === '') {
                $missing[] = $label;
            }
        }

        if (! $mpo->country_id || ! $mpo->country) {
            $missing[] = 'Assigned Country';
        }

        return $missing;
    }

    private function downloadDocument(MpoCompliance $compliance, string $document)
    {
        $column = match ($document) {
            'proof-of-license' => 'proof_of_license_path',
            'insurance-certificate' => 'insurance_certificate_path',
            default => abort(404),
        };
        $path = $compliance->{$column};
        abort_unless(is_string($path) && str_starts_with($path, 'docs/legal/'), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download(
            $path,
            basename($path),
            [
                'Content-Type' => 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }

    private function deleteIfUnreferenced(string $path, MpoCompliance $compliance): void
    {
        $referenced = MpoCompliance::query()
            ->where('id', '<>', $compliance->getKey())
            ->where(function ($query) use ($path): void {
                $query->where('proof_of_license_path', $path)
                    ->orWhere('insurance_certificate_path', $path);
            })
            ->exists();

        if (! $referenced) {
            Storage::disk('local')->delete($path);
        }
    }

    private function ensureMpo(AdminUser $administrator): void
    {
        abort_unless($administrator->admin_type === 'mpo', 404);
    }

    private function safeOriginalFilename(string $filename): string
    {
        $filename = basename(str_replace('\\', '/', $filename));
        $filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename) ?? '';

        return mb_substr($filename !== '' ? $filename : 'uploaded-document', 0, 255);
    }
}
