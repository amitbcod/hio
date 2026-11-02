@extends($isAdminView ? 'layouts.admin' : 'layouts.mpo-portal')

@section('title', $staffUser ? 'Edit MPO User' : 'Create MPO User')

@section('content')
@php
    $formAction = $isAdminView
        ? ($staffUser
            ? route('admin.country-admins.users.update', [$mpoAdmin, $staffUser->id])
            : route('admin.country-admins.users.store', $mpoAdmin))
        : ($staffUser ? route('mpo.users.update', $staffUser->id) : route('mpo.users.store'));
    $indexRoute = $isAdminView ? route('admin.country-admins.users', $mpoAdmin) : route('mpo.users');
    $selectedRole = old('role', $staffUser ? app(\App\Services\MpoStaffPermissionService::class)->canonicalRole($staffUser->role) : null);
    $currentOverrides = $staffUser?->permissionOverrides?->keyBy(fn ($override) => ($override->module?->slug ?? '').'.'.$override->action) ?? collect();
@endphp

@if($isAdminView)<p><a href="{{ $indexRoute }}">Back to MPO Users</a></p>@endif
<form method="POST" action="{{ $formAction }}">
    @csrf
    @if($staffUser) @method('PUT') @endif
    <section class="card mb-4">
        <div class="card-header">Staff Account</div>
        <div class="card-body row g-3">
            @if($staffUser)
                <div class="col-md-6">
                    <label class="form-label" for="staff-id">User ID</label>
                    <input class="form-control" id="staff-id" value="{{ $staffUser->id }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="staff-mpo-id">MPO ID</label>
                    <input class="form-control" id="staff-mpo-id" value="{{ $mpoAdmin->id }}" readonly>
                </div>
            @endif
            <div class="col-md-6">
                <label class="form-label" for="full_name">Full Name <span class="text-danger">*</span></label>
                <input class="form-control" id="full_name" name="full_name" maxlength="191" value="{{ old('full_name', $staffUser?->full_name) }}" required>
                @error('full_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                <input class="form-control" id="email" name="email" type="email" maxlength="191" value="{{ old('email', $staffUser?->email) }}" required autocomplete="email">
                @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="mobile">Mobile / WhatsApp</label>
                <input class="form-control" id="mobile" name="mobile" type="tel" maxlength="20" placeholder="+230XXXXXXXX" value="{{ old('mobile', $staffUser?->mobile) }}" autocomplete="tel">
                    <div class="form-text">Optional; use international E.164 format.</div>
                @error('mobile')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
                <select class="form-select" id="role" name="role" required>
                    <option value="">Select a role</option>
                    @foreach($roleOptions as $role)<option value="{{ $role }}" @selected($selectedRole === $role)>{{ $role }}</option>@endforeach
                </select>
                <div class="form-text">MPO staff roles are separate from global Admin roles.</div>
                @error('role')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password">{{ $staffUser ? 'New Password (optional)' : 'Password' }} @unless($staffUser)<span class="text-danger">*</span>@endunless</label>
                <input class="form-control" id="password" name="password" type="password" minlength="8" autocomplete="new-password" @required(!$staffUser)>
                <div class="form-text">At least 8 characters including uppercase, lowercase, a number, and one of @#%!*^$&.</div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Confirm Password @unless($staffUser)<span class="text-danger">*</span>@endunless</label>
                <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" @required(!$staffUser)>
            </div>
            @if($staffUser)
                <div class="col-md-6"><span class="form-label d-block">Status</span><span class="badge text-bg-{{ $staffUser->status === 'Active' ? 'success' : 'secondary' }}">{{ $staffUser->status }}</span> <span class="form-text">Status changes use the separate authorized action.</span></div>
                <div class="col-md-6"><span class="form-label d-block">Last Login</span>{{ $staffUser->last_login?->timezone(config('app.timezone'))->format('d M Y H:i') ?? 'Never logged in' }}</div>
            @endif
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header">Access Rights</div>
        <div class="card-body">
            <p class="text-muted">Role permissions are inherited from this MPO's role configuration. Explicit overrides are limited to permissions you are authorized to delegate.</p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead><tr><th>Module</th><th>Action</th><th>Role Inherited</th><th>Capacity</th><th>Data Scope</th><th>Explicit Override</th><th>May Delegate</th></tr></thead>
                    <tbody>
                        @foreach($modules as $module)
                            @foreach(config('mpo_staff.modules.'.$module->slug, []) as $action)
                                @php
                                    $canDelegate = (bool) ($delegatableActions[$module->slug][$action] ?? false);
                                    $roleGrant = (bool) ($roleMatrices[$selectedRole][$module->slug][$action]['allowed'] ?? false);
                                    $roleModuleSettings = $roleSettings[$selectedRole][$module->slug] ?? [];
                                    $override = $currentOverrides->get($module->slug.'.'.$action);
                                    $overrideValue = old("permission_overrides.{$module->slug}.{$action}.effect", $override?->effect ?? 'inherit');
                                    $delegationValue = old("permission_overrides.{$module->slug}.{$action}.can_delegate", $override?->can_delegate ?? false);
                                @endphp
                                <tr data-module="{{ $module->slug }}" data-action="{{ $action }}">
                                    <td>{{ $module->name }}</td>
                                    <td>{{ config('mpo_staff.actions.'.$action, $action) }}</td>
                                    <td class="role-inherited">{{ $roleGrant ? 'Allowed' : 'Not granted' }}</td>
                                    <td class="role-capacity">{{ $roleModuleSettings['capacity_level'] ?? 'Not set' }}</td>
                                    <td class="role-scope">{{ config('mpo_staff.data_scopes.'.($roleModuleSettings['data_scope'] ?? 'all'), 'Unavailable') }}</td>
                                    <td>
                                        @if($canDelegate)
                                            <select class="form-select form-select-sm permission-effect" name="permission_overrides[{{ $module->slug }}][{{ $action }}][effect]">
                                                <option value="inherit" @selected($overrideValue === 'inherit')>Inherit role</option>
                                                <option value="allow" @selected($overrideValue === 'allow')>Explicitly allow</option>
                                                <option value="deny" @selected($overrideValue === 'deny')>Explicitly deny</option>
                                            </select>
                                        @else
                                            <span class="text-muted">{{ $override ? ucfirst($override->effect).' (not editable)' : 'Not delegatable' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($canDelegate)
                                            <input type="hidden" name="permission_overrides[{{ $module->slug }}][{{ $action }}][can_delegate]" value="0">
                                            <input class="form-check-input permission-delegate" type="checkbox" name="permission_overrides[{{ $module->slug }}][{{ $action }}][can_delegate]" value="1" @checked((bool) $delegationValue)>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <button class="btn btn-primary" type="submit">{{ $staffUser ? 'Save User' : 'Create User' }}</button>
    <a class="btn btn-outline-secondary" href="{{ $indexRoute }}">Cancel</a>
</form>

@push('scripts')
<script>
    (() => {
        const roleMatrices = @json($roleMatrices);
        const roleSettings = @json($roleSettings);
        const roleSelect = document.getElementById('role');
        if (!roleSelect) return;
        const updateInherited = () => {
            const matrix = roleMatrices[roleSelect.value] || {};
            document.querySelectorAll('tr[data-module][data-action]').forEach((row) => {
                const allowed = Boolean(matrix[row.dataset.module]?.[row.dataset.action]?.allowed);
                row.querySelector('.role-inherited').textContent = allowed ? 'Allowed' : 'Not granted';
                const settings = roleSettings[roleSelect.value]?.[row.dataset.module] || {};
                row.querySelector('.role-capacity').textContent = settings.capacity_level || 'Not set';
                row.querySelector('.role-scope').textContent = settings.data_scope === 'all'
                    ? 'All records within this MPO and assigned country'
                    : 'Unavailable';
            });
        };
        const updateDelegationControls = () => {
            document.querySelectorAll('tr[data-module][data-action]').forEach((row) => {
                const effect = row.querySelector('.permission-effect');
                const checkbox = row.querySelector('.permission-delegate');
                if (!effect || !checkbox) return;
                checkbox.disabled = effect.value !== 'allow';
                if (checkbox.disabled) checkbox.checked = false;
            });
        };
        roleSelect.addEventListener('change', updateInherited);
        document.querySelectorAll('.permission-effect').forEach((select) => {
            select.addEventListener('change', updateDelegationControls);
        });
        updateInherited();
        updateDelegationControls();
    })();
</script>
@endpush
@endsection
