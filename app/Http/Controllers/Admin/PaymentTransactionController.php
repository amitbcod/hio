<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\AgaingencyPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentTransactionController extends Controller
{
    public function index()
    {
        $transactions = PaymentTransaction::with('booking')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.payment-transactions.index', compact('transactions'));
    }

    public function show(PaymentTransaction $transaction)
    {
        $transaction->load('booking');

        return view('admin.payment-transactions.show', compact('transaction'));
    }

    public function getCallbacks(PaymentTransaction $transaction)
    {
        try {
            $paymentId = $transaction->payment_id;
            if (empty($paymentId) && Str::isUuid($transaction->transaction_ref)) {
                $paymentId = $transaction->transaction_ref;
            }

            if (empty($paymentId)) {
                throw new \RuntimeException('No valid external payment ID is available for this transaction.');
            }

            $callbacks = AgaingencyPaymentService::getPaymentCallbacks($paymentId);

            return response()->json([
                'success' => true,
                'callbacks' => $callbacks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}