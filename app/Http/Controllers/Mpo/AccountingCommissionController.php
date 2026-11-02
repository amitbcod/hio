<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\MpoAccountingConfiguration;
use App\Models\MpoAccountingConfigurationEvent;
use App\Models\MpoStaffUser;
use App\Services\MpoAccountingService;
use App\Services\MpoStaffPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class AccountingCommissionController extends Controller
{
    public function __construct(
        private readonly MpoAccountingService $accounting,
        private readonly MpoStaffPermissionService $permissions,
    ) {}

    public function show(Request $request)
    {
        $mpo = $request->attributes->get('mpo_admin');
        $actor = $request->attributes->get('mpo_staff_user', $mpo);

        return $this->render($mpo, $actor, false);
    }

    public function update(Request $request)
    {
        $mpo = $request->attributes->get('mpo_admin');
        $actor = $request->attributes->get('mpo_staff_user', $mpo);
        abort_unless($this->canUpdate($actor), 403);

        $validator = Validator::make($request->all(), [
            'tax_id' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9][A-Za-z0-9 .\\/-]*$/'],
            'vat_exempted' => ['nullable', 'boolean'],
            'vat_number' => [Rule::requiredIf(! $request->boolean('vat_exempted')), 'nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9][A-Za-z0-9 .\\/-]*$/'],
            'bank.account_holder_name' => ['required', 'string', 'max:191'],
            'bank.iban' => ['required', 'string', 'max:34'],
            'bank.bank_name' => ['required', 'string', 'max:191'],
            'bank.swift_bic' => ['required', 'string', 'max:11', 'regex:/^[A-Za-z]{4}[A-Za-z]{2}[A-Za-z0-9]{2}([A-Za-z0-9]{3})?$/'],
            'responsible_accountant_user_id' => [
                'nullable',
                'integer',
                Rule::exists('mpo_staff_users', 'id')->where(fn ($query) => $query
                    ->where('admin_user_id', $mpo->id)
                    ->where('status', 'Active')),
            ],
            'vat_percentage' => ['prohibited'],
            'country_id' => ['prohibited'],
            'admin_user_id' => ['prohibited'],
            'commission_value' => ['prohibited'],
            'commission_model' => ['prohibited'],
            'agreement_id' => ['prohibited'],
            'agreement_status' => ['prohibited'],
            'same_as_operator' => ['prohibited'],
            'inherited_vat_number' => ['prohibited'],
        ]);
        $validator->after(function ($validator) use ($request): void {
            $allowed = [
                '_token',
                '_method',
                'tax_id',
                'vat_exempted',
                'vat_number',
                'bank',
                'responsible_accountant_user_id',
                'vat_percentage',
                'country_id',
                'admin_user_id',
                'commission_value',
                'commission_model',
                'agreement_id',
                'agreement_status',
                'same_as_operator',
                'inherited_vat_number',
            ];
            $unexpected = array_diff(array_keys($request->all()), $allowed);
            if ($unexpected !== []) {
                $validator->errors()->add('request', 'The request contains unsupported accounting fields.');
            }
            $bankKeys = array_diff(array_keys((array) $request->input('bank', [])), [
                'account_holder_name',
                'iban',
                'bank_name',
                'swift_bic',
            ]);
            if ($bankKeys !== []) {
                $validator->errors()->add('bank', 'The bank details include unsupported fields.');
            }
            $iban = data_get($request->all(), 'bank.iban');
            if (is_string($iban) && ! $this->accounting->validIban($iban)) {
                $validator->errors()->add('bank.iban', 'Enter a valid IBAN, including its checksum.');
            }
        });
        $data = $validator->validate();

        $vatNumber = filled($data['vat_number'] ?? null) ? trim($data['vat_number']) : null;
        $vatExempted = $request->boolean('vat_exempted');
        $bank = [
            'account_holder_name' => trim($data['bank']['account_holder_name']),
            'iban' => strtoupper(preg_replace('/\s+/', '', $data['bank']['iban']) ?? ''),
            'bank_name' => trim($data['bank']['bank_name']),
            'swift_bic' => strtoupper(trim($data['bank']['swift_bic'])),
        ];
        $eligibleAccountantIds = collect($this->accounting->eligibleAccountants((int) $mpo->id))
            ->map(fn (MpoStaffUser $staff): int => (int) $staff->id);
        $accountantId = $data['responsible_accountant_user_id'] ?? null;
        if ($accountantId !== null && ! $eligibleAccountantIds->contains((int) $accountantId)) {
            return back()->withErrors(['responsible_accountant_user_id' => 'Select an active MPO staff member with accounting read permission.'])->withInput();
        }

        $configuration = DB::transaction(function () use ($mpo, $actor, $data, $bank, $vatNumber, $vatExempted, $accountantId): MpoAccountingConfiguration {
            $record = MpoAccountingConfiguration::query()->firstOrNew(['admin_user_id' => $mpo->id]);
            $oldBank = $record->bank_details;
            $oldVatNumber = $record->vat_number;
            $oldTaxId = $record->tax_id;
            $oldExempted = (bool) $record->vat_exempted;
            $oldAccountantId = $record->responsible_accountant_user_id;

            $record->tax_id = filled($data['tax_id'] ?? null) ? strtoupper(trim($data['tax_id'])) : null;
            $record->vat_number = $vatExempted ? ($vatNumber ?? $record->vat_number) : $vatNumber;
            $record->vat_exempted = $vatExempted;
            $record->bank_details = $bank;
            $record->responsible_accountant_user_id = $accountantId;
            if ($oldBank !== $bank) {
                $record->bank_details_changed_by_type = $actor instanceof MpoStaffUser ? 'mpo_staff' : 'mpo';
                $record->bank_details_changed_by_id = $actor->id;
                $record->bank_details_changed_at = now();
            }
            if (! $record->save()) {
                throw new RuntimeException('Accounting configuration could not be saved.');
            }

            $changed = [];
            foreach ([
                'tax_id' => [$oldTaxId, $record->tax_id],
                'vat_number' => [$oldVatNumber, $record->vat_number],
                'vat_exempted' => [$oldExempted, $vatExempted],
                'responsible_accountant_user_id' => [$oldAccountantId, $record->responsible_accountant_user_id],
            ] as $field => [$before, $after]) {
                if ($before !== $after) {
                    $changed[] = $field;
                }
            }
            if ($oldBank !== $bank) {
                $changed[] = 'bank_details';
            }

            if ($changed !== []) {
                MpoAccountingConfigurationEvent::query()->create([
                    'admin_user_id' => $mpo->id,
                    'event_type' => 'configuration_updated',
                    'actor_type' => $actor instanceof MpoStaffUser ? 'mpo_staff' : 'mpo',
                    'actor_id' => $actor->id,
                    'changes' => [
                        'fields' => $changed,
                        'bank_iban_last4' => substr($bank['iban'], -4),
                    ],
                ]);
            }

            return $record;
        });

        return redirect()->route('mpo.accounting-commission')->with('success', 'Accounting commission configuration saved.');
    }

    public function adminShow(AdminUser $administrator)
    {
        abort_unless($administrator->admin_type === 'mpo', 404);
        $administrator->load(['country', 'accountingConfiguration.responsibleAccountant', 'collaborationAgreements']);

        return $this->render($administrator, null, true);
    }

    private function render(AdminUser $mpo, AdminUser|MpoStaffUser|null $actor, bool $isAdminView)
    {
        $mpo->loadMissing(['country', 'accountingConfiguration.responsibleAccountant', 'collaborationAgreements']);
        $configuration = $mpo->accountingConfiguration;
        $agreement = $mpo->collaborationAgreements()->latest('id')->first();
        $bank = $configuration?->bank_details ?? [];
        $eligibleAccountants = $this->accounting->eligibleAccountants((int) $mpo->id);

        return view($isAdminView ? 'admin.country-admins.accounting-commission' : 'mpo.accounting.commission', [
            'mpoAdmin' => $mpo,
            'actor' => $actor,
            'configuration' => $configuration,
            'agreement' => $agreement,
            'eligibleAccountants' => $eligibleAccountants,
            'bank' => $bank,
            'canUpdate' => ! $isAdminView && $actor !== null && $this->canUpdate($actor),
            'isAdminView' => $isAdminView,
            'accountingService' => $this->accounting,
            'isComplete' => $this->accounting->isComplete(
                $configuration?->vat_number,
                (bool) $configuration?->vat_exempted,
                $mpo->country?->vat_percentage,
                $bank,
            ),
        ]);
    }

    private function canUpdate(AdminUser|MpoStaffUser $actor): bool
    {
        return ($actor instanceof AdminUser && $actor->admin_type === 'mpo')
            || ($actor instanceof MpoStaffUser && $this->permissions->can($actor, 'accounting', 'update'));
    }
}
