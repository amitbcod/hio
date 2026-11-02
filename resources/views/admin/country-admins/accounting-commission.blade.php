@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h1 class="h3 mb-1">MPO Accounting Commission</h1><p class="text-muted mb-0">{{ $mpoAdmin->name }} · {{ $mpoAdmin->country?->name }}</p></div>
        <a class="btn btn-outline-secondary" href="{{ route('admin.country-admins.index') }}">Back to MPOs</a>
    </div>
    @if(!$mpoAdmin->country?->vat_percentage)<div class="alert alert-warning">The assigned country VAT rate has not been configured.</div>@endif
    <div class="card mb-4">
        <div class="card-header">Tax and VAT Details</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4">MPO ID</dt><dd class="col-sm-8">{{ $mpoAdmin->id }}</dd>
                <dt class="col-sm-4">MPO Name</dt><dd class="col-sm-8">{{ $mpoAdmin->registered_business_name ?: $mpoAdmin->name }}</dd>
                <dt class="col-sm-4">Country VAT %</dt><dd class="col-sm-8">{{ $mpoAdmin->country?->vat_percentage !== null ? $mpoAdmin->country->vat_percentage.'%' : 'Not configured' }}</dd>
                <dt class="col-sm-4">Tax ID</dt><dd class="col-sm-8">{{ $configuration?->tax_id ?: 'Not provided' }}</dd>
                <dt class="col-sm-4">VAT Number</dt><dd class="col-sm-8">{{ $configuration?->vat_number ?: 'Not provided' }}</dd>
                <dt class="col-sm-4">VAT Exempted</dt><dd class="col-sm-8">{{ $configuration?->vat_exempted ? 'Yes (declared; verification status is managed separately)' : 'No' }}</dd>
            </dl>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Bank Account Details — Restricted Financial Data</div>
        <div class="card-body">
            @if($bank !== [])
                <dl class="row mb-0">
                    <dt class="col-sm-4">Account Holder</dt><dd class="col-sm-8">{{ $bank['account_holder_name'] ?? 'Not provided' }}</dd>
                    <dt class="col-sm-4">IBAN</dt><dd class="col-sm-8">{{ $bank['iban'] ?? 'Not provided' }}</dd>
                    <dt class="col-sm-4">Bank Name</dt><dd class="col-sm-8">{{ $bank['bank_name'] ?? 'Not provided' }}</dd>
                    <dt class="col-sm-4">SWIFT/BIC</dt><dd class="col-sm-8">{{ $bank['swift_bic'] ?? 'Not provided' }}</dd>
                </dl>
                @if($configuration?->bank_details_changed_at)
                    <p class="small text-muted mt-3 mb-0">Last changed {{ $configuration->bank_details_changed_at->format('d M Y H:i') }} by {{ $configuration->bank_details_changed_by_type }} #{{ $configuration->bank_details_changed_by_id }}.</p>
                @endif
            @else
                <p class="mb-0 text-muted">No bank details are configured.</p>
            @endif
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Responsible Accountant</div>
        <div class="card-body">{{ $configuration?->responsibleAccountant?->full_name ?? 'Not assigned' }}{{ $configuration?->responsibleAccountant ? ' — '.$configuration->responsibleAccountant->role : '' }}</div>
    </div>
    <div class="card">
        <div class="card-header">Collaboration Agreement Commission (read-only)</div>
        <div class="card-body">
            @if($agreement)
                <dl class="row mb-0">
                    <dt class="col-sm-4">Agreement ID</dt><dd class="col-sm-8">{{ $agreement->agreement_id }}</dd>
                    <dt class="col-sm-4">Status</dt><dd class="col-sm-8">{{ $agreement->status }}</dd>
                    <dt class="col-sm-4">Commission Model</dt><dd class="col-sm-8">{{ ucfirst($agreement->commission_model) }}</dd>
                    <dt class="col-sm-4">Commission Value</dt><dd class="col-sm-8">{{ $agreement->commission_value !== null ? $agreement->commission_value.'%' : 'Not configured' }}</dd>
                </dl>
            @else<p class="mb-0 text-muted">No agreement configured.</p>@endif
        </div>
    </div>
</div>
@endsection
