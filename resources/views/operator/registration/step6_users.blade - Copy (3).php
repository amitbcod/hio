@extends('layouts.app')

@section('progressbar')
@php
    $progress = \App\Models\OperatorRegistrationProgress::where(
        'operator_id',
        auth()->user()->operator_id ?? null
    )->first();

    $completionPercent = isset($progress)
        ? round((($progress->step2_profile ?? 0)+($progress->step3_legal ?? 0)+($progress->step4_system_process ?? 0)+($progress->step5_collaboration ?? 0)+($progress->step6_users ?? 0)+($progress->step7_accounting ?? 0)+($progress->step8_operations ?? 0)+($progress->step9_review ?? 0))/8*100)
        : 0;
@endphp

@include('operator.registration._progress', ['completionPercent' => $completionPercent])
@endsection

@section('content')
@php $currentStep = 6; @endphp

<div class="col-md-3">
    @include('operator.registration._sidebar', ['currentStep' => $currentStep, 'progress' => $progress ?? null])
</div>

<div class="col-md-9 d-flex align-items-center justify-content-center" style="min-height:90vh;">
<div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);padding:32px;width:100%;max-width:900px;">

<h2 class="mb-4 fw-bold">USERS & STAFF MANAGEMENT</h2>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

{{-- Buttons --}}
<div class="mb-3 d-flex justify-content-between">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        Add New User
    </button>
    <a href="{{ url('operator/dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
</div>

{{-- TEAM MEMBERS --}}
<div class="card mt-4">
<div class="card-header">Team Members</div>
<div class="card-body p-0">
<table class="table mb-0">
<thead>
<tr>
<th>Name</th>
<th>Email</th>
<th>Mobile</th>
<th>Role</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>

<tbody>
@forelse($users as $user)
<tr>
<td>{{ $user->full_name }}</td>
<td>{{ $user->email }}</td>
<td>{{ $user->mobile }}</td>
<td><span class="badge bg-info">{{ $user->role }}</span></td>
<td>
@if($user->status === 'Active')
<span class="badge bg-success">Active</span>
@elseif($user->status === 'Inactive')
<span class="badge bg-secondary">Inactive</span>
@else
<span class="badge bg-warning">Suspended</span>
@endif
</td>
<td>
{{-- Edit User triggers modal --}}
<button class="btn btn-sm btn-warning editUserBtn" 
    data-id="{{ $user->id }}"
    data-full_name="{{ $user->full_name }}"
    data-email="{{ $user->email }}"
    data-mobile="{{ $user->mobile }}"
    data-role="{{ $user->role }}"
    data-access='@json($user->access_rights ? json_decode($user->access_rights, true) : [])'
    data-bs-toggle="modal" data-bs-target="#addUserModal">
    Edit
</button>

<button type="button" class="btn btn-sm btn-info"
data-bs-toggle="modal"
data-bs-target="#roleAccessModal"
onclick="setUserForPermissions({{ $user->id }}, '{{ $user->full_name }}', '{{ $user->role }}')">
Permissions
</button>

<form method="POST"
action="{{ route('operator.register.step6.user.delete', $user->id) }}"
style="display:inline-block;"
onsubmit="return confirm('Are you sure?');">
@csrf
<button class="btn btn-sm btn-danger">Delete</button>
</form>
</td>
</tr>
@empty
<tr><td colspan="6">No team members found.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>

</div>
</div>

{{-- ================= ADD / EDIT USER MODAL ================= --}}
<div class="modal fade" id="addUserModal" tabindex="-1">
<div class="modal-dialog">
<form id="userForm" method="POST" action="{{ url('operator/register/step6-users') }}">
@csrf
<input type="hidden" name="user_id" id="modal_user_id">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="modalTitle">Add New User</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">

<div class="form-group mb-2">
<label>Full Name *</label>
<input type="text" name="full_name" id="modal_full_name" class="form-control" required>
</div>

<div class="form-group mb-2">
<label>Email *</label>
<input type="email" name="email" id="modal_email" class="form-control" required>
</div>

<div class="form-group mb-2">
<label>Mobile Number *</label>
<input type="text" name="mobile" id="modal_mobile" class="form-control" required>
</div>

<div class="form-group mb-2">
<label>Password *</label>
<input type="password" name="password" id="modal_password" class="form-control">
</div>

<div class="form-group mb-2">
<label>Role *</label>
<select name="role" id="modal_role" class="form-control" required>
<option value="">-- Select a Role --</option>
@foreach(['Admin','Head of Department','Reservation Manager','Operational Manager','Finance Manager','Marketing Manager','Support Manager','Content Manager'] as $role)
<option value="{{ $role }}">{{ $role }}</option>
@endforeach
</select>
</div>

