<div class="dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <!-- Section Header -->
            <div style="margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                <h3 style="text-transform: uppercase; color: #555; font-weight: 600; margin-bottom: 0; font-size: 1.1rem; letter-spacing: 1px;">PROFILE</h3>
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
            <form method="POST" enctype="multipart/form-data" style="max-width: 900px; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: table; width: 100%; margin-bottom: 18px;">
                    <div style="display: table-cell; width: 30%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 400; color: #555; font-size: 14px;">Business Legal Name</label>
                    </div>
                    <div style="display: table-cell; width: 70%;">
                        <?php 
                        $business_name = isset($operator->business_legal_name) ? $operator->business_legal_name : '';
                        if (empty($business_name) && isset($profile->business_legal_name)) {
                            $business_name = $profile->business_legal_name;
                        }
                        ?>
                        <input type="text" class="form-control" name="business_legal_name_display" 
                               value="<?php echo htmlspecialchars($business_name); ?>" 
                               readonly
                               style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #f5f5f5;">
                        <input type="hidden" name="business_legal_name" value="<?php echo htmlspecialchars($business_name); ?>">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 18px;">
                    <div style="display: table-cell; width: 30%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 400; color: #555; font-size: 14px;">Business Registration Number</label>
                    </div>
                    <div style="display: table-cell; width: 70%;">
                        <input type="text" class="form-control" name="business_registration_number" 
                               value="<?php echo isset($profile->business_registration_number) ? htmlspecialchars($profile->business_registration_number) : ''; ?>" 
                               style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 18px;">
                    <div style="display: table-cell; width: 30%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 400; color: #555; font-size: 14px;">Registered Address</label>
                    </div>
                    <div style="display: table-cell; width: 70%;">
                        <input type="text" class="form-control" name="registered_address" 
                               value="<?php echo isset($profile->registered_address) ? htmlspecialchars($profile->registered_address) : ''; ?>" 
                               style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 18px;">
                    <div style="display: table-cell; width: 30%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 400; color: #555; font-size: 14px;">Operational Address</label>
                    </div>
                    <div style="display: table-cell; width: 70%;">
                        <input type="text" class="form-control" name="operational_address" 
                               value="<?php echo isset($profile->operational_address) ? htmlspecialchars($profile->operational_address) : ''; ?>" 
                               style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 18px;">
                    <div style="display: table-cell; width: 30%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 400; color: #555; font-size: 14px;">Service Type</label>
                    </div>
                    <div style="display: table-cell; width: 70%;">
                        <?php $st = isset($profile->service_types) ? (array)$profile->service_types : array(); ?>
                        <select class="form-control" name="service_types[]" multiple style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                            <option value="accommodation" <?php echo in_array('accommodation', $st) ? 'selected' : ''; ?>>Accommodation</option>
                            <option value="transport" <?php echo in_array('transport', $st) ? 'selected' : ''; ?>>Transport</option>
                            <option value="activity" <?php echo in_array('activity', $st) ? 'selected' : ''; ?>>Activity</option>
                            <option value="food" <?php echo in_array('food', $st) ? 'selected' : ''; ?>>Food</option>
                        </select>
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 18px;">
                    <div style="display: table-cell; width: 30%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 400; color: #555; font-size: 14px;">Years in Operation</label>
                    </div>
                    <div style="display: table-cell; width: 70%;">
                        <input type="number" class="form-control" name="years_in_operation" 
                               value="<?php echo isset($profile->years_in_operation) ? $profile->years_in_operation : ''; ?>" 
                               style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" min="0">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 18px;">
                    <div style="display: table-cell; width: 30%; padding-right: 20px; vertical-align: top; padding-top: 10px;">
                        <label style="font-weight: 400; color: #555; font-size: 14px;">Contact Details</label>
                    </div>
                    <div style="display: table-cell; width: 70%;">
                        <div style="margin-bottom: 6px;"><span style="font-size: 13px; color: #666;">Name</span></div>
                        <input type="text" class="form-control" name="contact_name" 
                               value="<?php echo isset($profile->contact_name) ? htmlspecialchars($profile->contact_name) : ''; ?>" 
                               style="width: 100%; margin-bottom: 12px; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <div style="margin-bottom: 6px;"><span style="font-size: 13px; color: #666;">Phone</span></div>
                        <input type="text" class="form-control" name="contact_phone" 
                               value="<?php echo isset($profile->contact_phone) ? htmlspecialchars($profile->contact_phone) : ''; ?>" 
                               style="width: 100%; margin-bottom: 12px; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <div style="margin-bottom: 6px;"><span style="font-size: 13px; color: #666;">email</span></div>
                        <input type="email" class="form-control" name="contact_email" 
                               value="<?php echo isset($profile->contact_email) ? htmlspecialchars($profile->contact_email) : ''; ?>" 
                               style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 18px;">
                    <div style="display: table-cell; width: 30%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 400; color: #555; font-size: 14px;">Trading Name</label>
                    </div>
                    <div style="display: table-cell; width: 70%;">
                        <input type="text" class="form-control" name="trading_name" 
                               value="<?php echo isset($profile->trading_name) ? htmlspecialchars($profile->trading_name) : ''; ?>" 
                               style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 18px;">
                    <div style="display: table-cell; width: 30%; padding-right: 20px; vertical-align: middle;">
                        <label style="font-weight: 400; color: #555; font-size: 14px;">Company Logo</label>
                    </div>
                    <div style="display: table-cell; width: 70%;">
                        <div style="position: relative;">
                            <input type="file" class="form-control" name="company_logo" accept="image/*" 
                                   style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <button type="button" style="position: absolute; right: 5px; top: 5px; background-color: #5cb9b4; color: white; border: none; padding: 8px 20px; border-radius: 3px; font-size: 13px; cursor: pointer;">Upload</button>
                        </div>
                        <?php if (isset($profile->company_logo) && $profile->company_logo): ?>
                            <small class="d-block mt-2 text-success">Current: <img src="<?php echo base_url($profile->company_logo); ?>" style="max-height: 50px;"></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 18px;">
                    <div style="display: table-cell; width: 30%; padding-right: 20px; vertical-align: top; padding-top: 10px;">
                        <label style="font-weight: 400; color: #555; font-size: 14px;">Company Description</label>
                    </div>
                    <div style="display: table-cell; width: 70%;">
                        <textarea class="form-control" name="company_description" rows="4" 
                                  style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"><?php echo isset($profile->company_description) ? htmlspecialchars($profile->company_description) : ''; ?></textarea>
                    </div>
                </div>

                <div style="display: table; width: 100%; margin-bottom: 18px;">
                    <div style="display: table-cell; width: 30%; padding-right: 20px; vertical-align: top; padding-top: 10px;">
                        <label style="font-weight: 400; color: #555; font-size: 14px;">Social Media Links</label>
                    </div>
                    <div style="display: table-cell; width: 70%;">
                        <div style="margin-bottom: 6px;"><span style="font-size: 13px; color: #666;">Facebook</span></div>
                        <input type="text" class="form-control" name="facebook_link" placeholder="https://facebook.com/..." 
                               value="<?php echo isset($profile->facebook_link) ? htmlspecialchars($profile->facebook_link) : ''; ?>" 
                               style="width: 100%; margin-bottom: 12px; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <div style="margin-bottom: 6px;"><span style="font-size: 13px; color: #666;">Instagram</span></div>
                        <input type="text" class="form-control" name="instagram_link" placeholder="https://instagram.com/..." 
                               value="<?php echo isset($profile->instagram_link) ? htmlspecialchars($profile->instagram_link) : ''; ?>" 
                               style="width: 100%; margin-bottom: 12px; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <div style="margin-bottom: 6px;"><span style="font-size: 13px; color: #666;">Linkedin</span></div>
                        <input type="text" class="form-control" name="linkedin_link" placeholder="https://linkedin.com/..." 
                               value="<?php echo isset($profile->linkedin_link) ? htmlspecialchars($profile->linkedin_link) : ''; ?>" 
                               style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>

                <div style="margin-top: 35px; display: flex; gap: 12px;">
                    <button type="button" id="openLegalModal" class="btn" 
                            style="background-color: #4169E1; color: white; padding: 12px 35px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; transition: background-color 0.3s;"
                            onmouseover="this.style.backgroundColor='#365abd'" 
                            onmouseout="this.style.backgroundColor='#4169E1'">Advanced Settings</button>
                    <button type="submit" class="btn" 
                            style="background-color: #5cb9b4; color: white; padding: 12px 35px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; transition: background-color 0.3s;"
                            onmouseover="this.style.backgroundColor='#4a9d99'" 
                            onmouseout="this.style.backgroundColor='#5cb9b4'">Save and Continue</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Legal & Compliance Modal -->
