<div id="addnew" class="tab-pane fade addnew-bg">
	<div class="row add-new-section">
		<div class="col-md-6">
			<h3>Add Bulk Product</h3>
			<button class="puple-btn" type="button" onclick="gotoLocation('<?php echo base_url(); ?>seller/product/bulk-add');">Continue</button>
		</div>
		<div class="col-md-6">
			<h3>Add Single Product</h3>
			<?php if (empty($this->session->userdata('userPermission')) || in_array('seller/database/write', $this->session->userdata('userPermission'))) { ?>
			<?php } ?>
			<button type="button" class="puple-btn" onclick="gotoLocation('<?php echo base_url(); ?>seller/product/add');">Continue</button>
		</div>
		<!-- <div class="col-md-6">
			<h3>Add Bundle Product</h3>
			<button class="puple-btn" type="button" onclick="gotoLocation('<?php echo base_url(); ?>seller/product/add?type=bundle');">Continue</button>
		</div> -->
	</div>
	<!-- row -->
</div>