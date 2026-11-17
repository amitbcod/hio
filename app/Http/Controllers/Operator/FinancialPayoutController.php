<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\FinancialSettlement;
use App\Models\Operator;
use App\Models\OperatorUser;
use App\Services\FinancialSettlementService;
use App\Services\OperatorStaffPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FinancialPayoutController extends Controller
{
    public function index(Request $request, OperatorStaffPermissionService $permissions)
    {
        $owner = $this->owner($request, $permissions);
        $payouts = FinancialSettlement::query()
            ->where('settlement_type', 'operator_payout')
            ->where('operator_id', $owner->id)
            ->with('items')
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->paginate(25);

        return view('operator.payouts.index', [
            'payouts' => $payouts,
            'canRequest' => $this->can($request, 'create', $permissions),
            'eligibleTotal' => $payouts->getCollection()->sum(fn (FinancialSettlement $payout) => (float) $payout->net_amount),
        ]);
    }

    public function show(Request $request, int $settlement, OperatorStaffPermissionService $permissions)
    {
        $owner = $this->owner($request, $permissions);
        $payout = FinancialSettlement::query()
            ->where('settlement_type', 'operator_payout')
            ->where('operator_id', $owner->id)
            ->with(['items', 'transfers', 'events'])
            ->findOrFail($settlement);

        return view('operator.payouts.show', [
            'payout' => $payout,
            'canAcknowledge' => $payout->status === 'Paid' && $this->can($request, 'update', $permissions),
        ]);
    }

    public function request(Request $request, FinancialSettlementService $settlements, OperatorStaffPermissionService $permissions)
    {
        $owner = $this->owner($request, $permissions);
        abort_unless($this->can($request, 'create', $permissions), 403);
        $created = $settlements->requestOperatorPayout($owner, $request->user('operator_staff') ?? $owner);

        return redirect()->route('operator.payouts.index')
            ->with('success', $created > 0
                ? 'Eligible On Request payout records were submitted for MPO processing.'
                : 'No new eligible On Request bookings were available.');
    }

    public function acknowledge(Request $request, int $settlement, FinancialSettlementService $settlements, OperatorStaffPermissionService $permissions)
    {
        $owner = $this->owner($request, $permissions);
        abort_unless($this->can($request, 'update', $permissions), 403);
        $payout = FinancialSettlement::query()
            ->where('settlement_type', 'operator_payout')
            ->where('operator_id', $owner->id)
            ->findOrFail($settlement);
        $actor = $request->user('operator_staff') ?? $owner;
        $settlements->acknowledgeOperatorPayment($payout, $actor instanceof OperatorUser ? 'operator_staff' : 'operator', (int) $actor->id);

        return redirect()->route('operator.payouts.show', $payout)
            ->with('success', 'Receipt acknowledged.');
    }

    public function downloadEvidence(
        Request $request,
        int $settlement,
        int $transfer,
        OperatorStaffPermissionService $permissions,
    ) {
        $owner = $this->owner($request, $permissions);
        $payout = FinancialSettlement::query()
            ->where('settlement_type', 'operator_payout')
            ->where('operator_id', $owner->id)
            ->findOrFail($settlement);
        $evidencePath = $payout->transfers()->whereKey($transfer)->value('evidence_path');
        abort_unless(is_string($evidencePath) && Storage::disk('local')->exists($evidencePath), 404);

        return Storage::disk('local')->download($evidencePath);
    }

    private function owner(Request $request, OperatorStaffPermissionService $permissions): Operator
    {
        $actor = $request->user('operator') ?? $request->user('operator_staff');
        abort_unless($actor instanceof Operator || $actor instanceof OperatorUser, 403);
        $owner = $actor instanceof Operator ? $actor : $permissions->owner($actor);
        abort_unless($owner instanceof Operator && $owner->is_owner === 'yes', 403);

        return $owner;
    }

    private function can(Request $request, string $action, OperatorStaffPermissionService $permissions): bool
    {
        $actor = $request->user('operator') ?? $request->user('operator_staff');

        return $actor instanceof Operator
            ? $permissions->can($actor, 'accounting', $action)
            : ($actor instanceof OperatorUser && $permissions->can($actor, 'accounting', $action));
    }
}
