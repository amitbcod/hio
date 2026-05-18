@extends('layouts.admin')

@section('content')
    <div class="container mt-5">
        @php $currentStep = 4; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.activity._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,0.07);">
                    <h2 style="font-weight:700;margin-bottom:12px;">Step 4: Legal & Compliance</h2>
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

                    <form method="POST" action="{{ route('operator.activity.step4.save', $activity->id) }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Section 1: Service ID & Basic Compliance --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Service Identification</h6>
                            <p style="color:#666;font-size:14px;margin-bottom:12px;">
                                You may have several parent services (e.g., Hiking, Sea Kayaking, ATV) where child IDs are variations 
                                in equipment or itinerary (e.g., different hike trails or one/two-seat ATVs).
                            </p>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Service ID (Auto-generated)</label>
                                    <input type="text" class="form-control" value="{{ $activity->service_id }}" readonly style="background:#f5f5f5;">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Parent Service ID</label>
                                    <input type="text" name="parent_service_id" class="form-control" 
                                        value="{{ old('parent_service_id', $compliance->parent_service_id ?? '') }}"
                                        placeholder="e.g., SVC-HIKING-01">
                                    <small style="color:#666;display:block;margin-top:4px;">Optional: Group related service variations</small>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Primary Compliance Details --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Primary Compliance Details</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Business Registration Number *</label>
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                        <label style="margin-bottom:0;">
                                            <input type="checkbox" id="business_same_as_operator" style="margin-right:6px;"> 
                                            Same as Operator
                                        </label>
                                    </div>
                                    <input type="text" name="business_registration_number" id="business_registration_number" 
                                        class="form-control" required 
                                        value="{{ old('business_registration_number', $compliance->business_registration_number ?? '') }}"
                                        placeholder="Business license number">
                                    @error('business_registration_number')
                                        <small style="color:#dc3545;">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Tourism Activity Permit *</label>
                                    <!-- <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                        <label style="margin-bottom:0;">
                                            <input type="checkbox" id="permit_same_as_operator" style="margin-right:6px;"> 
                                            Same as Operator
                                        </label>
                                    </div> -->
                                    <input type="text" name="tourism_activity_permit" id="tourism_activity_permit" 
                                        class="form-control" required 
                                        value="{{ old('tourism_activity_permit', $compliance->tourism_activity_permit ?? '') }}"
                                        placeholder="Activity permit number">
                                    @error('tourism_activity_permit')
                                        <small style="color:#dc3545;">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Public Liability Insurance Number *</label>
                                    <!-- <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                        <label style="margin-bottom:0;">
                                            <input type="checkbox" id="insurance_same_as_operator" style="margin-right:6px;"> 
                                            Same as Operator
                                        </label> 
                                    </div> -->
                                    <input type="text" name="public_liability_insurance" id="public_liability_insurance" 
                                        class="form-control" required 
                                        value="{{ old('public_liability_insurance', $compliance->public_liability_insurance ?? '') }}"
                                        placeholder="Insurance policy number">
                                    @error('public_liability_insurance')
                                        <small style="color:#dc3545;">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Insurance Expiration</label>
                                    <input type="date" name="insurance_expiration" class="form-control" 
                                        value="{{ old('insurance_expiration', optional($compliance->insurance_expiration)->format('Y-m-d') ?? '') }}">
                                    <small style="color:#666;display:block;margin-top:4px;">Renewal reminders will be sent before expiration</small>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Permits/Authorisations Upload --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Permits/Authorisations</h6>
                            
                            <div class="mb-3">
                                <label style="font-weight:600;">Permits/Authorisations (Marine Parks, etc.)</label>
                                @if($compliance && $compliance->permits_authorisations_files && count($compliance->permits_authorisations_files) > 0)
                                    <div style="margin-bottom:8px;padding:8px;background:#f9f9f9;border-radius:4px;">
                                        <span style="color:#666;font-weight:600;">Current files:</span>
                                        @foreach($compliance->permits_authorisations_files as $file)
                                            <div style="margin-top:4px;">
                                                <a href="{{ asset('storage/' . $file) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-size:13px;">
                                                    📄 {{ basename($file) }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <input type="file" name="permits_authorisations[]" class="form-control" accept="application/pdf,image/*" multiple>
                                <small style="color:#666;display:block;margin-top:4px;">Optional. Upload any permits needed (marine parks, protected areas, etc.). Multiple files allowed.</small>
                            </div>
                        </div>

                        {{-- Section 4: Additional Document Uploads --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <button type="button" id="toggleDocs" class="btn" style="background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;border:none;cursor:pointer;margin-bottom:12px;">
                                Show Document Uploads
                            </button>
                            
                            <div id="docsSection" style="display:none;border:1px solid #ddd;padding:16px;border-radius:8px;background:#fff;margin-top:12px;">
                                <h6 style="margin-top:0;font-weight:600;margin-bottom:16px;color:#19b5b5;">Document Uploads</h6>
                            
                            <div class="mb-3">
                                <label style="font-weight:600;">Tourism Activity Permit (File)</label>
                                @if($compliance && $compliance->tourism_permit_file)
                                    <div style="margin-bottom:8px;padding:8px;background:#f9f9f9;border-radius:4px;">
                                        <span style="color:#666;">Current file: </span>
                                        <a href="{{ asset('storage/' . $compliance->tourism_permit_file) }}" target="_blank" style="color:#19b5b5;text-decoration:none;">
                                            View Document
                                        </a>
                                    </div>
                                @endif
                                <input type="file" name="tourism_permit_file" class="form-control" accept="application/pdf,image/*">
                                <small style="color:#666;display:block;margin-top:4px;">Optional. Upload permit/license for activity. Required for MPO property admin agreement.</small>
                            </div>

                            <div class="mb-3">
                                <label style="font-weight:600;">Public Liability Insurance Certificate (File)</label>
                                @if($compliance && $compliance->insurance_file)
                                    <div style="margin-bottom:8px;padding:8px;background:#f9f9f9;border-radius:4px;">
                                        <span style="color:#666;">Current file: </span>
                                        <a href="{{ asset('storage/' . $compliance->insurance_file) }}" target="_blank" style="color:#19b5b5;text-decoration:none;">
                                            View Document
                                        </a>
                                    </div>
                                @endif
                                <input type="file" name="insurance_file" class="form-control" accept="application/pdf,image/*">
                                <small style="color:#666;display:block;margin-top:4px;">Optional. Upload insurance coverage certificate. Required for MPO property admin agreement.</small>
                            </div>

                            <div class="mb-3">
                                <label style="font-weight:600;">Operational Assessment Document</label>
                                @if($compliance && $compliance->operational_assessment_doc)
                                    <div style="margin-bottom:8px;padding:8px;background:#f9f9f9;border-radius:4px;">
                                        <span style="color:#666;">Current file: </span>
                                        <a href="{{ asset('storage/' . $compliance->operational_assessment_doc) }}" target="_blank" style="color:#19b5b5;text-decoration:none;">
                                            View Document
                                        </a>
                                    </div>
                                @endif
                                <input type="file" name="operational_assessment_doc" class="form-control" accept="application/pdf,image/*">
                                <small style="color:#666;display:block;margin-top:4px;">Optional. Upload risk assessment (equipment failure and replacement). PDF format recommended.</small>
                            </div>

                            <div class="mb-3">
                                <label style="font-weight:600;">Emergency Plan</label>
                                @if($compliance && $compliance->emergency_plan_doc)
                                    <div style="margin-bottom:8px;padding:8px;background:#f9f9f9;border-radius:4px;">
                                        <span style="color:#666;">Current file: </span>
                                        <a href="{{ asset('storage/' . $compliance->emergency_plan_doc) }}" target="_blank" style="color:#19b5b5;text-decoration:none;">
                                            View Document
                                        </a>
                                    </div>
                                @endif
                                <input type="file" name="emergency_plan_doc" class="form-control" accept="application/pdf,image/*">
                                <small style="color:#666;display:block;margin-top:4px;">Optional. Upload emergency response instructions (accident procedure during activity/outing).</small>
                            </div>

                            <div class="mb-3">
                                <label style="font-weight:600;">Compliance Docs (Equipment-specific)</label>
                                @if($compliance && $compliance->equipment_compliance_doc)
                                    <div style="margin-bottom:8px;padding:8px;background:#f9f9f9;border-radius:4px;">
                                        <span style="color:#666;">Current file: </span>
                                        <a href="{{ asset('storage/' . $compliance->equipment_compliance_doc) }}" target="_blank" style="color:#19b5b5;text-decoration:none;">
                                            View Document
                                        </a>
                                    </div>
                                @endif
                                <input type="file" name="equipment_compliance_doc" class="form-control" accept="application/pdf,image/*">
                                <small style="color:#666;display:block;margin-top:4px;">Optional. Equipment-specific compliance/inspection docs (general procedure of equipment maintenance and inspection).</small>
                            </div>

                            <div class="mb-3">
                                <label style="font-weight:600;">Equipment Registration/Serial Number</label>
                                <input type="text" name="equipment_registration_serial" class="form-control" 
                                    value="{{ old('equipment_registration_serial', $compliance->equipment_registration_serial ?? '') }}"
                                    placeholder="Enter equipment registration or serial number">
                                <small style="color:#666;display:block;margin-top:4px;">Optional. Internal use only. Equipment registration or serial number if applicable.</small>
                            </div>

                            <div class="mb-3">
                                <label style="font-weight:600;">Other Compliance Documents</label>
                                @if($compliance && $compliance->other_permit_files && count($compliance->other_permit_files) > 0)
                                    <div style="margin-bottom:8px;padding:8px;background:#f9f9f9;border-radius:4px;">
                                        <span style="color:#666;font-weight:600;">Current files:</span>
                                        @foreach($compliance->other_permit_files as $file)
                                            <div style="margin-top:4px;">
                                                <a href="{{ asset('storage/' . $file) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-size:13px;">
                                                    📄 {{ basename($file) }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <input type="file" name="other_documents[]" class="form-control" accept="application/pdf,image/*" multiple>
                                <small style="color:#666;display:block;margin-top:4px;">Optional. Upload any additional compliance documents. Multiple files allowed.</small>
                            </div>
                            </div>
                        </div>

                        {{-- Submit Buttons --}}
                        <div style="display:flex;justify-content:space-between;gap:12px;">
                            <a href="{{ route('operator.activity.show', $activity->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;">
                                ← Back
                            </a>
                            <div style="display:flex;gap:8px;">
                                <a href="{{ route('operator.activity.show', $activity->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;">
                                    Skip
                                </a>
                                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 24px;border-radius:4px;font-weight:600;">
                                    Save Compliance
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle document uploads section
        const toggleBtn = document.getElementById('toggleDocs');
        const docsSection = document.getElementById('docsSection');
        
        if (toggleBtn && docsSection) {
            toggleBtn.addEventListener('click', function() {
                if (docsSection.style.display === 'none' || docsSection.style.display === '') {
                    docsSection.style.display = 'block';
                    toggleBtn.textContent = 'Hide Document Uploads';
                } else {
                    docsSection.style.display = 'none';
                    toggleBtn.textContent = 'Show Document Uploads';
                }
            });
        }

        // Auto-fill from operator data
        const operatorBusinessReg = "{{ addslashes($operatorBusinessRegistrationNumber ?? '') }}";
        const operatorTourismPermit = "{{ addslashes(optional($operator->business)->tourism_permit_number ?? '') }}";
        const operatorInsurance = "{{ addslashes(optional($operator->business)->public_liability_insurance_number ?? '') }}";

        const businessSame = document.getElementById('business_same_as_operator');
        if (businessSame) {
            businessSame.addEventListener('change', function() {
                if (this.checked) {
                    document.getElementById('business_registration_number').value = operatorBusinessReg;
                }
            });
        }

        const permitSame = document.getElementById('permit_same_as_operator');
        if (permitSame) {
            permitSame.addEventListener('change', function() {
                if (this.checked) {
                    document.getElementById('tourism_activity_permit').value = operatorTourismPermit;
                }
            });
        }

        const insuranceSame = document.getElementById('insurance_same_as_operator');
        if (insuranceSame) {
            insuranceSame.addEventListener('change', function() {
                if (this.checked) {
                    document.getElementById('public_liability_insurance').value = operatorInsurance;
                }
            });
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
