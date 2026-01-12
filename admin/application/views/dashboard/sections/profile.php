<div class="dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <!-- Section Header -->
            <div style="margin-bottom: 30px;">
                <h3 style="text-transform: uppercase; color: #333; font-weight: 600; margin-bottom: 20px;">PROFILE</h3>
            </div>

            <!-- Alert Messages -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> <?php echo $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> <?php echo $this->session->flashdata('error'); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" enctype="multipart/form-data" style="max-width: 800px;">
                <div style="display: table; width: 100%; margin-bottom: 15px;">
                    <div style="display: table-cell; width: 35%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 500; color: #333;">Business Legal Name</label>
                    </div>
                    <div style="display: table-cell; width: 65%;">
                        <input type="text" class="form-control" name="business_legal_name" 
                               value="<?php echo isset($profile->business_legal_name) ? htmlspecialchars($profile->business_legal_name) : ''; ?>" 
                               style="width: 100%;" required>
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 15px;">
                    <div style="display: table-cell; width: 35%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 500; color: #333;">Business Registration Number</label>
                    </div>
                    <div style="display: table-cell; width: 65%;">
                        <input type="text" class="form-control" name="business_registration_number" 
                               value="<?php echo isset($profile->business_registration_number) ? htmlspecialchars($profile->business_registration_number) : ''; ?>" 
                               style="width: 100%;">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 15px;">
                    <div style="display: table-cell; width: 35%; padding-right: 20px; vertical-align: top;">
                        <label style="font-weight: 500; color: #333;">Registered Address</label>
                    </div>
                    <div style="display: table-cell; width: 65%;">
                        <input type="text" class="form-control" name="registered_address" 
                               value="<?php echo isset($profile->registered_address) ? htmlspecialchars($profile->registered_address) : ''; ?>" 
                               style="width: 100%;">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 15px;">
                    <div style="display: table-cell; width: 35%; padding-right: 20px; vertical-align: top;">
                        <label style="font-weight: 500; color: #333;">Operational Address</label>
                    </div>
                    <div style="display: table-cell; width: 65%;">
                        <input type="text" class="form-control" name="operational_address" 
                               value="<?php echo isset($profile->operational_address) ? htmlspecialchars($profile->operational_address) : ''; ?>" 
                               style="width: 100%;">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 15px;">
                    <div style="display: table-cell; width: 35%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 500; color: #333;">Service Type</label>
                    </div>
                    <div style="display: table-cell; width: 65%;">
                        <?php $st = isset($profile->service_types) ? (array)$profile->service_types : array(); ?>
                        <select class="form-control" name="service_types[]" multiple style="width: 100%;">
                            <option value="accommodation" <?php echo in_array('accommodation', $st) ? 'selected' : ''; ?>>Accommodation</option>
                            <option value="transport" <?php echo in_array('transport', $st) ? 'selected' : ''; ?>>Transport</option>
                            <option value="activity" <?php echo in_array('activity', $st) ? 'selected' : ''; ?>>Activity</option>
                            <option value="food" <?php echo in_array('food', $st) ? 'selected' : ''; ?>>Food</option>
                        </select>
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 15px;">
                    <div style="display: table-cell; width: 35%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 500; color: #333;">Years in Operation</label>
                    </div>
                    <div style="display: table-cell; width: 65%;">
                        <input type="number" class="form-control" name="years_in_operation" 
                               value="<?php echo isset($profile->years_in_operation) ? $profile->years_in_operation : ''; ?>" 
                               style="width: 100%;" min="0">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 15px;">
                    <div style="display: table-cell; width: 35%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 500; color: #333;">Contact Details</label>
                    </div>
                    <div style="display: table-cell; width: 65%;">
                        <div style="margin-bottom: 8px;"><strong style="font-size: 0.9rem;">Name</strong></div>
                        <input type="text" class="form-control" name="contact_name" 
                               value="<?php echo isset($profile->contact_name) ? htmlspecialchars($profile->contact_name) : ''; ?>" 
                               style="width: 100%; margin-bottom: 10px;">
                        <div style="margin-bottom: 8px;"><strong style="font-size: 0.9rem;">Phone</strong></div>
                        <input type="text" class="form-control" name="contact_phone" 
                               value="<?php echo isset($profile->contact_phone) ? htmlspecialchars($profile->contact_phone) : ''; ?>" 
                               style="width: 100%; margin-bottom: 10px;">
                        <div style="margin-bottom: 8px;"><strong style="font-size: 0.9rem;">Email</strong></div>
                        <input type="email" class="form-control" name="contact_email" 
                               value="<?php echo isset($profile->contact_email) ? htmlspecialchars($profile->contact_email) : ''; ?>" 
                               style="width: 100%;">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 15px;">
                    <div style="display: table-cell; width: 35%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 500; color: #333;">Trading Name</label>
                    </div>
                    <div style="display: table-cell; width: 65%;">
                        <input type="text" class="form-control" name="trading_name" 
                               value="<?php echo isset($profile->trading_name) ? htmlspecialchars($profile->trading_name) : ''; ?>" 
                               style="width: 100%;">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 15px;">
                    <div style="display: table-cell; width: 35%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 500; color: #333;">Company Logo</label>
                    </div>
                    <div style="display: table-cell; width: 65%;">
                        <input type="file" class="form-control" name="company_logo" accept="image/*" style="width: 100%;">
                        <?php if (isset($profile->company_logo) && $profile->company_logo): ?>
                            <small class="d-block mt-2 text-success">Current: <img src="<?php echo base_url($profile->company_logo); ?>" style="max-height: 50px;"></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 15px;">
                    <div style="display: table-cell; width: 35%; padding-right: 20px; vertical-align: top;">
                        <label style="font-weight: 500; color: #333;">Company Description</label>
                    </div>
                    <div style="display: table-cell; width: 65%;">
                        <textarea class="form-control" name="company_description" rows="4" style="width: 100%;"><?php echo isset($profile->company_description) ? htmlspecialchars($profile->company_description) : ''; ?></textarea>
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 15px;">
                    <div style="display: table-cell; width: 35%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 500; color: #333;">Social Media Links</label>
                    </div>
                    <div style="display: table-cell; width: 65%;">
                        <div style="margin-bottom: 8px;"><strong style="font-size: 0.9rem;">Facebook</strong></div>
                        <input type="text" class="form-control" name="facebook_link" placeholder="https://facebook.com/..." 
                               value="<?php echo isset($profile->facebook_link) ? htmlspecialchars($profile->facebook_link) : ''; ?>" 
                               style="width: 100%; margin-bottom: 10px;">
                        <div style="margin-bottom: 8px;"><strong style="font-size: 0.9rem;">Instagram</strong></div>
                        <input type="text" class="form-control" name="instagram_link" placeholder="https://instagram.com/..." 
                               value="<?php echo isset($profile->instagram_link) ? htmlspecialchars($profile->instagram_link) : ''; ?>" 
                               style="width: 100%; margin-bottom: 10px;">
                        <div style="margin-bottom: 8px;"><strong style="font-size: 0.9rem;">Linkedin</strong></div>
                        <input type="text" class="form-control" name="linkedin_link" placeholder="https://linkedin.com/..." 
                               value="<?php echo isset($profile->linkedin_link) ? htmlspecialchars($profile->linkedin_link) : ''; ?>" 
                               style="width: 100%;">
                    </div>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <button type="button" id="openLegalModal" class="btn" style="background-color: #4169E1; color: white; padding: 10px 30px; border: none; border-radius: 4px; cursor: pointer;">Legal & Compliance</button>
                    <button type="submit" class="btn" style="background-color: #2bb4a8; color: white; padding: 10px 30px; border: none; border-radius: 4px; cursor: pointer;">Save and Continue</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Legal & Compliance Modal -->
