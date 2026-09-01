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

<div class="row">
    {{-- Sidebar --}}
    <div id="sidebar" class="col-md-3 mb-3 mb-md-0 net-section">
        @include('operator.registration._sidebar', ['currentStep' => $currentStep, 'progress' => $progress ?? null])
    </div>

    {{-- Main Content --}}
    <div class="col-md-6 d-flex align-items-start justify-content-center" style="">
        <div class="container-middle team-member-new">

            <h2 class="mb-4 fw-bold">USERS & STAFF MANAGEMENT</h2>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            {{-- Buttons --}}
            <div class="back-section mb-3 d-flex justify-content-between">
                <div class="add-section">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        Add New User
                    </button>
                    @if(!empty(auth()->user()->business_id))
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
                                    {{-- Edit User --}}
                                    <button class="btn btn-sm btn-warning editUserBtn" 
                                        data-id="{{ $user->id }}"
                                        data-full_name="{{ $user->full_name }}"
                                        data-email="{{ $user->email }}"
                                        data-mobile="{{ $user->mobile }}"
                                        data-role="{{ $user->role }}"
                                        data-bs-toggle="modal" data-bs-target="#addUserModal">
                                        Edit
                                    </button>

                                    {{-- Delete --}}
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
@foreach($roles ?? collect() as $r)
<option value="{{ $r->name }}">{{ $r->name }}{{ $r->business_id ? ' (Business)' : '' }}</option>
@endforeach
</select>
</div>

<div class="alert alert-info">
Permissions are assigned at the <strong>Role level</strong>. To set permissions for a role, go to <a class="link-orange" href="{{ route('operator.roles.index') }}">Manage Roles</a> and click "Manage Permissions" for the desired role.
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
// Base form action URLs
const addAction = '{{ url('operator/register/step6-users') }}';
const editActionBase = '{{ url('operator/register/step6-users') }}'; // we'll append /{id}/edit when editing

// Handle "Add New User" button click to reset form
document.querySelectorAll('[data-bs-target="#addUserModal"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
        // Only reset if it's the "Add New User" button, not an edit button
        if (!this.classList.contains('editUserBtn')) {
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('userForm').reset();
            document.getElementById('modal_user_id').value = '';
            document.getElementById('userForm').action = addAction;
        }
    });
});

// Edit User modal scripts
document.querySelectorAll('.editUserBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        document.getElementById('modalTitle').textContent = 'Edit User';
        document.getElementById('modal_user_id').value = id;
        document.getElementById('modal_full_name').value = this.dataset.full_name;
        document.getElementById('modal_email').value = this.dataset.email;
        document.getElementById('modal_mobile').value = this.dataset.mobile;
        document.getElementById('modal_role').value = this.dataset.role;
        document.getElementById('modal_password').placeholder = 'Leave blank to keep current password';
        document.getElementById('modal_password').value = '';
        // Update form action to point to the update route for this user
        document.getElementById('userForm').action = editActionBase + '/' + id + '/edit';
    });
});

// Ensure modal reset when closed so stale action doesn't persist
const addUserModal = document.getElementById('addUserModal');
if (addUserModal) {
    addUserModal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('userForm').action = addAction;
        document.getElementById('userForm').reset();
    });
}
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
