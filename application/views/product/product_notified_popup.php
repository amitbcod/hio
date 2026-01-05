<div class="popup-form">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">×</span>
	</button>
	<div class="sign-in-inner">
		<h3>Keep Me Notified </h3>
		<div class="forgotpassword-form ">
			<div class="form-box">
				<label>You have been successfully subscribed to updates of this product.<br><br>Keep me informed about other Zumba Wear news and promotions:</label>
			</div>
			<div class="signin-btn">
				<input type="hidden" name="nitified_subscribe_email" id="nitified_subscribe_email" value="<?=$email?>">
				<!-- <button class="btn keep-me-notified-subscribe" type="button" onclick="openNotifiedSubscribe('<?php echo $email; ?>')" >You have been successfully subscribed to updates of this product.<br><br>Keep me informed about other Zumba Wear news and promotions:
</button> -->
			</div><!-- signin-btn -->
		</div>
	</div><!-- sign-in-inner -->
</div><!-- grey-bg-user -->

<script type="text/javascript" src="<?php echo SKIN_JS; ?>product_notified_popup.js?v=<?php echo CSSJS_VERSION; ?>"></script>