<div class="form-group mb-3">
<label>Access Rights</label><br>
@foreach(['Account Management','Profile Management','Compliance Management','Users Management','Reservation Management','Payments & Finance','Reporting & Analytics'] as $ar)
<div class="form-check form-check-inline">
<input type="checkbox" class="form-check-input" name="access_rights[]" value="{{ $ar }}">
{{ $ar }}
</div>
@endforeach
</div>

</div>
<div class="modal-footer">
<button type="submit" class="btn btn-success">Save</button>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
</div>
</div>
</form>
</div>
</div>

{{-- ================= PERMISSIONS MODAL ================= --}}
<div class="modal fade" id="roleAccessModal" tabindex="-1">
<div class="modal-dialog modal-xl">
<div class="modal-content">
<form method="POST" action="{{ url('operator/register/step6-role-access') }}">
@csrf
<div class="modal-header">
<h5 class="modal-title">ADVANCED SETTINGS - ROLE ACCESS MAPPING</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">

<input type="hidden" id="user_id" name="user_id">

<div class="mb-3">
<label>User</label>
<input type="text" id="userNameDisplay" class="form-control" readonly>
</div>

<div class="mb-3">
<label>Role</label>
<input type="text" id="roleDisplay" class="form-control" readonly>
<input type="hidden" id="role" name="role">
</div>

<div class="mb-3">
<label>Module *</label>
<select id="moduleSelect" name="module" class="form-control" required>
<option value="">-- Select Module --</option>
@foreach(['Account','Profile','Compliance','Users','Reservation','Accounting','Operations','Marketing','Content','Support','Feedback'] as $m)
<option value="{{ $m }}">{{ $m }}</option>
@endforeach
</select>
</div>

<input type="hidden" name="capacity_level" value="Section">

<div class="mb-3">
<label>Permissions</label>
@foreach(['Read','Create','Update','Approve','Publish'] as $perm)
<div class="form-check">
<input type="checkbox" class="form-check-input perm-checkbox"
id="perm{{ $perm }}" name="permissions[]" value="{{ $perm }}">
<label class="form-check-label">{{ $perm }}</label>
</div>
@endforeach
</div>

<div class="mb-3">
<label>Notes</label>
<textarea id="notesField" name="notes" class="form-control"></textarea>
</div>

</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-success">Save</button>
</div>
</form>
</div>
</div>
</div>
<script>
const roleAccessData = @json($roleAccessMappingsByUser ?? []);
let currentUserId = null;

function setUserForPermissions(userId, userName, userRole) {

    currentUserId = userId;

    document.getElementById('user_id').value = userId;
    document.getElementById('userNameDisplay').value = userName;
    document.getElementById('role').value = userRole;
    document.getElementById('roleDisplay').value = userRole;

    // Reset all fields
    document.getElementById('moduleSelect').value = '';
    resetPermissions();

    // Bind onchange for module select
    document.getElementById('moduleSelect').onchange = function () {
        loadModulePermissions(this.value);
    };

    // Optional: load first module if data exists
    if (!roleAccessData[userId] || roleAccessData[userId].length === 0) return;

    const firstModuleData = roleAccessData[userId][0];
    document.getElementById('moduleSelect').value = firstModuleData.module;
    loadModulePermissions(firstModuleData.module);
}

function resetPermissions() {
    document.getElementById('notesField').value = '';
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
}

function loadModulePermissions(moduleName) {
    resetPermissions();

    if (!moduleName || !roleAccessData[currentUserId]) return;

    const record = roleAccessData[currentUserId].find(item => item.module === moduleName);
    if (!record) return;

    document.getElementById('notesField').value = record.notes ?? '';
    document.getElementById('permRead').checked     = record.can_read == 1;
    document.getElementById('permCreate').checked  = record.can_create == 1;
    document.getElementById('permUpdate').checked  = record.can_update == 1;
    document.getElementById('permApprove').checked = record.can_approve == 1;
    document.getElementById('permPublish').checked = record.can_publish == 1;
}

// Existing Edit/Add user modal scripts stay unchanged
document.querySelectorAll('.editUserBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('modalTitle').textContent = 'Edit User';
        document.getElementById('modal_user_id').value = this.dataset.id;
        document.getElementById('modal_full_name').value = this.dataset.full_name;
        document.getElementById('modal_email').value = this.dataset.email;
        document.getElementById('modal_mobile').value = this.dataset.mobile;
        document.getElementById('modal_role').value = this.dataset.role;

        const access = JSON.parse(this.dataset.access);
        document.querySelectorAll('input[name="access_rights[]"]').forEach(cb => {
            cb.checked = access.includes(cb.value);
        });
    });
});

document.querySelector('[data-bs-target="#addUserModal"]').addEventListener('click', function() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('userForm').reset();
    document.getElementById('modal_user_id').value = '';
    document.querySelectorAll('input[name="access_rights[]"]').forEach(cb => cb.checked = false);
});
</script>
