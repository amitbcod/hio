@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding:24px;">
    @php $currentStep = 6; @endphp
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-md-3">
            @include('operator.activity._steps_sidebar')
        </div>

        {{-- Main Content --}}
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 16px rgba(0,0,0,0.07);">
                <div style="margin-bottom:24px;">
                    <h4 style="font-weight:600;color:#333;margin:0;">Step 6: Policies & Rules</h4>
                    <p style="color:#666;margin:8px 0 0 0;">Define booking rules, cancellation policies, and safety requirements</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success" style="border-radius:12px;margin-bottom:20px;">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" style="border-radius:12px;margin-bottom:20px;">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('operator.activity.step6.save', $activity->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Section 1: Booking Rules --}}
                    <div style="border-bottom:1px solid #eee;padding-bottom:20px;margin-bottom:24px;">
                        <h5 style="font-weight:600;margin-bottom:16px;">Booking Rules</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;">Service ID</label>
                                <input type="text" 
                                       name="service_id" 
                                       class="form-control @error('service_id') is-invalid @enderror" 
                                       value="{{ old('service_id', $policy->service_id ?? $activity->service_id ?? '') }}" 
                                       placeholder="e.g., SVC12345">
                                @error('service_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small style="color:#666;display:block;margin-top:4px;">Link to parent service (optional)</small>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;">Booking Window Rules</label>
                                <input type="text" 
                                       name="booking_window_rules" 
                                       class="form-control @error('booking_window_rules') is-invalid @enderror" 
                                       value="{{ old('booking_window_rules', $policy->booking_window_rules ?? '') }}" 
                                       placeholder="e.g., Book minimum 24 hours in advance">
                                @error('booking_window_rules')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Policies --}}
                    <div style="border-bottom:1px solid #eee;padding-bottom:20px;margin-bottom:24px;">
                        <h5 style="font-weight:600;margin-bottom:16px;">Policies</h5>

                        <div class="mb-3">
                            <label style="font-weight:600;">No-Show Policy</label>
                            <textarea name="no_show_policy" 
                                      class="form-control @error('no_show_policy') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="Describe charges if participant does not arrive">{{ old('no_show_policy', $policy->no_show_policy ?? '') }}</textarea>
                            @error('no_show_policy')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label style="font-weight:600;">Amendment Policy</label>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="radio" name="amendment_policy_type" id="amendment_custom" value="Custom" 
                                           {{ old('amendment_policy_type', $policy->amendment_policy_type ?? 'Custom') == 'Custom' ? 'checked' : '' }}> Write Your Own</label>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="radio" name="amendment_policy_type" id="amendment_template" value="Template"
                                           {{ old('amendment_policy_type', $policy->amendment_policy_type ?? '') == 'Template' ? 'checked' : '' }}> Use HIO Template</label>
                                </div>
                            </div>
                            <div id="amendment_custom_field">
                                <textarea name="amendment_policy" 
                                          id="amendment_policy"
                                          class="form-control @error('amendment_policy') is-invalid @enderror" 
                                          rows="3" 
                                          placeholder="Date/participant change rules">{{ old('amendment_policy', $policy->amendment_policy ?? '') }}</textarea>
                                @error('amendment_policy')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="amendment_template_field" style="display:none;">
                                <select name="amendment_policy_template_id" id="amendment_policy_template_id" class="form-control">
                                    <option value="">Choose a template</option>
                                    <option value="template1" {{ old('amendment_policy_template_id', $policy->amendment_policy_template_id ?? '') === 'template1' ? 'selected' : '' }}>Standard Amendment Policy</option>
                                    <option value="template2" {{ old('amendment_policy_template_id', $policy->amendment_policy_template_id ?? '') === 'template2' ? 'selected' : '' }}>Flexible Amendment Policy</option>
                                    <option value="template3" {{ old('amendment_policy_template_id', $policy->amendment_policy_template_id ?? '') === 'template3' ? 'selected' : '' }}>Strict Amendment Policy</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label style="font-weight:600;">Cancellation Policy <span class="text-danger">*</span></label>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="radio" name="cancellation_policy_type" id="cancellation_custom" value="Custom" 
                                           {{ old('cancellation_policy_type', $policy->cancellation_policy_type ?? 'Custom') == 'Custom' ? 'checked' : '' }}> Write Your Own</label>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;"><input type="radio" name="cancellation_policy_type" id="cancellation_template" value="Template"
                                           {{ old('cancellation_policy_type', $policy->cancellation_policy_type ?? '') == 'Template' ? 'checked' : '' }}> Use HIO Template</label>
                                </div>
                            </div>
                            <div id="cancellation_custom_field">
                                <textarea name="cancellation_policy" 
                                          id="cancellation_policy"
                                          class="form-control @error('cancellation_policy') is-invalid @enderror" 
                                          rows="4" 
                                          placeholder="Cancellation policy text" 
                                          required>{{ old('cancellation_policy', $policy->cancellation_policy ?? '') }}</textarea>
                                @error('cancellation_policy')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="cancellation_template_field" style="display:none;">
                                <select name="cancellation_policy_template_id" id="cancellation_policy_template_id" class="form-control">
                                    <option value="">Choose a template</option>
                                    <option value="template1" {{ old('cancellation_policy_template_id', $policy->cancellation_policy_template_id ?? '') === 'template1' ? 'selected' : '' }}>Flexible Cancellation (Free up to 7 days)</option>
                                    <option value="template2" {{ old('cancellation_policy_template_id', $policy->cancellation_policy_template_id ?? '') === 'template2' ? 'selected' : '' }}>Moderate Cancellation (Free up to 14 days)</option>
                                    <option value="template3" {{ old('cancellation_policy_template_id', $policy->cancellation_policy_template_id ?? '') === 'template3' ? 'selected' : '' }}>Strict Cancellation (24 hours notice)</option>
                                    <option value="template4" {{ old('cancellation_policy_template_id', $policy->cancellation_policy_template_id ?? '') === 'template4' ? 'selected' : '' }}>Non-Refundable</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Cancellation Penalties --}}
                    <div style="border-bottom:1px solid #eee;padding-bottom:20px;margin-bottom:24px;">
                        <h5 style="font-weight:600;margin-bottom:16px;">Cancellation Penalties</h5>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label style="font-weight:600;">Penalties Apply? <span class="text-danger">*</span></label>
                                <select name="cancellation_penalties_enabled" 
                                        id="cancellation_penalties_enabled" 
                                        class="form-control @error('cancellation_penalties_enabled') is-invalid @enderror" 
                                        required>
                                    <option value="No" {{ old('cancellation_penalties_enabled', $policy->cancellation_penalties_enabled ?? 'No') === 'No' ? 'selected' : '' }}>No</option>
                                    <option value="Yes" {{ old('cancellation_penalties_enabled', $policy->cancellation_penalties_enabled ?? '') === 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                                @error('cancellation_penalties_enabled')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4" id="penalties_type_field" style="display:none;">
                                <label style="font-weight:600;">Penalty Type <span class="text-danger">*</span></label>
                                <select name="cancellation_penalties_type" 
                                        id="cancellation_penalties_type" 
                                        class="form-control @error('cancellation_penalties_type') is-invalid @enderror">
                                    <option value="">Select type</option>
                                    <option value="Person(s)" {{ old('cancellation_penalties_type', $policy->cancellation_penalties_type ?? '') === 'Person(s)' ? 'selected' : '' }}>Per Person(s)</option>
                                    <option value="Percentage" {{ old('cancellation_penalties_type', $policy->cancellation_penalties_type ?? '') === 'Percentage' ? 'selected' : '' }}>Percentage</option>
                                    <option value="Amount" {{ old('cancellation_penalties_type', $policy->cancellation_penalties_type ?? '') === 'Amount' ? 'selected' : '' }}>Fixed Amount</option>
                                </select>
                                @error('cancellation_penalties_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4" id="penalties_value_field" style="display:none;">
                                <label style="font-weight:600;">Penalty Value <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="cancellation_penalties_value" 
                                       id="cancellation_penalties_value" 
                                       class="form-control @error('cancellation_penalties_value') is-invalid @enderror" 
                                       value="{{ old('cancellation_penalties_value', $policy->cancellation_penalties_value ?? '') }}" 
                                       step="0.01" 
                                       min="0" 
                                       placeholder="0.00">
                                @error('cancellation_penalties_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Section 4: Age Policies --}}
                    <div style="border-bottom:1px solid #eee;padding-bottom:20px;margin-bottom:24px;">
                        <h5 style="font-weight:600;margin-bottom:16px;">Age Policies</h5>
                        <p style="color:#666;margin-bottom:16px;font-size:14px;">
                            <i class="fas fa-info-circle"></i> Note: Charges for children and infants are configured in Step 9b: Fees & Surcharges
                        </p>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;">Child Policy Age</label>
                                <input type="number" 
                                       name="child_policy_age" 
                                       class="form-control @error('child_policy_age') is-invalid @enderror" 
                                       value="{{ old('child_policy_age', $policy->child_policy_age ?? '') }}" 
                                       min="0" 
                                       max="17" 
                                       placeholder="e.g., 12">
                                @error('child_policy_age')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small style="color:#666;display:block;margin-top:4px;">Maximum age to be considered a child (years)</small>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;">Infant Policy Age</label>
                                <input type="number" 
                                       name="infant_policy_age" 
                                       class="form-control @error('infant_policy_age') is-invalid @enderror" 
                                       value="{{ old('infant_policy_age', $policy->infant_policy_age ?? '') }}" 
                                       min="0" 
                                       max="5" 
                                       placeholder="e.g., 2">
                                @error('infant_policy_age')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small style="color:#666;display:block;margin-top:4px;">Maximum age to be considered an infant (years)</small>
                            </div>
                        </div>
                    </div>

                    {{-- Section 5: Safety & Health --}}
                    <div style="margin-bottom:24px;">
                        <h5 style="font-weight:600;margin-bottom:16px;">Safety & Health Requirements</h5>

                        <div class="mb-3">
                            <label style="font-weight:600;">Safety Requirements <span class="text-danger">*</span></label>
                            <textarea name="safety_requirements" 
                                      class="form-control @error('safety_requirements') is-invalid @enderror" 
                                      rows="4" 
                                      placeholder="Describe mandatory safety briefings, gear, and equipment requirements" 
                                      required>{{ old('safety_requirements', $policy->safety_requirements ?? '') }}</textarea>
                            @error('safety_requirements')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small style="color:#666;display:block;margin-top:4px;">Essential for activities with physical risk</small>
                        </div>

                        <div class="mb-3">
                            <label style="font-weight:600;">Health Requirements Type</label>
                            <select name="health_requirements_type" 
                                    id="health_requirements_type" 
                                    class="form-control @error('health_requirements_type') is-invalid @enderror">
                                <option value="None" {{ old('health_requirements_type', $policy->health_requirements_type ?? 'None') === 'None' ? 'selected' : '' }}>None</option>
                                <option value="Upload" {{ old('health_requirements_type', $policy->health_requirements_type ?? '') === 'Upload' ? 'selected' : '' }}>Upload Waiver Form</option>
                                <option value="Generate" {{ old('health_requirements_type', $policy->health_requirements_type ?? '') === 'Generate' ? 'selected' : '' }}>Generate from Template</option>
                            </select>
                            @error('health_requirements_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="health_file_upload" style="display:none;">
                            <label style="font-weight:600;">Upload Waiver/Medical Form</label>
                            <input type="file" 
                                   name="health_requirements_file" 
                                   class="form-control @error('health_requirements_file') is-invalid @enderror" 
                                   accept=".pdf,.doc,.docx">
                            @error('health_requirements_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(isset($policy->health_requirements_file) && $policy->health_requirements_file)
                                <small style="color:#19b5b5;display:block;margin-top:4px;">
                                    <i class="fas fa-file"></i> Current file: 
                                    <a href="{{ asset('storage/' . $policy->health_requirements_file) }}" target="_blank" style="color:#19b5b5;">
                                        {{ basename($policy->health_requirements_file) }}
                                    </a>
                                </small>
                            @endif
                        </div>

                        <div id="health_generate_info" style="display:none;background:#e3f2fd;padding:12px;border-radius:8px;border-left:4px solid #2196f3;">
                            <p style="margin:0;color:#1565c0;font-size:14px;">
                                <i class="fas fa-info-circle"></i> You'll be able to generate a waiver form from HIO templates after saving this step.
                            </p>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('operator.activity.show', $activity->id) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Activity
                        </a>
                        <button type="submit" class="btn" style="background:#19b5b5;color:#fff;">
                            <i class="fas fa-save me-2"></i>Save Step 6
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Amendment Policy - Toggle between custom and template
    const amendmentCustomRadio = document.getElementById('amendment_custom');
    const amendmentTemplateRadio = document.getElementById('amendment_template');
    const amendmentCustomField = document.getElementById('amendment_custom_field');
    const amendmentTemplateField = document.getElementById('amendment_template_field');
    const amendmentPolicyTextarea = document.getElementById('amendment_policy');

    function updateAmendmentFields() {
        if (amendmentTemplateRadio.checked) {
            amendmentCustomField.style.display = 'none';
            amendmentTemplateField.style.display = 'block';
            amendmentPolicyTextarea.removeAttribute('required');
        } else {
            amendmentCustomField.style.display = 'block';
            amendmentTemplateField.style.display = 'none';
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
    const cancellationPolicyTextarea = document.getElementById('cancellation_policy');

    function updateCancellationFields() {
        if (cancellationTemplateRadio.checked) {
            cancellationCustomField.style.display = 'none';
            cancellationTemplateField.style.display = 'block';
            cancellationPolicyTextarea.removeAttribute('required');
        } else {
            cancellationCustomField.style.display = 'block';
            cancellationTemplateField.style.display = 'none';
            cancellationPolicyTextarea.setAttribute('required', 'required');
        }
    }

    if (cancellationCustomRadio) cancellationCustomRadio.addEventListener('change', updateCancellationFields);
    if (cancellationTemplateRadio) cancellationTemplateRadio.addEventListener('change', updateCancellationFields);
    updateCancellationFields();

    // Toggle cancellation penalties fields
    const penaltiesEnabled = document.getElementById('cancellation_penalties_enabled');
    const penaltiesTypeField = document.getElementById('penalties_type_field');
    const penaltiesValueField = document.getElementById('penalties_value_field');
    const penaltiesType = document.getElementById('cancellation_penalties_type');
    const penaltiesValue = document.getElementById('cancellation_penalties_value');

    function togglePenaltiesFields() {
        if (penaltiesEnabled.value === 'Yes') {
            penaltiesTypeField.style.display = 'block';
            penaltiesValueField.style.display = 'block';
            penaltiesType.required = true;
            penaltiesValue.required = true;
        } else {
            penaltiesTypeField.style.display = 'none';
            penaltiesValueField.style.display = 'none';
            penaltiesType.required = false;
            penaltiesValue.required = false;
        }
    }

    penaltiesEnabled.addEventListener('change', togglePenaltiesFields);
    togglePenaltiesFields(); // Initialize on page load

    // Toggle health requirements file upload
    const healthType = document.getElementById('health_requirements_type');
    const healthFileUpload = document.getElementById('health_file_upload');
    const healthGenerateInfo = document.getElementById('health_generate_info');

    function toggleHealthFields() {
        if (healthType.value === 'Upload') {
            healthFileUpload.style.display = 'block';
            healthGenerateInfo.style.display = 'none';
        } else if (healthType.value === 'Generate') {
            healthFileUpload.style.display = 'none';
            healthGenerateInfo.style.display = 'block';
        } else {
            healthFileUpload.style.display = 'none';
            healthGenerateInfo.style.display = 'none';
        }
    }

    healthType.addEventListener('change', toggleHealthFields);
    toggleHealthFields(); // Initialize on page load
});
</script>

<!-- Back Button -->
<div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
    <a href="{{ route('operator.activity.show', $activity->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
        ← Back to Activity Overview
    </a>
</div>
@endsection
