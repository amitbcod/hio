@extends('layouts.admin')

@section('content')
    <div class="container mt-5">
        @php $currentStep = 2; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.accommodation._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 32px;">
                    
                    {{-- Header --}}
                    <div style="margin-bottom: 24px;">
                        <h2 style="font-weight: bold; margin-bottom: 8px;">{{ $accommodation->property_name }}</h2>
                        <p style="color: #666; margin-bottom: 0;">ID: {{ $accommodation->accommodation_id }}</p>
                    </div>

                    {{-- Property Completion --}}
                    <div style="background: #f8f8f8; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-weight: 600;">Property Completion</span>
                            <span style="font-weight: bold; color: #19b5b5;">{{ $accommodation->getCompletionPercentage() }}%</span>
                        </div>
                        <div style="height: 8px; background: #e0e0e0; border-radius: 4px; overflow: hidden;">
                            <div style="height: 100%; background: #19b5b5; width: {{ $accommodation->getCompletionPercentage() }}%; transition: width 0.3s;"></div>
                        </div>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('operator.accommodation.saveStep2', $accommodation->id) }}">
                        @csrf

                        {{-- Step Header --}}
                        <div style="background: #19b5b5; color: #fff; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-weight: bold;">
                            Step 2: Reservation and Communication
                        </div>

                        {{-- Alerts --}}
                        @if(session('success'))
                            <div class="alert alert-success" style="margin-bottom: 20px;">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger" style="margin-bottom: 20px;">
                                <strong>Please fix the following errors:</strong>
                                <ul style="margin-bottom: 0;">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Reservation Contact Section --}}
                        <div class="mb-4">
                            <h5 style="font-weight: 600; margin-bottom: 20px;">Reservation Department Contact</h5>
                            <p style="color: #666; margin-bottom: 16px; font-size: 14px;">This contact will handle all reservation inquiries and booking communications from travellers.</p>
                            
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Full Name *</label>
                                    <input type="text" name="reservation_contact_name" class="form-control" required
                                        value="{{ old('reservation_contact_name', $accommodation->reservation_contact_name ?? '') }}"
                                        placeholder="e.g., John Smith">
                                    @error('reservation_contact_name')
                                        <small style="color: #dc3545;">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label style="font-weight: 600; margin-bottom: 8px; display: block;">Email Address *</label>
                                        <input type="email" name="reservation_contact_email" class="form-control" required
                                            value="{{ old('reservation_contact_email', $accommodation->reservation_contact_email ?? '') }}"
                                            placeholder="reservations@property.com">
                                        @error('reservation_contact_email')
                                            <small style="color: #dc3545;">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label style="font-weight: 600; margin-bottom: 8px; display: block;">Phone Number *</label>
                                        <input type="tel" name="reservation_contact_phone" class="form-control" required
                                            value="{{ old('reservation_contact_phone', $accommodation->reservation_contact_phone ?? '') }}"
                                            placeholder="+230 5xxx xxxx">
                                        @error('reservation_contact_phone')
                                            <small style="color: #dc3545;">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label style="font-weight: 600; margin-bottom: 8px; display: block;">Mobile Number *</label>
                                        <input type="tel" name="reservation_contact_mobile" class="form-control" required
                                            value="{{ old('reservation_contact_mobile', $accommodation->reservation_contact_mobile ?? '') }}"
                                            placeholder="+230 5xxx xxxx">
                                        @error('reservation_contact_mobile')
                                            <small style="color: #dc3545;">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                        </div>

                        {{-- Management Contact Section --}}
                        <hr style="margin: 32px 0;">
                        <div class="mb-4">
                            <h5 style="font-weight: 600; margin-bottom: 20px;">Management Contact</h5>
                            <p style="color: #666; margin-bottom: 16px; font-size: 14px;">Provide management contact details for property operations and maintenance communications.</p>

                            <div style="margin-bottom: 8px;">
                                <label style="font-weight: 600;">
                                    <input type="checkbox" id="management_same_as_operator" style="margin-right:6px;"> Same as Operator
                                </label>
                                <label style="font-weight: 600; margin-left: 16px;">
                                    <input type="checkbox" id="management_same_as_reservation" style="margin-right:6px;"> Same as Reservation
                                </label>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Full Name *</label>
                                    <input type="text" name="management_contact_name" id="management_contact_name" class="form-control" required
                                        value="{{ old('management_contact_name', $accommodation->management_contact_name ?? '') }}"
                                        placeholder="Full name">
                                </div>
                                <div class="col-md-3">
                                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Phone Number *</label>
                                    <input type="tel" name="management_contact_phone" id="management_contact_phone" class="form-control" required
                                        value="{{ old('management_contact_phone', $accommodation->management_contact_phone ?? '') }}"
                                        placeholder="+230 5xxx xxxx">
                                </div>
                                <div class="col-md-3">
                                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Mobile Number *</label>
                                    <input type="tel" name="management_contact_mobile" id="management_contact_mobile" class="form-control" required
                                        value="{{ old('management_contact_mobile', $accommodation->management_contact_mobile ?? '') }}"
                                        placeholder="+230 5xxx xxxx">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Email Address *</label>
                                    <input type="email" name="management_contact_email" id="management_contact_email" class="form-control" required
                                        value="{{ old('management_contact_email', $accommodation->management_contact_email ?? '') }}"
                                        placeholder="manager@property.com">
                                </div>
                            </div>
                        </div>

                        {{-- Accounting Contact Section --}}
                        <hr style="margin: 32px 0;">
                        <div class="mb-4">
                            <h5 style="font-weight: 600; margin-bottom: 12px;">Accounting Contact (Optional)</h5>
                            <p style="color: #666; margin-bottom: 12px; font-size: 13px;">Provide accounting contact to receive invoices and payout details.</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label style="font-weight: 600; display: block;">Name</label>
                                    <input type="text" name="accounting_contact_name" class="form-control" value="{{ old('accounting_contact_name', $accommodation->accounting_contact_name ?? '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="font-weight: 600; display: block;">Email</label>
                                    <input type="email" name="accounting_contact_email" class="form-control" value="{{ old('accounting_contact_email', $accommodation->accounting_contact_email ?? '') }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label style="font-weight: 600; display: block;">Phone</label>
                                    <input type="tel" name="accounting_contact_phone" class="form-control" value="{{ old('accounting_contact_phone', $accommodation->accounting_contact_phone ?? '') }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label style="font-weight: 600; display: block;">Mobile</label>
                                    <input type="tel" name="accounting_contact_mobile" class="form-control" value="{{ old('accounting_contact_mobile', $accommodation->accounting_contact_mobile ?? '') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Communication Preferences Section --}}
                        <hr style="margin: 32px 0;">
                        <div class="mb-4">
                            <h5 style="font-weight: 600; margin-bottom: 20px;">Communication Preferences</h5>
                            
                            <div style="background: #f8f8f8; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                                <div style="display: flex; align-items: start; gap: 12px;">
                                    <div style="flex: 1;">
                                        <p style="font-weight: 600; margin-bottom: 4px;">Communication Model</p>
                                        <p style="font-size: 13px; color: #666; margin-bottom: 0;">
                                            HolidaysIO uses <strong>OTO (Operator To Order)</strong> communication model by default.
                                        </p>
                                        <div style="background: #fff; padding: 12px; border-radius: 4px; margin-top: 8px; font-size: 12px; line-height: 1.6;">
                                            <p style="margin-bottom: 8px;">
                                                <strong>Traveller Communication:</strong> Travellers communicate with MPO (accommodation management system), not directly with you.
                                            </p>
                                            <p style="margin-bottom: 8px;">
                                                <strong>Your Notifications:</strong> You receive notifications about booking events (confirmed, check-in, check-out, cancellation).
                                            </p>
                                            <p style="margin-bottom: 0;">
                                                <strong>Benefit:</strong> Your direct contact details remain private and secure.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Onsite / Front Desk --}}
                        <hr style="margin: 32px 0;">
                        <div class="mb-4">
                            <h5 style="font-weight: 600; margin-bottom: 12px;">Onsite / Front Desk</h5>
                            <div style="margin-bottom: 8px;">
                                <label style="font-weight: 600;"><input type="checkbox" id="onsite_same_as_reservation" style="margin-right:6px;"> Same as Reservation</label>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight: 600; display: block;">Department</label>
                                    <input type="text" name="onsite_department" id="onsite_department" class="form-control" value="{{ old('onsite_department', $accommodation->onsite_department ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight: 600; display: block;">Phone</label>
                                    <input type="tel" name="onsite_phone" id="onsite_phone" class="form-control" value="{{ old('onsite_phone', $accommodation->onsite_phone ?? '') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Booking Settings --}}
                        <hr style="margin: 32px 0;">
                        <div class="mb-4">
                            <h5 style="font-weight: 600; margin-bottom: 12px;">Booking Settings</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight: 600; display: block;">Listing / Registration Type</label>
                                    <select class="form-control" disabled>
                                        <option>{{ $accommodation->booking_registration_type ?? ($operator->booking_registration_type ?? ($accommodation->booking_registration_type ?? 'Listing')) }}</option>
                                    </select>
                                    <small style="color:#999;">Read-only, linked to Operator / Agreement</small>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight: 600; display: block;">Booking Confirmation Type *</label>
                                    <select name="booking_confirmation_type" class="form-control" required>
                                        <option value="">Select confirmation type</option>
                                        <option value="Instant" {{ old('booking_confirmation_type', $accommodation->booking_confirmation_type ?? '') === 'Instant' ? 'selected' : '' }}>Instant (Allotment)</option>
                                        <option value="On Request" {{ old('booking_confirmation_type', $accommodation->booking_confirmation_type ?? '') === 'On Request' ? 'selected' : '' }}>On Request</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <hr style="margin: 32px 0;">
                        <div style="display: flex; justify-content: space-between; gap: 12px;">
                            <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background: #f0f0f0; color: #333; border: none; padding: 10px 20px; border-radius: 4px;">
                                ← Back
                            </a>
                            <div style="display: flex; gap: 8px;">
                                <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background: #f0f0f0; color: #333; border: none; padding: 10px 20px; border-radius: 4px;">
                                    Skip
                                </a>
                                <button type="submit" class="btn" style="background: #19b5b5; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
                                    Save and Continue
                                </button>
                            </div>
                        </div>
                    </form>
                    <!-- Back Button -->
                    <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
                        <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
                            ← Back to Accommodation Overview
                        </a>
                    </div>                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const get = id => document.getElementById(id);

    const resName = document.querySelector('input[name="reservation_contact_name"]');
    const resPhone = document.querySelector('input[name="reservation_contact_phone"]');
    const resMobile = document.querySelector('input[name="reservation_contact_mobile"]');

    const mgName = get('management_contact_name');
    const mgEmail = get('management_contact_email');
    const mgPhone = get('management_contact_phone');
    const mgMobile = get('management_contact_mobile');

    const acctName = document.querySelector('input[name="accounting_contact_name"]');
    const acctEmail = document.querySelector('input[name="accounting_contact_email"]');
    const acctPhone = document.querySelector('input[name="accounting_contact_phone"]');
    const acctMobile = document.querySelector('input[name="accounting_contact_mobile"]');

    const onsiteDept = get('onsite_department');
    const onsitePhone = get('onsite_phone');

    const mgSameRes = get('management_same_as_reservation');
    const mgSameOp = get('management_same_as_operator');
    const onsiteSameRes = get('onsite_same_as_reservation');

    // Operator defaults (best-effort)
    const operatorName = "{{ addslashes($operator->name ?? $operator->business_legal_name ?? '') }}";
    const operatorEmail = "{{ addslashes($operator->email ?? '') }}";
    const operatorPhone = "{{ addslashes($operator->phone ?? '') }}";

    if (mgSameRes) {
        mgSameRes.addEventListener('change', function () {
            if (this.checked) {
                mgSameOp.checked = false;
                if (resName && mgName) mgName.value = resName.value || '';
                if (resPhone && mgPhone) mgPhone.value = resPhone.value || '';
                if (resMobile && mgMobile) mgMobile.value = resMobile.value || '';
                const resEmail = document.querySelector('input[name="reservation_contact_email"]');
                if (resEmail && mgEmail) mgEmail.value = resEmail.value || '';
            }
        });
    }

    if (mgSameOp) {
        mgSameOp.addEventListener('change', function () {
            if (this.checked) {
                if (mgSameRes) mgSameRes.checked = false;
                if (mgName) mgName.value = operatorName || '';
                if (mgEmail) mgEmail.value = operatorEmail || '';
                if (mgPhone) mgPhone.value = operatorPhone || '';
            }
        });
    }

    if (onsiteSameRes) {
        onsiteSameRes.addEventListener('change', function () {
            if (this.checked) {
                if (onsiteDept) onsiteDept.value = resName ? resName.value : '';
                if (onsitePhone) onsitePhone.value = resPhone ? resPhone.value : (resMobile ? resMobile.value : '');
            }
        });
    }
});
</script>
@endpush
