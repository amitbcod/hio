@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,0.07);">
                    <h2 style="font-weight:700;margin-bottom:12px;">Step 5: Accounting & Transaction</h2>

                    @if($errors->any())
                        <div class="alert alert-danger"><ul style="margin-bottom:0;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('operator.accommodation.saveStep5', $accommodation->id) }}">
                        @csrf

                         <div style="margin-bottom:12px;">
                                <label style="font-weight:600;">Property ID</label>
                                <div><strong>{{ $accommodation->accommodation_id }}</strong></div>
                            </div>

                        {{-- Section 1: Core Accounting Details --}}
                        <div style="margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Bank Account Details</h5>

                           

                            <div class="col-md-6">
                                <label style="font-weight:600;"><input type="checkbox" id="bank_same_as_operator" style="margin-right:6px;"> Same as Operator</label>
                                <!-- <small style="color:#666;display:block;margin-top:4px;">Auto-fill from your operator banking details</small> -->
                            </div>

                            {{-- Bank Details --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Account Holder Name *</label>
                                    <input type="text" name="bank_account_holder_name" id="bank_account_holder_name" class="form-control" required value="{{ old('bank_account_holder_name', $accommodation->bank_account_holder_name ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Bank Name *</label>
                                    <input type="text" name="bank_name" id="bank_name" class="form-control" required value="{{ old('bank_name', $accommodation->bank_name ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Account Number *</label>
                                    <input type="text" name="account_number" id="account_number" class="form-control" required value="{{ old('account_number', $accommodation->account_number ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">IBAN</label>
                                    <input type="text" name="iban" id="iban" class="form-control" value="{{ old('iban', $accommodation->iban ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">SWIFT Code</label>
                                    <input type="text" name="swift_code" id="swift_code" class="form-control" value="{{ old('swift_code', $accommodation->swift_code ?? '') }}">
                                </div>
                              
                            </div>

                            {{-- VAT Section --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="checkbox" name="vat_exempted" id="vat_exempted" {{ $accommodation->vat_exempted ? 'checked' : '' }}> VAT Exempted</label>
                                    <small style="color:#666;display:block;margin-top:4px;">If checked, VAT Number becomes optional</small>
                                    
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">VAT Number</label>
                                    <label style="margin-bottom:0;margin-left:20px;font-weight:300;"><input type="checkbox" id="vat_same_as_operator" style="margin-right:6px;"> Same as Operator</label>
                                    <div style="display:flex;gap:8px;align-items:center;">
                                        <input type="text" name="vat_number" id="vat_number" class="form-control" value="{{ old('vat_number', $accommodation->vat_number ?? '') }}">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Tax & Service Charges --}}
                        <div style="border-top:1px solid #eee;padding-top:20px;margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Tax & Service Charges</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Tax Type *</label>
                                    <select name="tax_type" id="tax_type" class="form-control" required>
                                        <option value="">Select tax type</option>
                                        <option value="Tourism" {{ old('tax_type', $accommodation->tax_type ?? '') === 'Tourism' ? 'selected' : '' }}>Tourism</option>
                                        <option value="City Tax" {{ old('tax_type', $accommodation->tax_type ?? '') === 'City Tax' ? 'selected' : '' }}>City Tax</option>
                                        <option value="None" {{ old('tax_type', $accommodation->tax_type ?? '') === 'None' ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;" id="tax_charges_label" style="display:none;">Tax Charges Type</label>
                                    <select name="tax_charges_type" id="tax_charges_type" class="form-control" style="display:none;">
                                        <option value="">Select type</option>
                                        <option value="Per Unit" {{ old('tax_charges_type', $accommodation->tax_charges_type ?? '') === 'Per Unit' ? 'selected' : '' }}>Per Unit</option>
                                        <option value="Per Person" {{ old('tax_charges_type', $accommodation->tax_charges_type ?? '') === 'Per Person' ? 'selected' : '' }}>Per Person</option>
                                        <option value="Per Adult" {{ old('tax_charges_type', $accommodation->tax_charges_type ?? '') === 'Per Adult' ? 'selected' : '' }}>Per Adult</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3" id="tax_charges_fields" style="display:none;">
                                <div class="col-md-4">
                                    <label style="font-weight:600;">Value Type</label>
                                    <select name="tax_charges_value_type" id="tax_charges_value_type" class="form-control">
                                        <option value="">Select</option>
                                        <option value="Amount" {{ old('tax_charges_value_type', $accommodation->tax_charges_value_type ?? '') === 'Amount' ? 'selected' : '' }}>Amount</option>
                                        <option value="Percentage" {{ old('tax_charges_value_type', $accommodation->tax_charges_value_type ?? '') === 'Percentage' ? 'selected' : '' }}>Percentage</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label style="font-weight:600;">Value</label>
                                    <input type="number" name="tax_charges_value" id="tax_charges_value" class="form-control" step="0.01" min="0" value="{{ old('tax_charges_value', $accommodation->tax_charges_value ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Tax Collection Method *</label>
                                    <select name="tax_collection_method" id="tax_collection_method" class="form-control" required>
                                        <option value="">Select method</option>
                                        <option value="Operator" {{ old('tax_collection_method', $accommodation->tax_collection_method ?? '') === 'Operator' ? 'selected' : '' }}>Operator</option>
                                        <option value="MPO" {{ old('tax_collection_method', $accommodation->tax_collection_method ?? '') === 'MPO' ? 'selected' : '' }}>MPO</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Currency (Net)</label>
                                    <select name="currency_code" id="currency_code" class="form-control">
                                        <option value="MUR" {{ old('currency_code', $accommodation->currency_code ?? 'MUR') === 'MUR' ? 'selected' : '' }}>MUR (Mauritian Rupee)</option>
                                        <option value="USD" {{ old('currency_code', $accommodation->currency_code ?? '') === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                                        <option value="EUR" {{ old('currency_code', $accommodation->currency_code ?? '') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                        <option value="GBP" {{ old('currency_code', $accommodation->currency_code ?? '') === 'GBP' ? 'selected' : '' }}>GBP (British Pound)</option>
                                        <option value="INR" {{ old('currency_code', $accommodation->currency_code ?? '') === 'INR' ? 'selected' : '' }}>INR (Indian Rupee)</option>
                                        <option value="AED" {{ old('currency_code', $accommodation->currency_code ?? '') === 'AED' ? 'selected' : '' }}>AED (UAE Dirham)</option>
                                        <option value="CNY" {{ old('currency_code', $accommodation->currency_code ?? '') === 'CNY' ? 'selected' : '' }}>CNY (Chinese Yuan)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Agreement & Commission (Read-Only) --}}
                        <div style="border-top:1px solid #eee;padding-top:20px;margin-bottom:24px;background:#f9f9f9;padding:12px;border-radius:6px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Agreement & Commission (Read-Only)</h5>
                            <small style="color:#666;display:block;margin-bottom:12px;">Agreement and commission details are automatically set based on your HIO Agreement with the operator. These values cannot be modified here.</small>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Agreement Name</label>
                                    <input type="text" class="form-control" value="{{ old('agreement_name', $accommodation->agreement_name ?? $operator->business->hio_agreement_name ?? 'OTO + Percentage') }}" disabled readonly style="background:#fff;">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Commission Type</label>
                                    <input type="text" class="form-control" value="{{ old('commission_type', $accommodation->commission_type ?? $operator->business->commission_type ?? 'Percentage') }}" disabled readonly style="background:#fff;">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Commission Value</label>
                                    <input type="text" class="form-control" value="{{ old('commission_value', $accommodation->commission_value ?? $operator->business->commission_value ?? 'N/A') }}" disabled readonly style="background:#fff;">
                                </div>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div style="display:flex;justify-content:space-between;gap:12px;">
                            <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">← Back</a>
                            <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:8px 14px;border-radius:4px;">Save Accounting Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const taxType = document.getElementById('tax_type');
        const chargesLabel = document.getElementById('tax_charges_label');
        const chargesType = document.getElementById('tax_charges_type');
        const chargesFields = document.getElementById('tax_charges_fields');

        function updateTaxFields(){
            if (taxType.value !== 'None') {
                chargesLabel.style.display = 'block';
                chargesType.style.display = 'block';
                chargesType.setAttribute('required', 'required');
                chargesFields.style.display = 'flex';
            } else {
                chargesLabel.style.display = 'none';
                chargesType.style.display = 'none';
                chargesType.removeAttribute('required');
                chargesFields.style.display = 'none';
            }
        }

        taxType.addEventListener('change', updateTaxFields);
        updateTaxFields();

        // Same as Operator autofill for bank details
        const bankSame = document.getElementById('bank_same_as_operator');
        if (bankSame) {
            bankSame.addEventListener('change', function(){
                if (this.checked) {
                    document.getElementById('bank_account_holder_name').value = "{{ addslashes($operator->accounting->bank_account_holder_name ?? '') }}";
                    document.getElementById('bank_name').value = "{{ addslashes($operator->accounting->bank_name ?? '') }}";
                    document.getElementById('account_number').value = "{{ addslashes($operator->accounting->account_number ?? '') }}";
                    document.getElementById('iban').value = "{{ addslashes($operator->accounting->iban ?? '') }}";
                    document.getElementById('swift_code').value = "{{ addslashes($operator->accounting->swift_code ?? '') }}";
                }
            });
        }

        const vatSame = document.getElementById('vat_same_as_operator');
        if (vatSame) {
            vatSame.addEventListener('change', function(){
                if (this.checked) {
                    const val = "{{ addslashes($operator->accounting->vat_number ?? '') }}";
                    document.getElementById('vat_number').value = val;
                }
            });
        }

        // VAT exempted logic
        const vatExempted = document.getElementById('vat_exempted');
        const vatNumberField = document.getElementById('vat_number');
        if (vatExempted) {
            vatExempted.addEventListener('change', function(){
                if (this.checked) {
                    vatNumberField.removeAttribute('required');
                } else {
                    vatNumberField.setAttribute('required', 'required');
                }
            });
        }
    });
    </script>
    @endpush

@endsection
