@extends('layouts.mpo-portal')

@section('title', 'Accounting Commission')

@section('content')
<section class="card mb-4">
    <div class="card-header">MPO Information</div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4">MPO ID</dt><dd class="col-sm-8">{{ $mpoAdmin->id }}</dd>
            <dt class="col-sm-4">MPO Name</dt><dd class="col-sm-8">{{ $mpoAdmin->registered_business_name ?: $mpoAdmin->name }}</dd>
            <dt class="col-sm-4">Country</dt><dd class="col-sm-8">{{ $mpoAdmin->country?->name ?? 'Not assigned' }}</dd>
            <dt class="col-sm-4">Tax ID</dt><dd class="col-sm-8">{{ $configuration?->tax_id ?: 'Not provided' }}</dd>
        </dl>
    </div>
</section>

@if(!$mpoAdmin->country || $mpoAdmin->country->vat_percentage === null)
    <div class="alert alert-warning" role="alert">
        The assigned country does not have a VAT percentage configured. Ask a Global Admin to configure the country's VAT setting. No rate will be assumed.
    </div>
@endif
@if(!$agreement)
    <div class="alert alert-warning" role="alert">No Collaboration Agreement is configured yet. Agreement commission terms are not available.</div>
@elseif($agreement->status !== 'Active')
    <div class="alert alert-info" role="status">The agreement is {{ $agreement->status }}. Its commission terms are shown for reference and do not activate or change the agreement.</div>
@endif

