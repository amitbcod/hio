@extends('layouts.app')

@section('progressbar')
    @php
        $progress = \App\Models\OperatorRegistrationProgress::where('operator_id', auth()->user()->operator_id ?? null)->first();
        $completionPercent = isset($progress) ? round((($progress->step2_profile ?? 0)
            + ($progress->step3_legal ?? 0)
            + ($progress->step4_system_process ?? 0)
            + ($progress->step5_collaboration ?? 0)
            + ($progress->step6_users ?? 0)
            + ($progress->step7_accounting ?? 0)
            + ($progress->step8_operations ?? 0)
            + ($progress->step9_review ?? 0)) / 8 * 100) : 0;
    @endphp
    @include('operator.registration._progress', ['completionPercent' => $completionPercent])
@endsection

@section('content')
    @php $currentStep = 6; @endphp
    <div class="col-md-3">
        @include('operator.registration._sidebar', ['currentStep' => $currentStep, 'progress' => $progress ?? null])
    </div>
    <div class="col-md-9 d-flex align-items-center justify-content-center" style="min-height: 90vh;">
        <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 32px 32px 24px 32px; width: 100%; max-width: 900px;">
            <h2 style="font-weight: bold; margin-bottom: 24px;">USERS & STAFF MANAGEMENT</h2>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }} <button type="button" class="close" data-dismiss="alert">&times;</button></div>
            @endif
            <div class="mb-3">
                <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#addUserForm">Add New User</button>
            </div>
            <div id="addUserForm" class="collapse {{ isset($user) ? 'show' : '' }}">
                <form method="POST" action="{{ isset($user) ? route('operator.register.step6.user.update', $user->id) : url('operator/register/step6-users') }}">
                    @csrf
                    <div class="form-group mb-2">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $user->full_name ?? '') }}" required>
                    </div>
                    <div class="form-group mb-2">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
                    </div>
                    <div class="form-group mb-2">
                        <label>Mobile Number *</label>
                        <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $user->mobile ?? '') }}" required>
                    </div>
                    <div class="form-group mb-2">
                        <label>Password {{ isset($user) ? '(leave blank to keep current)' : '*' }}</label>
                        <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required minlength=8' }}>
                        <small>Minimum 8 characters</small>
                    </div>
                    <div class="form-group mb-2">
                        <label>Role *</label>
                        <select name="role" class="form-control" required>
                            <option value="">-- Select a Role --</option>
                            @foreach(['Admin','Head of Department','Reservation Manager','Operational Manager','Finance Manager','Marketing Manager','Support Manager','Content Manager'] as $role)
                                <option value="{{ $role }}" {{ old('role', $user->role ?? '') == $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label>Access Rights</label><br>
                        @php
                            $access = old('access_rights', isset($user) && $user->access_rights ? json_decode($user->access_rights, true) : []);
                        @endphp
                        @foreach(['Account Management','Profile Management','Compliance Management','Users Management','Reservation Management','Payments & Finance','Reporting & Analytics'] as $ar)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="access_rights[]" value="{{ $ar }}" {{ in_array($ar, $access) ? 'checked' : '' }}> {{ $ar }}
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-success">{{ isset($user) ? 'Update User' : 'Add User' }}</button>
                    <a href="{{ route('operator.register.step6') }}" class="btn btn-secondary">Cancel</a>
                </form>
                <div class="mt-3">
                    <b>Password Requirements:</b>
                    <ul>
                        <li>At least 8 characters</li>
                        <li>Include uppercase letters</li>
                        <li>Include lowercase letters</li>
                        <li>Include numbers</li>
                    </ul>
                    <!-- <b>User Roles:</b> Each role has predefined permissions. You can customize access rights for specific modules after user creation.<br>
                    <b>First Login:</b> The new user will be required to set a new password on their first login for security purposes. -->
                </div>
            </div>
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
                                    @if($user->status == 'Active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($user->status == 'Inactive')
                                        <span class="badge bg-secondary">Inactive</span>
                                    @else
                                        <span class="badge bg-warning">Suspended</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('operator.register.step6.user.edit', $user->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#roleAccessModal" onclick="setUserForPermissions({{ $user->id }}, '{{ $user->full_name }}', '{{ $user->role }}')">Permissions</button>
                                    <form method="POST" action="{{ route('operator.register.step6.user.delete', $user->id) }}" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
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
            <a href="{{ url('operator/dashboard') }}" class="btn btn-secondary mt-3">Back to Dashboard</a>
        </div>
    </div>

    <!-- Role Access Mapping Modal (Advanced Settings) -->
    <div class="modal fade" id="roleAccessModal" tabindex="-1" aria-labelledby="roleAccessModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="roleAccessModalLabel">ADVANCED SETTINGS - ROLE ACCESS MAPPING</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="{{ url('operator/register/step6-role-access') }}">
            @csrf
            <div class="modal-body">
              <div class="alert alert-info">
                <strong>Role Access Mapping:</strong> Configure user roles, modules, permissions, and capacity levels for your team members.
              </div>

              <!-- Existing Role Access Mappings Table -->
              @if(isset($roleAccessMappings) && $roleAccessMappings && count($roleAccessMappings) > 0)
                <h6 class="mb-3">Current Role Access Mappings</h6>
                <div class="table-responsive mb-4">
                  <table class="table table-bordered table-sm">
                    <thead class="table-light">
                      <tr>
                        <th>User ID</th>
                        <th>Role</th>
                        <th>Module</th>
                        <th>Capacity Level</th>
                        <th>Permissions</th>
                        <th>Notes</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($roleAccessMappings as $mapping)
                        <tr>
                          <td>{{ $mapping->user_id }}</td>
                          <td><span class="badge bg-primary">{{ $mapping->role }}</span></td>
                          <td>{{ $mapping->module }}</td>
                          <td>{{ $mapping->capacity_level }}</td>
                          <td>
                            @php
                              $perms = [];
                              if ($mapping->can_read) $perms[] = 'Read';
                              if ($mapping->can_create) $perms[] = 'Create';
                              if ($mapping->can_update) $perms[] = 'Update';
                              if ($mapping->can_approve) $perms[] = 'Approve';
                              if ($mapping->can_publish) $perms[] = 'Publish';
                            @endphp
                            @foreach($perms as $perm)
                              <span class="badge bg-success">{{ $perm }}</span>
                            @endforeach
                          </td>
                          <td>{{ $mapping->notes ?? '-' }}</td>
                          <td>
                            <button type="button" class="btn btn-sm btn-warning" onclick="editRoleAccess({{ $mapping->id }})">Edit</button>
                            <form method="POST" action="{{ url('operator/register/step6-role-access/' . $mapping->id) }}" style="display:inline-block;" onsubmit="return confirm('Are you sure?');">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif

              <h6 class="mb-3">Add New Role Access Mapping</h6>
              <div class="form-group mb-3">
                <label>User *</label>
                <input type="text" id="userNameDisplay" class="form-control" readonly>
                <input type="hidden" id="user_id" name="user_id" required>
              </div>

              <div class="form-group mb-3">
                <label>Role *</label>
                <input type="text" id="roleDisplay" class="form-control" readonly>
                <input type="hidden" id="role" name="role" required>
              </div>

              <div class="form-group mb-3">
                <label>Module *</label>
                <select name="module" class="form-control" required>
                  <option value="">-- Select Module --</option>
                  <option value="Account">Account</option>
                  <option value="Profile - Compliance">Profile - Compliance</option>
                  <option value="Users">Users</option>
                  <option value="Reservation">Reservation</option>
                  <option value="Accounting">Accounting</option>
                  <option value="Operations">Operations</option>
                  <option value="Marketing">Marketing</option>
                  <option value="Content">Content</option>
                  <option value="Support">Support</option>
                  <option value="Feedback">Feedback</option>
                </select>
              </div>

              {{-- Capacity Level field hidden for now
              <div class="form-group mb-3">
                <label>Capacity Level *</label>
                <div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="capacity_level" id="capacitySection" value="Section" required>
                    <label class="form-check-label" for="capacitySection">Section Tab</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="capacity_level" id="capacityTick" value="Tick Function" required>
                    <label class="form-check-label" for="capacityTick">Tick Function from List</label>
                  </div>
                </div>
              </div>
              --}}
              <input type="hidden" name="capacity_level" value="Section">

              <div class="form-group mb-3">
                <label>Permissions * (Multi-select)</label>
                <div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="permissions[]" value="Read" id="permRead">
                    <label class="form-check-label" for="permRead">Read</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="permissions[]" value="Create" id="permCreate">
                    <label class="form-check-label" for="permCreate">Create</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="permissions[]" value="Update" id="permUpdate">
                    <label class="form-check-label" for="permUpdate">Update</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="permissions[]" value="Approve" id="permApprove">
                    <label class="form-check-label" for="permApprove">Approve</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="permissions[]" value="Publish" id="permPublish">
                    <label class="form-check-label" for="permPublish">Publish</label>
                  </div>
                </div>
              </div>

              <div class="form-group mb-3">
                <label>Notes (Optional)</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Additional constraints or exceptions..."></textarea>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success">Save Role Access Mapping</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
    function setUserForPermissions(userId, userName, userRole) {
      document.getElementById('user_id').value = userId;
      document.getElementById('userNameDisplay').value = userName;
      document.getElementById('role').value = userRole;
      document.getElementById('roleDisplay').value = userRole;
      // Clear previous selections
      document.querySelectorAll('input[name="permissions[]"]').forEach(el => el.checked = false);
      document.getElementById('permRead').checked = false;
    }
    </script>
@endsection
