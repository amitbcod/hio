<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div style="margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                <h3 style="text-transform: uppercase; color: #555; font-weight: 600; margin-bottom: 5px; font-size: 1.1rem; letter-spacing: 1px;">EDIT USER</h3>
                <p style="color: #999; margin-bottom: 0; font-size: 13px;">Update team member information</p>
            </div>

            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div style="background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
                        <h5 style="color: #333; font-weight: 600; margin-bottom: 25px; font-size: 16px; border-bottom: 1px solid #e0e0e0; padding-bottom: 12px;">User Information</h5>
                        <form method="post" action="<?php echo site_url('dashboard/users/edit/' . $user->id); ?>">
                            <div style="margin-bottom: 20px;">
                                <label for="full_name" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Full Name <span style="color: #dc3545;">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user->full_name); ?>" required 
                                       style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label for="email" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Email <span style="color: #dc3545;">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user->email); ?>" required 
                                       style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label for="mobile" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Mobile Number</label>
                                <input type="tel" class="form-control" id="mobile" name="mobile" 
                                       value="<?php echo htmlspecialchars($user->mobile ?? ''); ?>" 
                                       style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label for="password" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Password (Leave blank to keep current)</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       minlength="8" 
                                       style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                <small style="color: #999; font-size: 12px; display: block; margin-top: 6px;">Minimum 8 characters</small>
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label for="role" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Role <span style="color: #dc3545;">*</span></label>
                                <select class="form-control" id="role" name="role" required 
                                        style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                    <?php foreach ($roles as $role_key => $role_label): ?>
                                        <option value="<?php echo htmlspecialchars($role_key); ?>" 
                                                <?php echo ($user->role === $role_key) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($role_label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label for="status" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Account Status <span style="color: #dc3545;">*</span></label>
                                <select class="form-control" id="status" name="status" required 
                                        style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                    <option value="Active" <?php echo ($user->status === 'Active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo ($user->status === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="Suspended" <?php echo ($user->status === 'Suspended') ? 'selected' : ''; ?>>Suspended</option>
                                </select>
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 12px;">Access Rights</label>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; padding: 20px; background: #f8f9fa; border-radius: 6px; border: 1px solid #e0e0e0;">
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
                                        <div style="margin-bottom: 0;">
                                            <label style="display: flex; align-items: center; cursor: pointer;">
                                                <input type="checkbox" 
                                                       id="acc_<?php echo strtolower($module_key); ?>" 
                                                       name="access_rights[]" value="<?php echo htmlspecialchars($module_key); ?>"
                                                       <?php echo (in_array($module_key, $current_rights)) ? 'checked' : ''; ?> 
                                                       style="width: 18px; height: 18px; cursor: pointer; margin-right: 8px;">
                                                <span style="color: #555; font-size: 14px;"><?php echo htmlspecialchars($module_label); ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div style="margin-top: 25px;">
                                <div class="col-12" style="padding: 0;">
                                    <small style="color: #999; font-size: 12px; display: block; margin-bottom: 20px;">
                                        <i class="fas fa-info-circle"></i>
                                        User ID: <?php echo htmlspecialchars($user->user_id); ?> | 
                                        Created: <?php echo date('M d, Y H:i', strtotime($user->created_at)); ?>
                                    </small>
                                </div>
                            </div>

                            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                                <button type="submit" class="btn" 
                                        style="background-color: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; margin-right: 10px; transition: background-color 0.3s;"
                                        onmouseover="this.style.backgroundColor='#218838'" 
                                        onmouseout="this.style.backgroundColor='#28a745'">
                                    <i class="fas fa-save"></i> Update User
                                </button>
                                <a href="<?php echo site_url('dashboard/users'); ?>" class="btn" 
                                   style="background-color: #6c757d; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block; transition: background-color 0.3s;"
                                   onmouseover="this.style.backgroundColor='#5a6268'" 
                                   onmouseout="this.style.backgroundColor='#6c757d'">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div style="background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
                        <h5 style="color: #333; font-weight: 600; margin-bottom: 25px; font-size: 16px; border-bottom: 1px solid #e0e0e0; padding-bottom: 12px;">User Details</h5>
                        <p style="font-size: 13px; margin-bottom: 15px;">
                            <strong style="color: #555;">Status:</strong><br>
                            <span style="background-color: <?php echo ($user->status === 'Active') ? '#28a745' : ($user->status === 'Inactive' ? '#ffc107' : '#dc3545'); ?>; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; display: inline-block; margin-top: 6px;">
                                <?php echo htmlspecialchars($user->status); ?>
                            </span>
                        </p>

                        <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 20px 0;">

                        <p style="font-size: 13px; margin-bottom: 15px;">
                            <strong style="color: #555;">Last Login:</strong><br>
                            <span style="color: #666; margin-top: 6px; display: block;">
                            <?php echo isset($user->last_login) && !empty($user->last_login) 
                                ? date('M d, Y H:i', strtotime($user->last_login)) 
                                : 'Never'; ?>
                            </span>
                        </p>

                        <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 20px 0;">

                        <p style="font-size: 13px; margin-bottom: 15px;">
                            <strong style="color: #555;">Password Reset Required:</strong><br>
                            <span style="color: #666; margin-top: 6px; display: block;">
                            <?php echo $user->account_reset_required ? 'Yes' : 'No'; ?>
                            </span>
                        </p>

                        <?php if ($user->account_reset_required): ?>
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 15px; border-radius: 4px; margin-bottom: 20px;">
                                <small style="color: #856404; font-size: 12px;">This user must change their password on next login.</small>
                            </div>
                        <?php endif; ?>

                        <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 20px 0;">

                        <h6 style="color: #dc3545; font-weight: 600; margin-bottom: 15px; font-size: 14px;">Danger Zone</h6>
                        <a href="<?php echo site_url('dashboard/users/delete/' . $user->id); ?>" 
                           style="background-color: #dc3545; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; text-decoration: none; display: block; text-align: center; transition: background-color 0.3s;"
                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');" 
                           onmouseover="this.style.backgroundColor='#c82333'" 
                           onmouseout="this.style.backgroundColor='#dc3545'">
                            <i class="fas fa-trash"></i> Delete User
                        </a>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <a href="<?php echo site_url('dashboard/users'); ?>" class="btn" 
                       style="background-color: #6c757d; color: white; padding: 12px 35px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block; transition: background-color 0.3s;"
                       onmouseover="this.style.backgroundColor='#5a6268'" 
                       onmouseout="this.style.backgroundColor='#6c757d'">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>



<?php
$this->load->view('dashboard/footer');
?>
