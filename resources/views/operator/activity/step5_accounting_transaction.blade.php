@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        @php $currentStep = 5; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.activity._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,0.07);">
                    <h2 style="font-weight:700;margin-bottom:12px;">Step 5: Accounting & Transaction</h2>
                    <p style="color:#666;margin-bottom:24px;">Service ID: <strong>{{ $activity->service_id }}</strong></p>

                    {{-- Success/Error Messages --}}
                    @if($errors->any())
                    <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                        <h5 style="margin-top:0;color:#c62828;">❌ Validation Errors:</h5>
                        @foreach($errors->all() as $error)
                            <div style="margin-bottom:4px;">• {{ $error }}</div>
                        @endforeach
                    </div>
                    @endif

                    @if(session('success'))
                    <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;">
                        <strong>✓ {{ session('success') }}</strong>
                    </div>
                    @endif

                    @if(session('error'))
                    <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                        <strong>✗ {{ session('error') }}</strong>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('operator.activity.step5.save', $activity->id) }}">
                        @csrf

                        {{-- Section 1: Bank Account Details --}}
                        <div style="margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Bank Account Details</h5>

                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">
                                    <input type="checkbox" id="bank_same_as_operator" style="margin-right:6px;"> 
                                    Same as Operator
                                </label>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="bank_account_holder_name" 
                                           id="bank_account_holder_name" 
                                           class="form-control @error('bank_account_holder_name') is-invalid @enderror" 
                                           required 
                                           value="{{ old('bank_account_holder_name', $accounting->bank_account_holder_name ?? '') }}">
                                    @error('bank_account_holder_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="bank_name" 
                                           id="bank_name" 
                                           class="form-control @error('bank_name') is-invalid @enderror" 
                                           required 
                                           value="{{ old('bank_name', $accounting->bank_name ?? '') }}">
                                    @error('bank_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="account_number" 
                                           id="account_number" 
                                           class="form-control @error('account_number') is-invalid @enderror" 
                                           required 
                                           value="{{ old('account_number', $accounting->account_number ?? '') }}">
                                    @error('account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">IBAN</label>
                                    <input type="text" 
                                           name="iban" 
                                           id="iban" 
                                           class="form-control @error('iban') is-invalid @enderror" 
                                           value="{{ old('iban', $accounting->iban ?? '') }}">
                                    @error('iban')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">SWIFT Code</label>
                                    <input type="text" 
                                           name="swift_code" 
                                           id="swift_code" 
                                           class="form-control @error('swift_code') is-invalid @enderror" 
                                           value="{{ old('swift_code', $accounting->swift_code ?? '') }}">
                                    @error('swift_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- VAT Section --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">
                                        <input type="checkbox" name="vat_exempted" id="vat_exempted" value="1" {{ old('vat_exempted', $accounting->vat_exempted ?? false) ? 'checked' : '' }}> 
                                        VAT Exempted
                                    </label>
                                    <small style="color:#666;display:block;margin-top:4px;">If checked, VAT Number becomes optional</small>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">VAT Number <span class="text-danger" id="vat_required_asterisk">*</span></label>
                                    <label style="margin-bottom:0;margin-left:20px;font-weight:300;">
                                        <input type="checkbox" id="vat_same_as_operator" style="margin-right:6px;"> 
                                        Same as Operator
                                    </label>
                                    <input type="text" 
                                           name="vat_number" 
                                           id="vat_number" 
                                           class="form-control @error('vat_number') is-invalid @enderror" 
                                           value="{{ old('vat_number', $accounting->vat_number ?? '') }}">
                                    @error('vat_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Tax & Service Charges --}}
                        <div style="border-top:1px solid #eee;padding-top:20px;margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Tax & Service Charges</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Tax Type</label>
                                    <select name="tax_type" id="tax_type" class="form-control">
                                        <option value="None" {{ old('tax_type', $accounting->tax_type ?? 'None') === 'None' ? 'selected' : '' }}>None</option>
                                        <option value="Tourism" {{ old('tax_type', $accounting->tax_type ?? 'None') === 'Tourism' ? 'selected' : '' }}>Tourism Tax</option>
                                        <option value="City" {{ old('tax_type', $accounting->tax_type ?? 'None') === 'City' ? 'selected' : '' }}>City Tax</option>
                                        <option value="Environmental" {{ old('tax_type', $accounting->tax_type ?? 'None') === 'Environmental' ? 'selected' : '' }}>Environmental Tax</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;" id="tax_charges_basis_label">Tax Charges Basis</label>
                                    <select name="tax_charges_basis" id="tax_charges_basis" class="form-control">
                                        <option value="">Select basis</option>
                                        <option value="Per Activity" {{ old('tax_charges_basis', $accounting->tax_charges_basis ?? '') === 'Per Activity' ? 'selected' : '' }}>Per Activity</option>
                                        <option value="Per Person" {{ old('tax_charges_basis', $accounting->tax_charges_basis ?? '') === 'Per Person' ? 'selected' : '' }}>Per Person</option>
                                        <option value="Per Adult" {{ old('tax_charges_basis', $accounting->tax_charges_basis ?? '') === 'Per Adult' ? 'selected' : '' }}>Per Adult</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3" id="tax_charges_fields" style="display: {{ old('tax_type', $accounting->tax_type ?? 'None') === 'None' ? 'none' : 'flex' }};">
                                <div class="col-md-4">
                                    <label style="font-weight:600;">Tax Charges Type</label>
                                    <select name="tax_charges_type" id="tax_charges_type" class="form-control">
                                        <option value="">Select type</option>
                                        <option value="Amount" {{ old('tax_charges_type', $accounting->tax_charges_type ?? '') === 'Amount' ? 'selected' : '' }}>Amount</option>
                                        <option value="Percentage" {{ old('tax_charges_type', $accounting->tax_charges_type ?? '') === 'Percentage' ? 'selected' : '' }}>Percentage</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label style="font-weight:600;">Tax Value</label>
                                    <input type="number" 
                                           name="tax_charges_value" 
                                           id="tax_charges_value" 
                                           class="form-control" 
                                           step="0.01" 
                                           min="0" 
                                           value="{{ old('tax_charges_value', $accounting->tax_charges_value ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Tax Payment Collection</label>
                                    <select name="tax_payment_collection" id="tax_payment_collection" class="form-control">
                                        <option value="">Select collection method</option>
                                        <option value="Operator" {{ old('tax_payment_collection', $accounting->tax_payment_collection ?? '') === 'Operator' ? 'selected' : '' }}>Operator</option>
                                        <option value="MPO" {{ old('tax_payment_collection', $accounting->tax_payment_collection ?? '') === 'MPO' ? 'selected' : '' }}>MPO</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Agreement & Commission (Read-Only/Auto-filled) --}}
                        <div style="border-top:1px solid #eee;padding-top:20px;margin-bottom:24px;background:#f9f9f9;padding:16px;border-radius:6px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Agreement & Commission (Read-Only)</h5>
                            <p style="color:#666;margin-bottom:16px;font-size:14px;">
                                <i class="fas fa-info-circle"></i> Agreement and commission details are automatically set based on your HIO Agreement with the operator. These values cannot be modified here.
                            </p>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Agreement Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="agreement_name" 
                                           value="{{ old('agreement_name', $accounting->agreement_name ?? $operator->business->hio_agreement_name ?? 'OTO + Percentage') }}" 
                                           readonly 
                                           style="background:#fff;">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Commission Type</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="commission_type" 
                                           value="{{ old('commission_type', $accounting->commission_type ?? $operator->business->commission_type ?? 'Percentage') }}" 
                                           readonly 
                                           style="background:#fff;">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Commission Value</label>
                                    @php
                                        $displayValue = old('commission_value', $accounting->commission_value ?? $operator->business->commission_value ?? null);
                                        // Extract numeric value only for submission
                                        $numericValue = is_numeric($displayValue) ? $displayValue : preg_replace('/[^0-9.]/', '', $displayValue ?? '');
                                        $numericValue = $numericValue !== '' ? $numericValue : null;
                                    @endphp
                                    <input type="text" 
                                           class="form-control" 
                                           value="{{ $displayValue ?? 'N/A' }}" 
                                           readonly 
                                           style="background:#fff;">
                                    {{-- Hidden field with numeric value only --}}
                                    <input type="hidden" name="commission_value" value="{{ $numericValue }}">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Currency (Net)</label>
                                    <select name="currency_net" class="form-control" style="background:#fff;">
                                        <option value="MUR" {{ old('currency_net', $accounting->currency_net ?? 'MUR') === 'MUR' ? 'selected' : '' }}>MUR (Mauritian Rupee)</option>
                                        <option value="USD" {{ old('currency_net', $accounting->currency_net ?? '') === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                                        <option value="EUR" {{ old('currency_net', $accounting->currency_net ?? '') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                        <option value="GBP" {{ old('currency_net', $accounting->currency_net ?? '') === 'GBP' ? 'selected' : '' }}>GBP (British Pound)</option>
                                    </select>
                                    <small style="color:#666;display:block;margin-top:4px;">Processing in USD, net payment in MUR (MPO Controller can override)</small>
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="{{ route('operator.activity.show', $activity->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Activity
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save & Continue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

{{-- JavaScript for Dynamic Behavior --}}
<script>
    // Operator data for auto-fill
    const operatorBankAccountHolder = @json($operator->accounting->bank_account_holder_name ?? '');
    const operatorBankName = @json($operator->accounting->bank_name ?? '');
    const operatorAccountNumber = @json($operator->accounting->account_number ?? '');
    const operatorIban = @json($operator->accounting->iban ?? '');
    const operatorSwiftCode = @json($operator->accounting->swift_code ?? '');
    const operatorVatNumber = @json($operator->accounting->vat_number ?? '');

    // Fill bank details from operator
    document.getElementById('bank_same_as_operator').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('bank_account_holder_name').value = operatorBankAccountHolder;
            document.getElementById('bank_name').value = operatorBankName;
            document.getElementById('account_number').value = operatorAccountNumber;
            document.getElementById('iban').value = operatorIban;
            document.getElementById('swift_code').value = operatorSwiftCode;
        }
    });

    // Fill VAT number from operator
    document.getElementById('vat_same_as_operator').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('vat_number').value = operatorVatNumber;
        }
    });

    // Toggle VAT number requirement based on exemption
    document.getElementById('vat_exempted').addEventListener('change', function() {
        const vatInput = document.getElementById('vat_number');
        const vatRequired = document.getElementById('vat_required_asterisk');
        
        if (this.checked) {
            vatInput.removeAttribute('required');
            if (vatRequired) vatRequired.style.display = 'none';
        } else {
            vatInput.setAttribute('required', 'required');
            if (vatRequired) vatRequired.style.display = 'inline';
        }
    });

    // Toggle tax charge fields based on tax type
    document.getElementById('tax_type').addEventListener('change', function() {
        const taxChargesFields = document.getElementById('tax_charges_fields');
        const taxChargesBasis = document.getElementById('tax_charges_basis');
        const taxChargesLabel = document.getElementById('tax_charges_basis_label');
        
        if (this.value && this.value !== 'None') {
            taxChargesFields.style.display = 'flex';
            if (taxChargesBasis) {
                taxChargesBasis.setAttribute('required', 'required');
            }
        } else {
            taxChargesFields.style.display = 'none';
            if (taxChargesBasis) {
                taxChargesBasis.removeAttribute('required');
            }
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Trigger tax fields visibility on load
        const taxType = document.getElementById('tax_type').value;
        if (taxType && taxType !== 'None') {
            document.getElementById('tax_charges_fields').style.display = 'flex';
        }
    });
</script>

<!-- Back Button -->
<div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
    <a href="{{ route('operator.activity.show', $activity->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
        ← Back to Activity Overview
    </a>
</div>
@endsection