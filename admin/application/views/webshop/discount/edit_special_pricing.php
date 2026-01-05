<?php $this->load->view('common/fbc-user/header'); ?>
    <main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
<?php $this->load->view('webshop/discount/breadcrums');?>

  <div class="tab-content">
    <div id="Special Pricing" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">

        <!-- form -->            
        <form action="<?php echo base_url(); ?>WebshopController/update_special_pricing" method="POST" name="special_pricing_form" id="special_pricing_form">
			<div class="customize-add-section pad-t-20">
				<div class="row">
				<div class="left-form-sec">
					<input type="hidden" name="special_price_id" id="special_price_id" value="<?php echo $special_price_id; ?>">
					<div class="col-sm-6 customize-add-inner-sec">
						<select class="form-control" name="product_id" required readonly  id="product_id">
							<!-- <option value="">Select Product</option> -->
							<?php foreach ($product_details as $product) { ?>
								<option value="<?php echo $product->product_id ;?>" selected  ><?php echo $product->sku .' '.$product->name.' '; ?><?php foreach ($product->variant as $value) { 
			                	echo $value['attr_name'].' : '.$value['attr_options_name'].'<br/>';
		              		} ?> </option>
							<?php } ?>
						
						</select>
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 customize-add-inner-sec">
						<label >Webshop Price :(<span class="symbol">&#x20b9;</span>)</label>
						<input type="text" class="form-control" readonly name="webshop_price" value="<?php echo $product_details[0]->webshop_price ?>" id="webshop_price">
					</div><!-- col-sm-6 -->
					

					<div class="col-sm-6 customize-add-inner-sec  from-to">
						<label>From</label>
						<input class="form-control" type="date" required  name="from_date" id="from_date" value="<?php echo date('Y-m-d',$product_details[0]->special_price_from) ; ?>" placeholder="">
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 customize-add-inner-sec display-original">
						<label>Display Original</label>
						<div class="switch-onoff">
						<label class="checkbox">
						<input type="checkbox" name="display_original" value="<?php echo $product_details[0]->display_original; ?>" id="display_original" autocomplete="off" <?php 
						if($product_details[0]->display_original == 1) {echo "checked"; } ;?>> 
							<span class="checked"></span>
						</label>
						</div>
					</div><!-- col-sm-6 -->
					
				</div>
				
				
				<div class="right-form-sec coupon-code-select">										
					<div class="col-sm-6 customize-add-inner-sec">
						&nbsp;
					</div><!-- col-sm-6 -->

						<div class="col-sm-6 customize-add-inner-sec">
						<label>Special Price :(<span class="symbol">&#x20b9;</span>)</label>
						<input class="form-control" type="text" name="special_price" step="0.01" id="special_price" value="<?php echo $product_details[0]->special_price ?>" required placeholder="Special Price">
					</div><!-- col-sm-6 -->



				

					<div class="col-sm-6 customize-add-inner-sec from-to">
						<label>To</label>
						<input class="form-control" type="date" required  name="to_date" id="to_date" value="<?php echo date('Y-m-d',$product_details[0]->special_price_to) ; ?>" placeholder="">
					</div><!-- col-sm-6 -->


				</div>
				
				</div><!-- row -->
			</div><!-- customize-add-section -->
		
		
			
				<div class="download-discard-small mar-top">
					<button class="white-btn" name="discard_btn" id="discard_btn" onclick="gotoLocation('<?= $special_pricing_link; ?>');">Discard</button>
				<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/discounts/write',$this->session->userdata('userPermission'))){ ?> 
					<button class="download-btn" name="save_special_pricing" id="save_special_pricing" value="save" >Save</button>
				<?php } ?>

			 	</div><!-- download-discard-small  -->
		</form>
        <!--end form-->
    </div>
	
	

  </div>
        
        

    </main>
   <script src="<?php echo SKIN_JS; ?>special_pricing.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>