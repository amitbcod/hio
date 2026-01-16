<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div class="row mb-3">
                <div class="col-12">
                    <h4 class="mb-1">Legal & Compliance</h4>
                    <p class="text-muted mb-0">Licenses, certifications, and legal documents</p>
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
                                <h5 class="mb-3">License & Certification Details</h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="business_license_number" class="form-label">Business License Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="business_license_number" name="business_license_number" 
                                               value="<?php echo isset($legal->business_license_number) ? htmlspecialchars($legal->business_license_number) : ''; ?>" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="license_type" class="form-label">License Type <span class="text-danger">*</span></label>
                                        <select class="form-control" id="license_type" name="license_type" required>
                                            <option value="">Select License Type</option>
                                            <option value="Accommodation" <?php echo (isset($legal->license_type) && $legal->license_type === 'Accommodation') ? 'selected' : ''; ?>>Accommodation</option>
                                            <option value="Tour Operator" <?php echo (isset($legal->license_type) && $legal->license_type === 'Tour Operator') ? 'selected' : ''; ?>>Tour Operator</option>
                                            <option value="Car Rental" <?php echo (isset($legal->license_type) && $legal->license_type === 'Car Rental') ? 'selected' : ''; ?>>Car Rental</option>
                                            <option value="Guide" <?php echo (isset($legal->license_type) && $legal->license_type === 'Guide') ? 'selected' : ''; ?>>Guide</option>
                                            <option value="Other" <?php echo (isset($legal->license_type) && $legal->license_type === 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="license_expiry_date" class="form-label">License Expiry Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="license_expiry_date" name="license_expiry_date" 
                                               value="<?php echo isset($legal->license_expiry_date) ? $legal->license_expiry_date : ''; ?>" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="service_package" class="form-label">Service Package</label>
                                        <select class="form-control" id="service_package" name="service_package">
                                            <option value="HIO Listing Only" <?php echo (isset($legal->service_package) && $legal->service_package === 'HIO Listing Only') ? 'selected' : ''; ?>>HIO Listing Only</option>
                                            <option value="HIO Partner Standard" <?php echo (isset($legal->service_package) && $legal->service_package === 'HIO Partner Standard') ? 'selected' : ''; ?>>HIO Partner Standard</option>
                                            <option value="HIO Partner Pro" <?php echo (isset($legal->service_package) && $legal->service_package === 'HIO Partner Pro') ? 'selected' : ''; ?>>HIO Partner Pro</option>
                                            <option value="HIO Partner Elite" <?php echo (isset($legal->service_package) && $legal->service_package === 'HIO Partner Elite') ? 'selected' : ''; ?>>HIO Partner Elite</option>
                                            <option value="HIO Full Service" <?php echo (isset($legal->service_package) && $legal->service_package === 'HIO Full Service') ? 'selected' : ''; ?>>HIO Full Service</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="proof_of_license" class="form-label">Proof of License (PDF/JPEG)</label>
                                        <input type="file" class="form-control" id="proof_of_license" name="proof_of_license" accept=".pdf,.jpg,.jpeg">
                                        <?php if (isset($legal->proof_of_license)): ?>
                                            <small class="text-success d-block mt-2">Uploaded</small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="insurance_certificate" class="form-label">Insurance Certificate</label>
                                        <input type="file" class="form-control" id="insurance_certificate" name="insurance_certificate" accept=".pdf,.jpg,.jpeg">
                                        <?php if (isset($legal->insurance_certificate)): ?>
                                            <small class="text-success d-block mt-2">Uploaded</small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="signed_agreement" class="form-label">Signed Agreement</label>
                                        <input type="file" class="form-control" id="signed_agreement" name="signed_agreement" accept=".pdf,.jpg,.jpeg">
                                        <?php if (isset($legal->signed_agreement)): ?>
                                            <small class="text-success d-block mt-2">Uploaded</small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="business_legal_name" class="form-label">Business Legal Name</label>
                                        <input type="text" class="form-control" id="business_legal_name" name="business_legal_name" 
                                               value="<?php echo isset($legal->business_legal_name) ? htmlspecialchars($legal->business_legal_name) : ''; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-info">Save Legal Information</button>
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
