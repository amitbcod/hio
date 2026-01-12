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
                    <h4 class="mb-1">Add New User</h4>
                    <p class="text-muted mb-0">Create a new team member account</p>
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
                            <form method="post" action="<?php echo site_url('dashboard/users/add'); ?>">
                                <div class="form-group">
                                    <label for="full_name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo set_value('full_name'); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo set_value('email'); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="mobile">Mobile Number</label>
                                    <input type="tel" class="form-control" id="mobile" name="mobile" 
                                           value="<?php echo set_value('mobile'); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="password">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           required minlength="8">
                                    <small class="form-text text-muted">Minimum 8 characters</small>
                                </div>

                                <div class="form-group">
                                    <label for="role">Role <span class="text-danger">*</span></label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="">-- Select a Role --</option>
                                        <?php foreach ($roles as $role_key => $role_label): ?>
                                            <option value="<?php echo htmlspecialchars($role_key); ?>" 
                                                    <?php echo set_select('role', $role_key); ?>>
                                                <?php echo htmlspecialchars($role_label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Access Rights</label>
                                    <div class="access-rights-container">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="acc_account" 
                                                   name="access_rights[]" value="Account">
                                            <label class="form-check-label" for="acc_account">
                                                Account Management
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="acc_profile" 
                                                   name="access_rights[]" value="Profile">
                                            <label class="form-check-label" for="acc_profile">
                                                Profile Management
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="acc_compliance" 
                                                   name="access_rights[]" value="Compliance">
                                            <label class="form-check-label" for="acc_compliance">
                                                Compliance Management
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="acc_users" 
                                                   name="access_rights[]" value="Users">
                                            <label class="form-check-label" for="acc_users">
                                                Users Management
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="acc_reservation" 
                                                   name="access_rights[]" value="Reservation">
                                            <label class="form-check-label" for="acc_reservation">
                                                Reservation Management
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="acc_payments" 
                                                   name="access_rights[]" value="Payments">
                                            <label class="form-check-label" for="acc_payments">
                                                Payments & Finance
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="acc_reporting" 
                                                   name="access_rights[]" value="Reporting">
                                            <label class="form-check-label" for="acc_reporting">
                                                Reporting & Analytics
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Add User
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
                            <h5 class="mb-0">Tips</h5>
                        </div>
                        <div class="card-body">
                            <h6>Password Requirements:</h6>
                            <ul class="list-unstyled small text-muted">
                                <li><i class="fas fa-check"></i> At least 8 characters</li>
                                <li><i class="fas fa-check"></i> Include uppercase letters</li>
                                <li><i class="fas fa-check"></i> Include lowercase letters</li>
                                <li><i class="fas fa-check"></i> Include numbers</li>
                            </ul>

                            <hr>

                            <h6>User Roles:</h6>
                            <p class="small text-muted">
                                Each role has predefined permissions. You can customize access rights for specific modules after user creation.
                            </p>

                            <hr>

                            <h6>First Login:</h6>
                            <p class="small text-muted">
                                The new user will be required to set a new password on their first login for security purposes.
                            </p>
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
