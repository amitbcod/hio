<?php
$header_data = isset($header_data) ? $header_data : array();
$header_data['current_section'] = 'accounting';
$this->load->view('dashboard/header', $header_data);
?>

<div class="container-fluid dashboard-container">
    <div class="main-content">
        <div class="container-fluid p-0">
            <div class="row mb-3">
                <div class="col-12">
                    <h4 class="mb-1">Accounting & Payouts</h4>
                    <p class="text-muted mb-0">Bank details and payout management</p>
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
                                <h5 class="mb-3">Bank Account Details</h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="bank_account_holder_name" class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="bank_account_holder_name" name="bank_account_holder_name" 
                                               value="<?php echo isset($accounting->bank_account_holder_name) ? htmlspecialchars($accounting->bank_account_holder_name) : ''; ?>" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="bank_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                               value="<?php echo isset($accounting->bank_name) ? htmlspecialchars($accounting->bank_name) : ''; ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="account_number" name="account_number" 
                                               value="<?php echo isset($accounting->account_number) ? htmlspecialchars($accounting->account_number) : ''; ?>" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="iban" class="form-label">IBAN</label>
                                        <input type="text" class="form-control" id="iban" name="iban" 
                                               value="<?php echo isset($accounting->iban) ? htmlspecialchars($accounting->iban) : ''; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="swift_code" class="form-label">SWIFT Code</label>
                                        <input type="text" class="form-control" id="swift_code" name="swift_code" 
                                               value="<?php echo isset($accounting->swift_code) ? htmlspecialchars($accounting->swift_code) : ''; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="currency_preference" class="form-label">Currency Preference</label>
                                        <select class="form-control" id="currency_preference" name="currency_preference">
                                            <option value="MUR" <?php echo (isset($accounting->currency_preference) && $accounting->currency_preference === 'MUR') ? 'selected' : ''; ?>>MUR - Mauritian Rupee</option>
                                            <option value="USD" <?php echo (isset($accounting->currency_preference) && $accounting->currency_preference === 'USD') ? 'selected' : ''; ?>>USD - US Dollar</option>
                                            <option value="EUR" <?php echo (isset($accounting->currency_preference) && $accounting->currency_preference === 'EUR') ? 'selected' : ''; ?>>EUR - Euro</option>
                                            <option value="GBP" <?php echo (isset($accounting->currency_preference) && $accounting->currency_preference === 'GBP') ? 'selected' : ''; ?>>GBP - British Pound</option>
                                        </select>
                                    </div>
                                </div>

                                <hr>
                                <h5 class="mb-3">Tax Information</h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="vat_number" class="form-label">VAT Number</label>
                                        <input type="text" class="form-control" id="vat_number" name="vat_number" 
                                               value="<?php echo isset($accounting->vat_number) ? htmlspecialchars($accounting->vat_number) : ''; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">VAT Status</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="vat_exempted" name="vat_exempted" 
                                                   <?php echo (isset($accounting->vat_exempted) && $accounting->vat_exempted) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="vat_exempted">VAT Exempted</label>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h5 class="mb-3">Commission & Payment Settings</h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="commission_type" class="form-label">Commission Type</label>
                                        <select class="form-control" id="commission_type" name="commission_type">
                                            <option value="Fixed" <?php echo (isset($accounting->commission_type) && $accounting->commission_type === 'Fixed') ? 'selected' : ''; ?>>Fixed</option>
                                            <option value="Percentage" <?php echo (isset($accounting->commission_type) && $accounting->commission_type === 'Percentage') ? 'selected' : ''; ?>>Percentage</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="commission_value" class="form-label">Commission Value</label>
                                        <input type="number" step="0.01" class="form-control" id="commission_value" name="commission_value" 
                                               value="<?php echo isset($accounting->commission_value) ? $accounting->commission_value : ''; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="payment_schedule" class="form-label">Payment Schedule</label>
                                        <select class="form-control" id="payment_schedule" name="payment_schedule">
                                            <option value="Monthly" <?php echo (isset($accounting->payment_schedule) && $accounting->payment_schedule === 'Monthly') ? 'selected' : ''; ?>>Monthly</option>
                                            <option value="Quarterly" <?php echo (isset($accounting->payment_schedule) && $accounting->payment_schedule === 'Quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                                            <option value="On Request" <?php echo (isset($accounting->payment_schedule) && $accounting->payment_schedule === 'On Request') ? 'selected' : ''; ?>>On Request</option>
                                            <option value="Service Provided" <?php echo (isset($accounting->payment_schedule) && $accounting->payment_schedule === 'Service Provided') ? 'selected' : ''; ?>>Service Provided</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="draft" <?php echo (isset($accounting->status) && $accounting->status === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            <option value="active" <?php echo (isset($accounting->status) && $accounting->status === 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo (isset($accounting->status) && $accounting->status === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-info">Save Accounting Details</button>
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
