@extends($isAdminView ? 'layouts.admin' : 'layouts.mpo-portal')

@section('title', 'MPO Compliance')

@section('content')
@php
    $formAction = $isAdminView
        ? route('admin.country-admins.compliance.save', $mpoAdmin)
        : route('mpo.compliances.save');
    $profileReady = $missingProfileFields === [];
    $documentRoute = fn ($document) => $isAdminView
        ? route('admin.mpo-compliance.download', [$compliance, $document])
        : route('mpo.compliances.download', [$compliance, $document]);
    $expiryLabels = [
        'valid' => ['Valid', 'success'],
        'expiring_soon' => ['Expiring Soon', 'warning'],
        'expired' => ['Expired', 'danger'],
        'unknown' => ['Unknown', 'secondary'],
    ];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
    <div>
        <p class="text-muted mb-0">
            @if($isAdminView)
                Compliance details for {{ $mpoAdmin->name }} ({{ $mpoAdmin->email }}).
            @else
                Submit your business license and insurance documents for Admin review.
            @endif
        </p>
        @if($isAdminView)
            <a href="{{ route('admin.country-admins.edit', $mpoAdmin) }}">Back to MPO account</a>
        @endif
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

@unless($profileReady)
    <div class="alert alert-warning" role="alert">
        <h2 class="h6">Complete your Profile first</h2>
        <p>The Compliance form uses the Profile as the source for legal business information. The following Profile details are missing:</p>
        <ul class="mb-2">@foreach($missingProfileFields as $field)<li>{{ $field }}</li>@endforeach</ul>
        @if($isAdminView)
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.country-admins.profile.edit', $mpoAdmin) }}">Edit MPO Profile</a>
        @else
            <a class="btn btn-sm btn-outline-primary" href="{{ route('mpo.profile') }}">Complete Profile</a>
        @endif
    </div>
@endunless

@if($compliance)
    <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="badge text-bg-{{ $compliance->verification_status === 'verified' ? 'success' : ($compliance->verification_status === 'rejected' ? 'danger' : 'warning') }}">
            {{ $compliance->verificationStatusLabel() }}
        </span>
        @php([$expiryLabel, $expiryColor] = $expiryLabels[$expiryStatus] ?? $expiryLabels['unknown'])
        <span class="badge text-bg-{{ $expiryColor }}">License: {{ $expiryLabel }}</span>
    </div>
    @if($expiryStatus === 'expiring_soon')
        <div class="alert alert-warning" role="alert">This business license expires within 30 days. Arrange renewal and submit the updated license.</div>
    @elseif($expiryStatus === 'expired')
        <div class="alert alert-danger" role="alert">This business license has expired. It is not considered valid, regardless of its verification status.</div>
    @endif
    @if($compliance->verification_status === 'rejected' && $compliance->rejection_reason)
        <div class="alert alert-danger" role="alert">
            <strong>Admin rejection reason:</strong>
            <p class="mb-0">{{ $compliance->rejection_reason }}</p>
        </div>
    @endif
@endif

<section class="card mb-4">
    <div class="card-header">Business information from MPO Profile</div>
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label" for="business-legal-name">Business Legal Name</label>
            <input class="form-control" id="business-legal-name" value="{{ $mpoAdmin->registered_business_name }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="business-registration-number">Business Registration Number</label>
            <input class="form-control" id="business-registration-number" value="{{ $mpoAdmin->brn_number }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="registered-address-line1">Registered Address — Line 1</label>
            <input class="form-control" id="registered-address-line1" value="{{ $mpoAdmin->business_address }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="registered-address-line2">Registered Address — Line 2</label>
            <input class="form-control" id="registered-address-line2" value="{{ $mpoAdmin->business_address_line2 }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="assigned-country">Assigned Country</label>
            <input class="form-control" id="assigned-country" value="{{ $mpoAdmin->country?->name }}" readonly>
        </div>
    </div>
