<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\FinancialSettlement;
use App\Models\MpoStaffUser;
use App\Services\FinancialSettlementService;
use App\Services\MpoStaffPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class FinancialSettlementController extends Controller
{
    public function index(Request $request)
    {
        [$mpo] = $this->context($request);
        $settlements = FinancialSettlement::query()
            ->where('mpo_admin_user_id', $mpo->id)
            ->with(['operator.business', 'country'])
            ->when($request->filled('type'), fn ($query) => $query->where('settlement_type', $request->input('type')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('operator'), function ($query) use ($request): void {
                $term = '%'.$request->input('operator').'%';
                $query->whereHas('operator', fn ($operator) => $operator->where(function ($matches) use ($term): void {
                    $matches->where('business_legal_name', 'like', $term)
                        ->orWhere('full_name', 'like', $term)
                        ->orWhereHas('business', fn ($business) => $business->where('business_name', 'like', $term));
                }));
            })
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('accounting.settlements.index', [
            'settlements' => $settlements,
            'reviews' => \App\Models\FinancialBookingReview::query()
                ->where('mpo_admin_user_id', $mpo->id)
                ->where('status', 'Open')
                ->with('operator')
                ->latest()
                ->limit(100)
                ->get(),
            'isGlobalAdmin' => false,
            'canGenerate' => $this->can($request, 'create'),
            'canRecordTransfer' => $this->can($request, 'update'),
            'canAuthorizeOperatorPayout' => $this->can($request, 'approve'),
        ]);
    }

    public function show(Request $request, int $settlement)
    {
        [$mpo] = $this->context($request);
        $record = FinancialSettlement::query()
            ->where('mpo_admin_user_id', $mpo->id)
            ->with(['operator.business', 'country', 'items', 'transfers', 'events'])
            ->findOrFail($settlement);

        return view('accounting.settlements.show', [
            'settlement' => $record,
            'transferNotifications' => Schema::hasTable('notifications')
                ? $mpo->notifications()
                    ->where('type', \App\Notifications\FinancialSettlementTransferNotification::class)
                    ->latest()
                    ->limit(10)
                    ->get()
                : collect(),
            'isGlobalAdmin' => false,
            'canRecordTransfer' => $this->can($request, 'update'),
            'canAuthorizeOperatorPayout' => $this->can($request, 'approve')
                && $record->settlement_type === 'operator_payout',
            'canAcknowledge' => false,
        ]);
    }

    public function generate(Request $request, FinancialSettlementService $settlements)
    {
        [$mpo, $staff] = $this->context($request);
        $result = $settlements->generateForMpo($mpo, $staff ?? $mpo);

        return redirect()->route('mpo.accounting-settlements.index')
            ->with('success', sprintf(
                'Generation complete: %d monthly HIO SLA and %d scheduled Operator payout booking allocations created; %d bookings require review.',
                $result['hio_sla'],
                $result['operator_payout'],
                $result['review_required'],
            ));
    }

    public function recordTransfer(
        Request $request,
        int $settlement,
        FinancialSettlementService $settlements,
    ) {
        [$mpo, $staff] = $this->context($request);
        $record = FinancialSettlement::query()->where('mpo_admin_user_id', $mpo->id)->findOrFail($settlement);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'transaction_number' => ['required', 'string', 'max:100'],
            'transferred_on' => ['required', 'date', 'before_or_equal:today'],
            'evidence' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $path = $request->file('evidence')->store('financial-settlements/transfers', 'local');
        $settlements->recordTransfer(
            $record,
            [...$data, 'evidence_path' => $path],
            $staff ? 'mpo_staff' : 'mpo',
            (int) ($staff?->id ?? $mpo->id),
        );

        return redirect()->route('mpo.accounting-settlements.show', $record)
            ->with('success', 'Transfer evidence recorded; the beneficiary must verify receipt before the settlement is marked paid.');
    }

    public function authorizeOperatorPayout(
        Request $request,
        int $settlement,
        FinancialSettlementService $settlements,
    ) {
        [$mpo, $staff] = $this->context($request);
        $record = FinancialSettlement::query()
            ->where('mpo_admin_user_id', $mpo->id)
            ->where('settlement_type', 'operator_payout')
            ->findOrFail($settlement);
        $settlements->authorizeOperatorPayout($record, $staff ? 'mpo_staff' : 'mpo', (int) ($staff?->id ?? $mpo->id));

        return redirect()->route('mpo.accounting-settlements.show', $record)
            ->with('success', 'Operator payout authorized. The MPO may now record the manual bank transfer.');
    }

    public function downloadEvidence(Request $request, int $settlement, int $transfer)
    {
        [$mpo] = $this->context($request);
        $record = FinancialSettlement::query()
            ->where('mpo_admin_user_id', $mpo->id)
            ->findOrFail($settlement);
        $evidencePath = $record->transfers()->whereKey($transfer)->value('evidence_path');
        abort_unless(is_string($evidencePath) && Storage::disk('local')->exists($evidencePath), 404);

        return Storage::disk('local')->download($evidencePath);
    }

    private function context(Request $request): array
    {
        $mpo = $request->attributes->get('mpo_admin');
        $staff = $request->attributes->get('mpo_staff_user');
        abort_unless($mpo instanceof AdminUser && $mpo->admin_type === 'mpo' && $mpo->country_id, 403);
        abort_unless($staff === null || ($staff instanceof MpoStaffUser && (int) $staff->admin_user_id === (int) $mpo->id), 403);

        return [$mpo, $staff];
    }

    private function can(Request $request, string $action): bool
    {
        $staff = $request->attributes->get('mpo_staff_user');

        return $staff === null
            || ($staff instanceof MpoStaffUser && app(MpoStaffPermissionService::class)->can($staff, 'accounting', $action));
    }
}
