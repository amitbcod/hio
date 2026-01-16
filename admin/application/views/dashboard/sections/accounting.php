<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div style="margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                <h3 style="text-transform: uppercase; color: #555; font-weight: 600; margin-bottom: 5px; font-size: 1.1rem; letter-spacing: 1px;">ACCOUNTING & PAYOUTS</h3>
                <p style="color: #999; margin-bottom: 0; font-size: 13px;">Bank details and payout management</p>
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
                            <h5 style="color: #333; font-weight: 600; margin-bottom: 25px; font-size: 16px;">Bank Account Details</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="bank_account_holder_name" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Account Holder Name <span style="color: #dc3545;">*</span></label>
                                    <input type="text" class="form-control" id="bank_account_holder_name" name="bank_account_holder_name" 
                                           value="<?php echo isset($accounting->bank_account_holder_name) ? htmlspecialchars($accounting->bank_account_holder_name) : ''; ?>" required 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="bank_name" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Bank Name <span style="color: #dc3545;">*</span></label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                           value="<?php echo isset($accounting->bank_name) ? htmlspecialchars($accounting->bank_name) : ''; ?>" required 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="account_number" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Account Number <span style="color: #dc3545;">*</span></label>
                                    <input type="text" class="form-control" id="account_number" name="account_number" 
                                           value="<?php echo isset($accounting->account_number) ? htmlspecialchars($accounting->account_number) : ''; ?>" required 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="iban" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">IBAN</label>
                                    <input type="text" class="form-control" id="iban" name="iban" 
                                           value="<?php echo isset($accounting->iban) ? htmlspecialchars($accounting->iban) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="swift_code" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">SWIFT Code</label>
                                    <input type="text" class="form-control" id="swift_code" name="swift_code" 
                                           value="<?php echo isset($accounting->swift_code) ? htmlspecialchars($accounting->swift_code) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="currency_preference" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Currency Preference</label>
                                    <select class="form-control" id="currency_preference" name="currency_preference" 
                                            style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                            <option value="MUR" <?php echo (isset($accounting->currency_preference) && $accounting->currency_preference === 'MUR') ? 'selected' : ''; ?>>MUR - Mauritian Rupee</option>
                                            <option value="USD" <?php echo (isset($accounting->currency_preference) && $accounting->currency_preference === 'USD') ? 'selected' : ''; ?>>USD - US Dollar</option>
                                            <option value="EUR" <?php echo (isset($accounting->currency_preference) && $accounting->currency_preference === 'EUR') ? 'selected' : ''; ?>>EUR - Euro</option>
                                            <option value="GBP" <?php echo (isset($accounting->currency_preference) && $accounting->currency_preference === 'GBP') ? 'selected' : ''; ?>>GBP - British Pound</option>
                                        </select>
                                    </div>
                                </div>

                            <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 30px 0;">
                            <h5 style="color: #333; font-weight: 600; margin-bottom: 25px; font-size: 16px;">Tax Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="vat_number" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">VAT Number</label>
                                    <input type="text" class="form-control" id="vat_number" name="vat_number" 
                                           value="<?php echo isset($accounting->vat_number) ? htmlspecialchars($accounting->vat_number) : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">VAT Status</label>
                                    <div style="padding-top: 10px;">
                                        <input type="checkbox" id="vat_exempted" name="vat_exempted" 
                                               <?php echo (isset($accounting->vat_exempted) && $accounting->vat_exempted) ? 'checked' : ''; ?> 
                                               style="width: 18px; height: 18px; cursor: pointer; vertical-align: middle;">
                                        <label for="vat_exempted" style="margin-left: 8px; color: #555; font-size: 14px; cursor: pointer; vertical-align: middle;">VAT Exempted</label>
                                    </div>
                                    </div>
                                </div>

                            <hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 30px 0;">
                            <h5 style="color: #333; font-weight: 600; margin-bottom: 25px; font-size: 16px;">Commission & Payment Settings</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="commission_type" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Commission Type</label>
                                    <select class="form-control" id="commission_type" name="commission_type" 
                                            style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                            <option value="Fixed" <?php echo (isset($accounting->commission_type) && $accounting->commission_type === 'Fixed') ? 'selected' : ''; ?>>Fixed</option>
                                            <option value="Percentage" <?php echo (isset($accounting->commission_type) && $accounting->commission_type === 'Percentage') ? 'selected' : ''; ?>>Percentage</option>
                                        </select>
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="commission_value" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Commission Value</label>
                                    <input type="number" step="0.01" class="form-control" id="commission_value" name="commission_value" 
                                           value="<?php echo isset($accounting->commission_value) ? $accounting->commission_value : ''; ?>" 
                                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="payment_schedule" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Payment Schedule</label>
                                    <select class="form-control" id="payment_schedule" name="payment_schedule" 
                                            style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                            <option value="Monthly" <?php echo (isset($accounting->payment_schedule) && $accounting->payment_schedule === 'Monthly') ? 'selected' : ''; ?>>Monthly</option>
                                            <option value="Quarterly" <?php echo (isset($accounting->payment_schedule) && $accounting->payment_schedule === 'Quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                                            <option value="On Request" <?php echo (isset($accounting->payment_schedule) && $accounting->payment_schedule === 'On Request') ? 'selected' : ''; ?>>On Request</option>
                                            <option value="Service Provided" <?php echo (isset($accounting->payment_schedule) && $accounting->payment_schedule === 'Service Provided') ? 'selected' : ''; ?>>Service Provided</option>
                                        </select>
                                    </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" style="font-weight: 400; color: #555; font-size: 14px; display: block; margin-bottom: 8px;">Status</label>
                                    <select class="form-control" id="status" name="status" 
                                            style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                            <option value="draft" <?php echo (isset($accounting->status) && $accounting->status === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            <option value="active" <?php echo (isset($accounting->status) && $accounting->status === 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo (isset($accounting->status) && $accounting->status === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn" 
                                            style="background-color: #5cb9b4; color: white; padding: 12px 35px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; margin-right: 10px; transition: background-color 0.3s;"
                                            onmouseover="this.style.backgroundColor='#4a9d99'" 
                                            onmouseout="this.style.backgroundColor='#5cb9b4'">Save Accounting Details</button>
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
