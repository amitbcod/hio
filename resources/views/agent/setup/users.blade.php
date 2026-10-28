@extends('layouts.agent')
@section('title', 'Agent Users')
@section('content')
<h1 class="h3 mb-4">Agent Users</h1>
<div class="card mb-4">
    <div class="card-header py-3">Team Members</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Access Rights</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                @php
                    $accessRights = $user->permissions
                        ->map(function ($permission): ?string {
                            $actions = collect(['read' => 'Read', 'create' => 'Create', 'update' => 'Update', 'approve' => 'Approve', 'publish' => 'Publish'])
                                ->filter(fn ($label, $action) => (bool) $permission->{'can_'.$action})
                                ->values();

                            return $actions->isNotEmpty()
                                ? ($permission->module?->name ?? 'Module') . ': ' . $actions->join(', ')
                                : null;
                        })
                        ->filter()
                        ->values();
                    $isCurrentUser = (int) auth('agent_staff')->id() === (int) $user->id;
                @endphp
                <tr>
                    <td>{{ $user->full_name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>
                        @if($accessRights->isNotEmpty())
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($accessRights as $accessRight)
                                    <span class="badge bg-light text-dark border">{{ $accessRight }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">No access granted</span>
                        @endif
                    </td>
                    <td><span class="badge {{ $user->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">{{ $user->status }}</span></td>
                    <td>{{ $user->last_login?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>
                        @if($canManageUsers && ! $isCurrentUser)
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('agent.users.edit', $user) }}">Edit</a>
                                <form method="POST" action="{{ route('agent.users.status', $user) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $user->status === 'Active' ? 'Disabled' : 'Active' }}">
                                    <button class="btn btn-sm btn-outline-secondary">{{ $user->status === 'Active' ? 'Disable' : 'Enable' }}</button>
                                </form>
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No Agent staff users yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($canManageUsers)
    <form method="POST" action="{{ route('agent.users.save') }}" class="card">
        <div class="card-header">Add Agent User</div>
        <div class="card-body">
            @csrf
            <div class="mb-3"><label class="form-label" for="new-full-name">Full Name *</label><input class="form-control" id="new-full-name" name="full_name" required></div>
            <div class="mb-3"><label class="form-label" for="new-email">Email *</label><input class="form-control" id="new-email" type="email" name="email" required></div>
            <div class="mb-3"><label class="form-label" for="new-mobile">Mobile</label><input class="form-control" id="new-mobile" name="mobile" placeholder="+23012345678"><div class="form-text">Use E.164 format, including the +country code.</div></div>
            <div class="mb-3"><label class="form-label" for="new-password">Password *</label><input class="form-control" id="new-password" type="password" name="password" required></div>
            <div class="mb-3">
                <label class="form-label" for="new-role">Role *</label>
                <select class="form-select" id="new-role" name="role" required>
                    @foreach(['Manager', 'Consultant', 'Accountant', 'Support'] as $role)
                        <option value="{{ $role }}" @selected($role === 'Consultant')>{{ $role }}</option>
                    @endforeach
                </select>
                <div class="form-text">The selected role provides initial defaults. Adjust the exact module actions below as needed.</div>
            </div>
            <h2 class="h6">Access Rights</h2>
            @foreach($modules as $module)
                <input type="hidden" name="permission_modules[]" value="{{ $module->slug }}">
                <fieldset class="border rounded p-2 mb-2">
                    <legend class="float-none w-auto px-2 fs-6">{{ $module->name }}</legend>
                    @foreach(['read' => 'Read', 'create' => 'Create', 'update' => 'Update', 'approve' => 'Approve', 'publish' => 'Publish'] as $action => $label)
                        <label class="form-check form-check-inline">
                            <input class="form-check-input js-new-user-permission" type="checkbox" name="module_actions[{{ $module->slug }}][]" value="{{ $action }}" @checked(in_array($action, $roleDefaults['Consultant'][$module->slug] ?? [], true))>
                            {{ $label }}
                        </label>
                    @endforeach
                </fieldset>
            @endforeach
            <button class="btn btn-primary">Create User</button>
        </div>
    </form>

    <div class="card mt-4">
        <div class="card-header">Reset a Staff Password</div>
        <div class="card-body">
            @foreach($users->reject(fn ($user) => (int) auth('agent_staff')->id() === (int) $user->id) as $user)
                <form method="POST" action="{{ route('agent.users.reset-password', $user) }}" class="row g-2 align-items-end mb-3">
                    @csrf
                    <div class="col-md-4"><label class="form-label" for="reset-password-{{ $user->id }}">{{ $user->full_name }}</label><input class="form-control" id="reset-password-{{ $user->id }}" type="password" name="password" placeholder="New password" required></div>
                    <div class="col-md-4"><input class="form-control" type="password" name="password_confirmation" placeholder="Confirm password" required></div>
                    <div class="col-md-4"><button class="btn btn-outline-primary">Reset Password</button></div>
                </form>
            @endforeach
        </div>
    </div>
@push('scripts')
<script>
    const agentRoleDefaults = @json($roleDefaults);
    const newUserRole = document.getElementById('new-role');
    newUserRole?.addEventListener('change', function () {
        const selectedRole = this.value;
        document.querySelectorAll('.js-new-user-permission').forEach(function (permission) {
            const moduleSlug = permission.name.match(/^module_actions\[([^\]]+)\]/)?.[1];
            permission.checked = (agentRoleDefaults[selectedRole]?.[moduleSlug] || []).includes(permission.value);
        });
    });
</script>
@endpush
@endif
@endsection
