<form class="form-signin form-style" id="reset-password">

	<div class="modal-header">
		<h4 class="head-name">Password Reset</h4>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">×</span>
		</button>

	</div>
	<div class="modal-body">
		<!-- <p class="are-sure-message">Variants</p> -->
		<div class="row">
			<div class="form-fields text-left">
				<div class="mb-5">
					<label for="password" class="">Password <span class="required">*</span></label>
					<input type="password" id="inputPassword" name="inputPassword" class="form-control" required>
					<span class="eye-password toggle-password"></span>
					<div id="message" style=" display:none;">
						<p><strong>Password must contain the following:</strong></p>
						<p id="alphabetic" class="invalid">One <b>alphabetic</b> char</p>
						<p id="special" class="invalid">One <b>special</b> char</p>
						<p id="number" class="invalid">One <b>number</b></p>
						<p id="length" class="invalid">Minimum <b>8 characters</b></p>
					</div>
					<div id="error_message1">
						<span class="text-danger">This field is required</span>
					</div>
				</div>
				<div class="mb-5">
					<label for="inputPassword" class="">Confirm Password <span class="required">*</span></label>
					<input type="password" id="inputConfPassword" name="inputConfPassword" class="form-control" required>
					<span class="eye-password toggle-Confpassword"></span>
					<div id="error_message2">
						<span class="text-danger">This field is required</span>
					</div>
				</div>

			</div>
		</div>
	</div>
	<div class="modal-footer">
		<input class="purple-btn" type="button" id="reset-pass-btn" name="reset-pass-btn" value="Submit" onclick="Onsubmit()">
	</div>
</form>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>reset_password.js"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>publisher.js"></script>

