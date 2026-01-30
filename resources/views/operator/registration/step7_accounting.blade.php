@extends('layouts.app')

@section('progressbar')
    @php
        $progress = \App\Models\OperatorRegistrationProgress::where('operator_id', auth()->user()->operator_id ?? null)->first();
        $completionPercent = isset($progress) ? round((($progress->step2_profile ?? 0)
            + ($progress->step3_legal ?? 0)
            + ($progress->step4_system_process ?? 0)
            + ($progress->step5_collaboration ?? 0)
            + ($progress->step6_users ?? 0)
            + ($progress->step7_accounting ?? 0)
            + ($progress->step8_operations ?? 0)
            + ($progress->step9_review ?? 0)) / 8 * 100) : 0;
    @endphp
    @include('operator.registration._progress', ['completionPercent' => $completionPercent])
@endsection

@section('content')
    @php $currentStep = 7; @endphp
    <div class="col-md-3">
        @include('operator.registration._sidebar', ['currentStep' => $currentStep, 'progress' => $progress ?? null])
    </div>
    <div class="col-md-9 d-flex align-items-center justify-content-center" style="min-height: 90vh;">
        <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 32px 32px 24px 32px; width: 100%; max-width: 700px;">
            <h2 style="font-weight: bold; margin-bottom: 24px;">ACCOUNTING & PAYOUTS</h2>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('operator.register.step7') }}">
                @csrf
                <div class="card mb-4">
                    <div class="card-body">
                        <h5>Bank Account Details</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Account Holder Name *</label>
                                <input type="text" name="bank_account_holder_name" class="form-control" required value="{{ old('bank_account_holder_name', $accounting?->bank_account_holder_name) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Bank Name *</label>
                                <input type="text" name="bank_name" class="form-control" required value="{{ old('bank_name', $accounting?->bank_name) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Account Number *</label>
                                <input type="text" name="account_number" class="form-control" required value="{{ old('account_number', $accounting?->account_number) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>IBAN</label>
                                <input type="text" name="iban" class="form-control" value="{{ old('iban', $accounting?->iban) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>SWIFT Code</label>
                                <input type="text" name="swift_code" class="form-control" value="{{ old('swift_code', $accounting?->swift_code) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Currency Preference</label>
                                <select name="currency_preference" class="form-control">
                                    <option value="MUR" {{ old('currency_preference', $accounting?->currency_preference) == 'MUR' ? 'selected' : '' }}>MUR - Mauritian Rupee</option>
                                    <option value="USD" {{ old('currency_preference', $accounting?->currency_preference) == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ old('currency_preference', $accounting?->currency_preference) == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                </select>
                            </div>
                        </div>
                        <h5 class="mt-4">Tax Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3 d-flex align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="vat_exempted" id="vat_exempted" value="1" {{ old('vat_exempted', $accounting?->vat_exempted) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="vat_exempted">VAT Exempted</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>VAT Number</label>
                                <input type="text" name="vat_number" class="form-control" value="{{ old('vat_number', $accounting?->vat_number) }}">
                            </div>
                        </div>
                        <h5 class="mt-4">Commission, Credit & Payment Settings</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Commission Type</label>
                                <select name="commission_type" class="form-control">
                                    <option value="Fixed" {{ old('commission_type', $accounting?->commission_type) == 'Fixed' ? 'selected' : '' }}>Fixed</option>
                                    <option value="Percentage" {{ old('commission_type', $accounting?->commission_type) == 'Percentage' ? 'selected' : '' }}>Percentage</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Credit Limit (Days)</label>
                                <input type="number" step="1" min="0" name="credit_limit_days" class="form-control" placeholder="Number of days (optional)" value="{{ old('credit_limit_days', $accounting?->credit_limit_days) }}">
                                <small class="text-muted d-block">Optional: number of days allowed as credit.</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Credit Limit (Amount)</label>
                                <input type="number" step="0.01" min="0" name="credit_limit_amount" class="form-control" placeholder="Amount (optional)" value="{{ old('credit_limit_amount', $accounting?->credit_limit_amount) }}">
                                <small class="text-muted d-block">Optional: monetary credit limit.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Maximum Credit Allowed (Credit Value)</label>
                                <input type="number" step="0.01" name="credit_value" class="form-control" value="{{ old('credit_value', $accounting?->credit_value) }}">
                                <small class="text-muted">Optional: maximum credit allowed for payouts.</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Payment Schedule</label>
                                <select name="payment_schedule" class="form-control">
                                    <option value="Monthly" {{ old('payment_schedule', $accounting?->payment_schedule) == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="On Request" {{ old('payment_schedule', $accounting?->payment_schedule) == 'On Request' ? 'selected' : '' }}>On Request</option>
                                    <option value="Service Provided" {{ old('payment_schedule', $accounting?->payment_schedule) == 'Service Provided' ? 'selected' : '' }}>Service Provided</option>
                                    <option value="Quarterly" {{ old('payment_schedule', $accounting?->payment_schedule) == 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                                </select>
                            </div>

                             <div class="col-md-4 mb-3">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="draft" {{ old('status', $accounting?->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="active" {{ old('status', $accounting?->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $accounting?->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <!-- Additional details button (opens modal) -->
                        <div class="d-flex justify-content-between mb-3">
                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#payoutModal">Additional Details</button>
                            <button type="submit" class="btn btn-primary">Save Accounting Details</button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Payouts Modal -->
            <div class="modal fade" id="payoutModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Additional Payout Details @if(auth()->user()->user_type === 'Operator') <small class="text-muted">(Read-only)</small>@endif</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="payoutForm" method="POST" action="{{ route('operator.register.step7.payouts.save') }}">
                                @csrf
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label>Beneficiary</label>
                                        <input type="text" class="form-control" value="{{ auth()->user()->business_legal_name ?? auth()->user()->full_name }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Beneficiary ID</label>
                                        <input type="text" class="form-control" value="{{ auth()->user()->operator_id }}" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label>Period Covered</label>
                                        <input type="text" name="period_covered" class="form-control" placeholder="YYYY-MM or YYYY-MM to YYYY-MM" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Currency</label>
                                        <select name="currency" class="form-control" required>
                                            <option value="MUR" {{ old('currency', $accounting?->currency_preference ?? 'MUR') == 'MUR' ? 'selected' : '' }}>MUR</option>
                                            <option value="USD" {{ old('currency', $accounting?->currency_preference ?? '') == 'USD' ? 'selected' : '' }}>USD</option>
                                            <option value="EUR" {{ old('currency', $accounting?->currency_preference ?? '') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <label>Total Commission</label>
                                        <input type="number" step="0.01" min="0" name="total_commission" class="form-control" value="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Adjustments</label>
                                        <input type="number" step="0.01" name="adjustments" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Processing Fee</label>
                                        <input type="number" step="0.01" min="0" name="processing_fee" class="form-control" value="0">
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label>Payout Amount</label>
                                        <input type="number" step="0.01" min="0" name="payout_amount" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Payout Method</label>
                                        <select name="payout_method" class="form-control" required>
                                            <option value="Bank">Bank</option>
                                            <option value="Wallet">Wallet</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label>Transaction Ref</label>
                                        <input type="text" name="transaction_ref" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Status</label>
                                        <select name="status" class="form-control" required>
                                            <option value="Pending">Pending</option>
                                            <option value="Processing">Processing</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Failed">Failed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label>Processed By</label>
                                        <input type="text" name="processed_by" class="form-control" value="{{ auth()->user()->full_name ?? auth()->user()->business_legal_name }}">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" id="payoutSaveBtn" class="btn btn-primary">Save</button>
                                </div>
                            </form>

                            <hr>
                            <h6 class="mt-3">Recent Payouts</h6>
                            <div class="table-responsive mt-2">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Payout ID</th>
                                            <th>Period</th>
                                            <th>Amount</th>
                                            <th>Currency</th>
                                            <th>Status</th>
                                            <th>Processed By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payouts ?? [] as $p)
                                            <tr>
                                                <td>{{ $p->payout_id }}</td>
                                                <td>{{ $p->period_covered }}</td>
                                                <td>{{ number_format($p->payout_amount, 2) }}</td>
                                                <td>{{ $p->currency }}</td>
                                                <td>{{ $p->status }}</td>
                                                <td>{{ $p->processed_by }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6">No payouts found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                // Toggle VAT Number visibility depending on VAT Exempted
                function toggleVatField() {
                    const vatEx = document.getElementById('vat_exempted');
                    const vatField = document.querySelector('input[name="vat_number"]')?.closest('.col-md-6');
                    if (!vatField) return;
                    if (vatEx.checked) {
                        vatField.style.display = 'none';
                    } else {
                        vatField.style.display = '';
                    }
                }
                document.getElementById('vat_exempted')?.addEventListener('change', toggleVatField);
                document.addEventListener('DOMContentLoaded', function() {
                    toggleVatField();

                    // If current user is an Operator, make the payouts modal read-only (can view but not add/edit)
                    const isOperator = {{ auth()->user()->user_type === 'Operator' ? 'true' : 'false' }};
                    if (isOperator) {
                        const payoutsForm = document.getElementById('payoutForm');
                        if (payoutsForm) {
                            // Disable form controls so operator can view but not modify
                            payoutsForm.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);

                            // Hide/disable the Save button
                            const saveBtn = document.getElementById('payoutSaveBtn');
                            if (saveBtn) {
                                saveBtn.disabled = true;
                                saveBtn.style.display = 'none';
                            }

                            // Keep the Cancel button enabled so they can close the modal
                            payoutsForm.querySelector('button[data-bs-dismiss]')?.removeAttribute('disabled');
                        }
                        // Allow the Add button to remain enabled so Operators can open the modal and view entries
                    }
                });
            </script>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
