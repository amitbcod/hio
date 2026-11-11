@extends('layouts.app')

@section('progressbar')
@php
    $completionPercent = isset($progress)
        ? round((($progress->step2_profile ?? 0)+($progress->step3_legal ?? 0)+($progress->step4_system_process ?? 0)+($progress->step5_collaboration ?? 0)+($progress->step6_users ?? 0)+($progress->step7_accounting ?? 0)+($progress->step8_operations ?? 0)+($progress->step9_review ?? 0))/8*100)
        : 0;
@endphp

@include('operator.registration._progress', ['completionPercent' => $completionPercent])
@endsection

@section('content')
@php $currentStep = 6; @endphp

<div class="row">
    {{-- Sidebar --}}
    <div id="sidebar" class="col-md-3 mb-3 mb-md-0 net-section">
        @include('operator.registration._sidebar', ['currentStep' => $currentStep, 'progress' => $progress ?? null])
    </div>

    {{-- Main Content --}}
    <div class="col-md-6 d-flex align-items-start justify-content-center" style="">
        <div class="container-middle team-member-new">

            <h2 class="mb-4 fw-bold">Users & Staff Management</h2>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif

            {{-- Buttons --}}
            <div class="back-section mb-3 d-flex justify-content-between">
                <div class="add-section">
                    @if($permissions->canManageUsers($actor, 'create'))
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Add New User</button>
                    @endif
                    @if($actor instanceof \App\Models\Operator)
                        <a href="{{ route('operator.roles.index') }}" class="btn btn-secondary ms-2">Manage Roles</a>
                    @endif
                </div>
                @if(!auth('operator_staff')->check())
                <!-- <a href="{{ url('operator/dashboard') }}" class="btn btn-secondary">Back to Dashboard</a> -->
                @endif
            </div>

            {{-- TEAM MEMBERS --}}
            <div class="card mt-4">
                <div class="card-header">Team Members</div>
                <div class="card-body p-0" style="overflow-x: scroll">
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
                                <td style="display:flex">
                                    @if($permissions->canManageUsers($actor, 'update'))
                                        <button class="btn btn-sm btn-warning editUserBtn"
                                            data-id="{{ $user->id }}"
                                            data-full_name="{{ $user->full_name }}"
                                            data-email="{{ $user->email }}"
                                            data-mobile="{{ $user->mobile }}"
                                            data-role="{{ $user->role }}"
                                            data-grants='@json($permissionGrantsByUser[$user->id] ?? [])'
                                            data-bs-toggle="modal" data-bs-target="#addUserModal">Edit</button>
                                    @endif
                                    @if($actor instanceof \App\Models\Operator)
                                        <form method="POST" action="{{ route('operator.register.step6.user.delete', $user->id) }}"
                                            class="d-inline-block" onsubmit="return confirm('Delete this staff account?');">
                                            @csrf
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @endif
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
<label>Mobile Number</label>
<input type="text" name="mobile" id="modal_mobile" class="form-control">
</div>

<div class="form-group mb-2">
<label>Password <span id="password-required-label">*</span></label>
<input type="password" name="password" id="modal_password" class="form-control">
</div>

@if($actor instanceof \App\Models\Operator)
<div class="form-group mb-2">
<label>Role *</label>
<select name="role" id="modal_role" class="form-control" required>
<option value="">-- Select a Role --</option>
@foreach($roles ?? collect() as $r)
<option value="{{ $r->name }}">{{ $r->name }}{{ $r->scope === 'global' ? ' (Global)' : ' (Country)' }}</option>
@endforeach
</select>
</div>
@else
<div class="form-group mb-2">
    <label>Role</label>
    <div class="form-control-plaintext">{{ $actor->role }}</div>
</div>
@endif

<div class="alert alert-info mb-2">
    Select only the permissions this staff member needs. They can delegate only actions allowed by their role and their own current permissions.
</div>
<div id="staff-permissions" class="border rounded p-2" data-role-options='@json($rolePermissionOptions)'>
    @foreach($modules as $module)
            @php
                $initialRole = $actor instanceof \App\Models\Operator ? ($roles->first()->name ?? '') : $actor->role;
                $initialModuleOptions = $rolePermissionOptions[$initialRole][$module->slug] ?? [];
            @endphp
            <div class="staff-permission-module mb-3" data-module="{{ $module->slug }}" @if(empty($initialModuleOptions)) hidden @endif>
                <strong>{{ $module->name }}</strong>
                <div class="row g-2 mt-1">
                    @foreach(config('operator_staff.actions') as $action => $label)
                        @php
                            $option = $initialModuleOptions[$action] ?? null;
                        @endphp
                        <div class="col-6 col-md-4 staff-permission-action" data-action="{{ $action }}" @if(! $option) hidden @endif>
                            <label class="form-check">
                                <input class="form-check-input staff-grant" type="checkbox"
                                    name="permissions[{{ $module->slug }}][{{ $action }}][allowed]" value="1"
                                data-module="{{ $module->slug }}" data-action="{{ $action }}" @disabled(! $option)>
                            <span class="form-check-label">{{ $label }}</span>
                        </label>
                        <label class="form-check ms-3 small" @if(! ($option['can_delegate'] ?? false)) hidden @endif>
                            <input class="form-check-input staff-delegate" type="checkbox"
                                name="permissions[{{ $module->slug }}][{{ $action }}][can_delegate]" value="1"
                                data-module="{{ $module->slug }}" data-action="{{ $action }}"
                                @disabled(! ($option['can_delegate'] ?? false))>
                            <span class="form-check-label">Allow delegation</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