<div id="legalComplianceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 8px; max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e0e0e0;">
            <h4 style="margin: 0; text-transform: uppercase; font-size: 16px; font-weight: 600; color: #333; letter-spacing: 0.5px;">LEGAL & COMPLIANCE</h4>
            <button type="button" id="closeLegalModal" style="background: none; border: none; font-size: 28px; cursor: pointer; color: #999; line-height: 1; padding: 0; transition: color 0.2s;" onmouseover="this.style.color='#333'" onmouseout="this.style.color='#999'">&times;</button>
        </div>

        <form id="legalComplianceForm" enctype="multipart/form-data">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">Business License Number <span style="color: #dc3545;">*</span></label>
                <input type="text" name="business_license_number" 
                       value="<?php echo isset($profile->business_license_number) ? htmlspecialchars($profile->business_license_number) : ''; ?>" 
                       style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#5cb9b4'" onmouseout="this.style.borderColor='#ddd'" onfocus="this.style.borderColor='#5cb9b4'; this.style.outline='none';" onblur="this.style.borderColor='#ddd';" required>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">License Type <span style="color: #dc3545;">*</span></label>
                <select name="license_type" style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#5cb9b4'" onmouseout="this.style.borderColor='#ddd'" onfocus="this.style.borderColor='#5cb9b4'; this.style.outline='none';" onblur="this.style.borderColor='#ddd';" required>
                    <option value="">Select License Type</option>
                    <option value="Accommodation" <?php echo (isset($profile->license_type) && $profile->license_type === 'Accommodation') ? 'selected' : ''; ?>>Accommodation</option>
                    <option value="Tour Operator" <?php echo (isset($profile->license_type) && $profile->license_type === 'Tour Operator') ? 'selected' : ''; ?>>Tour Operator</option>
                    <option value="Car Rental" <?php echo (isset($profile->license_type) && $profile->license_type === 'Car Rental') ? 'selected' : ''; ?>>Car Rental</option>
                    <option value="Guide" <?php echo (isset($profile->license_type) && $profile->license_type === 'Guide') ? 'selected' : ''; ?>>Guide</option>
                    <option value="Other" <?php echo (isset($profile->license_type) && $profile->license_type === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">License Expiry Date <span style="color: #dc3545;">*</span></label>
                <input type="date" name="license_expiry_date" 
                       value="<?php echo isset($profile->license_expiry_date) ? $profile->license_expiry_date : ''; ?>" 
                       style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#5cb9b4'" onmouseout="this.style.borderColor='#ddd'" onfocus="this.style.borderColor='#5cb9b4'; this.style.outline='none';" onblur="this.style.borderColor='#ddd';" required>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">Service Package</label>
                <select name="service_package" style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#5cb9b4'" onmouseout="this.style.borderColor='#ddd'" onfocus="this.style.borderColor='#5cb9b4'; this.style.outline='none';" onblur="this.style.borderColor='#ddd';">
                    <option value="HIO Listing Only" <?php echo (isset($profile->service_package) && $profile->service_package === 'HIO Listing Only') ? 'selected' : ''; ?>>HIO Listing Only</option>
                    <option value="HIO Partner Standard" <?php echo (isset($profile->service_package) && $profile->service_package === 'HIO Partner Standard') ? 'selected' : ''; ?>>HIO Partner Standard</option>
                    <option value="HIO Partner Pro" <?php echo (isset($profile->service_package) && $profile->service_package === 'HIO Partner Pro') ? 'selected' : ''; ?>>HIO Partner Pro</option>
                    <option value="HIO Partner Elite" <?php echo (isset($profile->service_package) && $profile->service_package === 'HIO Partner Elite') ? 'selected' : ''; ?>>HIO Partner Elite</option>
                    <option value="HIO Full Service" <?php echo (isset($profile->service_package) && $profile->service_package === 'HIO Full Service') ? 'selected' : ''; ?>>HIO Full Service</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">Proof of License (PDF/JPEG)</label>
                <input type="file" name="proof_of_license" accept=".pdf,.jpg,.jpeg" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                <?php if (isset($profile->proof_of_license) && $profile->proof_of_license): ?>
                    <small style="display: block; margin-top: 8px; color: #28a745; font-size: 13px;">✓ File uploaded</small>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">Insurance Certificate</label>
                <input type="file" name="insurance_certificate" accept=".pdf,.jpg,.jpeg" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                <?php if (isset($profile->insurance_certificate) && $profile->insurance_certificate): ?>
                    <small style="display: block; margin-top: 8px; color: #28a745; font-size: 13px;">✓ File uploaded</small>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-size: 14px; font-weight: 500;">Signed Agreement</label>
                <input type="file" name="signed_agreement" accept=".pdf,.jpg,.jpeg" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                <?php if (isset($profile->signed_agreement) && $profile->signed_agreement): ?>
                    <small style="display: block; margin-top: 8px; color: #28a745; font-size: 13px;">✓ File uploaded</small>
                <?php endif; ?>
            </div>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" id="cancelLegalModal" style="background: #6c757d; color: white; padding: 10px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">Cancel</button>
                <button type="submit" style="background: #28a745; color: white; padding: 10px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.background='#218838'" onmouseout="this.style.background='#28a745'">Save Compliance Info</button>
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
