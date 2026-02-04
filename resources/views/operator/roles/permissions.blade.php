@extends('layouts.app')

@section('content')
<div class="col-md-10 offset-md-1">
    <div class="card mt-5">
        <div class="card-body">
            <h4>Manage Permissions</h4>

            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Select Role</label>
                    <select id="roleSelect" class="form-select">
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ $r->id == $role->id ? 'selected' : '' }}>{{ $r->name }}{{ $r->business_id ? ' (Business)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-end">
                    <div class="me-3">
                        <input type="text" id="quickFilter" class="form-control" placeholder="Quick filter...">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="grantedOnly">
                        <label class="form-check-label" for="grantedOnly">Granted only</label>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('operator.roles.permissions.update', $role->id) }}">
                @csrf

                @if(isset($modules) && $modules->count())
                    <p class="text-muted">Assign module-level permissions for <strong>{{ $role->name }}</strong>.</p>

                    <div class="table-responsive">
                        <table class="table table-striped" id="permissionsTable">
                            <thead>
                                <tr>
                                    <th style="width:1%"><input type="checkbox" id="masterSelect"></th>
                                    <th>Module</th>
                                    <th class="text-center">Read</th>
                                    <th class="text-center">Create</th>
                                    <th class="text-center">Update</th>
                                    <th class="text-center">Approve</th>
                                    <th class="text-center">Publish</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modules as $m)
                                @php
                                    $row = $roleModulePermissions->get($m->slug) ?? null;
                                @endphp
                                <tr data-module="{{ strtolower($m->name) }}">
                                    <td><input type="checkbox" class="row-select" data-slug="{{ $m->slug }}"></td>
                                    <td class="module-name">{{ $m->name }}</td>
                                    <td class="text-center"><input type="checkbox" name="permissions[{{ $m->slug }}][]" value="Read" {{ $row && $row->can_read ? 'checked' : '' }}></td>
                                    <td class="text-center"><input type="checkbox" name="permissions[{{ $m->slug }}][]" value="Create" {{ $row && $row->can_create ? 'checked' : '' }}></td>
                                    <td class="text-center"><input type="checkbox" name="permissions[{{ $m->slug }}][]" value="Update" {{ $row && $row->can_update ? 'checked' : '' }}></td>
                                    <td class="text-center"><input type="checkbox" name="permissions[{{ $m->slug }}][]" value="Approve" {{ $row && $row->can_approve ? 'checked' : '' }}></td>
                                    <td class="text-center"><input type="checkbox" name="permissions[{{ $m->slug }}][]" value="Publish" {{ $row && $row->can_publish ? 'checked' : '' }}></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @else
                    <p class="text-muted">No modules available. Admin must add modules first.</p>
                @endif

                <div class="mb-3">
                    <button class="btn btn-success">Save</button>
                    <a href="{{ route('operator.roles.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.getElementById('roleSelect').addEventListener('change', function () {
    var id = this.value;
    if (!id) return;
    window.location = '{{ url('operator/roles') }}/' + id + '/permissions';
});

// Quick filter + Granted-only unified visibility handling
const quickFilter = document.getElementById('quickFilter');
const grantedOnlyCheckbox = document.getElementById('grantedOnly');

function updateVisibility() {
    const term = quickFilter.value.toLowerCase().trim();
    const grantedOnlyChecked = grantedOnlyCheckbox.checked;
    document.querySelectorAll('#permissionsTable tbody tr').forEach(tr => {
        const name = tr.querySelector('.module-name').textContent.toLowerCase();
        const matchesFilter = name.indexOf(term) !== -1;
        const anyChecked = Array.from(tr.querySelectorAll('input[type="checkbox"]')).some(cb => cb.checked);
        if (!matchesFilter) {
            tr.style.display = 'none';
        } else if (grantedOnlyChecked && !anyChecked) {
            tr.style.display = 'none';
        } else {
            tr.style.display = '';
        }
    });
}

quickFilter.addEventListener('input', updateVisibility);
grantedOnlyCheckbox.addEventListener('change', updateVisibility);

// Re-evaluate visibility when any checkbox changes so the "Granted only" view stays accurate
document.querySelectorAll('#permissionsTable tbody input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', updateVisibility);
});

// Master select toggles all visible permission checkboxes
document.getElementById('masterSelect').addEventListener('change', function () {
    const on = this.checked;
    document.querySelectorAll('#permissionsTable tbody tr').forEach(tr => {
        if (tr.style.display === 'none') return;
        tr.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = on);
    });
});

// Row-level select toggles all permissions for that module
document.querySelectorAll('.row-select').forEach(cb => {
    cb.addEventListener('change', function () {
        const row = this.closest('tr');
        const on = this.checked;
        row.querySelectorAll('input[type="checkbox"]').forEach(ch => ch.checked = on);
    });
});
</script>
@endsection