</div>
<div class="modal-footer">
<button type="submit" class="btn btn-primary">Save</button>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
</div>
</div>
</form>
</div>
</div>

<script>
const addAction = '{{ url('operator/register/step6-users') }}';
const editActionBase = '{{ url('operator/register/step6-users') }}';
const userForm = document.getElementById('userForm');
const roleSelect = document.getElementById('modal_role');
const permissionBox = document.getElementById('staff-permissions');
const roleOptions = JSON.parse(permissionBox.dataset.roleOptions || '{}');
const fixedRole = @json($actor instanceof \App\Models\OperatorUser ? $actor->role : null);
const permissionFields = Array.from(permissionBox.querySelectorAll('.staff-grant, .staff-delegate'));

function applyRoleOptions(roleName) {
    const options = roleOptions[roleName] || {};
    permissionBox.querySelectorAll('.staff-permission-module').forEach(function (moduleElement) {
        const moduleOptions = options[moduleElement.dataset.module] || {};
        let moduleHasOptions = false;
        moduleElement.querySelectorAll('.staff-permission-action').forEach(function (actionElement) {
            const action = actionElement.dataset.action;
            const option = moduleOptions[action];
            const grantInput = actionElement.querySelector('.staff-grant');
            const delegateInput = actionElement.querySelector('.staff-delegate');
            actionElement.hidden = !option;
            grantInput.disabled = !option;
            if (!option) grantInput.checked = false;

            const canDelegate = Boolean(option?.can_delegate);
            delegateInput.closest('label').hidden = !canDelegate;
            delegateInput.disabled = !canDelegate || !grantInput.checked;
            if (delegateInput.disabled) delegateInput.checked = false;
            moduleHasOptions = moduleHasOptions || Boolean(option);
        });
        moduleElement.hidden = !moduleHasOptions;
    });
}

function loadGrants(grants) {
    permissionFields.forEach(function (input) {
        const value = grants[input.dataset.module]?.[input.dataset.action];
        input.checked = input.classList.contains('staff-grant')
            ? Boolean(value?.allowed)
            : Boolean(value?.can_delegate);
    });
    applyRoleOptions(roleSelect?.value || fixedRole);
}

document.querySelectorAll('[data-bs-target="#addUserModal"]:not(.editUserBtn)').forEach(function (button) {
    button.addEventListener('click', function () {
        userForm.reset();
        userForm.action = addAction;
        document.getElementById('modalTitle').textContent = 'Add New User';
        document.getElementById('modal_user_id').value = '';
        document.getElementById('modal_password').required = true;
        document.getElementById('modal_password').placeholder = '';
        if (roleSelect) roleSelect.selectedIndex = roleSelect.options.length > 1 ? 1 : 0;
        applyRoleOptions(roleSelect?.value || fixedRole);
    });
});

document.querySelectorAll('.editUserBtn').forEach(function (button) {
    button.addEventListener('click', function () {
        userForm.reset();
        const id = this.dataset.id;
        document.getElementById('modalTitle').textContent = 'Edit User';
        document.getElementById('modal_user_id').value = id;
        document.getElementById('modal_full_name').value = this.dataset.full_name;
        document.getElementById('modal_email').value = this.dataset.email;
        document.getElementById('modal_mobile').value = this.dataset.mobile;
        document.getElementById('modal_password').required = false;
        document.getElementById('modal_password').placeholder = 'Leave blank to keep current password';
        if (roleSelect) roleSelect.value = this.dataset.role;
        userForm.action = editActionBase + '/' + id + '/edit';
        applyRoleOptions(roleSelect?.value || fixedRole);
        loadGrants(JSON.parse(this.dataset.grants || '{}'));
    });
});

if (roleSelect) {
    roleSelect.addEventListener('change', function () {
        applyRoleOptions(this.value);
        loadGrants({});
    });
}

permissionBox.addEventListener('change', function (event) {
    if (event.target.classList.contains('staff-grant')) {
        applyRoleOptions(roleSelect?.value || fixedRole);
    }
});

document.getElementById('addUserModal').addEventListener('hidden.bs.modal', function () {
    userForm.reset();
    userForm.action = addAction;
    document.getElementById('modal_password').required = false;
});
</script>
<script>
      function toggleMenu(element) {
         let submenu = element.nextElementSibling;

         element.classList.toggle("active");
         submenu.classList.toggle("hidden");
      }
   </script>
   <script>
      function toggleSidebar() {
         document.getElementById("sidebar").classList.toggle("active");
      }
   </script>

   <script>
      function toggleSidebar() {
         document.getElementById("sidebar").classList.toggle("active");
      }

      document.addEventListener("click", function (e) {
         let sidebar = document.getElementById("sidebar");
         let hamburger = document.querySelector(".hamburger");

         if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
            sidebar.classList.remove("active");
         }
      });
   </script>
@endsection
