<?php
$header_data = isset($header_data) ? $header_data : array();
$header_data['current_section'] = 'users';
$this->load->view('dashboard/header', $header_data);
?>

<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e0e0e0;">
                <h4 style="margin: 0; text-transform: uppercase; font-size: 16px; font-weight: 600; color: #333;">ADD NEW USER</h4>
            </div>

            <?php if (isset($error) && !empty($error)): ?>
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <strong style="color: #856404;">Error!</strong>
                    <span style="color: #856404;"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div style="background: white; border-radius: 6px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">
                            <form method="post" action="<?php echo site_url('dashboard/users/add'); ?>">
                                <div style="margin-bottom: 20px;">
                                    <label for="full_name" style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">Full Name <span style="color: #dc3545;">*</span></label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo set_value('full_name'); ?>" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#5cb9b4'" onmouseout="this.style.borderColor='#ddd'" onfocus="this.style.borderColor='#5cb9b4'; this.style.outline='none';" onblur="this.style.borderColor='#ddd';">
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label for="email" style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">Email <span style="color: #dc3545;">*</span></label>
                                    <input type="email" id="email" name="email" value="<?php echo set_value('email'); ?>" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#5cb9b4'" onmouseout="this.style.borderColor='#ddd'" onfocus="this.style.borderColor='#5cb9b4'; this.style.outline='none';" onblur="this.style.borderColor='#ddd';">
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label for="mobile" style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">Mobile Number</label>
                                    <input type="tel" id="mobile" name="mobile" value="<?php echo set_value('mobile'); ?>" style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#5cb9b4'" onmouseout="this.style.borderColor='#ddd'" onfocus="this.style.borderColor='#5cb9b4'; this.style.outline='none';" onblur="this.style.borderColor='#ddd';">
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label for="password" style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">Password <span style="color: #dc3545;">*</span></label>
                                    <input type="password" id="password" name="password" required minlength="8" style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#5cb9b4'" onmouseout="this.style.borderColor='#ddd'" onfocus="this.style.borderColor='#5cb9b4'; this.style.outline='none';" onblur="this.style.borderColor='#ddd';">
                                    <small style="display: block; margin-top: 5px; font-size: 12px; color: #6c757d;">Minimum 8 characters</small>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label for="role" style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">Role <span style="color: #dc3545;">*</span></label>
                                    <select id="role" name="role" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#5cb9b4'" onmouseout="this.style.borderColor='#ddd'" onfocus="this.style.borderColor='#5cb9b4'; this.style.outline='none';" onblur="this.style.borderColor='#ddd';">
                                        <option value="">-- Select a Role --</option>
                                        <?php foreach ($roles as $role_key => $role_label): ?>
                                            <option value="<?php echo htmlspecialchars($role_key); ?>" 
                                                    <?php echo set_select('role', $role_key); ?>>
                                                <?php echo htmlspecialchars($role_label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">Access Rights</label>
                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px; padding: 20px; background: #f8f9fa; border-radius: 4px;">
                                        <div style="display: flex; align-items: center;">
                                            <input type="checkbox" id="acc_account" name="access_rights[]" value="Account" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                                            <label for="acc_account" style="margin: 0; cursor: pointer; font-size: 14px; color: #333;">
                                                Account Management
                                            </label>
                                        </div>
                                        <div style="display: flex; align-items: center;">
                                            <input type="checkbox" id="acc_profile" name="access_rights[]" value="Profile" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                                            <label for="acc_profile" style="margin: 0; cursor: pointer; font-size: 14px; color: #333;">
                                                Profile Management
                                            </label>
                                        </div>
                                        <div style="display: flex; align-items: center;">
                                            <input type="checkbox" id="acc_compliance" name="access_rights[]" value="Compliance" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                                            <label for="acc_compliance" style="margin: 0; cursor: pointer; font-size: 14px; color: #333;">
                                                Compliance Management
                                            </label>
                                        </div>
                                        <div style="display: flex; align-items: center;">
                                            <input type="checkbox" id="acc_users" name="access_rights[]" value="Users" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                                            <label for="acc_users" style="margin: 0; cursor: pointer; font-size: 14px; color: #333;">
                                                Users Management
                                            </label>
                                        </div>
                                        <div style="display: flex; align-items: center;">
                                            <input type="checkbox" id="acc_reservation" name="access_rights[]" value="Reservation" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                                            <label for="acc_reservation" style="margin: 0; cursor: pointer; font-size: 14px; color: #333;">
                                                Reservation Management
                                            </label>
                                        </div>
                                        <div style="display: flex; align-items: center;">
                                            <input type="checkbox" id="acc_payments" name="access_rights[]" value="Payments" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                                            <label for="acc_payments" style="margin: 0; cursor: pointer; font-size: 14px; color: #333;">
                                                Payments & Finance
                                            </label>
                                        </div>
                                        <div style="display: flex; align-items: center;">
                                            <input type="checkbox" id="acc_reporting" name="access_rights[]" value="Reporting" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                                            <label for="acc_reporting" style="margin: 0; cursor: pointer; font-size: 14px; color: #333;">
                                                Reporting & Analytics
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                                    <button type="submit" style="background: #28a745; color: white; padding: 10px 24px; border: none; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s; margin-right: 10px;" onmouseover="this.style.background='#218838'" onmouseout="this.style.background='#28a745'">
                                        <i class="fas fa-plus"></i> Add User
                                    </button>
                                    <a href="<?php echo site_url('dashboard/users'); ?>" style="display: inline-block; background: #6c757d; color: white; padding: 10px 24px; border-radius: 4px; font-size: 14px; font-weight: 500; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">
                                        Cancel
                                    </a>
                                </div>
                            </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div style="background: white; border-radius: 6px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h6 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #333;">Password Requirements:</h6>
                        <ul style="list-style: none; padding: 0; margin: 0 0 20px 0; font-size: 13px; color: #6c757d;">
                            <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #28a745; margin-right: 8px;"></i> At least 8 characters</li>
                            <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #28a745; margin-right: 8px;"></i> Include uppercase letters</li>
                            <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #28a745; margin-right: 8px;"></i> Include lowercase letters</li>
                            <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #28a745; margin-right: 8px;"></i> Include numbers</li>
                        </ul>

                        <hr style="margin: 20px 0; border: none; border-top: 1px solid #e0e0e0;">

                        <h6 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 600; color: #333;">User Roles:</h6>
                        <p style="margin: 0 0 20px 0; font-size: 13px; color: #6c757d; line-height: 1.6;">
                            Each role has predefined permissions. You can customize access rights for specific modules after user creation.
                        </p>

                        <hr style="margin: 20px 0; border: none; border-top: 1px solid #e0e0e0;">

                        <h6 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 600; color: #333;">First Login:</h6>
                        <p style="margin: 0; font-size: 13px; color: #6c757d; line-height: 1.6;">
                            The new user will be required to set a new password on their first login for security purposes.
                        </p>
                    </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <a href="<?php echo site_url('dashboard/users'); ?>" style="display: inline-block; background: #6c757d; color: white; padding: 10px 20px; border-radius: 4px; font-size: 14px; font-weight: 500; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">
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
