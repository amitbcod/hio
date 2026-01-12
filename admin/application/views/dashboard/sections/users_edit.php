<?php
$header_data = isset($header_data) ? $header_data : array();
$header_data['current_section'] = 'users';
$this->load->view('dashboard/header', $header_data);
?>

<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div class="row mb-3">
                <div class="col-12">
                    <h4 class="mb-1">Edit User</h4>
                    <p class="text-muted mb-0">Update team member information</p>
                </div>
            </div>

            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">User Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?php echo site_url('dashboard/users/edit/' . $user->id); ?>">
                                <div class="form-group">
                                    <label for="full_name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user->full_name); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user->email); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="mobile">Mobile Number</label>
                                    <input type="tel" class="form-control" id="mobile" name="mobile" 
                                           value="<?php echo htmlspecialchars($user->mobile ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="password">Password (Leave blank to keep current)</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="8">
                                    <small class="form-text text-muted">Minimum 8 characters</small>
                                </div>

                                <div class="form-group">
                                    <label for="role">Role <span class="text-danger">*</span></label>
                                    <select class="form-control" id="role" name="role" required>
                                        <?php foreach ($roles as $role_key => $role_label): ?>
                                            <option value="<?php echo htmlspecialchars($role_key); ?>" 
                                                    <?php echo ($user->role === $role_key) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($role_label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="status">Account Status <span class="text-danger">*</span></label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="Active" <?php echo ($user->status === 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo ($user->status === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="Suspended" <?php echo ($user->status === 'Suspended') ? 'selected' : ''; ?>>Suspended</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Access Rights</label>
                                    <div class="access-rights-container">
                                        <?php
                                        $access_modules = array(
                                            'Account' => 'Account Management',
                                            'Profile' => 'Profile Management',
                                            'Compliance' => 'Compliance Management',
                                            'Users' => 'Users Management',
                                            'Reservation' => 'Reservation Management',
                                            'Payments' => 'Payments & Finance',
                                            'Reporting' => 'Reporting & Analytics'
                                        );

                                        $current_rights = isset($user->access_rights) && !empty($user->access_rights) 
                                            ? (is_string($user->access_rights) ? json_decode($user->access_rights, true) : $user->access_rights)
                                            : array();
                                        ?>
                                        <?php foreach ($access_modules as $module_key => $module_label): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="acc_<?php echo strtolower($module_key); ?>" 
                                                       name="access_rights[]" value="<?php echo htmlspecialchars($module_key); ?>"
                                                       <?php echo (in_array($module_key, $current_rights)) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="acc_<?php echo strtolower($module_key); ?>">
                                                    <?php echo htmlspecialchars($module_label); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <small class="text-muted d-block mb-3">
                                            <i class="fas fa-info-circle"></i>
                                            User ID: <?php echo htmlspecialchars($user->user_id); ?> | 
                                            Created: <?php echo date('M d, Y H:i', strtotime($user->created_at)); ?>
                                        </small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Update User
                                    </button>
                                    <a href="<?php echo site_url('dashboard/users'); ?>" class="btn btn-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">User Details</h5>
                        </div>
                        <div class="card-body">
                            <p class="small">
                                <strong>Status:</strong><br>
                                <span class="badge badge-<?php echo ($user->status === 'Active') ? 'success' : ($user->status === 'Inactive' ? 'warning' : 'danger'); ?>">
                                    <?php echo htmlspecialchars($user->status); ?>
                                </span>
                            </p>

                            <hr>

                            <p class="small">
                                <strong>Last Login:</strong><br>
                                <?php echo isset($user->last_login) && !empty($user->last_login) 
                                    ? date('M d, Y H:i', strtotime($user->last_login)) 
                                    : 'Never'; ?>
                            </p>

                            <hr>

                            <p class="small">
                                <strong>Password Reset Required:</strong><br>
                                <?php echo $user->account_reset_required ? 'Yes' : 'No'; ?>
                            </p>

                            <?php if ($user->account_reset_required): ?>
                                <div class="alert alert-warning" role="alert">
                                    <small>This user must change their password on next login.</small>
                                </div>
                            <?php endif; ?>

                            <hr>

                            <h6>Danger Zone</h6>
                            <a href="<?php echo site_url('dashboard/users/delete/' . $user->id); ?>" 
                               class="btn btn-sm btn-danger btn-block" 
                               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                <i class="fas fa-trash"></i> Delete User
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <a href="<?php echo site_url('dashboard/users'); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.access-rights-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 4px;
}

.form-check {
    margin-bottom: 0;
}

.form-check-input:checked + .form-check-label {
    color: #007bff;
    font-weight: 500;
}
</style>

<?php
$this->load->view('dashboard/footer');
?>
