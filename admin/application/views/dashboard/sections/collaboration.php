<?php
$header_data = isset($header_data) ? $header_data : array();
$header_data['current_section'] = 'collaboration';
$this->load->view('dashboard/header', $header_data);
?>

<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div class="row mb-3">
                <div class="col-12">
                    <h4 class="mb-1">Collaboration Agreement</h4>
                    <p class="text-muted mb-0">Review and sign collaboration terms</p>
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
                            <form method="POST" enctype="multipart/form-data">
                                <h5 class="mb-3">Contact Information</h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_management_name" class="form-label">Management Contact Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="contact_management_name" name="contact_management_name" 
                                               value="<?php echo isset($agreement->contact_management_name) ? htmlspecialchars($agreement->contact_management_name) : ''; ?>" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="contact_management_email" class="form-label">Management Email</label>
                                        <input type="email" class="form-control" id="contact_management_email" name="contact_management_email" 
                                               value="<?php echo isset($agreement->contact_management_email) ? htmlspecialchars($agreement->contact_management_email) : ''; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_management_phone" class="form-label">Management Phone</label>
                                        <input type="text" class="form-control" id="contact_management_phone" name="contact_management_phone" 
                                               value="<?php echo isset($agreement->contact_management_phone) ? htmlspecialchars($agreement->contact_management_phone) : ''; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="contact_management_mobile" class="form-label">Management Mobile</label>
                                        <input type="text" class="form-control" id="contact_management_mobile" name="contact_management_mobile" 
                                               value="<?php echo isset($agreement->contact_management_mobile) ? htmlspecialchars($agreement->contact_management_mobile) : ''; ?>">
                                    </div>
                                </div>

                                <hr>
                                <h5 class="mb-3">Accounting Contact Information</h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_accounting_name" class="form-label">Accounting Contact Name</label>
                                        <input type="text" class="form-control" id="contact_accounting_name" name="contact_accounting_name" 
                                               value="<?php echo isset($agreement->contact_accounting_name) ? htmlspecialchars($agreement->contact_accounting_name) : ''; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="contact_accounting_email" class="form-label">Accounting Email</label>
                                        <input type="email" class="form-control" id="contact_accounting_email" name="contact_accounting_email" 
                                               value="<?php echo isset($agreement->contact_accounting_email) ? htmlspecialchars($agreement->contact_accounting_email) : ''; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_accounting_phone" class="form-label">Accounting Phone</label>
                                        <input type="text" class="form-control" id="contact_accounting_phone" name="contact_accounting_phone" 
                                               value="<?php echo isset($agreement->contact_accounting_phone) ? htmlspecialchars($agreement->contact_accounting_phone) : ''; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="contact_accounting_mobile" class="form-label">Accounting Mobile</label>
                                        <input type="text" class="form-control" id="contact_accounting_mobile" name="contact_accounting_mobile" 
                                               value="<?php echo isset($agreement->contact_accounting_mobile) ? htmlspecialchars($agreement->contact_accounting_mobile) : ''; ?>">
                                    </div>
                                </div>

                                <hr>
                                <h5 class="mb-3">Agreement Details</h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="agreement_type" class="form-label">Agreement Type <span class="text-danger">*</span></label>
                                        <select class="form-control" id="agreement_type" name="agreement_type" required>
                                            <option value="">Select Agreement Type</option>
                                            <option value="Listing Only" <?php echo (isset($agreement->agreement_type) && $agreement->agreement_type === 'Listing Only') ? 'selected' : ''; ?>>Listing Only</option>
                                            <option value="OTO" <?php echo (isset($agreement->agreement_type) && $agreement->agreement_type === 'OTO') ? 'selected' : ''; ?>>OTO</option>
                                            <option value="Widget Only" <?php echo (isset($agreement->agreement_type) && $agreement->agreement_type === 'Widget Only') ? 'selected' : ''; ?>>Widget Only</option>
                                            <option value="OTO + Widget" <?php echo (isset($agreement->agreement_type) && $agreement->agreement_type === 'OTO + Widget') ? 'selected' : ''; ?>>OTO + Widget</option>
                                            <option value="Full Service" <?php echo (isset($agreement->agreement_type) && $agreement->agreement_type === 'Full Service') ? 'selected' : ''; ?>>Full Service</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="commission_model" class="form-label">Commission Model <span class="text-danger">*</span></label>
                                        <select class="form-control" id="commission_model" name="commission_model" required>
                                            <option value="">Select Commission Model</option>
                                            <option value="Percentage" <?php echo (isset($agreement->commission_model) && $agreement->commission_model === 'Percentage') ? 'selected' : ''; ?>>Percentage</option>
                                            <option value="Fixed Fee" <?php echo (isset($agreement->commission_model) && $agreement->commission_model === 'Fixed Fee') ? 'selected' : ''; ?>>Fixed Fee</option>
                                            <option value="Hybrid" <?php echo (isset($agreement->commission_model) && $agreement->commission_model === 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="commission_value" class="form-label">Commission Value <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="commission_value" name="commission_value" 
                                               value="<?php echo isset($agreement->commission_value) ? $agreement->commission_value : ''; ?>" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="<?php echo isset($agreement->start_date) ? $agreement->start_date : ''; ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="agreement_file" class="form-label">Signed Agreement (PDF)</label>
                                        <input type="file" class="form-control" id="agreement_file" name="agreement_file" accept=".pdf">
                                        <?php if (isset($agreement->agreement_file)): ?>
                                            <small class="text-success d-block mt-2">Uploaded</small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-info">Save Agreement</button>
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
