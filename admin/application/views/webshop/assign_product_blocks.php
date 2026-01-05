<?php $this->load->view('common/fbc-user/header'); ?>



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<ul class="nav nav-pills">

    	<!-- <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->
    	<li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>

    	<li><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>

		<li><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>

		<li><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>

		<li class="active"><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
		<li class=""><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>
		

  	</ul>

	

  	<div class="tab-content">

		<div id="customize-tab" class="tab-pane fade in active common-tab-section min-height-480" style="opacity:1;">

	

      		<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

          		<h1 class="head-name pad-bt-20"><?php if(isset($pBlock)){ echo $pBlock->block_name; } ?></h1> 

          	</div>

		

			<?php //print_r($selectedProductList); ?>

	        <!-- form -->

	        <div class="content-main form-dashboard">

            	<form method="POST" action="<?= base_url('WebshopController/submitProductBlock') ?>" id="productBlockForm">

            		<input type="hidden" name="product_master_id" value="<?= isset($pBlock) ? $pBlock->id : '' ?>">

            		<input type="hidden" name="product_block_id" value="<?= isset($selectedProductList) ? $selectedProductList->pb_master_id : '' ?>">

            		<div class="customize-add-section">

						<div class="row">

							<div class="col-sm-6">

								<div class="customize-add-inner-sec">

									<label for="productList">Product *</label>

									<select name="productList[]" id="productList" multiple class="form-control" required="">

										<!--<option value="">Select</option>-->

										<?php if(isset($productList) && count($productList)>0){

													if(isset($selectedProductList) && $selectedProductList > 0) {

														$products = substr($selectedProductList->assigned_products,1,-1);

														$products_arr = explode(",",$products);										

													}

																

											foreach($productList as $val){

											?>

											<?php

												$selected = '';

												if(isset($selectedProductList)) {										

													if(in_array($val->id, $products_arr)) {

														$selected = 'selected="selected"';

													}

												} ?>

										<option value="<?php echo  $val->id; ?>" <?php echo $selected; ?>><?php echo  $val->name.' - '.$val->product_code.' - '.date("d-m-Y", $val->launch_date); ?></option>

										<?php } 

										} ?>

									</select>

								</div>

								

								<div class="download-discard-small ">

									<button class="white-btn" type="button" onclick="window.location.href='<?= base_url('webshop/product-blocks') ?>';">Discard</button>
								<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
									<button class="download-btn" type="submit">Save</button>
								<?php } ?>

								 </div><!-- download-discard-small  -->

							</div>

						</div><!-- row -->

					</div><!-- customize-add-section -->

            	</form>

        	</div>

        	<!--end form-->

    	</div>

    </div>

</main>

<script type="text/javascript">

jQuery('#productList').multiselect({

	placeholder: 'Select Product',

	search: true

});	



$("#productBlockForm").validate({     

	//ignore: ".ignore",

	ignore: ':hidden:not("#productList")',

	rules: {

		"productList[]": { required: true }, 

	},

	submitHandler: function(form) {

		$.ajax({

			url: form.action,

			type: 'ajax',

			method: form.method,

			dataType: 'json',

			data: $(form).serialize(),

			success: function(response) {

				if (response.flag == 1) {

					swal({ title: "",text: response.msg, button: false, icon: 'success' })

					setTimeout(function() { window.location.href = response.redirect }, 1000);

				} else {

					swal({ title: "",text: response.msg, button: false, icon: 'error' })

					return false;

				}

			}

		});

	}

});

</script>

<script src="<?php echo SKIN_JS; ?>webshop.js"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>