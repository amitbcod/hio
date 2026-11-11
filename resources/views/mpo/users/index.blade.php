@extends($isAdminView ? 'layouts.admin' : 'layouts.mpo-portal')

@section('title', 'MPO Users')

@section('content')
@php
    $indexRoute = $isAdminView
        ? route('admin.country-admins.users', $mpoAdmin)
        : route('mpo.users');
    $createRoute = $isAdminView
        ? route('admin.country-admins.users.create', $mpoAdmin)
        : route('mpo.users.create');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        @if($isAdminView)
            <p class="text-muted mb-0">Staff accounts belonging to {{ $mpoAdmin->name }} ({{ $mpoAdmin->country?->name ?? 'Unassigned country' }}).</p>
        @elseif($actor instanceof \App\Models\MpoStaffUser)
            <p class="text-muted mb-0">Signed in as {{ $actor->full_name }} — {{ $permissionService->canonicalRole($actor->role) }} — {{ $actor->status }}.</p>
        @else
            <p class="text-muted mb-0">Manage staff accounts and their delegated access for your MPO.</p>
        @endif
    </div>
    @if($permissionService->canManage($actor, 'create'))
        <a class="btn btn-primary" href="{{ $createRoute }}">Create User</a>
    @endif
</div>

<form method="GET" action="{{ $indexRoute }}" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label" for="search">Search User ID, Full Name, or Email</label>
            <input class="form-control" id="search" name="search" value="{{ request('search') }}" maxlength="191">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="role-filter">Role</label>
            <select class="form-select" id="role-filter" name="role">
                <option value="">All roles</option>
                @foreach($roleOptions as $role)<option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="status-filter">Status</label>
            <select class="form-select" id="status-filter" name="status">
                <option value="">All statuses</option>
                @foreach(['Active', 'Disabled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-outline-primary" type="submit">Search</button>
            <a class="btn btn-outline-secondary" href="{{ $indexRoute }}">Clear</a>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>User ID</th><th>Full Name</th><th>Email</th><th>Mobile</th><th>Role</th>
                <th>Effective Module Access</th><th>Status</th><th>Last Login</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staffUsers as $staffUser)
                @php
                    $showRoute = $isAdminView
                        ? route('admin.country-admins.users.show', [$mpoAdmin, $staffUser->id])
                        : route('mpo.users.show', $staffUser->id);
                    $editRoute = $isAdminView
                        ? route('admin.country-admins.users.edit', [$mpoAdmin, $staffUser->id])
                        : route('mpo.users.edit', $staffUser->id);
                    $statusRoute = $isAdminView
                        ? route('admin.country-admins.users.status', [$mpoAdmin, $staffUser->id])
                        : route('mpo.users.status', $staffUser->id);
                    $resetRoute = $isAdminView
                        ? route('admin.country-admins.users.reset-password', [$mpoAdmin, $staffUser->id])
                        : route('mpo.users.send-password-reset', $staffUser->id);
                    $access = collect($permissionService->effectiveMatrix($staffUser))
                        ->map(fn ($actions) => collect($actions)->filter(fn ($permission) => $permission['allowed'])->keys())
                        ->filter(fn ($actions) => $actions->isNotEmpty());
                    $accessEntries = $access
                        ->map(function ($actions, $module) {
                            $label = $actions
                                ->map(fn ($action) => config('mpo_staff.actions.'.$action, $action))
                                ->implode(', ');

                            return [
                                'module' => $module,
                                'label' => $label,
                            ];
                        })
                        ->values()
                        ->all();
                    $visibleAccess = array_slice($accessEntries, 0, 2);
                    $hiddenAccess = array_slice($accessEntries, 2);
                @endphp
                <tr>
                    <td>{{ $staffUser->id }}</td>
                    <td>{{ $staffUser->full_name }}</td>
                    <td>{{ $staffUser->email }}</td>
                    <td>{{ $staffUser->mobile ?: '—' }}</td>
                    <td>{{ $permissionService->canonicalRole($staffUser->role) }}</td>
                    <td>
                        @if($accessEntries === [])
                            <span class="text-muted">No module access</span>
                        @else
                            <div class="d-flex flex-column gap-1">
                                @foreach($visibleAccess as $entry)
                                    <span class="badge text-bg-light border align-self-start text-start">
                                        {{ $entry['module'] }}: {{ \Illuminate\Support\Str::limit($entry['label'], 60) }}
                                    </span>
                                @endforeach

                                @if($hiddenAccess !== [])
                                    <div class="access-more d-none d-flex flex-column gap-1">
                                        @foreach($hiddenAccess as $entry)
                                            <span class="badge text-bg-light border align-self-start text-start">
                                                {{ $entry['module'] }}: {{ \Illuminate\Support\Str::limit($entry['label'], 60) }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-start access-toggle" data-expanded="false">View more</button>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td><span class="badge text-bg-{{ $staffUser->status === 'Active' ? 'success' : 'secondary' }}">{{ $staffUser->status }}</span></td>
                    <td>{{ $staffUser->last_login?->timezone(config('app.timezone'))->format('d M Y H:i') ?? 'Never logged in' }}</td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ $showRoute }}">View</a>
                        @if($permissionService->canManage($actor, 'update') && ! ($actor instanceof \App\Models\MpoStaffUser && (int) $actor->id === (int) $staffUser->id))
                            <a class="btn btn-sm btn-outline-primary" href="{{ $editRoute }}">Edit</a>
                        @endif
                        @if($permissionService->canManage($actor, 'reset_password') && ! ($actor instanceof \App\Models\MpoStaffUser && (int) $actor->id === (int) $staffUser->id))
                            <form method="POST" action="{{ $resetRoute }}" class="d-inline" onsubmit="return confirm('Send a secure password reset link to this user?');">
                                @csrf
                                <button class="btn btn-sm btn-outline-warning" type="submit">Reset Password</button>
                            </form>
                        @endif
                        @if($permissionService->canManage($actor, 'disable') && ! ($actor instanceof \App\Models\MpoStaffUser && (int) $actor->id === (int) $staffUser->id))
                            <form method="POST" action="{{ $statusRoute }}" class="d-inline" onsubmit="return confirm('{{ $staffUser->status === 'Active' ? 'Disable' : 'Enable' }} this user?');">
                                @csrf
                                <input type="hidden" name="status" value="{{ $staffUser->status === 'Active' ? 'Disabled' : 'Active' }}">
                                <button class="btn btn-sm btn-outline-{{ $staffUser->status === 'Active' ? 'danger' : 'success' }}" type="submit">
                                    {{ $staffUser->status === 'Active' ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center py-4">No MPO staff users found. Create the first staff account to begin assigning access.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $staffUsers->links('pagination::bootstrap-5') }}

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.access-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const container = this.previousElementSibling;
                const expanded = this.dataset.expanded === 'true';

                this.dataset.expanded = expanded ? 'false' : 'true';
                this.textContent = expanded ? 'View more' : 'View less';
                container.classList.toggle('d-none', expanded);
            });
        });
    });
</script>
@endpush
@endsection
