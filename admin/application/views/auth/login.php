<?php $this->load->view('auth/layout_head'); ?>

<body>
<div class="auth-container">
	<div class="auth-card">
		<div class="auth-row">
			<!-- Left Side: Form -->
			<div class="auth-form-side">
				<h2 class="auth-title">Login to Your Account</h2>

				<?php if ($this->session->flashdata('success')): ?>
					<div class="alert alert-success">
						<i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
					</div>
				<?php endif; ?>

				<?php if ($this->session->flashdata('error')): ?>
					<div class="alert alert-danger">
						<i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
					</div>
				<?php endif; ?>

				<?php if (validation_errors()): ?>
					<div class="alert alert-danger">
						<?php echo validation_errors(); ?>
					</div>
				<?php endif; ?>

				<form id="login-form" method="POST" action="">
					<div class="form-group">
						<label>Email Address <span class="required">*</span></label>
						<input type="email" name="user_email" class="form-control" placeholder="your.email@example.com" value="<?php echo set_value('user_email'); ?>" required>
					</div>

					<div class="form-group">
						<label>Password <span class="required">*</span></label>
						<div style="position: relative;">
							<input type="password" id="password" name="user_password" class="form-control" placeholder="Enter your password" required>
							<i class="fas fa-eye" id="toggle-password" style="position: absolute; right: 10px; top: 10px; cursor: pointer;" onclick="togglePasswordVisibility('password', 'toggle-password')"></i>
						</div>
					</div>

					<div class="form-check mb-3">
						<input type="checkbox" name="remember_me" id="remember_me" class="form-check-input" value="1">
						<label class="form-check-label" for="remember_me">Remember me for 30 days</label>
					</div>

					<button type="submit" class="btn btn-auth">
						<i class="fas fa-sign-in-alt"></i> Sign In
					</button>

					<div class="form-link">
						<p><a href="<?php echo site_url('auth/forgot_password'); ?>">Forgot your password?</a></p>
						<hr style="margin: 15px 0;">
						Don't have an account? <a href="<?php echo site_url('auth/signup'); ?>"><strong>Sign up here</strong></a>
					</div>
				</form>
			</div>

			<!-- Right Side: Branding -->
			<div class="auth-branding-side">
				<div class="auth-logo">
					<i class="fas fa-umbrella"></i>
				</div>
				<h2>Holidays.io</h2>
				<p><strong>Operator Management System</strong></p>
				<p style="font-size: 0.9rem;">Sign in to access your business dashboard</p>
				
				<ul class="benefits-list">
					<li><i class="fas fa-chart-line"></i> Real-time business analytics</li>
					<li><i class="fas fa-lock"></i> Secure account management</li>
					<li><i class="fas fa-support"></i> 24/7 customer support</li>
					<li><i class="fas fa-mobile-alt"></i> Mobile-friendly interface</li>
					<li><i class="fas fa-sync"></i> Instant updates & notifications</li>
				</ul>
			</div>
		</div>
	</div>
</div>
</body>

<?php $this->load->view('auth/layout_footer');
