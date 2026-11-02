@extends('layouts.mpo-portal')

@section('title', 'Capacity Permissions and Roles')

@section('content')
@php
    $roleSelectAction = route('mpo.capacity-permissions');
    $saveAction = route('mpo.capacity-permissions.update');
    $actionLabels = config('mpo_staff.actions', []);
    $roleDescriptions = config('mpo_staff.role_descriptions', []);
    $roleByName = $roles->keyBy('name');
    $selectedRoleDetails = $roleByName->get($selectedRole);
    $isOwner = $actor instanceof \App\Models\AdminUser;
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <p class="text-muted mb-0">Configure MPO staff access using this MPO's role and delegation mappings.</p>
    </div>
</div>

<section class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ $roleSelectAction }}" id="role-selection-form">
            <label for="role-select" class="form-label">Role</label>
            <select class="form-select" name="role" id="role-select" required>
                @foreach($roles as $role)
                    <option value="{{ $role['name'] }}" @selected($selectedRole === $role['name'])>
                        {{ $role['name'] }} ({{ $role['staff_count'] }} staff)
                    </option>
                @endforeach
            </select>
            @if($selectedRoleDetails)
                <p class="form-text mb-0 mt-2">
                    {{ $roleDescriptions[$selectedRole] ?? $selectedRoleDetails['description'] }}
                    @unless($selectedRoleDetails['configurable'])
                        <span class="text-muted">You can view this role but cannot configure it.</span>
                    @endunless
                </p>
            @endif
        </form>
    </div>
</section>

