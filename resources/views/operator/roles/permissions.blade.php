@extends('layouts.app')

@section('content')
<div class="col-md-10 offset-md-1">
    <div class="card mt-5">
        <div class="card-body">
            <h4>Manage Permissions</h4>
            <p class="text-muted">Configure the maximum permissions for this Operator's staff using this role. Individual staff access is assigned separately and cannot exceed these limits or the creator's current permissions.</p>

            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="roleSelect">Select Role</label>
                    <select id="roleSelect" class="form-select">
                        @foreach($roles as $item)
                            <option value="{{ $item->id }}" @selected($item->id === $role->id)>
                                {{ $item->name }}{{ $item->scope === 'global' ? ' (Global)' : ' (Country)' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-end">
                    <div class="me-3"><input type="text" id="quickFilter" class="form-control" placeholder="Quick filter..."></div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="grantedOnly">
                        <label class="form-check-label" for="grantedOnly">Granted only</label>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('operator.roles.permissions.update', $role->id) }}">
                @csrf
                <p class="text-muted">Role access limits for <strong>{{ $role->name }}</strong>. Check “Delegate” only when staff with this action may pass it to their own staff.</p>
                <div class="table-responsive">
                    <table class="table table-striped align-middle" id="permissionsTable">
                        <thead>
                            <tr>
                                <th style="width:1%"><input type="checkbox" id="masterSelect" aria-label="Select all visible grants"></th>
                                <th>Module</th>
                                @foreach(config('operator_staff.actions') as $label)
                                    <th class="text-center">{{ $label }}<br><small class="text-muted">Grant / Delegate</small></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modules as $module)
                                <tr data-module="{{ strtolower($module->name) }}">
                                    <td><input type="checkbox" class="row-select" aria-label="Select {{ $module->name }} permissions"></td>
                                    <td class="module-name">{{ $module->name }}</td>
                                    @foreach(config('operator_staff.actions') as $action => $label)
                                        @php $permission = $roleMatrix[$module->slug][$action] ?? []; @endphp
                                        <td class="text-center text-nowrap">
                                            <input type="checkbox"
                                                name="permissions[{{ $module->slug }}][{{ $action }}][allowed]"
                                                value="1"
                                                aria-label="Grant {{ $label }} for {{ $module->name }}"
                                                @checked($permission['allowed'] ?? false)>
                                            <input type="checkbox"
                                                name="permissions[{{ $module->slug }}][{{ $action }}][can_delegate]"
                                                value="1"
                                                aria-label="Allow delegation of {{ $label }} for {{ $module->name }}"
                                                @checked($permission['can_delegate'] ?? false)>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-success">Save Role Limits</button>
                <a href="{{ route('operator.roles.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('roleSelect').addEventListener('change', function () {
    if (this.value) {
        window.location = '{{ url('operator/roles') }}/' + encodeURIComponent(this.value) + '/permissions';
    }
});

const quickFilter = document.getElementById('quickFilter');
const grantedOnly = document.getElementById('grantedOnly');
function updateVisibility() {
    const term = quickFilter.value.toLowerCase().trim();
    document.querySelectorAll('#permissionsTable tbody tr').forEach(function (row) {
        const matches = row.querySelector('.module-name').textContent.toLowerCase().includes(term);
        const hasGranted = Array.from(row.querySelectorAll('input[type="checkbox"]:not(.row-select)'))
            .some(function (checkbox) { return checkbox.checked; });
        row.hidden = !matches || (grantedOnly.checked && !hasGranted);
    });
}
quickFilter.addEventListener('input', updateVisibility);
grantedOnly.addEventListener('change', updateVisibility);
document.querySelectorAll('#permissionsTable input[type="checkbox"]').forEach(function (checkbox) {
    checkbox.addEventListener('change', updateVisibility);
});
document.getElementById('masterSelect').addEventListener('change', function () {
    document.querySelectorAll('#permissionsTable tbody tr:not([hidden]) input[type="checkbox"]:not(.row-select)')
        .forEach((checkbox) => { checkbox.checked = this.checked; });
});
document.querySelectorAll('.row-select').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        this.closest('tr').querySelectorAll('input[type="checkbox"]:not(.row-select)')
            .forEach((input) => { input.checked = this.checked; });
    });
});
</script>
@endsection
