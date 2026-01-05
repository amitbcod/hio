<?php $this->load->view('common/header'); ?>
    <div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="<?php echo base_url(); ?>"><?=lang('home')?></a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active"><?=lang('order_details')?></li>
				</ul>
			</div>
        </div>
      </div><!-- breadcrum section -->
    <div class="my-profile-page-full">
      <div class="container">
          <div class="row">
		  	<?php (new Orders())->list('guest'); 	?>	
          </div><!-- row -->
      </div><!-- container -->
    </div><!-- my-profile-page-full -->


<div id="cancel-order-modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?=lang('cancel_order')?></h4>
      </div>
      <div class="modal-body">
       		<h5><?=lang('are_you_sure_you_want_to_cancel_this_order')?></h5>
					<form id="cancel-guest-order-form" method="POST" action="<?php echo BASE_URL;?>MyGuestOrdersController/cancelOrder">
					<div class="cancel-order-form">
						<div class="form-box">
							<textarea class="form-control" name="cancel_reason" id="cancel_reason" placeholder="<?=lang('reason_for_cancellation')?>" required="required"></textarea>
							<input type="hidden" id="order_id" name="order_id" value="">
						</div><!-- form-box -->


						<div class="signin-btn">
							<input type="submit" class="black-btn blue-btn" name="submit" id="submit_cancel_order" value="<?=lang('confirm')?>">
							<input type="button" class="black-btn blue-btn" data-dismiss="modal" name="cancel" id="cancel" value="<?=lang('cancel')?>">
						</div><!-- signin-btn -->
					</div><!-- sigin-form -->
					</form>
      </div>
    </div>

  </div>
</div>
	<?php $this->load->view('common/footer'); ?>
	<script src="<?php echo SKIN_JS ?>myprofile.js?v=<?php echo CSSJS_VERSION; ?>"></script>
	<script src="<?php echo SKIN_JS ?>my_orders_guest.js?v=<?php echo CSSJS_VERSION; ?>"></script>
	</body>
</html>