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
                    $userPermissions = $effectivePermissions[$user->id] ?? [];
                    $isCurrentUser = (int) auth('agent_staff')->id() === (int) $user->id;
                    $accessRights = collect($userPermissions)
                        ->map(function (array $actions, string $moduleSlug) use ($modules): ?string {
                            $labels = collect(['read' => 'Read', 'create' => 'Create', 'update' => 'Update', 'approve' => 'Approve', 'publish' => 'Publish'])
                                ->filter(fn ($label, $action) => $actions[$action]['allowed'] ?? false)
                                ->values();

                            return $labels->isNotEmpty()
                                ? ($modules->firstWhere('slug', $moduleSlug)?->name ?? $moduleSlug) . ': ' . $labels->join(', ')
                                : null;
                        })
                        ->filter()
                        ->values();
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
                        @if($agentCan('users', 'update') && ! $isCurrentUser)
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('agent.users.edit', $user) }}">Edit</a>
                                <form method="POST" action="{{ route('agent.users.status', $user) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $user->status === 'Active' ? 'Disabled' : 'Active' }}">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">{{ $user->status === 'Active' ? 'Disable' : 'Enable' }}</button>
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

@if($agentCan('users', 'create'))
    <form method="POST" action="{{ route('agent.users.save') }}" class="card">
        <div class="card-header">Add Agent User</div>
        <div class="card-body">
            @csrf
            <div class="mb-3"><label class="form-label" for="new-full-name">Full Name *</label><input class="form-control" id="new-full-name" name="full_name" required></div>
            <div class="mb-3"><label class="form-label" for="new-email">Email *</label><input class="form-control" id="new-email" type="email" name="email" required></div>
            <div class="mb-3"><label class="form-label" for="new-mobile">Mobile</label><input class="form-control" id="new-mobile" name="mobile" placeholder="+23012345678"><div class="form-text">Use E.164 format, including the +country code.</div></div>
            <div class="mb-3"><label class="form-label" for="new-password">Password *</label><input class="form-control" id="new-password" type="password" name="password" required></div>
            @if($isAgentOwner)
                <div class="mb-3">
                    <label class="form-label" for="new-role">Role *</label>
                    <select class="form-select" id="new-role" name="role" required>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected($role === $selectedRole)>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <p class="mb-3"><span class="fw-semibold">Role:</span> {{ $selectedRole }}</p>
            @endif
            <h2 class="h6">Access Rights</h2>
            <p class="text-muted">Select only the permissions this user should receive. You can delegate only permissions available to you.</p>
            <div class="row g-3">
                @foreach($modules as $module)
                    @php
                        $moduleRoleOptions = collect($rolePermissionMatrices)
                            ->filter(fn (array $matrix): bool => ! empty($matrix[$module->slug] ?? []))
                            ->keys()
                            ->all();
                        $moduleActions = collect($rolePermissionMatrices)
                            ->flatMap(fn (array $matrix): array => $matrix[$module->slug] ?? [])
                            ->unique()
                            ->values();
                    @endphp
                    @if($moduleActions->isNotEmpty())
                        <div class="col-12 col-xl-6 js-agent-module" data-module="{{ $module->slug }}">
                            <fieldset class="border rounded p-3 h-100">
                                <legend class="float-none w-auto px-2 fs-6 mb-0">{{ $module->name }}</legend>
                                <div class="d-flex flex-wrap gap-3 pt-2">
                                    @foreach(['read' => 'Read', 'create' => 'Create', 'update' => 'Update', 'approve' => 'Approve', 'publish' => 'Publish'] as $action => $label)
                                        @if($moduleActions->contains($action))
                                            @php
                                                $eligibleRoles = collect($rolePermissionMatrices)
                                                    ->filter(fn (array $matrix): bool => in_array($action, $matrix[$module->slug] ?? [], true))
                                                    ->keys()
                                                    ->implode(',');
                                            @endphp
                                            <label class="form-check js-agent-permission" data-module="{{ $module->slug }}" data-roles="{{ $eligibleRoles }}">
                                                <input class="form-check-input" type="checkbox" name="module_actions[{{ $module->slug }}][]" value="{{ $action }}">
                                                <span class="form-check-label">{{ $label }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </fieldset>
                        </div>
                    @endif
                @endforeach
            </div>
            <button class="btn btn-primary mt-3" type="submit">Create User</button>
        </div>
    </form>
@endif

@if($agentCan('users', 'update'))
    <div class="card mt-4">
        <div class="card-header">Reset a Staff Password</div>
        <div class="card-body">
            @foreach($users->reject(fn ($user) => (int) auth('agent_staff')->id() === (int) $user->id) as $user)
                <form method="POST" action="{{ route('agent.users.reset-password', $user) }}" class="row g-2 align-items-end mb-3">
                    @csrf
                    <div class="col-md-4"><label class="form-label" for="reset-password-{{ $user->id }}">{{ $user->full_name }}</label><input class="form-control" id="reset-password-{{ $user->id }}" type="password" name="password" placeholder="New password" required></div>
                    <div class="col-md-4"><input class="form-control" type="password" name="password_confirmation" placeholder="Confirm password" required></div>
                    <div class="col-md-4"><button class="btn btn-outline-primary" type="submit">Reset Password</button></div>
                </form>
            @endforeach
        </div>
    </div>
@endif

@push('scripts')
<script>
    const agentPermissionMatrices = @json($rolePermissionMatrices);
    const newUserRole = document.getElementById('new-role');
    const updateAgentPermissionOptions = function () {
        const selectedRole = newUserRole?.value || @json($selectedRole);
        document.querySelectorAll('.js-agent-permission').forEach(function (label) {
            const allowed = (label.dataset.roles || '').split(',').includes(selectedRole);
            label.hidden = !allowed;
            const checkbox = label.querySelector('input');
            if (!allowed) checkbox.checked = false;
        });
        document.querySelectorAll('.js-agent-module').forEach(function (module) {
            module.hidden = !module.querySelector('.js-agent-permission:not([hidden])');
        });
    };
    newUserRole?.addEventListener('change', updateAgentPermissionOptions);
    updateAgentPermissionOptions();
</script>
@endpush
@endsection
