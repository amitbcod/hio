<?php
$header_data = isset($header_data) ? $header_data : array();
$header_data['current_section'] = 'collaboration';
$this->load->view('dashboard/header', $header_data);
?>

<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div style="margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                <h3 style="text-transform: uppercase; color: #555; font-weight: 600; margin-bottom: 5px; font-size: 1.1rem; letter-spacing: 1px;">COLLABORATION AGREEMENT</h3>
                <p style="color: #999; margin-bottom: 0; font-size: 13px;">Review and sign collaboration terms</p>
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
                        <form method="POST" enctype="multipart/form-data">
                            <h5 style="color: #333; font-weight: 600; margin-bottom: 25px; font-size: 16px;">Contact Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contact_management_name" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Management Contact Name <span style="color: #dc3545;">*</span></label>
                                    <input type="text" class="form-control" id="contact_management_name" name="contact_management_name" 
                                           value="<?php echo isset($agreement->contact_management_name) ? htmlspecialchars($agreement->contact_management_name) : ''; ?>" required 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="contact_management_email" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Management Email</label>
                                    <input type="email" class="form-control" id="contact_management_email" name="contact_management_email" 
                                           value="<?php echo isset($agreement->contact_management_email) ? htmlspecialchars($agreement->contact_management_email) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contact_management_phone" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Management Phone</label>
                                    <input type="text" class="form-control" id="contact_management_phone" name="contact_management_phone" 
                                           value="<?php echo isset($agreement->contact_management_phone) ? htmlspecialchars($agreement->contact_management_phone) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="contact_management_mobile" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Management Mobile</label>
                                    <input type="text" class="form-control" id="contact_management_mobile" name="contact_management_mobile" 
                                           value="<?php echo isset($agreement->contact_management_mobile) ? htmlspecialchars($agreement->contact_management_mobile) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 30px 0;">
                            <h5 style="color: #333; font-weight: 600; margin-bottom: 25px; font-size: 16px;">Accounting Contact Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contact_accounting_name" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Accounting Contact Name</label>
                                    <input type="text" class="form-control" id="contact_accounting_name" name="contact_accounting_name" 
                                           value="<?php echo isset($agreement->contact_accounting_name) ? htmlspecialchars($agreement->contact_accounting_name) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="contact_accounting_email" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Accounting Email</label>
                                    <input type="email" class="form-control" id="contact_accounting_email" name="contact_accounting_email" 
                                           value="<?php echo isset($agreement->contact_accounting_email) ? htmlspecialchars($agreement->contact_accounting_email) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contact_accounting_phone" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Accounting Phone</label>
                                    <input type="text" class="form-control" id="contact_accounting_phone" name="contact_accounting_phone" 
                                           value="<?php echo isset($agreement->contact_accounting_phone) ? htmlspecialchars($agreement->contact_accounting_phone) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="contact_accounting_mobile" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Accounting Mobile</label>
                                    <input type="text" class="form-control" id="contact_accounting_mobile" name="contact_accounting_mobile" 
                                           value="<?php echo isset($agreement->contact_accounting_mobile) ? htmlspecialchars($agreement->contact_accounting_mobile) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 30px 0;">
                            <h5 style="color: #333; font-weight: 600; margin-bottom: 25px; font-size: 16px;">Agreement Details</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="agreement_type" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Agreement Type <span style="color: #dc3545;">*</span></label>
                                    <select class="form-control" id="agreement_type" name="agreement_type" required 
                                            style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                            <option value="">Select Agreement Type</option>
                                            <option value="Listing Only" <?php echo (isset($agreement->agreement_type) && $agreement->agreement_type === 'Listing Only') ? 'selected' : ''; ?>>Listing Only</option>
                                            <option value="OTO" <?php echo (isset($agreement->agreement_type) && $agreement->agreement_type === 'OTO') ? 'selected' : ''; ?>>OTO</option>
                                            <option value="Widget Only" <?php echo (isset($agreement->agreement_type) && $agreement->agreement_type === 'Widget Only') ? 'selected' : ''; ?>>Widget Only</option>
                                            <option value="OTO + Widget" <?php echo (isset($agreement->agreement_type) && $agreement->agreement_type === 'OTO + Widget') ? 'selected' : ''; ?>>OTO + Widget</option>
                                            <option value="Full Service" <?php echo (isset($agreement->agreement_type) && $agreement->agreement_type === 'Full Service') ? 'selected' : ''; ?>>Full Service</option>
                                        </select>
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="commission_model" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Commission Model <span style="color: #dc3545;">*</span></label>
                                    <select class="form-control" id="commission_model" name="commission_model" required 
                                            style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                            <option value="">Select Commission Model</option>
                                            <option value="Percentage" <?php echo (isset($agreement->commission_model) && $agreement->commission_model === 'Percentage') ? 'selected' : ''; ?>>Percentage</option>
                                            <option value="Fixed Fee" <?php echo (isset($agreement->commission_model) && $agreement->commission_model === 'Fixed Fee') ? 'selected' : ''; ?>>Fixed Fee</option>
                                            <option value="Hybrid" <?php echo (isset($agreement->commission_model) && $agreement->commission_model === 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                                        </select>
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="commission_value" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Commission Value <span style="color: #dc3545;">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="commission_value" name="commission_value" 
                                           value="<?php echo isset($agreement->commission_value) ? $agreement->commission_value : ''; ?>" required 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="start_date" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Start Date <span style="color: #dc3545;">*</span></label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="<?php echo isset($agreement->start_date) ? $agreement->start_date : ''; ?>" required 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="agreement_file" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Signed Agreement (PDF)</label>
                                    <input type="file" class="form-control" id="agreement_file" name="agreement_file" accept=".pdf" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    <?php if (isset($agreement->agreement_file)): ?>
                                        <small style="color: #28a745; display: block; margin-top: 8px;">Uploaded</small>
                                    <?php endif; ?>
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn" 
                                            style="background-color: #5cb9b4; color: white; padding: 12px 35px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; margin-right: 10px; transition: background-color 0.3s;"
                                            onmouseover="this.style.backgroundColor='#4a9d99'" 
                                            onmouseout="this.style.backgroundColor='#5cb9b4'">Save Agreement</button>
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