</section>

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
    @csrf
    @if($isAdminView) @method('PATCH') @endif
    <fieldset @disabled(!$profileReady)>
        <section class="card mb-4">
            <div class="card-header">Business License</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="business_license_number">Business License Number <span class="text-danger">*</span></label>
                    <input class="form-control" id="business_license_number" name="business_license_number" maxlength="100" value="{{ old('business_license_number', $compliance?->business_license_number) }}" required>
                    @error('business_license_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="license_type">License Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="license_type" name="license_type" required>
                        <option value="">Select license type</option>
                        @foreach($licenseTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('license_type', $compliance?->license_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('license_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="other_license_type_description">Other License Type Description</label>
                    <input class="form-control" id="other_license_type_description" name="other_license_type_description" maxlength="191" value="{{ old('other_license_type_description', $compliance?->other_license_type_description) }}">
                    <div class="form-text">Required when Other is selected.</div>
                    @error('other_license_type_description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="license_expiry_date">License Expiry Date <span class="text-danger">*</span></label>
                    <input class="form-control" id="license_expiry_date" name="license_expiry_date" type="date" value="{{ old('license_expiry_date', $compliance?->license_expiry_date?->toDateString()) }}" required>
                    <div class="form-text">Expired licenses can still be submitted for Admin review; they will be clearly marked expired.</div>
                    @error('license_expiry_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>

        <section class="card mb-4">
            <div class="card-header">Required Legal Documents</div>
            <div class="card-body row g-3">
                @foreach([
                    'proof_of_license' => ['label' => 'Proof of License', 'path' => $compliance?->proof_of_license_path, 'route' => 'proof-of-license'],
                    'insurance_certificate' => ['label' => 'Insurance Certificate', 'path' => $compliance?->insurance_certificate_path, 'route' => 'insurance-certificate'],
                ] as $field => $document)
                    <div class="col-md-6">
                        <label class="form-label" for="{{ $field }}">{{ $document['label'] }} @if(!$document['path'])<span class="text-danger">*</span>@endif</label>
                        @if($document['path'])
                            <p class="mb-2">
                                Current document:
                                <a href="{{ $documentRoute($document['route']) }}">{{ $field === 'proof_of_license' ? ($compliance->proof_of_license_filename ?: basename($document['path'])) : ($compliance->insurance_certificate_filename ?: basename($document['path'])) }}</a>
                            </p>
                        @else
                            <p class="small text-muted">No document uploaded yet.</p>
                        @endif
                        <input class="form-control" id="{{ $field }}" type="file" name="{{ $field }}" accept=".pdf,.jpg,.jpeg,application/pdf,image/jpeg" @required(!$document['path'])>
                        <div class="form-text">PDF or JPEG/JPG, maximum 10 MB. Replacement is optional when a current document exists.</div>
                        @error($field)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
        </section>

        <button class="btn btn-primary mb-4" type="submit">
            {{ $compliance ? 'Save and Submit for Review' : 'Submit Compliance' }}
        </button>
    </fieldset>
</form>

@if($isAdminView && $compliance)
    <section class="card mb-4">
        <div class="card-header">Admin Review</div>
        <div class="card-body">
            <p>
                Current status: <strong>{{ $compliance->verificationStatusLabel() }}</strong>
                @if($compliance->reviewed_at)
                    <span class="text-muted">— reviewed {{ $compliance->reviewed_at->format('d M Y H:i') }} by {{ $compliance->reviewedBy?->name ?? 'Former Admin' }}</span>
                @endif
            </p>
            <form method="POST" action="{{ route('admin.country-admins.compliance.review', $mpoAdmin) }}" class="d-flex flex-wrap align-items-end gap-2">
                @csrf
                @method('PATCH')
                <input type="hidden" name="verification_status" value="verified">
                <button class="btn btn-success" type="submit">Mark Verified</button>
            </form>
            <form method="POST" action="{{ route('admin.country-admins.compliance.review', $mpoAdmin) }}" class="mt-3">
                @csrf
                @method('PATCH')
                <input type="hidden" name="verification_status" value="rejected">
                <div class="mb-2">
                    <label class="form-label" for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" maxlength="5000" required>{{ old('rejection_reason') }}</textarea>
                    @error('rejection_reason')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-outline-danger" type="submit">Reject Submission</button>
            </form>
        </div>
    </section>
@endif
@endsection
