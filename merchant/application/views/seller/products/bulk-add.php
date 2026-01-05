<?php $this->load->view('common/fbc-user/header'); ?>




<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('seller/products/breadcrums'); ?>
	<div class="tab-content">
<div id="Warehouse" class="tab-pane fade active show">

	<div class="product-details-block">
		<div class="row">
		<div class="add-bulk-block">

		<input type="hidden" value="bulk-add" id="current_page" name="current_page">
		<input type="hidden" value="" id="pid" name="pid">

		<div class="add-bulk-inner1">
		<h1 class="head-name">ADD BULK PRODUCT</h1>

		 <div class="save-discard-btn upload-csv">
		<?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
			<button class="purple-btn" type="button" onclick="OpenBulkUploadPopup();">Upload CSV</button>
		<?php } ?>
			<button class="white-btn"  type="button" onclick="OpenBulkSelectCategory('import');">Download CSV</button>
			<button class="white-btn"  type="button" onclick="OpenDownloadAll();">Download All</button>
		 </div>
		 </div>
		 <!-- add-bulk-inner1 -->


		 </div> <!-- add-bulk-block -->

	</div><!-- row -->
	</div><!-- product-details-block -->


	

    </div>

	<?php  $this->load->view('seller/products/add_new_type'); ?>
	</div>
</main>
<script src="<?php echo SKIN_JS; ?>seller_product_common.js"></script>
<script>

$(document).ready(function() {
//swal("Good job!", "You clicked the button!", "success");
});
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
