<?php
$header_data = isset($header_data) ? $header_data : array();
$header_data['current_section'] = 'status_review';
$this->load->view('dashboard/header', $header_data);
$status_review = isset($status_review) ? $status_review : NULL;
?>

<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div style="margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                <h3 style="text-transform: uppercase; color: #555; font-weight: 600; margin-bottom: 0; font-size: 1.1rem; letter-spacing: 1px;">STATUS REVIEW</h3>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> <?php echo $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div style="background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <form method="POST">
                            <!-- Account Status -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label style="font-weight: 400; color: #555; font-size: 14px; padding-top: 10px;">Account Status</label>
                                </div>
                                <div class="col-md-8">
                                    <select class="form-control" disabled 
                                            style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #f5f5f5; color: #666;">
                                            <option>-- select --</option>
                                            <option <?php echo (isset($status_review->account_status) && $status_review->account_status === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option <?php echo (isset($status_review->account_status) && $status_review->account_status === 'Active') ? 'selected' : ''; ?>>Active</option>
                                            <option <?php echo (isset($status_review->account_status) && $status_review->account_status === 'Suspended') ? 'selected' : ''; ?>>Suspended</option>
                                            <option <?php echo (isset($status_review->account_status) && $status_review->account_status === 'Archived') ? 'selected' : ''; ?>>Archived</option>
                                        </select>
                                    </div>
                                </div>

                            <!-- Last Approval Date -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label style="font-weight: 400; color: #555; font-size: 14px; padding-top: 10px;">Last Approval Date</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" disabled 
                                           value="<?php echo (isset($status_review->last_approval_date) && !empty($status_review->last_approval_date)) ? date('m / d / Y', strtotime($status_review->last_approval_date)) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #f5f5f5; color: #666;">
                                    </div>
                                </div>

                            <!-- Profile Verified By -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label style="font-weight: 400; color: #555; font-size: 14px; padding-top: 10px;">Profile Verified By</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" disabled 
                                           value="<?php echo (isset($status_review->profile_verified_by) && !empty($status_review->profile_verified_by)) ? $status_review->profile_verified_by : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #f5f5f5; color: #666;">
                                    </div>
                                </div>

                            <!-- Operator Rating -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label style="font-weight: 400; color: #555; font-size: 14px; padding-top: 10px;">Operator Rating</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" disabled 
                                           value="<?php echo isset($status_review->operator_rating) ? number_format($status_review->operator_rating, 1) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #f5f5f5; color: #666;">
                                    </div>
                                </div>

                            <!-- Testimonials -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label style="font-weight: 400; color: #555; font-size: 14px; padding-top: 10px;">Testimonials</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" disabled 
                                           value="<?php echo isset($status_review->testimonials_count) ? $status_review->testimonials_count : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #f5f5f5; color: #666;">
                                    </div>
                                </div>

                            <!-- Renewal Reminder -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label style="font-weight: 400; color: #555; font-size: 14px; padding-top: 10px;">Renewal Reminder</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" disabled 
                                           value="<?php echo (isset($status_review->renewal_reminder_date) && !empty($status_review->renewal_reminder_date)) ? date('m / d / Y', strtotime($status_review->renewal_reminder_date)) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #f5f5f5; color: #666;">
                                    </div>
                                </div>

                            <!-- Duration of Agreement -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label style="font-weight: 400; color: #555; font-size: 14px; padding-top: 10px;">Duration of Agreement</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" disabled 
                                           value="<?php echo isset($status_review->agreement_duration_days) ? $status_review->agreement_duration_days . ' days' : 'Standard'; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #f5f5f5; color: #666;">
                                    <small style="color: #999; font-size: 12px; display: block; margin-top: 6px;">Listing agreements are limited to one year and renewable. Other agreements valid until terminated or suspended.</small>
                                    </div>
                                </div>

                            <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 25px 0;">

                            <!-- Save Button -->
                            <div class="row">
                                <div class="col-12 text-right">
                                    <a href="<?php echo site_url('dashboard'); ?>" class="btn" 
                                       style="background-color: #6c757d; color: white; padding: 12px 35px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block; margin-right: 10px; transition: background-color 0.3s;"
                                       onmouseover="this.style.backgroundColor='#5a6268'" 
                                       onmouseout="this.style.backgroundColor='#6c757d'">Back</a>
                                    <button type="submit" class="btn" 
                                            style="background-color: #5cb9b4; color: white; padding: 12px 35px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; transition: background-color 0.3s;"
                                            onmouseover="this.style.backgroundColor='#4a9d99'" 
                                            onmouseout="this.style.backgroundColor='#5cb9b4'">Save and Continue</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 2px solid #f0f0f0; margin: 40px 0;">

            <!-- Section Status -->
            <div class="row">
                <div class="col-12">
                    <h5 style="text-transform: uppercase; color: #555; font-weight: 600; margin-bottom: 25px; font-size: 1rem; letter-spacing: 1px;">PROFILE CREATION STEPS</h5>
                </div>
            </div>

            <div class="row">
                <?php 
                $sections = array(
                    'profile' => 'Registration',
                    'legal' => 'Profile',
                    'system_process' => 'System Process',
                    'collaboration' => 'Collaboration Agreement',
                    'users' => 'Users',
                    'accounting' => 'Accounting Operation',
                    'operations' => 'Service Operation'
                );

                foreach ($sections as $key => $label):
                    $is_complete = isset($section_status[$key]) ? $section_status[$key] : false;
                ?>
                    <div class="col-md-4 mb-3">
                        <div style="background: #ffffff; padding: 18px 20px; border-radius: 6px; border: 2px solid <?php echo $is_complete ? '#28a745' : '#dee2e6'; ?>; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: <?php echo $is_complete ? '#28a745' : '#6c757d'; ?>; font-weight: <?php echo $is_complete ? '600' : '400'; ?>; font-size: 14px;">
                                    <?php echo $label; ?>
                                </span>
                                <i class="fas fa-<?php echo $is_complete ? 'check-circle' : 'circle'; ?>" style="color: <?php echo $is_complete ? '#28a745' : '#dee2e6'; ?>; font-size: 20px;"></i>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php
$this->load->view('dashboard/footer');
?>