<form method="POST" action="{{ route('mpo.accounting-commission.update') }}">
    @csrf
    @method('PUT')
    <section class="card mb-4">
        <div class="card-header">VAT Configuration</div>
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label" for="tax_id">Tax ID (optional)</label>
                <input class="form-control" id="tax_id" name="tax_id" maxlength="100" value="{{ old('tax_id', $configuration?->tax_id) }}" @disabled(!$canUpdate)>
                @error('tax_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label d-block">VAT Exempted</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="vat_exempted" name="vat_exempted" value="1" @checked(old('vat_exempted', $configuration?->vat_exempted ?? false)) @disabled(!$canUpdate)>
                    <label class="form-check-label" for="vat_exempted">Our MPO is VAT exempted</label>
                </div>
                <div class="form-text">This declaration does not itself change tax treatment or prove exemption.</div>
                @error('vat_exempted')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="vat_number">VAT Number <span class="text-danger vat-required">*</span></label>
                <input class="form-control" id="vat_number" name="vat_number" maxlength="100" value="{{ old('vat_number', $configuration?->vat_number) }}" @required(!($configuration?->vat_exempted ?? false)) @disabled(!$canUpdate || old('vat_exempted', $configuration?->vat_exempted ?? false))>
                <div class="form-text">VAT number is required unless VAT Exempted is selected.</div>
                @error('vat_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="vat_percentage">Country VAT % (read-only)</label>
                <input class="form-control" id="vat_percentage" value="{{ $mpoAdmin->country?->vat_percentage !== null ? $mpoAdmin->country->vat_percentage.'%' : 'Not configured' }}" readonly>
                <div class="form-text">Configured for {{ $mpoAdmin->country?->name ?? 'the assigned country' }} by a Global Admin.</div>
            </div>
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header">Bank Account Details</div>
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label" for="account_holder_name">Account Holder Name <span class="text-danger">*</span></label>
                <input class="form-control" id="account_holder_name" name="bank[account_holder_name]" maxlength="191" value="{{ old('bank.account_holder_name', $bank['account_holder_name'] ?? '') }}" required @disabled(!$canUpdate)>
                @error('bank.account_holder_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="iban">IBAN <span class="text-danger">*</span></label>
                <input class="form-control" id="iban" name="bank[iban]" maxlength="34" autocomplete="off" value="{{ old('bank.iban', $bank['iban'] ?? '') }}" required @disabled(!$canUpdate)>
                <div class="form-text">Stored encrypted. Display and edit access requires Accounting permission.</div>
                @error('bank.iban')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="bank_name">Bank Name <span class="text-danger">*</span></label>
                <input class="form-control" id="bank_name" name="bank[bank_name]" maxlength="191" value="{{ old('bank.bank_name', $bank['bank_name'] ?? '') }}" required @disabled(!$canUpdate)>
                @error('bank.bank_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="swift_bic">SWIFT/BIC Code <span class="text-danger">*</span></label>
                <input class="form-control text-uppercase" id="swift_bic" name="bank[swift_bic]" maxlength="11" value="{{ old('bank.swift_bic', $bank['swift_bic'] ?? '') }}" required @disabled(!$canUpdate)>
                @error('bank.swift_bic')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header">Responsible Accountant</div>
        <div class="card-body">
            <label class="form-label" for="responsible_accountant_user_id">Responsible Accountant User (optional)</label>
            <select class="form-select" id="responsible_accountant_user_id" name="responsible_accountant_user_id" @disabled(!$canUpdate)>
                <option value="">Not assigned</option>
                @foreach($eligibleAccountants as $staff)
                    <option value="{{ $staff->id }}" @selected((string) old('responsible_accountant_user_id', $configuration?->responsible_accountant_user_id) === (string) $staff->id)>{{ $staff->full_name }} — {{ $staff->role }}</option>
                @endforeach
            </select>
            @if($eligibleAccountants === [])<div class="form-text">No active MPO staff member with Accounting Read permission is available.</div>@endif
            @error('responsible_accountant_user_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header">Agreement Commission Reference (read-only)</div>
        <div class="card-body">
            @if($agreement)
                @php($terms = $agreement->accountingCommissionTerms())
                <dl class="row mb-0">
                    <dt class="col-sm-4">Agreement ID</dt><dd class="col-sm-8">{{ $agreement->agreement_id }}</dd>
                    <dt class="col-sm-4">Agreement Status</dt><dd class="col-sm-8">{{ $terms['status'] }}</dd>
                    <dt class="col-sm-4">Commission Model</dt><dd class="col-sm-8">{{ ucfirst($terms['model']) }}</dd>
                    <dt class="col-sm-4">Commission Value</dt><dd class="col-sm-8">{{ $terms['value'] !== null ? $terms['value'].'%' : 'Not configured' }}</dd>
                    <dt class="col-sm-4">Start Date</dt><dd class="col-sm-8">{{ $terms['start_date']?->format('d M Y') ?? 'Not configured' }}</dd>
                </dl>
            @else
                <p class="mb-0 text-muted">Commission terms will appear after Global Admin creates an agreement.</p>
            @endif
            <p class="small text-muted mt-3 mb-0">VAT and commission are separate values. No tax or booking calculation is changed by this page.</p>
        </div>
    </section>

    @if($canUpdate)<button class="btn btn-primary" type="submit">Save Changes</button>@endif
</form>

@if(!$canUpdate)<div class="alert alert-info mt-3">You have read-only access to Accounting Commission.</div>@endif
<div class="alert alert-{{ $isComplete ? 'success' : 'info' }} mt-3" role="status">
    Accounting Commission onboarding: <strong>{{ $isComplete ? 'Completed' : 'In Progress' }}</strong>.
    Completion requires a configured country VAT rate and valid bank details, plus a VAT number unless exempted.
</div>
@if($configuration?->bank_details_changed_at)
    <p class="small text-muted">Bank details last changed {{ $configuration->bank_details_changed_at->format('d M Y H:i') }}. IBAN ending {{ substr($bank['iban'] ?? '', -4) }}.</p>
@endif
@push('scripts')
<script>
    (() => {
        const exempt = document.getElementById('vat_exempted');
        const vatNumber = document.getElementById('vat_number');
        const requiredLabel = document.querySelector('.vat-required');
        if (!exempt || !vatNumber) return;
        const syncVatField = () => {
            const isExempt = exempt.checked;
            vatNumber.required = !isExempt;
            vatNumber.disabled = isExempt || vatNumber.hasAttribute('data-readonly');
            if (requiredLabel) requiredLabel.hidden = isExempt;
        };
        if (!@json($canUpdate)) {
            exempt.disabled = true;
            vatNumber.dataset.readonly = 'true';
        }
        exempt.addEventListener('change', syncVatField);
        syncVatField();
    })();
</script>
@endpush
@endsection
