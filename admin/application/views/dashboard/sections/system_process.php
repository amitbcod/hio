<?php
$header_data = isset($header_data) ? $header_data : array();
$header_data['current_section'] = 'system_process';
$this->load->view('dashboard/header', $header_data);
?>

<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div class="row mb-3">
                <div class="col-12">
                    <h4 class="mb-1">System Processes</h4>
                    <p class="text-muted mb-0">Payment gateway and system configuration</p>
                </div>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> <?php echo $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <h5 class="mb-3">System Configuration</h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="service_category" class="form-label">Service Category <span class="text-danger">*</span></label>
                                        <select class="form-control" id="service_category" name="service_category" required>
                                            <option value="">Select Service Category</option>
                                            <option value="Accommodation" <?php echo (isset($system->service_category) && $system->service_category === 'Accommodation') ? 'selected' : ''; ?>>Accommodation</option>
                                            <option value="Activities" <?php echo (isset($system->service_category) && $system->service_category === 'Activities') ? 'selected' : ''; ?>>Activities</option>
                                            <option value="Transport" <?php echo (isset($system->service_category) && $system->service_category === 'Transport') ? 'selected' : ''; ?>>Transport</option>
                                            <option value="Services" <?php echo (isset($system->service_category) && $system->service_category === 'Services') ? 'selected' : ''; ?>>Services</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="communication_preference" class="form-label">Communication Preference</label>
                                        <select class="form-control" id="communication_preference" name="communication_preference">
                                            <option value="Email" <?php echo (isset($system->communication_preference) && $system->communication_preference === 'Email') ? 'selected' : ''; ?>>Email</option>
                                            <option value="Messaging System" <?php echo (isset($system->communication_preference) && $system->communication_preference === 'Messaging System') ? 'selected' : ''; ?>>Messaging System</option>
                                            <option value="WhatsApp" <?php echo (isset($system->communication_preference) && $system->communication_preference === 'WhatsApp') ? 'selected' : ''; ?>>WhatsApp</option>
                                            <option value="Phone" <?php echo (isset($system->communication_preference) && $system->communication_preference === 'Phone') ? 'selected' : ''; ?>>Phone</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="assigned_operator_name" class="form-label">Assigned Operator Name</label>
                                        <input type="text" class="form-control" id="assigned_operator_name" name="assigned_operator_name" 
                                               value="<?php echo isset($system->assigned_operator_name) ? htmlspecialchars($system->assigned_operator_name) : ''; ?>" readonly>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="assigned_operator_role" class="form-label">Assigned Operator Role</label>
                                        <input type="text" class="form-control" id="assigned_operator_role" name="assigned_operator_role" 
                                               value="<?php echo isset($system->assigned_operator_role) ? htmlspecialchars($system->assigned_operator_role) : ''; ?>" readonly>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="draft" <?php echo (isset($system->status) && $system->status === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            <option value="active" <?php echo (isset($system->status) && $system->status === 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo (isset($system->status) && $system->status === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <h6>Payment Gateway Configuration</h6>
                                    <p class="mb-0">Your payment gateway will be configured by our technical team. We will contact you with the API credentials and setup instructions.</p>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-info">Save System Settings</button>
                                        <a href="<?php echo site_url('dashboard'); ?>" class="btn btn-secondary">Back</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->load->view('dashboard/footer');
?>
