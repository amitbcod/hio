@extends('layouts.admin')

@section('content')
    <div class="container mt-5">
        @php $currentStep = 2; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.activity._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <h2 style="font-weight:700;margin:0;">Step 2: Management & Communication</h2>
                    <p style="margin:8px 0 0 0;color:#666;">Service ID: <strong>{{ $activity->service_id }}</strong></p>
                </div>

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

                <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;">
                    <form method="POST" action="{{ route('operator.activity.step2.save', $activity->id) }}">
                        @csrf

                        {{-- Reservation Contact Section --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Reservation Department Contact *</h6>
                            <p style="color:#666;margin-bottom:16px;font-size:14px;">This contact will handle all reservation inquiries and booking communications from travellers.</p>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label style="font-weight:600;">Full Name *</label>
                                    <input type="text" name="reservation_contact_name" class="form-control" required
                                        value="{{ old('reservation_contact_name', $activity->reservation_contact_name) }}"
                                        placeholder="e.g., John Smith">
                                    @error('reservation_contact_name')
                                        <small style="color:#dc3545;">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Email Address *</label>
                                    <input type="email" name="reservation_contact_email" class="form-control" required
                                        value="{{ old('reservation_contact_email', $activity->reservation_contact_email) }}"
                                        placeholder="reservations@company.com">
                                    @error('reservation_contact_email')
                                        <small style="color:#dc3545;">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label style="font-weight:600;">Phone Number *</label>
                                    <input type="tel" name="reservation_contact_phone" class="form-control" required
                                        value="{{ old('reservation_contact_phone', $activity->reservation_contact_phone) }}"
                                        placeholder="+230 5xxx xxxx">
                                    @error('reservation_contact_phone')
                                        <small style="color:#dc3545;">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label style="font-weight:600;">Mobile Number *</label>
                                    <input type="tel" name="reservation_contact_mobile" class="form-control" required
                                        value="{{ old('reservation_contact_mobile', $activity->reservation_contact_mobile) }}"
                                        placeholder="+230 5xxx xxxx">
                                    @error('reservation_contact_mobile')
                                        <small style="color:#dc3545;">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Accounting Contact Section --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Accounting Contact (Optional)</h6>
                            <p style="color:#666;margin-bottom:12px;font-size:13px;">Provide accounting contact to receive invoices and payout details.</p>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Name</label>
                                    <input type="text" name="accounting_contact_name" class="form-control"
                                        value="{{ old('accounting_contact_name', $activity->accounting_contact_name) }}"
                                        placeholder="Full name">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Email</label>
                                    <input type="email" name="accounting_contact_email" class="form-control"
                                        value="{{ old('accounting_contact_email', $activity->accounting_contact_email) }}"
                                        placeholder="accounting@company.com">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Phone</label>
                                    <input type="tel" name="accounting_contact_phone" class="form-control"
                                        value="{{ old('accounting_contact_phone', $activity->accounting_contact_phone) }}"
                                        placeholder="+230 5xxx xxxx">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Mobile</label>
                                    <input type="tel" name="accounting_contact_mobile" class="form-control"
                                        value="{{ old('accounting_contact_mobile', $activity->accounting_contact_mobile) }}"
                                        placeholder="+230 5xxx xxxx">
                                </div>
                            </div>
                        </div>

                        {{-- Management Contact Section --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Management Contact *</h6>
                            <p style="color:#666;margin-bottom:16px;font-size:14px;">Provide management contact details for activity operations and communication.</p>

                            <div style="margin-bottom:12px;">
                                <label style="font-weight:600;">
                                    <input type="checkbox" id="management_same_as_operator" style="margin-right:6px;"> Same as Operator
                                </label>
                                <label style="font-weight:600;margin-left:16px;">
                                    <input type="checkbox" id="management_same_as_reservation" style="margin-right:6px;"> Same as Reservation
                                </label>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Full Name *</label>
                                    <input type="text" name="management_contact_name" id="management_contact_name" class="form-control" required
                                        value="{{ old('management_contact_name', $activity->management_contact_name) }}"
                                        placeholder="Full name">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Email Address *</label>
                                    <input type="email" name="management_contact_email" id="management_contact_email" class="form-control" required
                                        value="{{ old('management_contact_email', $activity->management_contact_email) }}"
                                        placeholder="manager@company.com">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Phone Number *</label>
                                    <input type="tel" name="management_contact_phone" id="management_contact_phone" class="form-control" required
                                        value="{{ old('management_contact_phone', $activity->management_contact_phone) }}"
                                        placeholder="+230 5xxx xxxx">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Mobile Number *</label>
                                    <input type="tel" name="management_contact_mobile" id="management_contact_mobile" class="form-control" required
                                        value="{{ old('management_contact_mobile', $activity->management_contact_mobile) }}"
                                        placeholder="+230 5xxx xxxx">
                                </div>
                            </div>
                        </div>

                        {{-- Operational Manager Section --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Operational Manager (Optional)</h6>
                            <p style="color:#666;margin-bottom:12px;font-size:13px;">Person on-site for on-arrival support and operational queries.</p>

                            <div style="margin-bottom:12px;">
                                <label style="font-weight:600;">
                                    <input type="checkbox" id="operational_same_as_operator" style="margin-right:6px;"> Same as Operator
                                </label>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Name</label>
                                    <input type="text" name="operational_manager_name" id="operational_manager_name" class="form-control"
                                        value="{{ old('operational_manager_name', $activity->operational_manager_name) }}"
                                        placeholder="Full name">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Phone</label>
                                    <input type="tel" name="operational_manager_phone" id="operational_manager_phone" class="form-control"
                                        value="{{ old('operational_manager_phone', $activity->operational_manager_phone) }}"
                                        placeholder="+230 5xxx xxxx">
                                </div>
                            </div>
                        </div>

                        {{-- Booking Settings --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Booking Settings</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Booking Registration Type</label>
                                    <select class="form-control" disabled>
                                        <option>{{ $activity->booking_registration_type ?? ($operator->booking_registration_type ?? 'Listing') }}</option>
                                    </select>
                                    <small style="color:#999;">Read-only, linked to Operator / HIO Agreement</small>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Booking Confirmation Type *</label>
                                    <select name="booking_confirmation_type" class="form-control" required>
                                        <option value="">Select confirmation type</option>
                                        <option value="Instant" {{ old('booking_confirmation_type', $activity->booking_confirmation_type) === 'Instant' ? 'selected' : '' }}>Instant (Allotment)</option>
                                        <option value="On Request" {{ old('booking_confirmation_type', $activity->booking_confirmation_type) === 'On Request' ? 'selected' : '' }}>On Request</option>
                                    </select>
                                    <small style="color:#999;">Related to Booking Registration Type / Impacts booking flow</small>
                                </div>
                            </div>

                            {{-- Communication Model Info --}}
                            <div style="background:#fff;padding:16px;border-radius:8px;border:1px solid #ddd;margin-top:16px;">
                                <p style="font-weight:600;margin-bottom:8px;font-size:14px;">Communication Model</p>
                                <p style="font-size:13px;color:#666;margin-bottom:8px;">
                                    HolidaysIO uses <strong>OTO (Operator To Order)</strong> communication model by default.
                                </p>
                                <div style="font-size:12px;line-height:1.6;color:#666;">
                                    <p style="margin-bottom:4px;">
                                        <strong>Traveller Communication:</strong> Travellers communicate with MPO (activity management system), not directly with you.
                                    </p>
                                    <p style="margin-bottom:4px;">
                                        <strong>Your Notifications:</strong> You receive notifications about booking events (confirmed, check-in, cancellation).
                                    </p>
                                    <p style="margin-bottom:0;">
                                        <strong>Benefit:</strong> Your direct contact details remain private and secure.
                                    </p>
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
                                    Save Step 2 & Continue
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
            const get = id => document.getElementById(id);

            const resName = document.querySelector('input[name="reservation_contact_name"]');
            const resEmail = document.querySelector('input[name="reservation_contact_email"]');
            const resPhone = document.querySelector('input[name="reservation_contact_phone"]');
            const resMobile = document.querySelector('input[name="reservation_contact_mobile"]');

            const mgName = get('management_contact_name');
            const mgEmail = get('management_contact_email');
            const mgPhone = get('management_contact_phone');
            const mgMobile = get('management_contact_mobile');

            const opName = get('operational_manager_name');
            const opPhone = get('operational_manager_phone');

            const mgSameRes = get('management_same_as_reservation');
            const mgSameOp = get('management_same_as_operator');
            const opSameOp = get('operational_same_as_operator');

            // Operator defaults (best-effort)
            const operatorName = "{{ addslashes($operator->name ?? $operator->business_legal_name ?? '') }}";
            const operatorEmail = "{{ addslashes($operator->email ?? '') }}";
            const operatorPhone = "{{ addslashes($operator->phone ?? '') }}";

            if (mgSameRes) {
                mgSameRes.addEventListener('change', function() {
                    if (this.checked) {
                        mgSameOp.checked = false;
                        if (resName && mgName) mgName.value = resName.value || '';
                        if (resEmail && mgEmail) mgEmail.value = resEmail.value || '';
                        if (resPhone && mgPhone) mgPhone.value = resPhone.value || '';
                        if (resMobile && mgMobile) mgMobile.value = resMobile.value || '';
                    }
                });
            }

            if (mgSameOp) {
                mgSameOp.addEventListener('change', function() {
                    if (this.checked) {
                        if (mgSameRes) mgSameRes.checked = false;
                        if (mgName) mgName.value = operatorName || '';
                        if (mgEmail) mgEmail.value = operatorEmail || '';
                        if (mgPhone) mgPhone.value = operatorPhone || '';
                    }
                });
            }

            if (opSameOp) {
                opSameOp.addEventListener('change', function() {
                    if (this.checked) {
                        if (opName) opName.value = operatorName || '';
                        if (opPhone) opPhone.value = operatorPhone || '';
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
