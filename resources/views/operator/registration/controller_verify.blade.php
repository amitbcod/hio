@extends('layouts.app')

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card mt-5">
        <div class="card-body">
            <h4>Owner verification for business: <strong>{{ $cv->business->legal_name }}</strong></h4>

            @if(isset($requester) && $requester)
                <div class="alert alert-info">
                    <h6>Requester details</h6>
                    <p><strong>{{ $requester->full_name }}</strong></p>
                    <p>{{ $requester->email }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            @if(isset($owner) && $owner)
                <div class="alert alert-info">
                    <h6>Account Found</h6>
                    <p>We found an existing account for <strong>{{ $owner->email }}</strong>.</p>
                    <p>Please <a href="{{ route('operator.login') }}">login</a> with that account to approve this verification request.</p>
                </div>

                <form method="POST" action="{{ url('/operator/register/controller/verify/'.$cv->token.'/accept') }}">
                    @csrf
                    <h6>Approve Verification</h6>
                    <p class="text-muted">By approving, you confirm that <strong>{{ $requester->full_name ?? 'the requester' }}</strong> is authorized to manage this business on your behalf.</p>
                    <button class="btn btn-success">Approve Verification</button>
                    <a class="btn btn-secondary" href="{{ url('/') }}">Cancel</a>
                </form>
            @else
                <p>No existing owner account found for <strong>{{ $cv->owner_email }}</strong>.</p>
                <p>You can claim and create an owner account below. We will not ask you to re-enter business details — they are shown below for confirmation.</p>

                <div class="alert alert-light">
                    <h6>Business Summary</h6>
                    <p><strong>{{ $cv->business->legal_name }}</strong></p>
                    @if($cv->business->registration_number)<p>Registration #: {{ $cv->business->registration_number }}</p>@endif
                    @if($cv->business->country)<p>Country: {{ $cv->business->country }}</p>@endif
                    @if($cv->business->primary_contact_email)<p>Primary contact: {{ $cv->business->primary_contact_email }}</p>@endif
                </div>

                <form method="POST" action="{{ url('/operator/register/controller/verify/'.$cv->token.'/claim') }}">
                    @csrf
                    <input type="hidden" name="owner_email" value="{{ $cv->owner_email }}">
                    <div class="mb-3">
                        <label>Your Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email (readonly)</label>
                        <input type="email" name="owner_email_readonly" class="form-control" value="{{ $cv->owner_email }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Phone (optional)</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3 d-flex align-items-start">
                        <div style="flex: 1;">
                            <label>Agreement Type *</label>
                            <select id="agreement_type_select" name="agreement_type" class="form-control" required>
                                <option value="">-- Select Agreement Type --</option>
                                <option value="Listing Only" {{ (old('agreement_type') == 'Listing Only' || ($cv->business->agreement_type ?? '') == 'Listing Only') ? 'selected' : '' }}>Listing Only</option>
                                <option value="OTO" {{ (old('agreement_type') == 'OTO' || ($cv->business->agreement_type ?? '') == 'OTO') ? 'selected' : '' }}>OTO</option>
                                <option value="Widget Only" {{ (old('agreement_type') == 'Widget Only' || ($cv->business->agreement_type ?? '') == 'Widget Only') ? 'selected' : '' }}>Widget Only</option>
                                <option value="OTO + Widget" {{ (old('agreement_type') == 'OTO + Widget' || ($cv->business->agreement_type ?? '') == 'OTO + Widget') ? 'selected' : '' }}>OTO + Widget</option>
                                <option value="Full Service" {{ (old('agreement_type') == 'Full Service' || ($cv->business->agreement_type ?? '') == 'Full Service') ? 'selected' : '' }}>Full Service</option>
                            </select>
                        </div>
                        <div style="width:260px; margin-left: 16px;">
                            <div id="agreement-preview" style="background:#f8f9fa;border:1px solid #e9ecef;padding:12px;border-radius:6px; display:none;">
                                <strong id="agreement-preview-title">Agreement Preview</strong>
                                <p id="agreement-preview-desc" style="font-size:13px;margin:8px 0 0;color:#666;">Select an agreement type to view details.</p>
                                <a id="agreement-preview-link" href="#" target="_blank" class="btn btn-outline-primary btn-sm mt-2" style="display:none;"><i class="fas fa-file-pdf"></i> Download Full Agreement (PDF)</a>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Confirm Agreement (type full name)</label>
                        <input type="text" name="agreement_confirm_name" class="form-control" required placeholder="Type full name to confirm">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="confirm_authority" id="confirm_authority" required>
                        <label class="form-check-label" for="confirm_authority">I confirm I am authorised to act for this business and accept the details provided.</label>
                    </div>
                    <button class="btn btn-primary">Create & Claim</button>
                </form>
            @endif

        </div>
    </div>
</div>
<script>
function updateAgreementPreview() {
    var preview = document.getElementById('agreement-preview');
    var title = document.getElementById('agreement-preview-title');
    var desc = document.getElementById('agreement-preview-desc');
    var link = document.getElementById('agreement-preview-link');
    var select = document.getElementById('agreement_type_select');
    if (!select) return;
    var value = select.value;
    if (value) {
        var map = {
            'Listing Only': {title: 'Listing Only Agreement', desc: 'Listing Only: minimal listing service. Download the full agreement PDF for details.', file: '/agreements/listing_only.pdf'},
            'OTO': {title: 'OTO Agreement', desc: 'OTO: On the one-off operator arrangement. Download the full agreement PDF for details.', file: '/agreements/oto.pdf'},
            'Widget Only': {title: 'Widget Only Agreement', desc: 'Widget Only: integration for widget-only bookings. Download the full agreement PDF for details.', file: '/agreements/widget_only.pdf'},
            'OTO + Widget': {title: 'OTO + Widget Agreement', desc: 'OTO + Widget: combined OTO and widget terms. Download the full agreement PDF for details.', file: '/agreements/oto_widget.pdf'},
            'Full Service': {title: 'Full Service Agreement', desc: 'Full Service: comprehensive managed service agreement. Download the full agreement PDF for details.', file: '/agreements/full_service.pdf'}
        };
        var info = map[value] || {title: value, desc: 'Download the full agreement for details.', file: '/agreements/' + value.replace(/\s+/g,'_').toLowerCase() + '.pdf'};
        preview.style.display = 'block';
        title.innerText = info.title;
        desc.innerText = info.desc;
        link.href = info.file;
        link.style.display = 'inline-block';
    } else {
        preview.style.display = 'none';
        link.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var select = document.getElementById('agreement_type_select');
    if (select) {
        select.addEventListener('change', updateAgreementPreview);
    }
    // initialize preview (handles preselected values)
    updateAgreementPreview();
});
</script>
@endsection