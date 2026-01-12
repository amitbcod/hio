<?php
$header_data = isset($header_data) ? $header_data : array();
$header_data['current_section'] = 'status_review';
$this->load->view('dashboard/header', $header_data);
$status_review = isset($status_review) ? $status_review : NULL;
?>

<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div class="row mb-3">
                <div class="col-12">
                    <h4 class="mb-1">STATUS REVIEW</h4>
                </div>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> <?php echo $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <!-- Account Status -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Account Status</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select class="form-control" disabled>
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
                                        <label class="form-label">Last Approval Date</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" disabled 
                                               value="<?php echo (isset($status_review->last_approval_date) && !empty($status_review->last_approval_date)) ? date('m / d / Y', strtotime($status_review->last_approval_date)) : ''; ?>">
                                    </div>
                                </div>

                                <!-- Profile Verified By -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Profile Verified By</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" disabled 
                                               value="<?php echo (isset($status_review->profile_verified_by) && !empty($status_review->profile_verified_by)) ? $status_review->profile_verified_by : ''; ?>">
                                    </div>
                                </div>

                                <!-- Operator Rating -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Operator Rating</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" disabled 
                                               value="<?php echo isset($status_review->operator_rating) ? number_format($status_review->operator_rating, 1) : ''; ?>">
                                    </div>
                                </div>

                                <!-- Testimonials -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Testimonials</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" disabled 
                                               value="<?php echo isset($status_review->testimonials_count) ? $status_review->testimonials_count : ''; ?>">
                                    </div>
                                </div>

                                <!-- Renewal Reminder -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Renewal Reminder</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" disabled 
                                               value="<?php echo (isset($status_review->renewal_reminder_date) && !empty($status_review->renewal_reminder_date)) ? date('m / d / Y', strtotime($status_review->renewal_reminder_date)) : ''; ?>">
                                    </div>
                                </div>

                                <!-- Duration of Agreement -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label">Duration of Agreement</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" disabled 
                                               value="<?php echo isset($status_review->agreement_duration_days) ? $status_review->agreement_duration_days . ' days' : 'Standard'; ?>">
                                        <small class="text-muted">Listing agreements are limited to one year and renewable. Other agreements valid until terminated or suspended.</small>
                                    </div>
                                </div>

                                <hr>

                                <!-- Save Button -->
                                <div class="row">
                                    <div class="col-12 text-right">
                                        <a href="<?php echo site_url('dashboard'); ?>" class="btn btn-secondary mr-2">Back</a>
                                        <button type="submit" class="btn btn-info">Save and Continue</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Section Status -->
            <div class="row">
                <div class="col-12">
                    <h5 class="mb-3">PROFILE CREATION STEPS</h5>
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
                        <div class="card <?php echo $is_complete ? 'border-success' : 'border-secondary'; ?>">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="<?php echo $is_complete ? 'text-success font-weight-bold' : 'text-secondary'; ?>">
                                        <?php echo $label; ?>
                                    </span>
                                    <i class="fas fa-<?php echo $is_complete ? 'check-circle text-success' : 'circle text-secondary'; ?> fa-lg"></i>
                                </div>
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