<div id="legalComplianceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 8px; max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto; padding: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h4 style="margin: 0; color: #333;">Legal & Compliance</h4>
            <button type="button" id="closeLegalModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
        </div>

        <form id="legalComplianceForm" enctype="multipart/form-data">
            <div style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: #333; display: block; margin-bottom: 5px;">Business License Number <span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="business_license_number" 
                       value="<?php echo isset($profile->business_license_number) ? htmlspecialchars($profile->business_license_number) : ''; ?>" 
                       style="width: 100%;" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: #333; display: block; margin-bottom: 5px;">License Type <span style="color: red;">*</span></label>
                <select class="form-control" name="license_type" style="width: 100%;" required>
                    <option value="">Select License Type</option>
                    <option value="Accommodation" <?php echo (isset($profile->license_type) && $profile->license_type === 'Accommodation') ? 'selected' : ''; ?>>Accommodation</option>
                    <option value="Tour Operator" <?php echo (isset($profile->license_type) && $profile->license_type === 'Tour Operator') ? 'selected' : ''; ?>>Tour Operator</option>
                    <option value="Car Rental" <?php echo (isset($profile->license_type) && $profile->license_type === 'Car Rental') ? 'selected' : ''; ?>>Car Rental</option>
                    <option value="Guide" <?php echo (isset($profile->license_type) && $profile->license_type === 'Guide') ? 'selected' : ''; ?>>Guide</option>
                    <option value="Other" <?php echo (isset($profile->license_type) && $profile->license_type === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: #333; display: block; margin-bottom: 5px;">License Expiry Date <span style="color: red;">*</span></label>
                <input type="date" class="form-control" name="license_expiry_date" 
                       value="<?php echo isset($profile->license_expiry_date) ? $profile->license_expiry_date : ''; ?>" 
                       style="width: 100%;" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: #333; display: block; margin-bottom: 5px;">Service Package</label>
                <select class="form-control" name="service_package" style="width: 100%;">
                    <option value="HIO Listing Only" <?php echo (isset($profile->service_package) && $profile->service_package === 'HIO Listing Only') ? 'selected' : ''; ?>>HIO Listing Only</option>
                    <option value="HIO Partner Standard" <?php echo (isset($profile->service_package) && $profile->service_package === 'HIO Partner Standard') ? 'selected' : ''; ?>>HIO Partner Standard</option>
                    <option value="HIO Partner Pro" <?php echo (isset($profile->service_package) && $profile->service_package === 'HIO Partner Pro') ? 'selected' : ''; ?>>HIO Partner Pro</option>
                    <option value="HIO Partner Elite" <?php echo (isset($profile->service_package) && $profile->service_package === 'HIO Partner Elite') ? 'selected' : ''; ?>>HIO Partner Elite</option>
                    <option value="HIO Full Service" <?php echo (isset($profile->service_package) && $profile->service_package === 'HIO Full Service') ? 'selected' : ''; ?>>HIO Full Service</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: #333; display: block; margin-bottom: 5px;">Proof of License (PDF/JPEG)</label>
                <input type="file" class="form-control" name="proof_of_license" accept=".pdf,.jpg,.jpeg" style="width: 100%;">
                <?php if (isset($profile->proof_of_license) && $profile->proof_of_license): ?>
                    <small class="d-block mt-2 text-success">✓ File uploaded</small>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: #333; display: block; margin-bottom: 5px;">Insurance Certificate</label>
                <input type="file" class="form-control" name="insurance_certificate" accept=".pdf,.jpg,.jpeg" style="width: 100%;">
                <?php if (isset($profile->insurance_certificate) && $profile->insurance_certificate): ?>
                    <small class="d-block mt-2 text-success">✓ File uploaded</small>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-weight: 500; color: #333; display: block; margin-bottom: 5px;">Signed Agreement</label>
                <input type="file" class="form-control" name="signed_agreement" accept=".pdf,.jpg,.jpeg" style="width: 100%;">
                <?php if (isset($profile->signed_agreement) && $profile->signed_agreement): ?>
                    <small class="d-block mt-2 text-success">✓ File uploaded</small>
                <?php endif; ?>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" id="cancelLegalModal" class="btn" style="background-color: #ddd; color: #333; padding: 10px 25px; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn" style="background-color: #2bb4a8; color: white; padding: 10px 25px; border: none; border-radius: 4px; cursor: pointer;">Save Compliance Info</button>
            </div>
        </form>

        <div id="legalFormMessage" style="margin-top: 15px; display: none; padding: 10px; border-radius: 4px;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('legalComplianceModal');
    const openBtn = document.getElementById('openLegalModal');
    const closeBtn = document.getElementById('closeLegalModal');
    const cancelBtn = document.getElementById('cancelLegalModal');
    const form = document.getElementById('legalComplianceForm');
    const msgDiv = document.getElementById('legalFormMessage');

    // Open modal
    openBtn.addEventListener('click', function(e) {
        e.preventDefault();
        modal.style.display = 'flex';
    });

    // Close modal
    function closeModal() {
        modal.style.display = 'none';
    }

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Close when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch('<?php echo site_url("dashboard/save_legal_ajax"); ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                msgDiv.className = '';
                msgDiv.style.backgroundColor = '#d4edda';
                msgDiv.style.color = '#155724';
                msgDiv.innerHTML = '✓ Legal & Compliance information saved successfully!';
                msgDiv.style.display = 'block';
                setTimeout(() => {
                    closeModal();
                    msgDiv.style.display = 'none';
                    location.reload();
                }, 1500);
            } else {
                msgDiv.style.backgroundColor = '#f8d7da';
                msgDiv.style.color = '#721c24';
                msgDiv.innerHTML = '✗ ' + (data.message || 'Failed to save information');
                msgDiv.style.display = 'block';
            }
        })
        .catch(error => {
            msgDiv.style.backgroundColor = '#f8d7da';
            msgDiv.style.color = '#721c24';
            msgDiv.innerHTML = '✗ Error: ' + error.message;
            msgDiv.style.display = 'block';
        });
    });
});
</script>
