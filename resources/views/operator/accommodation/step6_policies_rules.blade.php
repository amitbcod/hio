@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,0.07);">
                    <h2 style="font-weight:700;margin-bottom:12px;">Step 6: Policies & Rules</h2>

                    @if($errors->any())
                        <div class="alert alert-danger"><ul style="margin-bottom:0;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('operator.accommodation.saveStep6', $accommodation->id) }}">
                        @csrf

                        <div style="margin-bottom:12px;">
                            <label style="font-weight:600;">Property ID</label>
                            <div><strong>{{ $accommodation->accommodation_id }}</strong></div>
                        </div>

                        {{-- Section 1: Check-in/Check-out --}}
                        <div style="margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Check-in / Check-out</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Check-in Time</label>
                                    <input type="time" name="checkin_time" id="checkin_time" class="form-control" value="{{ old('checkin_time', $accommodation->checkin_time ? substr($accommodation->checkin_time, 0, 5) : '') }}">
                                    @error('checkin_time')
                                        <small style="color: red; display: block; margin-top: 4px;">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Check-out Time</label>
                                    <input type="time" name="checkout_time" id="checkout_time" class="form-control" value="{{ old('checkout_time', $accommodation->checkout_time ? substr($accommodation->checkout_time, 0, 5) : '') }}">
                                    @error('checkout_time')
                                        <small style="color: red; display: block; margin-top: 4px;">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label style="font-weight:600;">Check-in / Check-out Rules</label>
                                    <textarea name="checkin_checkout_rules" id="checkin_checkout_rules" class="form-control" rows="3" placeholder="e.g., Early check-in available from 10am (€15), Late check-out available until 6pm (€20)">{{ old('checkin_checkout_rules', $accommodation->checkin_checkout_rules ?? '') }}</textarea>
                                    <small style="color:#666;display:block;margin-top:4px;">Describe any early check-in, late check-out, or other rules</small>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Booking Window --}}
                        <div style="border-top:1px solid #eee;padding-top:20px;margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Booking Window</h5>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label style="font-weight:600;">Booking Window Rules</label>
                                    <textarea name="booking_window_rules" id="booking_window_rules" class="form-control" rows="3" placeholder="e.g., Minimum 3 days in advance, Maximum 12 months in advance">{{ old('booking_window_rules', $accommodation->booking_window_rules ?? '') }}</textarea>
                                    <small style="color:#666;display:block;margin-top:4px;">Specify minimum/maximum advance booking requirements</small>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Amendment Policy --}}
                        <div style="border-top:1px solid #eee;padding-top:20px;margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Amendment Policy</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="radio" name="amendment_policy_type" id="amendment_custom" value="custom" {{ old('amendment_policy_type', $accommodation->amendment_policy_type ?? 'custom') === 'custom' ? 'checked' : '' }}> Write Your Own</label>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="radio" name="amendment_policy_type" id="amendment_template" value="template" {{ old('amendment_policy_type', $accommodation->amendment_policy_type ?? '') === 'template' ? 'checked' : '' }}> Use HIO Template</label>
                                </div>
                            </div>

                            <div id="amendment_custom_field" class="row mb-3">
                                <div class="col-md-12">
                                    <textarea name="amendment_policy" id="amendment_policy" class="form-control" rows="4" placeholder="Describe guest change, date modification rules, and any associated charges">{{ old('amendment_policy', $accommodation->amendment_policy ?? '') }}</textarea>
                                </div>
                            </div>

                            <div id="amendment_template_field" class="row mb-3" style="display:none;">
                                <div class="col-md-12">
                                    <label style="font-weight:600;">Select HIO Template</label>
                                    <select name="amendment_policy_template_id" id="amendment_policy_template_id" class="form-control">
                                        <option value="">Choose a template</option>
                                        <option value="template1" {{ old('amendment_policy_template_id', $accommodation->amendment_policy_template_id ?? '') === 'template1' ? 'selected' : '' }}>Standard Amendment Policy</option>
                                        <option value="template2" {{ old('amendment_policy_template_id', $accommodation->amendment_policy_template_id ?? '') === 'template2' ? 'selected' : '' }}>Flexible Amendment Policy</option>
                                        <option value="template3" {{ old('amendment_policy_template_id', $accommodation->amendment_policy_template_id ?? '') === 'template3' ? 'selected' : '' }}>Non-Refundable Amendment Policy</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: Cancellation Policy --}}
                        <div style="border-top:1px solid #eee;padding-top:20px;margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Cancellation Policy *</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="radio" name="cancellation_policy_type" id="cancellation_custom" value="custom" {{ old('cancellation_policy_type', $accommodation->cancellation_policy_type ?? 'custom') === 'custom' ? 'checked' : '' }}> Write Your Own</label>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="radio" name="cancellation_policy_type" id="cancellation_template" value="template" {{ old('cancellation_policy_type', $accommodation->cancellation_policy_type ?? '') === 'template' ? 'checked' : '' }}> Use HIO Template</label>
                                </div>
                            </div>

                            <div id="cancellation_custom_field" class="row mb-3">
                                <div class="col-md-12">
                                    <textarea name="cancellation_policy" id="cancellation_policy" class="form-control" rows="4" placeholder="Describe your cancellation policy in detail" required>{{ old('cancellation_policy', $accommodation->cancellation_policy ?? '') }}</textarea>
                                </div>
                            </div>

                            <div id="cancellation_template_field" class="row mb-3" style="display:none;">
                                <div class="col-md-12">
                                    <label style="font-weight:600;">Select HIO Template</label>
                                    <select name="cancellation_policy_template_id" id="cancellation_policy_template_id" class="form-control">
                                        <option value="">Choose a template</option>
                                        <option value="template1" {{ old('cancellation_policy_template_id', $accommodation->cancellation_policy_template_id ?? '') === 'template1' ? 'selected' : '' }}>Flexible Cancellation (Free up to 7 days)</option>
                                        <option value="template2" {{ old('cancellation_policy_template_id', $accommodation->cancellation_policy_template_id ?? '') === 'template2' ? 'selected' : '' }}>Moderate Cancellation (Free up to 14 days)</option>
                                        <option value="template3" {{ old('cancellation_policy_template_id', $accommodation->cancellation_policy_template_id ?? '') === 'template3' ? 'selected' : '' }}>Strict Cancellation (Non-refundable)</option>
                                        <option value="template4" {{ old('cancellation_policy_template_id', $accommodation->cancellation_policy_template_id ?? '') === 'template4' ? 'selected' : '' }}>Non-Refundable</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Section 5: Cancellation Penalties --}}
                        <div style="border-top:1px solid #eee;padding-top:20px;margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Cancellation Penalties *</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Apply Cancellation Penalties? *</label>
                                    <select name="cancellation_penalties_enabled" id="cancellation_penalties_enabled" class="form-control" required>
                                        <option value="">Select option</option>
                                        <option value="1" {{ old('cancellation_penalties_enabled', $accommodation->cancellation_penalties_enabled ?? '') === '1' || old('cancellation_penalties_enabled', $accommodation->cancellation_penalties_enabled ?? '') == 1 ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ old('cancellation_penalties_enabled', $accommodation->cancellation_penalties_enabled ?? '') === '0' || old('cancellation_penalties_enabled', $accommodation->cancellation_penalties_enabled ?? '') == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>

                            <div id="cancellation_penalties_fields" style="display:none;">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label style="font-weight:600;">Penalty Type *</label>
                                        <select name="cancellation_penalty_type" id="cancellation_penalty_type" class="form-control">
                                            <option value="">Select type</option>
                                            <option value="Night" {{ old('cancellation_penalty_type', $accommodation->cancellation_penalty_type ?? '') === 'Night' ? 'selected' : '' }}>Per Night</option>
                                            <option value="Percentage" {{ old('cancellation_penalty_type', $accommodation->cancellation_penalty_type ?? '') === 'Percentage' ? 'selected' : '' }}>Percentage</option>
                                            <option value="Amount" {{ old('cancellation_penalty_type', $accommodation->cancellation_penalty_type ?? '') === 'Amount' ? 'selected' : '' }}>Amount</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label style="font-weight:600;">Penalty Value *</label>
                                        <input type="number" name="cancellation_penalty_value" id="cancellation_penalty_value" class="form-control" step="0.01" min="0" placeholder="Enter value" value="{{ old('cancellation_penalty_value', $accommodation->cancellation_penalty_value ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 6: Security Deposit Policy --}}
                        <div style="border-top:1px solid #eee;padding-top:20px;margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Security Deposit Policy</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="radio" name="security_deposit_policy_type" id="deposit_custom" value="custom" {{ old('security_deposit_policy_type', $accommodation->security_deposit_policy_type ?? 'custom') === 'custom' ? 'checked' : '' }}> Write Your Own</label>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="radio" name="security_deposit_policy_type" id="deposit_template" value="template" {{ old('security_deposit_policy_type', $accommodation->security_deposit_policy_type ?? '') === 'template' ? 'checked' : '' }}> Use HIO Template</label>
                                </div>
                            </div>

                            <div id="deposit_custom_field" class="row mb-3">
                                <div class="col-md-12">
                                    <textarea name="security_deposit_policy" id="security_deposit_policy" class="form-control" rows="3" placeholder="Describe deposit coverage, refund conditions, and damage assessment rules">{{ old('security_deposit_policy', $accommodation->security_deposit_policy ?? '') }}</textarea>
                                </div>
                            </div>

                            <div id="deposit_template_field" class="row mb-3" style="display:none;">
                                <div class="col-md-12">
                                    <label style="font-weight:600;">Select HIO Template</label>
                                    <select name="security_deposit_policy_template_id" id="security_deposit_policy_template_id" class="form-control">
                                        <option value="">Choose a template</option>
                                        <option value="template1" {{ old('security_deposit_policy_template_id', $accommodation->security_deposit_policy_template_id ?? '') === 'template1' ? 'selected' : '' }}>Standard Deposit Policy</option>
                                        <option value="template2" {{ old('security_deposit_policy_template_id', $accommodation->security_deposit_policy_template_id ?? '') === 'template2' ? 'selected' : '' }}>Pet-Friendly Deposit Policy</option>
                                        <option value="template3" {{ old('security_deposit_policy_template_id', $accommodation->security_deposit_policy_template_id ?? '') === 'template3' ? 'selected' : '' }}>No Deposit Policy</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Section 7: Deposit Settings --}}
                        <div style="border-top:1px solid #eee;padding-top:20px;margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Deposit Settings *</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Require Deposit? *</label>
                                    <select name="deposit_required" id="deposit_required" class="form-control" required>
                                        <option value="">Select option</option>
                                        <option value="1" {{ old('deposit_required', $accommodation->deposit_required ?? '') === '1' || old('deposit_required', $accommodation->deposit_required ?? '') == 1 ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ old('deposit_required', $accommodation->deposit_required ?? '') === '0' || old('deposit_required', $accommodation->deposit_required ?? '') == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>

                            <div id="deposit_settings_fields" style="display:none;">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label style="font-weight:600;">Deposit Type *</label>
                                        <select name="deposit_type" id="deposit_type" class="form-control">
                                            <option value="">Select type</option>
                                            <option value="Night" {{ old('deposit_type', $accommodation->deposit_type ?? '') === 'Night' ? 'selected' : '' }}>Per Night</option>
                                            <option value="Percentage" {{ old('deposit_type', $accommodation->deposit_type ?? '') === 'Percentage' ? 'selected' : '' }}>Percentage</option>
                                            <option value="Amount" {{ old('deposit_type', $accommodation->deposit_type ?? '') === 'Amount' ? 'selected' : '' }}>Amount</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label style="font-weight:600;">Deposit Value *</label>
                                        <input type="number" name="deposit_value" id="deposit_value" class="form-control" step="0.01" min="0" placeholder="Enter value" value="{{ old('deposit_value', $accommodation->deposit_value ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 8: Child & Infant Policies --}}
                        <div style="border-top:1px solid #eee;padding-top:20px;margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">Child & Infant Policies</h5>
                            <small style="color:#666;display:block;margin-bottom:12px;">Note: Associated fees/charges are set in Step 9b (Fees & Surcharges)</small>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Child Policy - Maximum Age</label>
                                    <input type="number" name="child_max_age" id="child_max_age" class="form-control" min="0" max="18" placeholder="e.g., 12" value="{{ old('child_max_age', $accommodation->child_max_age ?? '') }}">
                                    <small style="color:#666;display:block;margin-top:4px;">Ages 0-18 considered children</small>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Infant Policy - Maximum Age</label>
                                    <input type="number" name="infant_max_age" id="infant_max_age" class="form-control" min="0" max="5" placeholder="e.g., 2" value="{{ old('infant_max_age', $accommodation->infant_max_age ?? '') }}">
                                    <small style="color:#666;display:block;margin-top:4px;">Ages 0-5 considered infants</small>
                                </div>
                            </div>
                        </div>

                        {{-- Section 9: House Rules --}}
                        <div style="border-top:1px solid #eee;padding-top:20px;margin-bottom:24px;">
                            <h5 style="font-weight:600;margin-bottom:12px;">House Rules</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="radio" name="house_rules_type" id="house_rules_custom" value="custom" {{ old('house_rules_type', $accommodation->house_rules_type ?? 'custom') === 'custom' ? 'checked' : '' }}> Write Your Own</label>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="radio" name="house_rules_type" id="house_rules_template" value="template" {{ old('house_rules_type', $accommodation->house_rules_type ?? '') === 'template' ? 'checked' : '' }}> Use HIO Template</label>
                                </div>
                            </div>

                            <div id="house_rules_custom_field" class="row mb-3">
                                <div class="col-md-12">
                                    <textarea name="house_rules" id="house_rules" class="form-control" rows="4" placeholder="e.g., No smoking, No pets, Quiet hours 10pm-8am, Maximum guests allowed: X">{{ old('house_rules', $accommodation->house_rules ?? '') }}</textarea>
                                </div>
                            </div>

                            <div id="house_rules_template_field" class="row mb-3" style="display:none;">
                                <div class="col-md-12">
                                    <label style="font-weight:600;">Select HIO Template</label>
                                    <select name="house_rules_template_id" id="house_rules_template_id" class="form-control">
                                        <option value="">Choose a template</option>
                                        <option value="template1" {{ old('house_rules_template_id', $accommodation->house_rules_template_id ?? '') === 'template1' ? 'selected' : '' }}>Standard House Rules</option>
                                        <option value="template2" {{ old('house_rules_template_id', $accommodation->house_rules_template_id ?? '') === 'template2' ? 'selected' : '' }}>Pet-Friendly House Rules</option>
                                        <option value="template3" {{ old('house_rules_template_id', $accommodation->house_rules_template_id ?? '') === 'template3' ? 'selected' : '' }}>Shared Property Rules</option>
                                        <option value="template4" {{ old('house_rules_template_id', $accommodation->house_rules_template_id ?? '') === 'template4' ? 'selected' : '' }}>Luxury Property Rules</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div style="display:flex;justify-content:space-between;gap:12px;">
                            <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">← Back</a>
                            <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:8px 14px;border-radius:4px;">Save Policies & Rules</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        // Amendment Policy - Toggle between custom and template
        const amendmentCustomRadio = document.getElementById('amendment_custom');
        const amendmentTemplateRadio = document.getElementById('amendment_template');
        const amendmentCustomField = document.getElementById('amendment_custom_field');
        const amendmentTemplateField = document.getElementById('amendment_template_field');

        function updateAmendmentFields() {
            if (amendmentTemplateRadio.checked) {
                amendmentCustomField.style.display = 'none';
                amendmentTemplateField.style.display = 'block';
                document.getElementById('amendment_policy').removeAttribute('required');
            } else {
                amendmentCustomField.style.display = 'block';
                amendmentTemplateField.style.display = 'none';
                document.getElementById('amendment_policy').setAttribute('required', 'required');
            }
        }

        if (amendmentCustomRadio) amendmentCustomRadio.addEventListener('change', updateAmendmentFields);
        if (amendmentTemplateRadio) amendmentTemplateRadio.addEventListener('change', updateAmendmentFields);
        updateAmendmentFields();

        // Cancellation Policy - Toggle between custom and template
        const cancellationCustomRadio = document.getElementById('cancellation_custom');
        const cancellationTemplateRadio = document.getElementById('cancellation_template');
        const cancellationCustomField = document.getElementById('cancellation_custom_field');
        const cancellationTemplateField = document.getElementById('cancellation_template_field');

        function updateCancellationFields() {
            if (cancellationTemplateRadio.checked) {
                cancellationCustomField.style.display = 'none';
                cancellationTemplateField.style.display = 'block';
                document.getElementById('cancellation_policy').removeAttribute('required');
            } else {
                cancellationCustomField.style.display = 'block';
                cancellationTemplateField.style.display = 'none';
                document.getElementById('cancellation_policy').setAttribute('required', 'required');
            }
        }

        if (cancellationCustomRadio) cancellationCustomRadio.addEventListener('change', updateCancellationFields);
        if (cancellationTemplateRadio) cancellationTemplateRadio.addEventListener('change', updateCancellationFields);
        updateCancellationFields();

        // Cancellation Penalties - Show/hide penalty fields
        const cancellationPenaltiesSelect = document.getElementById('cancellation_penalties_enabled');
        const cancellationPenaltiesFields = document.getElementById('cancellation_penalties_fields');

        function updateCancellationPenalties() {
            if (cancellationPenaltiesSelect.value === '1') {
                cancellationPenaltiesFields.style.display = 'block';
                document.getElementById('cancellation_penalty_type').setAttribute('required', 'required');
                document.getElementById('cancellation_penalty_value').setAttribute('required', 'required');
            } else {
                cancellationPenaltiesFields.style.display = 'none';
                document.getElementById('cancellation_penalty_type').removeAttribute('required');
                document.getElementById('cancellation_penalty_value').removeAttribute('required');
            }
        }

        if (cancellationPenaltiesSelect) cancellationPenaltiesSelect.addEventListener('change', updateCancellationPenalties);
        updateCancellationPenalties();

        // Security Deposit Policy - Toggle between custom and template
        const depositCustomRadio = document.getElementById('deposit_custom');
        const depositTemplateRadio = document.getElementById('deposit_template');
        const depositCustomField = document.getElementById('deposit_custom_field');
        const depositTemplateField = document.getElementById('deposit_template_field');

        function updateDepositPolicyFields() {
            if (depositTemplateRadio.checked) {
                depositCustomField.style.display = 'none';
                depositTemplateField.style.display = 'block';
            } else {
                depositCustomField.style.display = 'block';
                depositTemplateField.style.display = 'none';
            }
        }

        if (depositCustomRadio) depositCustomRadio.addEventListener('change', updateDepositPolicyFields);
        if (depositTemplateRadio) depositTemplateRadio.addEventListener('change', updateDepositPolicyFields);
        updateDepositPolicyFields();

        // Deposit Settings - Show/hide deposit fields
        const depositRequiredSelect = document.getElementById('deposit_required');
        const depositSettingsFields = document.getElementById('deposit_settings_fields');

        function updateDepositSettings() {
            if (depositRequiredSelect.value === '1') {
                depositSettingsFields.style.display = 'block';
                document.getElementById('deposit_type').setAttribute('required', 'required');
                document.getElementById('deposit_value').setAttribute('required', 'required');
            } else {
                depositSettingsFields.style.display = 'none';
                document.getElementById('deposit_type').removeAttribute('required');
                document.getElementById('deposit_value').removeAttribute('required');
            }
        }

        if (depositRequiredSelect) depositRequiredSelect.addEventListener('change', updateDepositSettings);
        updateDepositSettings();

        // House Rules - Toggle between custom and template
        const houseRulesCustomRadio = document.getElementById('house_rules_custom');
        const houseRulesTemplateRadio = document.getElementById('house_rules_template');
        const houseRulesCustomField = document.getElementById('house_rules_custom_field');
        const houseRulesTemplateField = document.getElementById('house_rules_template_field');

        function updateHouseRulesFields() {
            if (houseRulesTemplateRadio.checked) {
                houseRulesCustomField.style.display = 'none';
                houseRulesTemplateField.style.display = 'block';
            } else {
                houseRulesCustomField.style.display = 'block';
                houseRulesTemplateField.style.display = 'none';
            }
        }

        if (houseRulesCustomRadio) houseRulesCustomRadio.addEventListener('change', updateHouseRulesFields);
        if (houseRulesTemplateRadio) houseRulesTemplateRadio.addEventListener('change', updateHouseRulesFields);
        updateHouseRulesFields();
    });
    </script>
    @endpush

@endsection
