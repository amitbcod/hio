<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\FinancialSettlement;
use App\Services\FinancialSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FinancialSettlementController extends Controller
{
    public function index(Request $request)
    {
        $settlements = FinancialSettlement::query()
            ->with(['operator.business', 'mpo', 'country'])
            ->when($request->filled('country_id'), fn ($query) => $query->where('country_id', $request->integer('country_id')))
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
            'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
            'reviews' => \App\Models\FinancialBookingReview::query()->where('status', 'Open')->with(['mpo', 'operator', 'country'])->latest()->limit(100)->get(),
            'isGlobalAdmin' => true,
            'canGenerate' => false,
            'canRecordTransfer' => false,
            'canVerifyOperatorTransfer' => false,
        ]);
    }

    public function show(int $settlement)
    {
        $record = FinancialSettlement::query()
            ->with(['operator.business', 'mpo', 'country', 'items', 'transfers', 'events'])
            ->findOrFail($settlement);

        return view('accounting.settlements.show', [
            'settlement' => $record,
            'isGlobalAdmin' => true,
            'canRecordTransfer' => false,
            'canAuthorizeOperatorPayout' => false,
            'canVerifyOperatorTransfer' => false,
            'canVerifyHioTransfer' => $record->settlement_type === 'hio_sla',
            'canIssueHioInvoice' => $record->settlement_type === 'hio_sla'
                && $record->status === 'Statement Generated',
            'canAcknowledge' => $record->settlement_type === 'hio_sla'
                && $record->status === 'Received',
        ]);
    }

    public function verify(
        int $settlement,
        int $transfer,
        FinancialSettlementService $settlements,
    ) {
        $record = FinancialSettlement::query()
            ->where('settlement_type', 'hio_sla')
            ->findOrFail($settlement);
        abort_unless($record->transfers()->whereKey($transfer)->exists(), 404);
        $adminId = (int) (session('admin_id') ?? 0);
        abort_if($adminId < 1, 403);
        $settlements->verifyTransfer($record, $transfer, 'global_admin', $adminId);

        return redirect()->route('admin.accounting-settlements.show', $record)
            ->with('success', 'HIO Global SLA transfer receipt verified.');
    }

    public function issueInvoice(
        Request $request,
        int $settlement,
        FinancialSettlementService $settlements,
    ) {
        $record = FinancialSettlement::query()->where('settlement_type', 'hio_sla')->findOrFail($settlement);
        $data = $request->validate([
            'invoice_reference' => ['required', 'string', 'max:64', 'unique:financial_settlements,invoice_reference'],
            'invoice_issued_on' => ['required', 'date'],
            'invoice_due_on' => ['required', 'date', 'after_or_equal:invoice_issued_on'],
        ]);
        $adminId = (int) (session('admin_id') ?? 0);
        abort_if($adminId < 1, 403);
        $settlements->issueHioInvoice(
            $record,
            $data['invoice_reference'],
            $data['invoice_issued_on'],
            $data['invoice_due_on'],
            'global_admin',
            $adminId,
        );

        return redirect()->route('admin.accounting-settlements.show', $record)
            ->with('success', 'Monthly HIO SLA invoice issued. The MPO can now record its manual USD bank transfer.');
    }

    public function acknowledgeReceipt(int $settlement, FinancialSettlementService $settlements)
    {
        $record = FinancialSettlement::query()->where('settlement_type', 'hio_sla')->findOrFail($settlement);
        $adminId = (int) (session('admin_id') ?? 0);
        abort_if($adminId < 1, 403);
        $settlements->acknowledgeHioReceipt($record, 'global_admin', $adminId);

        return redirect()->route('admin.accounting-settlements.show', $record)
            ->with('success', 'HIO Global SLA receipt acknowledged.');
    }

    public function downloadEvidence(int $settlement, int $transfer)
    {
        $record = FinancialSettlement::query()->findOrFail($settlement);
        $evidencePath = $record->transfers()->whereKey($transfer)->value('evidence_path');
        abort_unless(is_string($evidencePath) && Storage::disk('local')->exists($evidencePath), 404);

        return Storage::disk('local')->download($evidencePath);
    }
}
