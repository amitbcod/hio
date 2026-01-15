<?php
$header_data = isset($header_data) ? $header_data : array();
$header_data['current_section'] = 'system_process';
$this->load->view('dashboard/header', $header_data);
?>

<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div style="margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                <h3 style="text-transform: uppercase; color: #555; font-weight: 600; margin-bottom: 5px; font-size: 1.1rem; letter-spacing: 1px;">SYSTEM PROCESSES</h3>
                <p style="color: #999; margin-bottom: 0; font-size: 13px;">Payment gateway and system configuration</p>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> <?php echo $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-10">
                    <div style="background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <form method="POST">
                            <h5 style="color: #333; font-weight: 600; margin-bottom: 25px; font-size: 16px;">System Configuration</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="service_category" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Service Category <span style="color: #dc3545;">*</span></label>
                                    <select class="form-control" id="service_category" name="service_category" required 
                                            style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                            <option value="">Select Service Category</option>
                                            <option value="Accommodation" <?php echo (isset($system->service_category) && $system->service_category === 'Accommodation') ? 'selected' : ''; ?>>Accommodation</option>
                                            <option value="Activities" <?php echo (isset($system->service_category) && $system->service_category === 'Activities') ? 'selected' : ''; ?>>Activities</option>
                                            <option value="Transport" <?php echo (isset($system->service_category) && $system->service_category === 'Transport') ? 'selected' : ''; ?>>Transport</option>
                                            <option value="Services" <?php echo (isset($system->service_category) && $system->service_category === 'Services') ? 'selected' : ''; ?>>Services</option>
                                        </select>
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="communication_preference" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Communication Preference</label>
                                    <select class="form-control" id="communication_preference" name="communication_preference" 
                                            style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                            <option value="Email" <?php echo (isset($system->communication_preference) && $system->communication_preference === 'Email') ? 'selected' : ''; ?>>Email</option>
                                            <option value="Messaging System" <?php echo (isset($system->communication_preference) && $system->communication_preference === 'Messaging System') ? 'selected' : ''; ?>>Messaging System</option>
                                            <option value="WhatsApp" <?php echo (isset($system->communication_preference) && $system->communication_preference === 'WhatsApp') ? 'selected' : ''; ?>>WhatsApp</option>
                                            <option value="Phone" <?php echo (isset($system->communication_preference) && $system->communication_preference === 'Phone') ? 'selected' : ''; ?>>Phone</option>
                                        </select>
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="assigned_operator_name" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Assigned Operator Name</label>
                                    <input type="text" class="form-control" id="assigned_operator_name" name="assigned_operator_name" 
                                           value="<?php echo isset($system->assigned_operator_name) ? htmlspecialchars($system->assigned_operator_name) : ''; ?>" readonly 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #f5f5f5;">
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="assigned_operator_role" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Assigned Operator Role</label>
                                    <input type="text" class="form-control" id="assigned_operator_role" name="assigned_operator_role" 
                                           value="<?php echo isset($system->assigned_operator_role) ? htmlspecialchars($system->assigned_operator_role) : ''; ?>" readonly 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #f5f5f5;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Status</label>
                                    <select class="form-control" id="status" name="status" 
                                            style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                            <option value="draft" <?php echo (isset($system->status) && $system->status === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            <option value="active" <?php echo (isset($system->status) && $system->status === 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo (isset($system->status) && $system->status === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                            <div style="background-color: #e7f3ff; border-left: 4px solid #4169E1; padding: 15px 20px; border-radius: 4px; margin-top: 20px; margin-bottom: 25px;">
                                <h6 style="color: #333; font-weight: 600; margin-bottom: 8px; font-size: 14px;">Payment Gateway Configuration</h6>
                                <p style="margin-bottom: 0; color: #555; font-size: 13px; line-height: 1.5;">Your payment gateway will be configured by our technical team. We will contact you with the API credentials and setup instructions.</p>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn" 
                                            style="background-color: #5cb9b4; color: white; padding: 12px 35px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; margin-right: 10px; transition: background-color 0.3s;"
                                            onmouseover="this.style.backgroundColor='#4a9d99'" 
                                            onmouseout="this.style.backgroundColor='#5cb9b4'">Save System Settings</button>
                                    <a href="<?php echo site_url('dashboard'); ?>" class="btn" 
                                       style="background-color: #6c757d; color: white; padding: 12px 35px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block; transition: background-color 0.3s;"
                                       onmouseover="this.style.backgroundColor='#5a6268'" 
                                       onmouseout="this.style.backgroundColor='#6c757d'">Back</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->load->view('dashboard/footer');
?>
