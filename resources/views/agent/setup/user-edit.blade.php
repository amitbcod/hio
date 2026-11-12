@extends('layouts.agent')
@section('title', 'Edit Agent User')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Edit Agent User</h1>
        <p class="text-muted mb-0">Update {{ $user->full_name }} and their module access.</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('agent.users') }}">Back to Users</a>
</div>

<form method="POST" action="{{ route('agent.users.update', $user) }}">
    @csrf
    @method('PUT')
    <section class="card mb-4">
        <div class="card-header py-3">User Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="full-name">Full Name *</label>
                    <input class="form-control" id="full-name" name="full_name" value="{{ old('full_name', $user->full_name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email *</label>
                    <input class="form-control" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="mobile">Mobile</label>
                    <input class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}" placeholder="+23012345678">
                    <div class="form-text">Use E.164 format, including the +country code.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    @if($isAgentOwner)
                        <select class="form-select" id="edit-role" name="role" required>
                            @foreach(array_keys($rolePermissionMatrices) as $role)
                                <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ $role }}</option>
                            @endforeach
                        </select>
                    @else
                        <p class="form-control-plaintext mb-0">{{ $user->role }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header py-3">Module Access Rights</div>
        <div class="card-body">
            <p class="text-muted">Select the exact permissions this user should receive. Only permissions available to you can be delegated.</p>
            <div class="row g-3">
                @foreach($modules as $module)
                    @php
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
                                                $isAllowed = $effectivePermissions[$module->slug][$action]['allowed'] ?? false;
                                            @endphp
                                            <label class="form-check js-agent-permission" data-module="{{ $module->slug }}" data-roles="{{ $eligibleRoles }}">
                                                <input class="form-check-input" type="checkbox" name="module_actions[{{ $module->slug }}][]" value="{{ $action }}" @checked(in_array($action, old("module_actions.{$module->slug}", $isAllowed ? [$action] : []), true))>
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
        </div>
    </section>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <button class="btn btn-primary" type="submit">Save User and Access Rights</button>
        <a class="btn btn-outline-secondary" href="{{ route('agent.users') }}">Cancel</a>
    </div>
</form>
@push('scripts')
<script>
    const editPermissionMatrices = @json($rolePermissionMatrices);
    const editRole = document.getElementById('edit-role');
    const updateEditPermissionOptions = function () {
        const selectedRole = editRole?.value || @json($user->role);
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
    editRole?.addEventListener('change', updateEditPermissionOptions);
    updateEditPermissionOptions();
</script>
@endpush
@endsection
