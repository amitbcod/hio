<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div style="margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                <h3 style="text-transform: uppercase; color: #555; font-weight: 600; margin-bottom: 5px; font-size: 1.1rem; letter-spacing: 1px;">USERS & STAFF MANAGEMENT</h3>
                <p style="color: #999; margin-bottom: 0; font-size: 13px;">Manage team members and permissions</p>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> <?php echo $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-12">
                    <a href="<?php echo site_url('dashboard/users/add'); ?>" class="btn" 
                       style="background-color: #5cb9b4; color: white; padding: 10px 25px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block; transition: background-color 0.3s;"
                       onmouseover="this.style.backgroundColor='#4a9d99'" 
                       onmouseout="this.style.backgroundColor='#5cb9b4'">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div style="background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <h5 style="color: #333; font-weight: 600; margin-bottom: 25px; font-size: 16px;">Team Members</h5>
                        <?php if (!empty($users)): ?>
                            <div class="table-responsive">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #e0e0e0;">
                                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #555; font-size: 14px;">Name</th>
                                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #555; font-size: 14px;">Email</th>
                                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #555; font-size: 14px;">Mobile</th>
                                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #555; font-size: 14px;">Role</th>
                                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #555; font-size: 14px;">Status</th>
                                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #555; font-size: 14px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                                <td style="padding: 15px 12px; color: #333; font-size: 14px;"><?php echo htmlspecialchars($user->full_name); ?></td>
                                                <td style="padding: 15px 12px; color: #333; font-size: 14px;"><?php echo htmlspecialchars($user->email); ?></td>
                                                <td style="padding: 15px 12px; color: #333; font-size: 14px;"><?php echo htmlspecialchars($user->mobile); ?></td>
                                                <td style="padding: 15px 12px;">
                                                    <span style="background-color: #5cb9b4; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500;"><?php echo htmlspecialchars($user->role); ?></span>
                                                </td>
                                                <td style="padding: 15px 12px;">
                                                    <span style="background-color: <?php echo ($user->status === 'Active') ? '#28a745' : '#ffc107'; ?>; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                                        <?php echo $user->status; ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 15px 12px;">
                                                    <a href="<?php echo site_url('dashboard/users/edit/' . $user->id); ?>" 
                                                       style="background-color: #ffc107; color: #333; padding: 6px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; text-decoration: none; display: inline-block; margin-right: 5px; transition: background-color 0.3s;"
                                                       onmouseover="this.style.backgroundColor='#e0a800'" 
                                                       onmouseout="this.style.backgroundColor='#ffc107'">Edit</a>
                                                    <a href="<?php echo site_url('dashboard/users/delete/' . $user->id); ?>" 
                                                       onclick="return confirm('Are you sure?')" 
                                                       style="background-color: #dc3545; color: white; padding: 6px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; text-decoration: none; display: inline-block; transition: background-color 0.3s;"
                                                       onmouseover="this.style.backgroundColor='#c82333'" 
                                                       onmouseout="this.style.backgroundColor='#dc3545'">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="color: #999; font-size: 14px;">No team members added yet. <a href="<?php echo site_url('dashboard/users/add'); ?>" style="color: #5cb9b4; text-decoration: none; font-weight: 500;">Add one now</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <a href="<?php echo site_url('dashboard'); ?>" class="btn" 
                       style="background-color: #6c757d; color: white; padding: 12px 35px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block; transition: background-color 0.3s;"
                       onmouseover="this.style.backgroundColor='#5a6268'" 
                       onmouseout="this.style.backgroundColor='#6c757d'">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->load->view('dashboard/footer');
?>
