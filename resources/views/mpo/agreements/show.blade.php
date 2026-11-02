@extends('layouts.mpo-portal')

@section('title', 'Collaboration Agreements')

@section('content')
@php
    $canUpdateContacts = !($actor instanceof \App\Models\MpoStaffUser)
        || app(\App\Services\MpoStaffPermissionService::class)->can($actor, 'collaboration-agreements', 'update');
    $contactGroups = [
        'management' => ['title' => 'Management Contact', 'prefix' => 'management_contact_'],
        'accounting' => ['title' => 'Accounting Contact', 'prefix' => 'accounting_contact_'],
    ];
@endphp
@if(!$agreement)
    <div class="alert alert-info" role="status">Your collaboration agreement is pending administrative setup. You can continue with other available onboarding steps.</div>
@else
    <div class="card mb-4">
        <div class="card-header">Agreement Information</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4">MPO ID</dt><dd class="col-sm-8">{{ $mpoAdmin->id }}</dd>
                <dt class="col-sm-4">MPO Name</dt><dd class="col-sm-8">{{ $mpoAdmin->registered_business_name ?: $mpoAdmin->name }}</dd>
                <dt class="col-sm-4">Agreement ID</dt><dd class="col-sm-8">{{ $agreement->agreement_id }}</dd>
                <dt class="col-sm-4">Agreement Type</dt><dd class="col-sm-8">{{ $agreement->agreement_type === 'mpo' ? 'MPO Agreement' : $agreement->agreement_type }}</dd>
                <dt class="col-sm-4">Onboarding Date</dt><dd class="col-sm-8">{{ $agreement->onboarding_date?->format('d M Y') ?? 'Not recorded' }}</dd>
                <dt class="col-sm-4">Start Date</dt><dd class="col-sm-8">{{ $agreement->start_date?->format('d M Y') ?? 'Not configured' }}</dd>
                <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge text-bg-{{ $agreement->status === 'Active' ? 'success' : ($agreement->status === 'Terminated' ? 'dark' : 'warning') }}">{{ $agreement->status }}</span></dd>
            </dl>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Responsibilities</div>
        <div class="card-body">
            @if($agreement->currentDocument)
                <p class="mb-1">{{ $agreement->currentDocument->original_name }}</p>
                <p class="small text-muted">Uploaded {{ $agreement->currentDocument->created_at?->format('d M Y H:i') }} · {{ number_format($agreement->currentDocument->size_bytes / 1024, 1) }} KB</p>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('mpo.collaboration-agreements.documents.download', $agreement->currentDocument) }}">Download responsibilities document</a>
            @else
                <p class="mb-0 text-muted">No responsibilities document available.</p>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Commercial Terms</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4">Commission Model</dt><dd class="col-sm-8">{{ ucfirst($agreement->commission_model) }}</dd>
                <dt class="col-sm-4">Commission Value</dt><dd class="col-sm-8">{{ $agreement->commission_value !== null ? $agreement->commission_value.'%' : 'Not configured' }}</dd>
                <dt class="col-sm-4">Marketing Contribution</dt><dd class="col-sm-8">{{ $agreement->marketing_contribution_percent !== null ? $agreement->marketing_contribution_percent.'% ('.ucfirst($agreement->marketing_contribution_payer).' funded)' : 'Not configured' }}</dd>
            </dl>
            <p class="small text-muted mt-3 mb-0">These agreement terms are read-only here and do not by themselves change booking prices or payouts.</p>
        </div>
    </div>

    @foreach($contactGroups as $type => $group)
        <section class="card mb-4">
            <div class="card-header">{{ $group['title'] }}</div>
            <div class="card-body">
                @if($canUpdateContacts)
                    <form method="POST" action="{{ route('mpo.collaboration-agreements.contacts.update', $type) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            @foreach(['name' => 'Contact Name', 'email' => 'Email', 'phone' => 'Phone', 'mobile' => 'Mobile'] as $field => $label)
                                @php($fieldName = $group['prefix'].$field)
                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $type }}-{{ $field }}">{{ $label }} <span class="text-danger">*</span></label>
                                    <input class="form-control" id="{{ $type }}-{{ $field }}" name="{{ $field }}" value="{{ old($field, $agreement->{$fieldName}) }}" @if($field === 'email') type="email" @elseif(in_array($field, ['phone', 'mobile'], true)) type="tel" placeholder="+230XXXXXXXX" @endif required>
                                    @error($field)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
                        </div>
                        <p class="form-text mt-2">Phone and mobile are both required. Enter international numbers in E.164 format, for example +23051234567.</p>
                        <button class="btn btn-primary" type="submit">Save {{ $type }} contact</button>
                    </form>
                @else
                    <dl class="row mb-0">
                        @foreach(['name' => 'Contact Name', 'email' => 'Email', 'phone' => 'Phone', 'mobile' => 'Mobile'] as $field => $label)
                            <dt class="col-sm-4">{{ $label }}</dt><dd class="col-sm-8">{{ $agreement->{$group['prefix'].$field} ?: 'Not provided' }}</dd>
                        @endforeach
                    </dl>
                @endif
            </div>
        </section>
    @endforeach

    <div class="alert alert-{{ $agreement->isOnboardingComplete() ? 'success' : 'info' }}" role="status">
        Collaboration Agreements onboarding: <strong>{{ $agreement->isOnboardingComplete() ? 'Completed' : 'In Progress' }}</strong>.
        Agreement lifecycle status remains {{ $agreement->status }}.
    </div>
@endif
@endsection
