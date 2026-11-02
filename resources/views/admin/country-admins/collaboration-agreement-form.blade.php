@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">{{ $isCreate ? 'Create MPO Agreement' : 'Manage MPO Agreement' }}</h1>
            <p class="text-muted mb-0">{{ $administrator->name }} · {{ $administrator->country?->name }}</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('admin.country-admins.index') }}">Back to MPOs</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    @if($isCreate)
        <form method="POST" action="{{ route('admin.country-admins.collaboration-agreements.store', $administrator) }}" enctype="multipart/form-data">
    @else
        <form method="POST" action="{{ route('admin.country-admins.collaboration-agreements.update', [$administrator, $agreement]) }}" enctype="multipart/form-data">
            @method('PUT')
    @endif
        @csrf
        <div class="card mb-4">
            <div class="card-header">Controlled Agreement Terms</div>
            <div class="card-body row g-3">
                @if(!$isCreate)<div class="col-md-6"><strong>Agreement ID</strong><div>{{ $agreement->agreement_id }}</div></div><div class="col-md-6"><strong>Status</strong><div>{{ $agreement->status }}</div></div>@endif
                <div class="col-md-6">
                    <label class="form-label" for="agreement_type">Agreement Type</label>
                    <select class="form-select" id="agreement_type" name="agreement_type" required>
                        <option value="mpo" @selected(old('agreement_type', $agreement->agreement_type) === 'mpo')>MPO Agreement</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="start_date">Start Date</label>
                    <input class="form-control" id="start_date" name="start_date" type="date" value="{{ old('start_date', $agreement->start_date?->toDateString()) }}" required>
                    <div class="form-text">System onboarding date: {{ $administrator->mpo_onboarded_at?->format('d M Y') ?? 'Not recorded' }}. Start date is independently configurable.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="commission_model">Commission Model</label>
                    <select class="form-select" id="commission_model" name="commission_model" required><option value="percentage" selected>Percentage</option></select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="commission_value">Commission Value (%)</label>
                    <input class="form-control" id="commission_value" name="commission_value" type="number" min="0" max="100" step="0.0001" value="{{ old('commission_value', $agreement->commission_value) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="marketing_contribution_percent">Marketing Contribution (%)</label>
                    <input class="form-control" id="marketing_contribution_percent" name="marketing_contribution_percent" type="number" min="0" max="100" step="0.0001" value="{{ old('marketing_contribution_percent', $agreement->marketing_contribution_percent) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="marketing_contribution_payer">Marketing Contribution Funded By</label>
                    <select class="form-select" id="marketing_contribution_payer" name="marketing_contribution_payer">
                        <option value="">Not configured</option>
                        <option value="mpo" @selected(old('marketing_contribution_payer', $agreement->marketing_contribution_payer) === 'mpo')>MPO</option>
                        <option value="operator" @selected(old('marketing_contribution_payer', $agreement->marketing_contribution_payer) === 'operator')>Operator</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="responsibilities_document">Responsibilities document (PDF or DOCX, max 10 MB)</label>
                    <input class="form-control" id="responsibilities_document" name="responsibilities_document" type="file" accept=".pdf,.docx">
                    @if($agreement->currentDocument)<div class="form-text">Current: {{ $agreement->currentDocument->original_name }}. Uploading a new file preserves the previous version in history.</div>@endif
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">Management and Accounting Contacts</div>
            <div class="card-body row g-3">
                @foreach([
                    'management' => ['title' => 'Management Contact', 'prefix' => 'management_contact_'],
                    'accounting' => ['title' => 'Accounting Contact', 'prefix' => 'accounting_contact_'],
                ] as $contact => $group)
                    <div class="col-12"><h2 class="h6 mb-0">{{ $group['title'] }}</h2></div>
                    @foreach(['name' => 'Contact Name', 'email' => 'Email', 'phone' => 'Phone', 'mobile' => 'Mobile'] as $field => $label)
                        @php($fieldName = $group['prefix'].$field)
                        <div class="col-md-6">
                            <label class="form-label" for="{{ $fieldName }}">{{ $label }}</label>
                            <input class="form-control" id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ old($fieldName, $agreement->{$fieldName}) }}" @if($field === 'email') type="email" @elseif(in_array($field, ['phone', 'mobile'], true)) type="tel" placeholder="+230XXXXXXXX" @endif>
                        </div>
                    @endforeach
                @endforeach
                <p class="form-text mb-0">Optional while preparing a draft. MPO users must provide all four contact fields when saving their own contact details.</p>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">{{ $isCreate ? 'Create Draft Agreement' : 'Save Agreement Terms' }}</button>
    </form>

    @if(!$isCreate)
        @if($agreement->currentDocument)
            <p class="mt-3"><a href="{{ route('admin.country-admins.collaboration-agreements.documents.download', [$administrator, $agreement->currentDocument]) }}">Download current responsibilities document</a></p>
        @endif
        <div class="card my-4">
            <div class="card-header">Agreement Lifecycle</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.country-admins.collaboration-agreements.status', [$administrator, $agreement]) }}" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label" for="status">Transition to</label>
                        <select class="form-select" name="status" id="status" required>
                            @if($agreement->status === 'Draft')<option>Active</option>@elseif($agreement->status === 'Active')<option>Suspended</option><option>Terminated</option>@elseif($agreement->status === 'Suspended')<option>Active</option><option>Terminated</option>@endif
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label" for="reason">Reason (required for suspension or termination)</label><input class="form-control" id="reason" name="reason" maxlength="1000"></div>
                    <div class="col-md-2 d-flex align-items-end"><button class="btn btn-warning" type="submit" @disabled($agreement->status === 'Terminated')>Change Status</button></div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header">History</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0"><thead><tr><th>Date</th><th>Event</th><th>Change</th><th>Reason</th></tr></thead><tbody>
                    @forelse($agreement->events as $event)
                        <tr><td>{{ $event->created_at?->format('d M Y H:i') }}</td><td>{{ str_replace('_', ' ', ucfirst($event->event_type)) }}{{ $event->from_status ? " ({$event->from_status} to {$event->to_status})" : '' }}</td><td><code>{{ json_encode($event->changes, JSON_UNESCAPED_SLASHES) }}</code></td><td>{{ $event->reason }}</td></tr>
                    @empty<tr><td colspan="4" class="text-center text-muted">No history available.</td></tr>@endforelse
                </tbody></table>
            </div>
        </div>
    @endif
</div>
@endsection
