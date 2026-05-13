<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\AgaingencyPaymentService;
use Illuminate\Http\Request;

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
            // Assuming the transaction_ref is the payment-id for the API
            $callbacks = AgaingencyPaymentService::getPaymentCallbacks($transaction->transaction_ref);

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