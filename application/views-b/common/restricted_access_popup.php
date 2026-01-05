<div class="popup-form restricted-popup">
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">×</span>
</button>
	<div class="">
		<!-- <h3>Restricted Access </h3> -->
		<h3><?php echo $msg_for_customer; ?></h3>
		<div class="signin-btn">
			<button type="button" class="black-btn blue-btn" onClick="window.location.reload();" data-dismiss="modal" ><?=lang('close')?></button>
		</div><!-- signin-btn -->
	</div><!-- sign-in-inner -->
</div><!-- grey-bg-user -->
<script src="<?php echo SKIN_JS ?>navbar.js?v=<?php echo CSSJS_VERSION; ?>"></script>