<section class="card mb-4">
    <div class="card-header">Supported MPO Modules and Actions</div>
    <div class="card-body">
        <p class="text-muted">Section grants every supported action for that module. Tick Function grants only the individually selected actions. Every operation remains subject to country data scope and existing booking business rules.</p>
        @if($canConfigureSelectedRole)
            <form method="POST" action="{{ $saveAction }}" id="role-permissions-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="role" value="{{ $selectedRole }}">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Module</th>
                                <th scope="col">Action</th>
                                <th scope="col">Role access</th>
                                <th scope="col">Delegation authority</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modules as $module)
                                @foreach($supportedActions[$module->slug] ?? [] as $action)
                                    @php
                                        $grant = $roleMatrix[$module->slug][$action] ?? [];
                                        $granted = (bool) ($grant['allowed'] ?? false);
                                        $delegable = (bool) ($delegatableActions[$module->slug][$action] ?? false);
                                        $sectionAccess = ($settings[$module->slug]['capacity_level'] ?? 'Tick Function') === 'Section';
                                    @endphp
                                    <tr data-module="{{ $module->slug }}" data-action="{{ $action }}" data-action-label="{{ $actionLabels[$action] ?? $action }}">
                                        <th scope="row">
                                            <span class="d-block small text-muted">{{ $module->category }}</span>
                                            {{ $module->display_name }}
                                            @if($module->display_description)
                                                <span class="d-block small text-muted">{{ $module->display_description }}</span>
                                            @endif
                                        </th>
                                        <td>{{ $actionLabels[$action] ?? $action }}</td>
                                        <td>
                                            @if($delegable || $sectionAccess)
                                                <input type="hidden" name="permissions[{{ $module->slug }}][{{ $action }}][allowed]" value="{{ $sectionAccess ? '1' : '0' }}">
                                                <label class="form-check">
                                                    <input class="form-check-input permission-allowed" type="checkbox" name="permissions[{{ $module->slug }}][{{ $action }}][allowed]" value="1" @checked($granted) @disabled($sectionAccess)>
                                                    <span class="form-check-label permission-state-label">{{ $sectionAccess ? 'Allowed by Section' : ($granted ? 'Allowed' : 'Not granted') }}</span>
                                                </label>
                                            @else
                                                <span class="{{ $granted ? 'text-success' : 'text-muted' }}">
                                                    {{ $granted ? 'Allowed (not delegable)' : 'Not granted' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($delegable)
                                                <input type="hidden" name="permissions[{{ $module->slug }}][{{ $action }}][can_delegate]" value="0">
                                                <label class="form-check">
                                                    <input class="form-check-input permission-delegation" type="checkbox" name="permissions[{{ $module->slug }}][{{ $action }}][can_delegate]" value="1" @checked((bool) ($grant['can_delegate'] ?? false))>
                                                    <span class="form-check-label">May delegate</span>
                                                </label>
                                            @else
                                                <span class="text-muted">Not authorized</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <section class="border rounded p-3 mb-4" aria-labelledby="capacity-settings-title">
                    <h2 class="h5" id="capacity-settings-title">Capacity and data boundary</h2>
                    <p class="small text-muted">Section grants every supported action for that module. Tick Function keeps only selected actions; switching from Section to Tick clears the inherited grants so you can select the permitted actions. Service modules and their booking modules are configured separately. All records remain bounded by the authenticated MPO, assigned country, and data-scope rules.</p>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead><tr><th scope="col">Module</th><th scope="col">Capacity level</th><th scope="col">Data scope</th><th scope="col">Notes</th></tr></thead>
                            <tbody>
                                @foreach($modules as $module)
                                    @php($moduleSettings = $settings[$module->slug] ?? [])
                                    <tr>
                                        <th scope="row">{{ $module->name }}</th>
                                        <td>
                                            @if($canChangeRoleSettings)
                                                <select class="form-select form-select-sm capacity-level" data-capacity-module="{{ $module->slug }}" name="settings[{{ $module->slug }}][capacity_level]">
                                                    @foreach(config('mpo_staff.capacity_levels', []) as $value => $label)
                                                        <option value="{{ $value }}" @selected(($moduleSettings['capacity_level'] ?? null) === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ $moduleSettings['capacity_level'] ?? 'Not set' }}
                                                <input type="hidden" name="settings[{{ $module->slug }}][capacity_level]" value="{{ $moduleSettings['capacity_level'] ?? '' }}">
                                            @endif
                                        </td>
                                        <td>
                                            @if($canChangeRoleSettings)
                                                <select class="form-select form-select-sm" name="settings[{{ $module->slug }}][data_scope]" required>
                                                    @foreach(config('mpo_staff.data_scopes', []) as $value => $label)
                                                        <option value="{{ $value }}" @selected(($moduleSettings['data_scope'] ?? 'all') === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ config('mpo_staff.data_scopes.'.($moduleSettings['data_scope'] ?? 'all'), 'Unavailable') }}
                                                <input type="hidden" name="settings[{{ $module->slug }}][data_scope]" value="{{ $moduleSettings['data_scope'] ?? 'all' }}">
                                            @endif
                                        </td>
                                        <td>
                                            @if($canChangeRoleSettings)
                                                <textarea class="form-control form-control-sm" name="settings[{{ $module->slug }}][notes]" rows="1" maxlength="2000">{{ $moduleSettings['notes'] ?? '' }}</textarea>
                                            @else
                                                {{ $moduleSettings['notes'] ?? '—' }}
                                                <input type="hidden" name="settings[{{ $module->slug }}][notes]" value="{{ $moduleSettings['notes'] ?? '' }}">
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if($canChangeDelegations)
                    <section class="border rounded p-3 mb-4" aria-labelledby="role-delegations-title">
                        <h2 class="h5" id="role-delegations-title">Roles this role may assign</h2>
                        <p class="small text-muted">A role can be assigned only when its permissions and further role delegations stay within the acting user's own delegated authority.</p>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach(config('mpo_staff.roles', []) as $roleOption)
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="assignable_roles[]" value="{{ $roleOption }}" @checked(in_array($roleOption, $delegatedRoles, true))>
                                    <span class="form-check-label">{{ $roleOption }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endif

                <div class="border rounded p-3 mb-4">
                    <h2 class="h5">Effective access preview</h2>
                    <p class="small text-muted mb-2">This preview is generated from the same saved role mappings used by server-side checks. Changes shown in the form take effect after saving.</p>
                    @forelse($modules as $module)
                        @php($grantedActions = collect($roleMatrix[$module->slug] ?? [])->filter(fn ($permission) => $permission['allowed'])->keys())
                        <div class="mb-1" data-role-preview="{{ $module->slug }}" data-module-label="{{ $module->name }}"><strong>{{ $module->name }}:</strong> {{ $grantedActions->map(fn ($action) => $actionLabels[$action] ?? $action)->join(', ') ?: 'No actions granted' }}</div>
                    @empty
                        <p class="mb-0">No protected MPO modules are configured.</p>
                    @endforelse
                    <p class="small text-muted mt-2 mb-0">Approval limits are unavailable because the current MPO workflows expose no monetary approval action. Any approval permission without an explicit limit is denied by the permission service.</p>
                </div>

                @if($isOwner)
                    <p class="small text-muted">Region and assigned-operator data scopes are unavailable: the current MPO staff resources do not yet have region or operator assignment enforcement. This page therefore permits only the safe MPO-and-country boundary.</p>
                @endif
                <button class="btn btn-primary" type="submit">Save role configuration</button>
                <a class="btn btn-outline-secondary" href="{{ route('mpo.capacity-permissions', ['role' => $selectedRole]) }}">Cancel</a>
            </form>
        @else
            <p class="mb-0 text-muted">No role configuration is available for editing under your current delegation authority.</p>
        @endif
    </div>
</section>

@push('scripts')
<script>
    (() => {
        const roleForm = document.getElementById('role-permissions-form');
        const roleSelect = document.getElementById('role-select');
        const initialMatrix = @json($roleMatrix);
        let dirty = false;
        const applyCapacityLevel = (module, level, previousLevel = null) => {
            const leavingSection = previousLevel === 'Section' && level !== 'Section';
            document.querySelectorAll(`tr[data-module="${module}"]`).forEach((row) => {
                const allowed = row.querySelector('.permission-allowed');
                if (!allowed) return;
                const sectionAccess = level === 'Section';
                const hiddenAllowed = row.querySelector(`input[type="hidden"][name$="[allowed]"]`);
                if (hiddenAllowed) hiddenAllowed.value = sectionAccess ? '1' : '0';
                if (sectionAccess) {
                    allowed.checked = true;
                } else if (leavingSection) {
                    allowed.checked = false;
                }
                allowed.disabled = sectionAccess;
                const delegation = row.querySelector('.permission-delegation');
                if (delegation) {
                    if (leavingSection) delegation.checked = false;
                    delegation.disabled = !sectionAccess && !allowed.checked;
                }
                const label = row.querySelector('.permission-state-label');
                if (label) {
                    label.textContent = sectionAccess
                        ? 'Allowed by Section'
                        : (allowed.checked ? 'Allowed' : 'Not granted');
                }
            });
        };
        const updatePreview = () => {
            const actionsByModule = {};
            document.querySelectorAll('tr[data-module][data-action]').forEach((row) => {
                const allowedInput = row.querySelector('.permission-allowed');
                const initiallyAllowed = Boolean(initialMatrix[row.dataset.module]?.[row.dataset.action]?.allowed);
                if (allowedInput ? allowedInput.checked : initiallyAllowed) {
                    actionsByModule[row.dataset.module] = actionsByModule[row.dataset.module] || [];
                    actionsByModule[row.dataset.module].push(row.dataset.actionLabel);
                }
            });
            document.querySelectorAll('[data-role-preview]').forEach((preview) => {
                const actions = actionsByModule[preview.dataset.rolePreview] || [];
                preview.textContent = `${preview.dataset.moduleLabel}: ${actions.length ? actions.join(', ') : 'No actions granted'}`;
            });
        };
        if (roleForm) {
            roleForm.addEventListener('change', () => { dirty = true; });
            roleForm.addEventListener('submit', (event) => {
                if (!window.confirm('Save these role permission and delegation changes?')) event.preventDefault();
            });
        }
        if (roleSelect) {
            roleSelect.addEventListener('change', (event) => {
                if (dirty && !window.confirm('Discard unsaved role changes?')) {
                    event.preventDefault();
                    roleSelect.value = @json($selectedRole);
                    return;
                }
                document.getElementById('role-selection-form').submit();
            });
        }
        document.querySelectorAll('.permission-allowed').forEach((input) => {
            input.addEventListener('change', () => {
                const row = input.closest('tr');
                const delegation = row?.querySelector('.permission-delegation');
                if (delegation) {
                    delegation.disabled = !input.checked;
                    if (!input.checked) delegation.checked = false;
                }
                const label = row?.querySelector('.permission-state-label');
                if (label) label.textContent = input.checked ? 'Allowed' : 'Not granted';
                updatePreview();
            });
            input.dispatchEvent(new Event('change'));
        });
        document.querySelectorAll('.capacity-level').forEach((select) => {
            let previousLevel = select.value;
            applyCapacityLevel(select.dataset.capacityModule, previousLevel);
            select.addEventListener('change', () => {
                applyCapacityLevel(select.dataset.capacityModule, select.value, previousLevel);
                previousLevel = select.value;
                dirty = true;
                updatePreview();
            });
        });
        updatePreview();
    })();
</script>
@endpush
@endsection
