@extends('layouts.app')

@section('title', 'Transport Setup | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            @include('operator.transport._steps_wizard_sidebar', ['step' => $step ?? 2])
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                <h2 style="font-weight:700;margin:0;">Step 2: {{ $title ?? 'Accounting and Transaction' }}</h2>
                <p style="margin:8px 0 0 0;color:#666;">{{ $description ?? 'Configure accounting and transaction preferences.' }}</p>
            </div>

            <form method="POST" action="{{ route('operator.transport.accounting-and-transaction.save') }}">
                @csrf
                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Sales Currency</label>
                            <input type="text" name="accounting_and_transaction[sales_currency]" class="form-control" required value="{{ old('accounting_and_transaction.sales_currency', data_get($transportSettings, 'accounting_and_transaction.sales_currency')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payout Currency</label>
                            <input type="text" name="accounting_and_transaction[payout_currency]" class="form-control" required value="{{ old('accounting_and_transaction.payout_currency', data_get($transportSettings, 'accounting_and_transaction.payout_currency')) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Model</label>
                        <textarea name="accounting_and_transaction[payment_model]" class="form-control" rows="3" required>{{ old('accounting_and_transaction.payment_model', data_get($transportSettings, 'accounting_and_transaction.payment_model')) }}</textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tax Registration Number</label>
                            <input type="text" name="accounting_and_transaction[tax_registration_number]" class="form-control" value="{{ old('accounting_and_transaction.tax_registration_number', data_get($transportSettings, 'accounting_and_transaction.tax_registration_number')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice Notes</label>
                            <textarea name="accounting_and_transaction[invoice_notes]" class="form-control" rows="3">{{ old('accounting_and_transaction.invoice_notes', data_get($transportSettings, 'accounting_and_transaction.invoice_notes')) }}</textarea>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save & Continue</button>
            </form>
        </div>
    </div>
</div>
@endsection
