@extends('layouts.app')

@section('progressbar')
@php
    $progress = \App\Models\OperatorRegistrationProgress::where(
        'operator_id',
        auth()->user()->operator_id ?? null
    )->first();

    $completionPercent = isset($progress)
        ? round((
            ($progress->step2_profile ?? 0) +
            ($progress->step3_legal ?? 0) +
            ($progress->step4_system_process ?? 0) +
            ($progress->step5_collaboration ?? 0) +
            ($progress->step6_users ?? 0) +
            ($progress->step7_accounting ?? 0) +
            ($progress->step8_operations ?? 0) +
            ($progress->step9_review ?? 0)
        ) / 8 * 100)
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

<div class="mb-3">
    <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#addUserForm">
        Add New User
    </button>
</div>

<div id="addUserForm" class="collapse {{ isset($user) ? 'show' : '' }}">
<form method="POST" action="{{ isset($user)
    ? route('operator.register.step6.user.update', $user->id)
    : url('operator/register/step6-users') }}">
@csrf

<div class="form-group mb-2">
<label>Full Name *</label>
<input type="text" name="full_name" class="form-control"
value="{{ old('full_name', $user->full_name ?? '') }}" required>
</div>

<div class="form-group mb-2">
<label>Email *</label>
<input type="email" name="email" class="form-control"
value="{{ old('email', $user->email ?? '') }}" required>
</div>

<div class="form-group mb-2">
<label>Mobile Number *</label>
<input type="text" name="mobile" class="form-control"
value="{{ old('mobile', $user->mobile ?? '') }}" required>
</div>

<div class="form-group mb-2">
<label>Password {{ isset($user) ? '(leave blank to keep current)' : '*' }}</label>
<input type="password" name="password" class="form-control"
{{ isset($user) ? '' : 'required minlength=8' }}>
</div>

<div class="form-group mb-2">
<label>Role *</label>
<select name="role" class="form-control" required>
<option value="">-- Select a Role --</option>
@foreach(['Admin','Head of Department','Reservation Manager','Operational Manager','Finance Manager','Marketing Manager','Support Manager','Content Manager'] as $role)
<option value="{{ $role }}"
{{ old('role', $user->role ?? '') == $role ? 'selected' : '' }}>
{{ $role }}
</option>
@endforeach
</select>
</div>

<div class="form-group mb-3">
<label>Access Rights</label><br>
@php
$access = old('access_rights', isset($user) && $user->access_rights
    ? json_decode($user->access_rights, true) : []);
@endphp
@foreach(['Account Management','Profile Management','Compliance Management','Users Management','Reservation Management','Payments & Finance','Reporting & Analytics'] as $ar)
<div class="form-check form-check-inline">
<input type="checkbox" class="form-check-input" name="access_rights[]"
value="{{ $ar }}" {{ in_array($ar, $access) ? 'checked' : '' }}>
{{ $ar }}
</div>
@endforeach
</div>

<button type="submit" class="btn btn-success">
{{ isset($user) ? 'Update User' : 'Add User' }}
</button>
<a href="{{ route('operator.register.step6') }}" class="btn btn-secondary">Cancel</a>
</form>
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
<a href="{{ route('operator.register.step6.user.edit', $user->id) }}"
class="btn btn-sm btn-warning">Edit</a>

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

<a href="{{ url('operator/dashboard') }}" class="btn btn-secondary mt-3">
Back to Dashboard
</a>

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
@foreach(['Account','Profile - Compliance','Users','Reservation','Accounting','Operations','Marketing','Content','Support','Feedback'] as $m)
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

    function setUserForPermissions(userId, userName, userRole) {

        document.getElementById('user_id').value = userId;
        document.getElementById('userNameDisplay').value = userName;
        document.getElementById('role').value = userRole;
        document.getElementById('roleDisplay').value = userRole;

        // Reset
        document.getElementById('moduleSelect').value = '';
        document.getElementById('notesField').value = '';
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);

        if (!roleAccessData[userId] || roleAccessData[userId].length === 0) {
            return;
        }

        const data = roleAccessData[userId][0];

        document.getElementById('moduleSelect').value = data.module;
        document.getElementById('notesField').value = data.notes ?? '';

        if (data.can_read == 1) document.getElementById('permRead').checked = true;
        if (data.can_create == 1) document.getElementById('permCreate').checked = true;
        if (data.can_update == 1) document.getElementById('permUpdate').checked = true;
        if (data.can_approve == 1) document.getElementById('permApprove').checked = true;
        if (data.can_publish == 1) document.getElementById('permPublish').checked = true;
    }
</script>
@endsection
