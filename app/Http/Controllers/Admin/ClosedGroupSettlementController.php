<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRef;
use App\Models\PaymentTransaction;
use App\Services\AdminGroupBookingSettlementService;
use App\Services\BookingOrderStatusSynchronizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClosedGroupSettlementController extends Controller
{
    public function index(Request $request, AdminGroupBookingSettlementService $settlements)
    {
        $order = null;
        $reference = trim((string) $request->query('booking_reference', ''));

        if ($reference !== '') {
            try {
                $order = $settlements->findEligibleOrder($reference);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
                return redirect()->route('admin.closed-groups.settlement')
                    ->withInput(['booking_reference' => $reference])
                    ->with('error', 'No eligible admin-created group booking was found for that common booking reference.');
            }
        }

        $recentOrders = BookingRef::query()
            ->whereHas('bookings', fn ($query) => $query
                ->where('booking_type', 'open-group')
                ->where('is_admin_created', true))
            ->with(['trip', 'paymentTransaction'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.closed_groups.settlement', compact('order', 'reference', 'recentOrders'));
    }

    public function downloadInvoice(BookingRef $bookingRef, AdminGroupBookingSettlementService $settlements)
    {
        $order = $settlements->findEligibleOrder($bookingRef->booking_ref_code);
        $tableRows = '';
        foreach ($order['items'] as $item) {
            $tableRows .= '<tr>'
                . '<td>' . e($item['type']) . '</td>'
                . '<td>' . e($item['name']) . '<br><small>' . e($item['details']) . '</small></td>'
                . '<td>' . e($item['reference']) . '</td>'
                . '<td>' . e(($item['date'] ?? 'N/A') . (($item['end_date'] ?? null) && $item['end_date'] !== $item['date'] ? ' to ' . $item['end_date'] : '')) . '</td>'
                . '<td align="right">' . number_format((float) $item['quantity'], 2) . ' × ' . number_format((float) $item['unit_price'], 2) . '</td>'
                . '<td align="right">' . number_format((float) $item['amount'], 2) . '</td>'
                . '</tr>';
        }

        $groupName = e($order['group']?->name ?? 'Group booking');
        $travellerName = e($order['traveller']?->name ?? 'Traveller');
        $total = number_format($order['total'], 2);
        $referenceCode = e($order['booking_ref']->booking_ref_code);
        $invoiceDate = optional($order['booking_ref']->created_at)->format('d/m/Y') ?? now()->format('d/m/Y');
        $html = '<h1>Group Booking Invoice</h1>'
            . '<p><strong>Common Booking Reference:</strong> ' . $referenceCode . '</p>'
            . '<p><strong>Group:</strong> ' . $groupName . '<br><strong>Traveller:</strong> ' . $travellerName
            . '<br><strong>Invoice Date:</strong> ' . e($invoiceDate) . '</p>'
            . '<table border="1" cellpadding="6"><thead><tr><th>Service</th><th>Description</th><th>Service Reference</th><th>Date</th><th>Qty × Unit (USD)</th><th>Amount (USD)</th></tr></thead>'
            . '<tbody>' . $tableRows . '</tbody></table>'
            . (abs($order['order_adjustment']) >= 0.01 ? '<p align="right">Persisted order adjustment: USD ' . number_format($order['order_adjustment'], 2) . '</p>' : '')
            . '<h2 align="right">Total: USD ' . $total . '</h2>';

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Holidays.io');
        $pdf->SetTitle('Invoice ' . $referenceCode);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 9);
        $pdf->writeHTML($html, true, false, true, false, '');

        return response()->streamDownload(
            fn () => print $pdf->Output('invoice-' . $order['booking_ref']->id . '.pdf', 'S'),
            'invoice-' . $order['booking_ref']->id . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function downloadReceipt(BookingRef $bookingRef, AdminGroupBookingSettlementService $settlements)
    {
        $order = $settlements->findEligibleOrder($bookingRef->booking_ref_code);
        $path = $order['payment']->bank_transfer_receipt_path;
        abort_if(!$path || !Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download(
            $path,
            'bank-transfer-receipt-' . $bookingRef->id . '.' . pathinfo($path, PATHINFO_EXTENSION)
        );
    }

    public function submitProof(Request $request, BookingRef $bookingRef, AdminGroupBookingSettlementService $settlements)
    {
        $request->validate([
            'bank_transfer_date' => 'required|date|before_or_equal:today',
            'receipt' => 'required|file|mimes:pdf,png,jpg,jpeg|max:5120',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $order = $settlements->findEligibleOrder($bookingRef->booking_ref_code);
        $payment = $order['payment'];
        if ($payment->status === 'paid' || $payment->settlement_status === 'verified_settled') {
            throw ValidationException::withMessages(['receipt' => 'This group booking has already been verified and settled.']);
        }
        if ($payment->bank_transfer_receipt_path) {
            return redirect()->route('admin.closed-groups.settlement', ['booking_reference' => $bookingRef->booking_ref_code])
                ->with('status', 'Receipt already submitted. The existing submission remains pending verification.');
        }

        $path = null;
        $storedPath = null;
        try {
            $path = DB::transaction(function () use ($request, $payment, &$storedPath): ?string {
                $lockedPayment = PaymentTransaction::whereKey($payment->id)->lockForUpdate()->firstOrFail();
                if ($lockedPayment->bank_transfer_receipt_path) {
                    return null;
                }
                if ($lockedPayment->status === 'paid' || $lockedPayment->settlement_status === 'verified_settled') {
                    throw ValidationException::withMessages(['receipt' => 'This group booking has already been verified and settled.']);
                }

                $storedPath = $request->file('receipt')->store('bank-transfer-receipts', 'local');
                $lockedPayment->forceFill([
                    'bank_transfer_date' => $request->date('bank_transfer_date'),
                    'bank_transfer_receipt_path' => $storedPath,
                    'admin_notes' => $request->input('admin_notes'),
                    'submitted_by' => (int) session('admin_id'),
                    'submitted_at' => now(),
                    'settlement_status' => 'pending_verification',
                    'status' => 'pending',
                ])->save();

                return $storedPath;
            });
        } catch (\Throwable $exception) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }
            throw $exception;
        }

        if (!$path) {
            return redirect()->route('admin.closed-groups.settlement', ['booking_reference' => $bookingRef->booking_ref_code])
                ->with('status', 'Receipt already submitted. The existing submission remains pending verification.');
        }

        return redirect()->route('admin.closed-groups.settlement', ['booking_reference' => $bookingRef->booking_ref_code])
            ->with('status', 'Bank transfer proof submitted. Payment is pending verification.');
    }

    public function verify(
        BookingRef $bookingRef,
        AdminGroupBookingSettlementService $settlements,
        BookingOrderStatusSynchronizer $statusSynchronizer
    )
    {
        $order = $settlements->findEligibleOrder($bookingRef->booking_ref_code);

        DB::transaction(function () use ($order, $statusSynchronizer): void {
            $payment = PaymentTransaction::whereKey($order['payment']->id)->lockForUpdate()->firstOrFail();
            if ($payment->settlement_status === 'verified_settled' || $payment->status === 'paid') {
                $statusSynchronizer->markProcessingForBookingReference((int) $order['booking_ref']->id);
                return;
            }
            if (!$payment->bank_transfer_receipt_path || !$payment->submitted_at) {
                throw ValidationException::withMessages(['receipt' => 'A transfer receipt must be submitted before verification.']);
            }

            $payment->forceFill([
                'status' => 'paid',
                'settlement_status' => 'verified_settled',
                'verified_by' => (int) session('admin_id'),
                'verified_at' => now(),
            ])->save();

            $statusSynchronizer->markProcessingForBookingReference((int) $order['booking_ref']->id);
        });

        return redirect()->route('admin.closed-groups.settlement', ['booking_reference' => $bookingRef->booking_ref_code])
            ->with('status', 'Bank transfer verified and settled.');
    }
}