@extends($isAdminView ? 'layouts.admin' : 'layouts.mpo-portal')

@section('title', 'MPO User Details')

@section('content')
@php
    $indexRoute = $isAdminView ? route('admin.country-admins.users', $mpoAdmin) : route('mpo.users');
    $editRoute = $isAdminView
        ? route('admin.country-admins.users.edit', [$mpoAdmin, $staffUser->id])
        : route('mpo.users.edit', $staffUser->id);
    $access = collect($effectiveMatrix)
        ->map(fn ($actions) => collect($actions)->filter(fn ($permission) => $permission['allowed'])->keys())
        ->filter(fn ($actions) => $actions->isNotEmpty());
@endphp

<p><a href="{{ $indexRoute }}">Back to MPO Users</a></p>
<div class="card mb-4">
    <div class="card-header">Staff Account</div>
    <div class="card-body row g-3">
        <div class="col-md-3"><strong>User ID</strong><div>{{ $staffUser->id }}</div></div>
        <div class="col-md-3"><strong>MPO ID</strong><div>{{ $mpoAdmin->id }}</div></div>
        <div class="col-md-3"><strong>Full Name</strong><div>{{ $staffUser->full_name }}</div></div>
        <div class="col-md-3"><strong>Email</strong><div>{{ $staffUser->email }}</div></div>
        <div class="col-md-3"><strong>Mobile</strong><div>{{ $staffUser->mobile ?: '—' }}</div></div>
        <div class="col-md-3"><strong>Role</strong><div>{{ app(\App\Services\MpoStaffPermissionService::class)->canonicalRole($staffUser->role) }}</div></div>
        <div class="col-md-3"><strong>Status</strong><div><span class="badge text-bg-{{ $staffUser->status === 'Active' ? 'success' : 'secondary' }}">{{ $staffUser->status }}</span></div></div>
        <div class="col-md-3"><strong>Last Login</strong><div>{{ $staffUser->last_login?->timezone(config('app.timezone'))->format('d M Y H:i') ?? 'Never logged in' }}</div></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Effective Module Access</div>
    <div class="card-body">
        @forelse($access as $module => $actions)
            <div class="mb-2"><strong>{{ $module }}:</strong>
                @foreach($actions as $action)
                    @php($permission = $effectiveMatrix[$module][$action])
                    <span class="badge text-bg-{{ $permission['source'] === 'explicit' ? 'primary' : 'light border' }}">
                        {{ config('mpo_staff.actions.'.$action, $action) }} ({{ $permission['source'] === 'explicit' ? 'Explicit' : 'Inherited' }})
                    </span>
                    @if($permission['can_delegate'])<span class="badge text-bg-info">Delegable</span>@endif
                @endforeach
            </div>
        @empty
            <p class="text-muted mb-0">No module permissions are assigned.</p>
        @endforelse
        @foreach($effectiveMatrix as $module => $actions)
            @foreach($actions as $action => $permission)
                @if(!$permission['allowed'] && $permission['source'] === 'explicit')
                    <div class="mb-2"><strong>{{ $module }}:</strong> {{ config('mpo_staff.actions.'.$action, $action) }} <span class="badge text-bg-danger">Explicitly denied</span></div>
                @endif
            @endforeach
        @endforeach
    </div>
</div>

<a class="btn btn-outline-primary" href="{{ $editRoute }}">Edit User</a>
@endsection
