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
                    <h4 class="mb-1">Users & Staff Management</h4>
                    <p class="text-muted mb-0">Manage team members and permissions</p>
                </div>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> <?php echo $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-12">
                    <a href="<?php echo site_url('dashboard/users/add'); ?>" class="btn btn-info">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Team Members</h5>
                            <?php if (!empty($users)): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
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
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($user->full_name); ?></td>
                                                    <td><?php echo htmlspecialchars($user->email); ?></td>
                                                    <td><?php echo htmlspecialchars($user->mobile); ?></td>
                                                    <td>
                                                        <span class="badge badge-info"><?php echo htmlspecialchars($user->role); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?php echo ($user->status === 'Active') ? 'success' : 'warning'; ?>">
                                                            <?php echo $user->status; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo site_url('dashboard/users/edit/' . $user->id); ?>" class="btn btn-sm btn-warning">Edit</a>
                                                        <a href="<?php echo site_url('dashboard/users/delete/' . $user->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No team members added yet. <a href="<?php echo site_url('dashboard/users/add'); ?>">Add one now</a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <a href="<?php echo site_url('dashboard'); ?>" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->load->view('dashboard/footer');
?>
