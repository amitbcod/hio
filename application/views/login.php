<?php $this->load->view('common/header'); ?>
<div class="section sign-page">
	<div class="container">
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
				<div class="content-page shadow margbot20 p-3">
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active">
							<a href="#login" aria-controls="login" role="tab" data-toggle="tab" aria-expanded="true">
								<?= $this->lang->line('login'); ?>
							</a>
						</li>
						<li role="presentation">
							<a href="#register" aria-controls="register" role="tab" data-toggle="tab" aria-expanded="false">
								<?= $this->lang->line('register'); ?>
							</a>
						</li>
					</ul>

					<div class="tab-content">
						<!-- Login Tab -->
						<div role="tabpanel" class="tab-pane fade active in" id="login">
							<h4><?= $this->lang->line('existing_users'); ?></h4>
							<form id="sigin-form" method="POST" action="<?php echo BASE_URL; ?>customer/login" style="margin-top:10px;">
								<?php (new Login('login'))->render(); ?>
							</form>
						</div>

						<!-- Register Tab -->
						<div role="tabpanel" class="tab-pane fade" id="register">
							<h4><?= $this->lang->line('new_users'); ?></h4>
							<h4><?= $this->lang->line('fill_all_fields'); ?></h4>
							<form id="signup-form" method="POST" action="<?php echo BASE_URL; ?>customer/register" style="margin-top:10px;">
								<?php (new Register('register'))->render(); ?>
							</form>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view('common/footer'); ?>
<script src="<?php echo SKIN_JS ?>login.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script src="<?php echo SKIN_JS ?>register1.js?v=<?php echo CSSJS_VERSION; ?>"></script>
</body>
</html>
