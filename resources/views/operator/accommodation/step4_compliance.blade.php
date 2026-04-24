@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        @php $currentStep = 4; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.accommodation._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,0.07);">
                    <h2 style="font-weight:700;margin-bottom:12px;">Step 4: Compliance & Legal</h2>

                    @if($errors->any())
                        <div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('operator.accommodation.saveStep4', $accommodation->id) }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Section 1: Text fields --}}
                        <div style="margin-bottom:20px;">
                            <h5 style="font-weight:600;">Primary Compliance Details</h5>
                            <div style="margin-bottom:8px;">
                                <label style="font-weight:600;">Property ID</label>
                                <div><strong>{{ $accommodation->accommodation_id }}</strong></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Business Registration Number *</label>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <input type="text" name="business_registration_number" class="form-control" required value="{{ old('business_registration_number', $accommodation->business_registration_number ?? $operator->business->registration_number ?? '') }}">
                                        <label style="margin-bottom:0;"><input type="checkbox" id="business_same_as_operator" style="margin-right:6px;"> Same as Operator</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Tourism Accommodation Permit *</label>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <input type="text" name="tourism_permit_number" class="form-control" required value="{{ old('tourism_permit_number', $accommodation->tourism_permit_number ?? $operator->business->tourism_permit_number ?? '') }}">
                                        <label style="margin-bottom:0;"><input type="checkbox" id="permit_same_as_operator" style="margin-right:6px;"> Same as Operator</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Tourism Permit Expiration</label>
                                    <input type="date" name="tourism_permit_expiration" class="form-control" value="{{ old('tourism_permit_expiration', optional($accommodation->tourism_permit_expiration)->format('Y-m-d') ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Public Liability Insurance Number *</label>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <input type="text" name="public_liability_insurance_number" class="form-control" required value="{{ old('public_liability_insurance_number', $accommodation->public_liability_insurance_number ?? $operator->business->insurance_number ?? '') }}">
                                        <label style="margin-bottom:0;"><input type="checkbox" id="insurance_same_as_operator" style="margin-right:6px;"> Same as Operator</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Insurance Expiration</label>
                                    <input type="date" name="insurance_expiration" class="form-control" value="{{ old('insurance_expiration', optional($accommodation->insurance_expiration)->format('Y-m-d') ?? '') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Documents (collapsible by default) --}}
                        <div style="margin-bottom:20px;">
                            <button type="button" id="toggleDocs" class="btn" style="background:#f0f0f0;color:#333;margin-bottom:8px;">Show Compliance Documents</button>
                            <div id="docsSection" style="display:none;border:1px solid #eee;padding:12px;border-radius:8px;">
                                <div class="mb-3">
                                    <label style="font-weight:600;">Property ID</label>
                                    <select name="property_id_lookup" class="form-control" required>
                                        <option value="{{ $accommodation->id }}">{{ $accommodation->accommodation_id }} - {{ $accommodation->property_name }}</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label style="font-weight:600;">Tourism Accommodation Permit (file)</label>
                                    <div style="display:flex;align-items:flex-start;gap:12px;">
                                        <div style="flex:1;">
                                            <input type="file" name="tourism_permit_file" accept="application/pdf,image/*">
                                            <small style="color:#666;display:block;margin-top:4px;">Optional except when MPO has property admin agreement.</small>
                                        </div>
                                        @if(isset($complianceDocs['compliance_permit']) && $complianceDocs['compliance_permit']->count() > 0)
                                            <div style="padding-top:8px;">
                                                @foreach($complianceDocs['compliance_permit'] as $doc)
                                                    <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-weight:600;display:flex;align-items:center;gap:6px;">
                                                        <span style="font-size:18px;">📄</span>{{ $doc->original_name }}
                                                    </a>
                                                    <small style="color:#999;display:block;">Uploaded: {{ $doc->created_at->format('M d, Y') }}</small>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label style="font-weight:600;">Public Liability Insurance (file)</label>
                                    <div style="display:flex;align-items:flex-start;gap:12px;">
                                        <div style="flex:1;">
                                            <input type="file" name="insurance_file" accept="application/pdf,image/*">
                                            <small style="color:#666;display:block;margin-top:4px;">Optional except when MPO has property admin agreement.</small>
                                        </div>
                                        @if(isset($complianceDocs['compliance_insurance']) && $complianceDocs['compliance_insurance']->count() > 0)
                                            <div style="padding-top:8px;">
                                                @foreach($complianceDocs['compliance_insurance'] as $doc)
                                                    <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-weight:600;display:flex;align-items:center;gap:6px;">
                                                        <span style="font-size:18px;">📄</span>{{ $doc->original_name }}
                                                    </a>
                                                    <small style="color:#999;display:block;">Uploaded: {{ $doc->created_at->format('M d, Y') }}</small>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label style="font-weight:600;">Fire Safety Certificate</label>
                                    <div style="display:flex;align-items:flex-start;gap:12px;">
                                        <div style="flex:1;">
                                            <input type="file" name="fire_safety_file" accept="application/pdf,image/*">
                                        </div>
                                        @if(isset($complianceDocs['compliance_fire']) && $complianceDocs['compliance_fire']->count() > 0)
                                            <div style="padding-top:8px;">
                                                @foreach($complianceDocs['compliance_fire'] as $doc)
                                                    <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-weight:600;display:flex;align-items:center;gap:6px;">
                                                        <span style="font-size:18px;">📄</span>{{ $doc->original_name }}
                                                    </a>
                                                    <small style="color:#999;display:block;">Uploaded: {{ $doc->created_at->format('M d, Y') }}</small>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label style="font-weight:600;">Health and Sanitation Certificate</label>
                                    <div style="display:flex;align-items:flex-start;gap:12px;">
                                        <div style="flex:1;">
                                            <input type="file" name="health_file" accept="application/pdf,image/*">
                                        </div>
                                        @if(isset($complianceDocs['compliance_health']) && $complianceDocs['compliance_health']->count() > 0)
                                            <div style="padding-top:8px;">
                                                @foreach($complianceDocs['compliance_health'] as $doc)
                                                    <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-weight:600;display:flex;align-items:center;gap:6px;">
                                                        <span style="font-size:18px;">📄</span>{{ $doc->original_name }}
                                                    </a>
                                                    <small style="color:#999;display:block;">Uploaded: {{ $doc->created_at->format('M d, Y') }}</small>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label style="font-weight:600;">Other Compliance Documents</label>
                                    <div style="display:flex;align-items:flex-start;gap:12px;">
                                        <div style="flex:1;">
                                            <input type="file" name="other_docs[]" accept="application/pdf,image/*" multiple>
                                        </div>
                                        @if(isset($complianceDocs['compliance_other']) && $complianceDocs['compliance_other']->count() > 0)
                                            <div style="padding-top:8px;">
                                                @foreach($complianceDocs['compliance_other'] as $doc)
                                                    <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-weight:600;display:flex;align-items:center;gap:6px;">
                                                        <span style="font-size:18px;">📄</span>{{ $doc->original_name }}
                                                    </a>
                                                    <small style="color:#999;display:block;">Uploaded: {{ $doc->created_at->format('M d, Y') }}</small>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;justify-content:space-between;gap:12px;">
                            <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">← Back</a>
                            <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:8px 14px;border-radius:4px;">Save Compliance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const toggle = document.getElementById('toggleDocs');
        const sect = document.getElementById('docsSection');
        toggle.addEventListener('click', function(){
            sect.style.display = sect.style.display === 'none' ? 'block' : 'none';
            toggle.textContent = sect.style.display === 'none' ? 'Show Compliance Documents' : 'Hide Compliance Documents';
        });

        const copy = (srcSel, destSel) => {
            const src = document.querySelector(srcSel);
            const dest = document.querySelector(destSel);
            if (!src || !dest) return;
            return () => { dest.value = src.value || ''; };
        };

        // Operator same-as autofill
        const businessSame = document.getElementById('business_same_as_operator');
        if (businessSame) {
            businessSame.addEventListener('change', function(){
                if (this.checked) {
                    const val = "{{ addslashes($operator->business->registration_number ?? '') }}";
                    document.querySelector('input[name="business_registration_number"]').value = val;
                }
            });
        }

        const permitSame = document.getElementById('permit_same_as_operator');
        if (permitSame) {
            permitSame.addEventListener('change', function(){
                if (this.checked) {
                    const val = "{{ addslashes($operator->business->tourism_permit_number ?? '') }}";
                    document.querySelector('input[name="tourism_permit_number"]').value = val;
                }
            });
        }

        const insureSame = document.getElementById('insurance_same_as_operator');
        if (insureSame) {
            insureSame.addEventListener('change', function(){
                if (this.checked) {
                    const val = "{{ addslashes($operator->business->insurance_number ?? '') }}";
                    document.querySelector('input[name="public_liability_insurance_number"]').value = val;
                }
            });
        }
    });
    </script>
    @endpush

    <!-- Back Button -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const backButton = document.createElement('div');
        backButton.style.marginTop = '24px';
        backButton.style.paddingTop = '24px';
        backButton.style.borderTop = '1px solid #e0e0e0';
        backButton.innerHTML = '<a href="{{ route('operator.accommodation.show', $accommodation->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">← Back to Accommodation Overview</a>';
        document.querySelector('form').parentElement.appendChild(backButton);
    });
    </script>

@endsection
