<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\MpoPayoutConfiguration;
use App\Models\MpoPayoutConfigurationEvent;
use App\Models\MpoStaffUser;
use App\Services\MpoPayoutConfigurationService;
use App\Services\MpoStaffPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AccountingPayoutController extends Controller
{
    public function __construct(
        private readonly MpoPayoutConfigurationService $payouts,
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
            'payment_schedule' => ['required', 'string', Rule::in(['Monthly', 'Service Provided', 'On Request'])],
            'payout_method' => ['nullable', 'string', Rule::in(['Bank'])],
            'payout_id' => ['prohibited'],
            'admin_user_id' => ['prohibited'],
            'beneficiary_id' => ['prohibited'],
            'beneficiary_name' => ['prohibited'],
            'commission_model' => ['prohibited'],
            'commission_value' => ['prohibited'],
            'processing_fee' => ['prohibited'],
            'credit_limit_type' => ['prohibited'],
            'credit_value' => ['prohibited'],
            'adjustment_amount' => ['prohibited'],
            'status' => ['prohibited'],
        ]);
        $validator->after(function ($validator) use ($request): void {
            $allowed = ['_token', '_method', 'payment_schedule', 'payout_method', 'payout_id', 'admin_user_id', 'beneficiary_id', 'beneficiary_name', 'commission_model', 'commission_value', 'processing_fee', 'credit_limit_type', 'credit_value', 'adjustment_amount', 'status'];
            if (array_diff(array_keys($request->all()), $allowed) !== []) {
                $validator->errors()->add('request', 'The request contains unsupported payout configuration fields.');
            }
        });
        $data = $validator->validate();
        if (($data['payout_method'] ?? null) === 'Bank'
            && ! $this->payouts->bankSummary($mpo->accountingConfiguration)['valid']) {
            return back()
                ->withErrors(['payout_method' => 'Configure valid bank details in Accounting Commission before selecting Bank.'])
                ->withInput();
        }

        $changes = DB::transaction(function () use ($mpo, $actor, $data): array {
            $configuration = MpoPayoutConfiguration::query()->firstOrCreate(
                ['admin_user_id' => $mpo->id],
                ['payment_schedule' => null, 'payout_method' => null],
            );
            if (! $configuration->payout_id) {
                $configuration->payout_id = 'MPO-PAY-'.str_pad((string) $configuration->id, 6, '0', STR_PAD_LEFT);
            }

            $before = [
                'payment_schedule' => $configuration->payment_schedule,
                'payout_method' => $configuration->payout_method,
            ];
            $configuration->payment_schedule = $data['payment_schedule'];
            $configuration->payout_method = $data['payout_method'] ?? null;
            $configuration->saveOrFail();

            $changedFields = [];
            foreach (['payment_schedule', 'payout_method'] as $field) {
                if ($before[$field] !== $configuration->{$field}) {
                    $changedFields[$field] = [
                        'from' => $before[$field],
                        'to' => $configuration->{$field},
                    ];
                }
            }
            if ($changedFields !== []) {
                MpoPayoutConfigurationEvent::query()->create([
                    'payout_configuration_id' => $configuration->id,
                    'admin_user_id' => $mpo->id,
                    'event_type' => 'configuration_updated',
                    'actor_type' => $actor instanceof MpoStaffUser ? 'mpo_staff' : 'mpo',
                    'actor_id' => $actor->id,
                    'changes' => $changedFields,
                ]);
            }

            return $changedFields;
        });

        return redirect()->route('mpo.accounting-payouts')
            ->with('success', $changes === [] ? 'Payout configuration is unchanged.' : 'Payout preferences saved. No payout was requested or executed.');
    }

    public function adminShow(AdminUser $administrator)
    {
        abort_unless($administrator->admin_type === 'mpo', 404);
        $administrator->load(['country', 'accountingConfiguration', 'payoutConfiguration', 'collaborationAgreements']);

        return $this->render($administrator, null, true);
    }

    private function render(AdminUser $mpo, AdminUser|MpoStaffUser|null $actor, bool $isAdminView)
    {
        abort_unless($mpo instanceof AdminUser && $mpo->admin_type === 'mpo', 404);
        $mpo->loadMissing(['country', 'accountingConfiguration', 'payoutConfiguration', 'collaborationAgreements']);
        $configuration = $mpo->payoutConfiguration;

        return view('mpo.accounting.payouts', [
            'mpoAdmin' => $mpo,
            'configuration' => $configuration,
            'agreement' => $this->payouts->applicableAgreement($mpo),
            'bankSummary' => $this->payouts->bankSummary($mpo->accountingConfiguration),
            'completion' => $this->payouts->completionState($mpo, $configuration),
            'canUpdate' => ! $isAdminView && $actor !== null && $this->canUpdate($actor),
            'isAdminView' => $isAdminView,
        ]);
    }

    private function canUpdate(AdminUser|MpoStaffUser $actor): bool
    {
        return ($actor instanceof AdminUser && $actor->admin_type === 'mpo')
            || ($actor instanceof MpoStaffUser && $this->permissions->can($actor, 'accounting', 'update'));
    }
}
