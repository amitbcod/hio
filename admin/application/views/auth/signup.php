<?php $this->load->view('auth/layout_head'); ?>
<div class="auth-container">
	<div class="auth-card">
		<div class="auth-row">
			<!-- Left Side: Form -->
			<div class="auth-form-side">
				<h2 class="auth-title">Create Account</h2>
				<form id="signup-form" method="post" action="<?php echo site_url('auth/signup'); ?>">
					<?php if (isset($error)): ?>
						<div class="alert alert-danger">
							<i class="fa fa-exclamation-circle"></i> <?php echo $error; ?>
						</div>
					<?php endif; ?>

					<?php if (isset($errors)): ?>
						<div class="alert alert-danger">
							<?php echo $errors; ?>
						</div>
					<?php endif; ?>

					<div class="form-fields text-left">
						<!-- Account Type Selection -->
						<div class="mb-4">
							<label class="d-block mb-2"><strong>Account Type <span class="required">*</span></strong></label>
							<div class="form-check">
								<input type="radio" name="user_type" value="Operator" id="type_operator" class="form-check-input" checked required>
								<label class="form-check-label" for="type_operator">Operator</label>
							</div>
							<div class="form-check">
								<input type="radio" name="user_type" value="MPO" id="type_mpo" class="form-check-input">
								<label class="form-check-label" for="type_mpo">MPO</label>
							</div>
							<div class="form-check">
								<input type="radio" name="user_type" value="Agent" id="type_agent" class="form-check-input">
								<label class="form-check-label" for="type_agent">Agent</label>
							</div>
						</div>

						<!-- Owner Question -->
						<div class="mb-4">
							<label class="d-block mb-2"><strong>Are you the owner of this account? <span class="required">*</span></strong></label>
							<div class="form-check">
								<input type="radio" name="is_owner" value="yes" id="owner_yes" class="form-check-input" checked required onchange="toggleOwnerFields()">
								<label class="form-check-label" for="owner_yes">Yes</label>
							</div>
							<div class="form-check">
								<input type="radio" name="is_owner" value="no" id="owner_no" class="form-check-input" onchange="toggleOwnerFields()">
								<label class="form-check-label" for="owner_no">No</label>
							</div>
						</div>

						<!-- Owner Information (shown if "No") -->
						<div id="owner_info_section" style="display:none; border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-bottom: 20px; background-color: #f9f9f9;">
							<h5 class="mb-3">Owner Information</h5>
							
							<div class="mb-3">
								<label for="owner_full_name">Owner's Full Name <span class="required">*</span></label>
								<input type="text" name="owner_full_name" id="owner_full_name" class="form-control">
								<?php echo form_error('owner_full_name', '<p class="error">', '</p>'); ?>
							</div>

							<div class="mb-3">
								<label for="owner_email">Owner's Email <span class="required">*</span></label>
								<input type="email" name="owner_email" id="owner_email" class="form-control">
								<?php echo form_error('owner_email', '<p class="error">', '</p>'); ?>
							</div>

							<div class="mb-3">
								<label for="owner_phone">Owner's Phone <span class="required">*</span></label>
								<input type="tel" name="owner_phone" id="owner_phone" class="form-control" placeholder="+230 5xxxxxxxx">
								<?php echo form_error('owner_phone', '<p class="error">', '</p>'); ?>
							</div>

							<div class="mb-3">
								<label for="owner_password">Owner's Password <span class="required">*</span></label>
								<div class="input-group">
									<input type="password" name="owner_password" id="owner_password" class="form-control" minlength="8">
									<span class="input-group-text" style="cursor: pointer;" onclick="togglePasswordVisibility('owner_password')">
										<i class="fa fa-eye"></i>
									</span>
								</div>
								<small class="text-muted">Minimum 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special char</small>
							</div>
						</div>

						<!-- Your Information -->
						<h5 class="mt-4 mb-3">Your Information</h5>

						<div class="mb-3">
							<label for="user_full_name">Your Full Name <span class="required">*</span></label>
							<input type="text" name="user_full_name" id="user_full_name" class="form-control" value="<?php echo set_value('user_full_name'); ?>" required>
							<?php echo form_error('user_full_name', '<p class="error">', '</p>'); ?>
						</div>

						<div class="mb-3">
							<label for="user_email">Your Email <span class="required">*</span></label>
							<input type="email" name="user_email" id="user_email" class="form-control" value="<?php echo set_value('user_email'); ?>" required>
							<?php echo form_error('user_email', '<p class="error">', '</p>'); ?>
							<small id="email_status" class="form-text text-muted"></small>
						</div>

						<div class="mb-3">
							<label for="user_phone">Your Phone <span class="required">*</span></label>
							<input type="tel" name="user_phone" id="user_phone" class="form-control" placeholder="+230 5xxxxxxxx" value="<?php echo set_value('user_phone'); ?>" required>
							<?php echo form_error('user_phone', '<p class="error">', '</p>'); ?>
							<small class="form-text text-muted">E.164 format (e.g., +230 5701234)</small>
						</div>

						<!-- Password Setup -->
						<div class="mb-3">
							<label for="password">Set Password <span class="required">*</span></label>
							<div class="input-group">
								<input type="password" name="password" id="password" class="form-control" required minlength="8">
								<span class="input-group-text" style="cursor: pointer;" onclick="togglePasswordVisibility('password')">
									<i class="fa fa-eye"></i>
								</span>
							</div>
							<small class="form-text text-muted">
								Requirements: 
								<ul style="margin-top: 5px; margin-bottom: 0;">
									<li id="req_length">At least 8 characters</li>
									<li id="req_upper">One uppercase letter (A-Z)</li>
									<li id="req_lower">One lowercase letter (a-z)</li>
									<li id="req_number">One number (0-9)</li>
									<li id="req_special">One special character (!@#$%^&*)</li>
								</ul>
							</small>
							<?php echo form_error('password', '<p class="error">', '</p>'); ?>
						</div>

						<div class="mb-3">
							<label for="password_confirm">Confirm Password <span class="required">*</span></label>
							<div class="input-group">
								<input type="password" name="password_confirm" id="password_confirm" class="form-control" required minlength="8">
								<span class="input-group-text" style="cursor: pointer;" onclick="togglePasswordVisibility('password_confirm')">
									<i class="fa fa-eye"></i>
								</span>
							</div>
							<small id="password_match_status" class="form-text text-muted"></small>
							<?php echo form_error('password_confirm', '<p class="error">', '</p>'); ?>
						</div>

						<!-- Terms Agreement -->
						<div class="form-check mb-4">
							<input type="checkbox" name="agree_terms" id="agree_terms" class="form-check-input" value="1" required>
							<label class="form-check-label" for="agree_terms">
								I agree to the <a href="#" target="_blank">terms and conditions</a> <span class="required">*</span>
							</label>
						</div>
					</div>

					<button type="submit" class="btn btn-lg btn-primary btn-block mt-4">Create Account</button>
					<p class="text-center mt-3">
						Already have an account? <a href="<?php echo base_url('auth/login'); ?>">Sign in here</a>
					</p>
				</form>
			</div>

			<div class="col-sm-7 d-flex align-items-center right-side bkg-img">
				<div class="right-content">
					<h1 class="heading-welcome"><span class="fw-300">Welcome to </span>Holidays.io</h1>
					<span class="sub-heading">Sign Up to Access your Account</span>
					
					<div class="mt-5">
						<h5>Why Register?</h5>
						<ul style="text-align: left; font-size: 14px; line-height: 1.8;">
							<li><i class="fa fa-check text-success"></i> Manage your business profile</li>
							<li><i class="fa fa-check text-success"></i> Upload legal documents</li>
							<li><i class="fa fa-check text-success"></i> Configure payment settings</li>
							<li><i class="fa fa-check text-success"></i> Manage staff users</li>
							<li><i class="fa fa-check text-success"></i> Monitor compliance status</li>
							<li><i class="fa fa-check text-success"></i> View payout history</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			function toggleOwnerFieldsLocal() {
				const ownerSection = document.getElementById('owner_info_section');
				const isOwner = document.getElementById('owner_yes') && document.getElementById('owner_yes').checked;
				if (!ownerSection) return;

				if (!isOwner) {
					ownerSection.style.display = 'block';
					['owner_full_name','owner_email','owner_phone','owner_password'].forEach(id => { const el = document.getElementById(id); if (el) el.required = true; });
				} else {
					ownerSection.style.display = 'none';
					['owner_full_name','owner_email','owner_phone','owner_password'].forEach(id => { const el = document.getElementById(id); if (el) el.required = false; });
				}
			}
			window.toggleOwnerFields = toggleOwnerFieldsLocal;

			// Password visibility via delegation
			document.body.addEventListener('click', function(ev) {
				const btn = ev.target.closest('.input-group-text'); if (!btn) return;
				const input = btn.parentElement.querySelector('input'); const icon = btn.querySelector('i'); if (!input || !icon) return;
				if (input.type === 'password') { input.type = 'text'; icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
				else { input.type = 'password'; icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
			});

			const passwordEl = document.getElementById('password');
			const confirmEl = document.getElementById('password_confirm');
			if (passwordEl) {
				passwordEl.addEventListener('keyup', function() {
					const password = this.value;
					const map = { req_length: password.length >= 8, req_upper: /[A-Z]/.test(password), req_lower: /[a-z]/.test(password), req_number: /[0-9]/.test(password), req_special: /[\W_]/.test(password) };
					Object.keys(map).forEach(id => { const el = document.getElementById(id); if (el) el.classList.toggle('text-success', map[id]); });
				});
			}
			if (confirmEl && passwordEl) {
				confirmEl.addEventListener('keyup', function() {
					const status = document.getElementById('password_match_status'); if (!status) return;
					if (this.value.length === 0) { status.textContent = ''; status.classList.remove('text-success','text-danger'); }
					else if (this.value === passwordEl.value) { status.textContent = '✓ Passwords match'; status.classList.remove('text-danger'); status.classList.add('text-success'); }
					else { status.textContent = '✗ Passwords do not match'; status.classList.remove('text-success'); status.classList.add('text-danger'); }
				});
			}

			const form = document.getElementById('signup-form');
			if (form) {
				form.addEventListener('submit', function(e) {
					const pw = document.getElementById('password') ? document.getElementById('password').value : '';
					const cpw = document.getElementById('password_confirm') ? document.getElementById('password_confirm').value : '';
					if (pw !== cpw) { e.preventDefault(); alert('Passwords do not match!'); return false; }
					const agree = document.getElementById('agree_terms'); if (agree && !agree.checked) { e.preventDefault(); alert('You must agree to the terms and conditions'); return false; }
				});
			}
		});
	</script>

<?php $this->load->view('auth/layout_footer'); ?>
