<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\MpoCollaborationAgreement;
use App\Models\MpoCollaborationAgreementDocument;
use App\Models\MpoStaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

class CollaborationAgreementController extends Controller
{
    private const PRIVATE_DISK = 'local';

    public function show(Request $request)
    {
        $mpo = $request->attributes->get('mpo_admin');
        $agreement = $mpo->collaborationAgreements()->with(['currentDocument', 'events'])->latest('id')->first();

        return view('mpo.agreements.show', [
            'mpoAdmin' => $mpo,
            'actor' => $request->attributes->get('mpo_staff_user', $mpo),
            'agreement' => $agreement,
        ]);
    }

    public function updateContact(Request $request, string $contactType)
    {
        abort_unless(in_array($contactType, ['management', 'accounting'], true), 404);
        $mpo = $request->attributes->get('mpo_admin');
        $agreement = $mpo->collaborationAgreements()->latest('id')->firstOrFail();
        $prefix = $contactType.'_contact_';
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'admin_user_id' => ['prohibited'],
            'agreement_id' => ['prohibited'],
            'agreement_type' => ['prohibited'],
            'onboarding_date' => ['prohibited'],
            'start_date' => ['prohibited'],
            'commission_model' => ['prohibited'],
            'commission_value' => ['prohibited'],
            'marketing_contribution_percent' => ['prohibited'],
            'marketing_contribution_payer' => ['prohibited'],
            'status' => ['prohibited'],
            'responsibilities_document' => ['prohibited'],
        ]);

        DB::transaction(function () use ($agreement, $prefix, $data, $request, $contactType): void {
            $before = [];
            $after = [];
            foreach (['name', 'email', 'phone', 'mobile'] as $field) {
                $column = $prefix.$field;
                $before[$column] = $agreement->{$column};
                $after[$column] = trim($data[$field]);
                $agreement->{$column} = $after[$column];
            }
            if (! $agreement->save()) {
                throw new RuntimeException('The agreement contact could not be saved.');
            }
            $this->recordEvent($agreement, 'contact_updated', $request->attributes->get('mpo_staff_user', $request->attributes->get('mpo_admin')), [
                'before' => $before,
                'after' => $after,
                'contact_type' => $contactType,
            ]);
        });

        return redirect()->route('mpo.collaboration-agreements')->with('success', ucfirst($contactType).' contact saved.');
    }

    public function download(Request $request, int $document)
    {
        $mpo = $request->attributes->get('mpo_admin');
        $record = MpoCollaborationAgreementDocument::query()
            ->whereKey($document)
            ->whereHas('agreement', fn ($query) => $query->where('admin_user_id', $mpo->id))
            ->firstOrFail();

        return $this->serveDocument($record);
    }

    public function adminCreate(AdminUser $administrator)
    {
        $this->ensureMpo($administrator);

        return view('admin.country-admins.collaboration-agreement-form', [
            'administrator' => $administrator->load('country'),
            'agreement' => new MpoCollaborationAgreement(['agreement_type' => 'mpo', 'commission_model' => 'percentage']),
            'isCreate' => true,
        ]);
    }

    public function adminStore(Request $request, AdminUser $administrator)
    {
        $this->ensureMpo($administrator);
        $data = $this->validateTerms($request, $administrator->mpo_onboarded_at?->toDateString());
        $document = $this->storeUpload($request);
        try {
            $agreement = DB::transaction(function () use ($administrator, $data, $document): MpoCollaborationAgreement {
                $agreement = new MpoCollaborationAgreement;
                $agreement->admin_user_id = $administrator->id;
                $agreement->agreement_type = $data['agreement_type'];
                $agreement->onboarding_date = $administrator->mpo_onboarded_at?->toDateString();
                $agreement->start_date = $data['start_date'];
                $agreement->commission_model = $data['commission_model'];
                $agreement->commission_value = $data['commission_value'];
                $agreement->marketing_contribution_percent = $data['marketing_contribution_percent'] ?? null;
                $agreement->marketing_contribution_payer = ($data['marketing_contribution_percent'] ?? null) !== null
                    ? $data['marketing_contribution_payer']
                    : null;
                $this->fillContacts($agreement, $data);
                $agreement->status = 'Draft';
                $agreement->saveOrFail();
                $agreement->agreement_id = 'MPO-AGR-'.str_pad((string) $agreement->id, 6, '0', STR_PAD_LEFT);
                $agreement->saveOrFail();
                if ($document) {
                    $this->attachDocument($agreement, $document, (int) session('admin_id'));
                }
                $this->recordEvent($agreement, 'created', $this->globalActor(), [
                    'terms' => $this->termsSnapshot($agreement),
                    'contacts' => $this->contactSnapshot($agreement),
                ]);

                return $agreement;
            });
        } catch (\Throwable $exception) {
            if ($document) {
                Storage::disk(self::PRIVATE_DISK)->delete($document['path']);
            }
            throw $exception;
        }

        return redirect()->route('admin.country-admins.collaboration-agreements.edit', [$administrator, $agreement])
            ->with('success', 'Draft MPO collaboration agreement created.');
    }

    public function adminEdit(AdminUser $administrator, MpoCollaborationAgreement $agreement)
    {
        $this->ensureOwnedAgreement($administrator, $agreement);
        $agreement->load(['currentDocument', 'documents', 'events']);

        return view('admin.country-admins.collaboration-agreement-form', [
            'administrator' => $administrator->load('country'),
            'agreement' => $agreement,
            'isCreate' => false,
        ]);
    }

    public function adminUpdate(Request $request, AdminUser $administrator, MpoCollaborationAgreement $agreement)
    {
        $this->ensureOwnedAgreement($administrator, $agreement);
        $data = $this->validateTerms($request, $agreement->onboarding_date?->toDateString());
        $beforeTerms = $this->termsSnapshot($agreement);
        $proposedTerms = [
            'agreement_type' => $data['agreement_type'],
            'start_date' => $data['start_date'],
            'commission_model' => $data['commission_model'],
            'commission_value' => $this->normalizeDecimal($data['commission_value']),
            'marketing_contribution_percent' => isset($data['marketing_contribution_percent'])
                ? $this->normalizeDecimal($data['marketing_contribution_percent'])
                : null,
            'marketing_contribution_payer' => ($data['marketing_contribution_percent'] ?? null) !== null
                ? $data['marketing_contribution_payer']
                : null,
        ];
        abort_if(
            $agreement->status !== 'Draft' && $beforeTerms !== $proposedTerms,
            422,
            'Terms on a non-Draft agreement cannot be changed. Create a new agreement version.',
        );
        $document = $this->storeUpload($request);

        try {
            DB::transaction(function () use ($agreement, $data, $document): void {
                $before = $this->termsSnapshot($agreement);
                $beforeContacts = $this->contactSnapshot($agreement);
                $agreement->agreement_type = $data['agreement_type'];
                $agreement->start_date = $data['start_date'];
                $agreement->commission_model = $data['commission_model'];
                $agreement->commission_value = $data['commission_value'];
                $agreement->marketing_contribution_percent = $data['marketing_contribution_percent'] ?? null;
                $agreement->marketing_contribution_payer = ($data['marketing_contribution_percent'] ?? null) !== null
                    ? $data['marketing_contribution_payer']
                    : null;
                $this->fillContacts($agreement, $data);
                $agreement->saveOrFail();
                $after = $this->termsSnapshot($agreement);
                $changes = [];
                foreach ($after as $key => $value) {
                    if ($before[$key] !== $value) {
                        $changes[$key] = ['before' => $before[$key], 'after' => $value];
                    }
                }
                if ($changes !== []) {
                    $this->recordEvent($agreement, 'terms_updated', $this->globalActor(), $changes);
                }
                $contactChanges = [];
                foreach ($this->contactSnapshot($agreement) as $key => $value) {
                    if ($beforeContacts[$key] !== $value) {
                        $contactChanges[$key] = ['before' => $beforeContacts[$key], 'after' => $value];
                    }
                }
                if ($contactChanges !== []) {
                    $this->recordEvent($agreement, 'contact_updated', $this->globalActor(), $contactChanges);
                }
                if ($document) {
                    $this->attachDocument($agreement, $document, (int) session('admin_id'));
                }
            });
        } catch (\Throwable $exception) {
            if ($document) {
                Storage::disk(self::PRIVATE_DISK)->delete($document['path']);
            }
            throw $exception;
        }

        return redirect()->route('admin.country-admins.collaboration-agreements.edit', [$administrator, $agreement])
            ->with('success', 'Agreement terms saved.');
    }

    public function adminTransition(Request $request, AdminUser $administrator, MpoCollaborationAgreement $agreement)
    {
        $this->ensureOwnedAgreement($administrator, $agreement);
        $data = $request->validate([
            'status' => ['required', Rule::in(['Active', 'Suspended', 'Terminated'])],
            'reason' => ['required_if:status,Suspended,Terminated', 'nullable', 'string', 'max:1000'],
        ]);
        DB::transaction(function () use ($administrator, $agreement, $data): void {
            $lockedAgreement = MpoCollaborationAgreement::query()
                ->where('admin_user_id', $administrator->id)
                ->lockForUpdate()
                ->findOrFail($agreement->id);
            $allowedTransitions = [
                'Draft' => ['Active'],
                'Active' => ['Suspended', 'Terminated'],
                'Suspended' => ['Active', 'Terminated'],
                'Terminated' => [],
            ];
            abort_unless(
                in_array($data['status'], $allowedTransitions[$lockedAgreement->status] ?? [], true),
                422,
                'This agreement status transition is not allowed.',
            );
            if ($data['status'] === 'Active') {
                abort_if(! $lockedAgreement->start_date || $lockedAgreement->commission_value === null, 422, 'Set valid agreement terms before activation.');
            }

            $previous = $lockedAgreement->status;
            $lockedAgreement->status = $data['status'];
            $lockedAgreement->saveOrFail();
            $this->recordEvent($lockedAgreement, 'status_changed', $this->globalActor(), [], $data['reason'] ?? null, $previous, $data['status']);
        });

        return redirect()->route('admin.country-admins.collaboration-agreements.edit', [$administrator, $agreement])
            ->with('success', 'Agreement status changed to '.$data['status'].'.');
    }

    public function adminDownload(AdminUser $administrator, int $document)
    {
        $this->ensureMpo($administrator);
        $record = MpoCollaborationAgreementDocument::query()
            ->whereKey($document)
            ->whereHas('agreement', fn ($query) => $query->where('admin_user_id', $administrator->id))
            ->firstOrFail();

        return $this->serveDocument($record);
    }

    private function validateTerms(Request $request, ?string $onboardingDate = null): array
    {
        return $request->validate([
            'agreement_type' => ['required', Rule::in(['mpo'])],
            'start_date' => ['required', 'date', ...($onboardingDate ? ['after_or_equal:'.$onboardingDate] : [])],
            'commission_model' => ['required', Rule::in(['percentage'])],
            'commission_value' => ['required', 'numeric', 'between:0,100', 'decimal:0,4'],
            'marketing_contribution_percent' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,4'],
            'marketing_contribution_payer' => ['nullable', 'required_with:marketing_contribution_percent', Rule::in(['mpo', 'operator'])],
            'responsibilities_document' => ['nullable', 'file', 'mimes:pdf,docx', 'max:10240'],
            'management_contact_name' => ['nullable', 'string', 'max:191'],
            'management_contact_email' => ['nullable', 'email', 'max:191'],
            'management_contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'management_contact_mobile' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'accounting_contact_name' => ['nullable', 'string', 'max:191'],
            'accounting_contact_email' => ['nullable', 'email', 'max:191'],
            'accounting_contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'accounting_contact_mobile' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
        ]);
    }

    private function fillContacts(MpoCollaborationAgreement $agreement, array $data): void
    {
        foreach (array_keys($this->contactSnapshot($agreement)) as $field) {
            if (array_key_exists($field, $data)) {
                $agreement->{$field} = filled($data[$field]) ? trim($data[$field]) : null;
            }
        }
    }

    private function contactSnapshot(MpoCollaborationAgreement $agreement): array
    {
        return [
            'management_contact_name' => $agreement->management_contact_name,
            'management_contact_email' => $agreement->management_contact_email,
            'management_contact_phone' => $agreement->management_contact_phone,
            'management_contact_mobile' => $agreement->management_contact_mobile,
            'accounting_contact_name' => $agreement->accounting_contact_name,
            'accounting_contact_email' => $agreement->accounting_contact_email,
            'accounting_contact_phone' => $agreement->accounting_contact_phone,
            'accounting_contact_mobile' => $agreement->accounting_contact_mobile,
        ];
    }

    private function normalizeDecimal(string|int|float $value): string
    {
        [$whole, $fraction] = array_pad(explode('.', (string) $value, 2), 2, '');

        return (ltrim($whole, '0') ?: '0').'.'.str_pad(substr($fraction, 0, 4), 4, '0');
    }

    private function storeUpload(Request $request): ?array
    {
        if (! $request->hasFile('responsibilities_document')) {
            return null;
        }
        $file = $request->file('responsibilities_document');
        $path = $file->store('mpo-agreements', self::PRIVATE_DISK);
        if (! is_string($path) || $path === '') {
            throw new RuntimeException('The responsibilities document could not be stored.');
        }

        return [
            'path' => $path,
            'name' => $this->safeFilename($file->getClientOriginalName()),
            'mime' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
        ];
    }

    private function attachDocument(MpoCollaborationAgreement $agreement, array $document, int $adminId): void
    {
        $agreement->documents()->where('is_current', true)->update(['is_current' => false]);
        $agreement->documents()->create([
            'storage_path' => $document['path'],
            'original_name' => $document['name'],
            'mime_type' => $document['mime'],
            'size_bytes' => $document['size'],
            'uploaded_by_admin_id' => $adminId,
            'is_current' => true,
        ]);
        $this->recordEvent($agreement, 'document_uploaded', $this->globalActor(), [
            'name' => $document['name'],
            'size_bytes' => $document['size'],
        ]);
    }

    private function serveDocument(MpoCollaborationAgreementDocument $document)
    {
        abort_unless(Storage::disk(self::PRIVATE_DISK)->exists($document->storage_path), 404);
        Log::info('MPO agreement document downloaded.', [
            'agreement_id' => $document->agreement_id,
            'document_id' => $document->id,
        ]);

        return Storage::disk(self::PRIVATE_DISK)->download(
            $document->storage_path,
            $document->original_name,
            ['Content-Type' => $document->mime_type, 'X-Content-Type-Options' => 'nosniff'],
        );
    }

    private function recordEvent(
        MpoCollaborationAgreement $agreement,
        string $type,
        AdminUser|MpoStaffUser|null $actor,
        array $changes = [],
        ?string $reason = null,
        ?string $fromStatus = null,
        ?string $toStatus = null,
    ): void {
        $agreement->events()->create([
            'event_type' => $type,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changes' => $changes ?: null,
            'reason' => $reason,
            'actor_type' => $actor instanceof MpoStaffUser ? 'mpo_staff' : ($actor ? $actor->admin_type : 'system'),
            'actor_id' => $actor?->id,
        ]);
    }

    private function termsSnapshot(MpoCollaborationAgreement $agreement): array
    {
        return [
            'agreement_type' => $agreement->agreement_type,
            'start_date' => $agreement->start_date?->toDateString(),
            'commission_model' => $agreement->commission_model,
            'commission_value' => (string) $agreement->commission_value,
            'marketing_contribution_percent' => $agreement->marketing_contribution_percent === null
                ? null
                : (string) $agreement->marketing_contribution_percent,
            'marketing_contribution_payer' => $agreement->marketing_contribution_payer,
        ];
    }

    private function ensureMpo(AdminUser $administrator): void
    {
        abort_unless($administrator->admin_type === 'mpo', 404);
    }

    private function ensureOwnedAgreement(AdminUser $administrator, MpoCollaborationAgreement $agreement): void
    {
        $this->ensureMpo($administrator);
        abort_unless((int) $agreement->admin_user_id === (int) $administrator->id, 404);
    }

    private function globalActor(): ?AdminUser
    {
        return AdminUser::query()->whereKey(session('admin_id'))->where('admin_type', 'global')->first();
    }

    private function safeFilename(string $filename): string
    {
        $filename = basename(str_replace('\\', '/', $filename));

        return preg_replace('/[^A-Za-z0-9._ -]/', '_', $filename) ?: 'responsibilities-document';
    }
}
