@extends('layouts.admin')

@section('title', 'Review Agent')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Review Agent: {{ $agent->agency_name }}</h1>
        <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-secondary">Back to Agents</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card mb-3"><div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Agent ID</dt><dd class="col-sm-9">{{ $agent->agent_id }}</dd>
            <dt class="col-sm-3">Agency</dt><dd class="col-sm-9">{{ $agent->agency_name }}</dd>
            <dt class="col-sm-3">Contact</dt><dd class="col-sm-9">{{ $agent->contact_person }} · {{ $agent->email }}</dd>
            <dt class="col-sm-3">Country</dt><dd class="col-sm-9">{{ $agent->country?->name }}</dd>
            <dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ ucfirst($agent->account_status) }}</dd>
            <dt class="col-sm-3">Approved at</dt><dd class="col-sm-9">{{ $agent->approved_at?->format('Y-m-d H:i') ?? '—' }}</dd>
            <dt class="col-sm-3">Setup progress</dt><dd class="col-sm-9">
                @php($progress = $agent->setupProgress)
                Profile {{ $progress?->profile_complete ? 'Complete' : 'Incomplete' }} ·
                Compliance {{ $progress?->compliance_complete ? 'Complete' : 'Incomplete' }} ·
                Users {{ $progress?->users_complete ? 'Complete' : 'Incomplete' }} ·
                Operations {{ $progress?->operations_complete ? 'Complete' : 'Incomplete' }}
            </dd>
            <dt class="col-sm-3">Address</dt><dd class="col-sm-9">{{ $agent->profile?->address ?? '—' }}, {{ $agent->profile?->city }}</dd>
            <dt class="col-sm-3">Agency type</dt><dd class="col-sm-9">{{ $agent->profile?->agency_type ?? '—' }}</dd>
            <dt class="col-sm-3">Focus segments</dt><dd class="col-sm-9">{{ implode(', ', $agent->profile?->focus_segments ?? []) ?: '—' }}</dd>
            <dt class="col-sm-3">Description</dt><dd class="col-sm-9">{{ $agent->profile?->description ?? '—' }}</dd>
            <dt class="col-sm-3">Business registration</dt><dd class="col-sm-9">{{ $agent->complianceDocuments->last()?->business_registration_number ?? '—' }}</dd>
        </dl>
    </div></div>
    <div class="card mb-3"><div class="card-header">Compliance Documents</div><div class="card-body">
        @forelse($agent->complianceDocuments as $document)
            <p>{{ $document->document_type ?: 'Business document' }} · {{ $document->status }} · expires {{ $document->expiry_date }}
                <a href="{{ route('admin.agents.compliance.download', $document) }}" class="btn btn-sm btn-outline-primary">Download</a>
            </p>
        @empty
            <p class="mb-0">No compliance document uploaded.</p>
        @endforelse
    </div></div>
    <div class="card mb-3"><div class="card-header">Agent Staff and Permissions</div><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Module Access</th><th>Update Access</th></tr></thead>
        <tbody>
        @forelse($agent->staffUsers as $user)
            <tr><td>{{ $user->full_name }}</td><td>{{ $user->email }}</td><td>{{ $user->role }}</td><td>{{ $user->status }}</td>
                <td>{{ $user->permissions->filter(fn ($permission) => $permission->can_read || $permission->can_create || $permission->can_update || $permission->can_approve || $permission->can_publish)->map(fn ($permission) => $permission->module?->name)->filter()->implode(', ') ?: '—' }}</td>
                <td><form method="POST" action="{{ route('admin.agents.staff.update', [$agent, $user]) }}" class="d-flex gap-2">@csrf
                    <select class="form-select form-select-sm" name="role">@foreach(['Manager','Consultant','Accountant','Support'] as $role)<option @selected($user->role === $role)>{{ $role }}</option>@endforeach</select>
                    <select class="form-select form-select-sm" name="status">@foreach(['Active','Disabled'] as $status)<option @selected($user->status === $status)>{{ $status }}</option>@endforeach</select>
                    <button class="btn btn-sm btn-outline-primary">Save</button>
                </form></td>
            </tr>
        @empty
            <tr><td colspan="6">No Agent staff users.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
    <div class="card mb-3"><div class="card-header">Operations</div><div class="card-body">
        <p class="mb-1">Languages: {{ $agent->operations?->preferred_language_ui ?? '—' }} / {{ $agent->operations?->preferred_language_communication ?? '—' }}</p>
        <p class="mb-1">Time zone: {{ $agent->operations?->time_zone ?? '—' }}</p>
        <p class="mb-1">Primary currency: {{ $agent->operations?->primary_currency ?? '—' }}</p>
        <p class="mb-0">Emergency contact: {{ $agent->operations?->emergency_contact_name ?? '—' }} · {{ $agent->operations?->emergency_contact_mobile ?? '—' }}</p>
    </div></div>
    <div class="d-flex gap-2">
        @if($agent->account_status !== 'approved')
            @php($progress = $agent->setupProgress)
            @if($progress?->profile_complete && $progress?->compliance_complete && $progress?->users_complete && $progress?->operations_complete)
                <form method="POST" action="{{ route('admin.agents.approve', $agent) }}">@csrf<button class="btn btn-success">Approve Agent</button></form>
            @else
                <button class="btn btn-success" disabled title="Complete all setup steps before approval">Approve Agent</button>
            @endif
        @endif
        @if($agent->account_status !== 'suspended')
            <form method="POST" action="{{ route('admin.agents.suspend', $agent) }}">@csrf<button class="btn btn-danger">Suspend Agent</button></form>
        @endif
    </div>
</div>
@endsection
