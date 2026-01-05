<?php $this->load->view('common/fbc-user/header'); ?>   
    <main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<ul class="nav nav-pills">
    <li ><a href="<?php echo base_url() ?>webshop/shipping-charges">Shipping Charges</a></li>
    <li><a href="<?php echo base_url() ?>webshop/createnew-shipping-charges">Create New</a></li>
  </ul>

  <div class="tab-content">
    <div id="create-new" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name pad-bt-20">Shipping Charges- Edit</h1> 
        </div><!-- d-flex -->
		
		
        <!-- form -->  
        <form action="<?php echo base_url(); ?>ShippingChargesController/save_shipping_charges" method="POST" name="createnew_shipping_charge_form" id="createnew_shipping_charge_form" >
			<div class="customize-add-section">
				<div class="row">
				<div class="left-form-sec coupon-code-select">
					<div class="col-sm-6 customize-add-inner-sec">
						<label>Shipping Code</label>
						<input type="hidden" name="shipping_charge_id" id="shipping_charge_id" value="<?php if(isset($shipping_charges_master_by_id->id) && $shipping_charges_master_by_id->id !='') echo $shipping_charges_master_by_id->id; ?>">
						<input class="form-control" type="text" name="shipping_code" id="shipping_code" value="<?php if(isset($shipping_charges_master_by_id->shipping_code) && $shipping_charges_master_by_id->shipping_code !='') echo $shipping_charges_master_by_id->shipping_code; ?>" placeholder="Shipping Code" required="required" readonly>
					</div><!-- col-sm-6 -->
					
					<div class="col-sm-6 customize-add-inner-sec page-content-textarea">
						<label>Description</label>
						<textarea class="form-control" name="descp" id="descp" placeholder="Content area"><?php if(isset($shipping_charges_master_by_id->shipping_details) && $shipping_charges_master_by_id->shipping_details !='') echo $shipping_charges_master_by_id->shipping_details; ?></textarea>
					</div><!-- col-sm-6 -->
					
				</div>
				
				
				<div class="right-form-sec coupon-code-select">										
					<div class="col-sm-6 customize-add-inner-sec">
						<label>Status</label>
						<select class="form-control" name="status" required="required" id="status">
							<!-- <option value="">--Please Select--</option> -->
							<option <?php  if(isset($shipping_charges_master_by_id->status) && $shipping_charges_master_by_id->status !='' && $shipping_charges_master_by_id->status ==1 ) echo 'selected'; ?>  value="1">Active</option>
							<option <?php  if(isset($shipping_charges_master_by_id->status) && $shipping_charges_master_by_id->status !='' && $shipping_charges_master_by_id->status ==0 ) echo 'selected'; ?>  value="0">In Active</option>
						</select>
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 customize-add-inner-sec">
						<label>Apply To</label>
						<select class="form-control" name="customer_type" required="required" id="customer_type">
							<option value="">--Please Select--</option>
							<?php 
							// print_r($new_customer_type_details);
							if(count($new_customer_type_details) > 1 )
							{ 
							   foreach ($new_customer_type_details as $value) { ?>
							<option <?php  if(isset($shipping_charges_master_by_id->customer_type_id) && $shipping_charges_master_by_id->customer_type_id !='' && $shipping_charges_master_by_id->customer_type_id == $value['id'] ) echo 'selected'; ?>  value="<?php echo $value['id'] ; ?>"><?php echo $value['name'] ;?></option>
							<?php } 
						}else{ ?>
							<option   value="<?php print_r($new_customer_type_details[0]['id']) ; ?>" selected readonly><?php print_r($new_customer_type_details[0]['name']) ;?></option>
							<?php 
						} 
						?>
						</select>
					</div><!-- col-sm-6 -->


				</div>
				
				</div><!-- row -->


				
				<div class="shipping-charges">
					<h6>Shipping Charges</h6>
					<div class="col-sm-12">
					<div class="row">
					<div class="col-sm-5">
						<div class="boxed-shadow-part">
							<p><label class="checkbox">
								<input type="checkbox" <?php  if(isset($shipping_charges_master_by_id->cart_cost_flag) && $shipping_charges_master_by_id->cart_cost_flag !='' && $shipping_charges_master_by_id->cart_cost_flag == 1 ) echo 'checked'; ?> name="based_on_cart" id="based_on_cart" class="form-control" value="1"><span class="checked"></span><b>Based on Cart Cost</b></label></p>
							<p class="mb-0"><label> <span class="charge-text">  Charge </span> <span class="country-currency"><?php //echo $currency_symbol; ?></span> 
								<input class="form-control charges-field" name="charge" id="charge" type="text" pattern="^\d*(\.\d{0,2})?$" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00"  value="<?php if(isset($shipping_charges_master_by_id->cart_cost_charge) && $shipping_charges_master_by_id->cart_cost_charge !='') echo $shipping_charges_master_by_id->cart_cost_charge; ?>"> </label> </p>
							<p class="ship-free"><label class="checkbox">
								<input type="checkbox" 
								<?php  if(isset($shipping_charges_master_by_id->cart_cost_freeship_flag) && $shipping_charges_master_by_id->cart_cost_freeship_flag !='' && $shipping_charges_master_by_id->cart_cost_freeship_flag == 1 ) echo 'checked'; ?> name="free_shipping" id="free_shipping" class="form-control" value="1"><span class="checked"></span>Free Shipping Charge Over</label> <span class="country-currency"><?php //echo $currency_symbol; ?></span>
								<input class="form-control charges-field" name="free_shipping_charge" id="free_shipping_charge" type="text" pattern="^\d*(\.\d{0,2})?$" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00" value="<?php if(isset($shipping_charges_master_by_id->cart_cost_freeship) && $shipping_charges_master_by_id->cart_cost_freeship !='') echo $shipping_charges_master_by_id->cart_cost_freeship; ?>"></p>
						</div><!-- boxed-shadow-part -->


						<div class="boxed-shadow-part">
							<p><label class="checkbox">
								<input type="checkbox" <?php  if(isset($shipping_charges_master_by_id->shipping_country_flag) && $shipping_charges_master_by_id->shipping_country_flag !='' && $shipping_charges_master_by_id->shipping_country_flag == 1 ) echo 'checked'; ?> name="based_on_country" id="based_on_country" class="form-control" value="1"><span class="checked"></span><b>Based on Country</b></label></p>
							<p class="mb-0"><label> <span class="charge-text">  Charge In <?php //echo $country_name; ?> </span> <span class="country-currency"><?php //echo $currency_symbol; ?></span>
							<input class="form-control charges-field" type="text" pattern="^\d*(\.\d{0,2})?$" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00"  name="charge_in_own_country" id="charge_in_own_country" value="<?php if(isset($shipping_charges_master_by_id->charge_in_own_webshop_country) && $shipping_charges_master_by_id->charge_in_own_webshop_country !='') echo $shipping_charges_master_by_id->charge_in_own_webshop_country; ?>"></label> </p>
							<p><label> <span class="charge-text">  Charge Outside <?php //echo $country_name; ?> </span> <span class="country-currency"><?php //echo $currency_symbol; ?></span>  <input class="form-control charges-field" type="text" pattern="^\d*(\.\d{0,2})?$" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00"  name="charge_in_other_country" id="charge_in_other_country" value="<?php if(isset($shipping_charges_master_by_id->charge_in_other_country) && $shipping_charges_master_by_id->charge_in_other_country !='') echo $shipping_charges_master_by_id->charge_in_other_country; ?>"> </label> </p>
						</div><!-- boxed-shadow-part -->


					</div><!-- col-sm-5 -->

					<div class="col-sm-7">
						<div class="boxed-shadow-part">
							<p><label class="checkbox">
								<input type="checkbox" <?php  if(isset($shipping_charges_master_by_id->cart_weight_flag) && $shipping_charges_master_by_id->cart_weight_flag !='' && $shipping_charges_master_by_id->cart_weight_flag == 1 ) echo 'checked'; ?> name="based_on_cart_weight" id="based_on_cart_weight" class="form-control" value="1"><span class="checked"></span><b>Based on Cart Weight</b></label></p>
					<?php
					 if(isset($shipping_charges_master_weight_by_id) && $shipping_charges_master_by_id !='' )
					 {
					 	$row_count= $num_rows; ?>
					<?php foreach ($shipping_charges_master_weight_by_id as  $single_row) { ?>
						
					
							<p  class="charge-p"><span class="charge-text"> Charge </span> <span class="country-currency"><?php //echo $currency_symbol; ?></span>  
								<input type="hidden" name="row_count[]" value="<?= $row_count ?>">
								<input class="form-control charges-field" name="charge_on_cart_weight[]"  type="text" pattern="^\d*(\.\d{0,2})?$" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00"  value="<?php if(isset($single_row->charge) && $single_row->charge !='') echo $single_row->charge; ?>"> 
							<span class="for-to">
							<span class="align-center charge-text">for</span>  
							<input class="form-control charges-field" type="text"  name="min_weight[]" pattern="^\d*(\.\d{0,2})?$" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00"  value="<?php if(isset($single_row->from_weight) && $single_row->from_weight !='') echo $single_row->from_weight; ?>">
							<span class="kg">Kg</span>
							</span>

							<span class="for-to">
							<span class="align-center charge-text"> To </span> 
							<input class="form-control charges-field" type="text" pattern="^\d*(\.\d{0,2})?$" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00"  name="max_weight[]"  value="<?php if(isset($single_row->to_weight) && $single_row->to_weight !='') echo $single_row->to_weight; ?>"><span class="kg">Kg</span>
							</span>
							<span class="for-to">
							<button type="button" class="delete-btn float-right " name="delete_weight_btn" id="delete_weight_btn" onclick="myFunction(this)" data-toggle="modal" data-target="#deleteModalForRow"  value="<?php if(isset($single_row->id) && $single_row->id !='') echo $single_row->id; ?>"><i class="fas fa-trash-alt"></i></button>
							</span>
							</p>

					<?php } 
					 } else
					 {
					 	$row_count= 1; ?>
							<p  class="charge-p"><span class="charge-text"> Charge </span> <span class="country-currency"><?php echo $currency_symbol; ?></span>  
								<input type="hidden" name="row_count[]" value="<?= $row_count ?>">
								<input class="form-control charges-field" name="charge_on_cart_weight[]"  type="text" pattern="^\d*(\.\d{0,2})?$" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00"  value="<?php if(isset($single_row->charge) && $single_row->charge !='') echo $single_row->charge; ?>"> 
							<span class="for-to">
							<span class="align-center charge-text">for</span>  
							<input class="form-control charges-field" type="text"  name="min_weight[]"  pattern="^\d*(\.\d{0,2})?$" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00"  value="">
							<span class="kg">Kg</span>
							</span>

							<span class="for-to">
							<span class="align-center charge-text"> To </span> 
							<input class="form-control charges-field" type="text" pattern="^\d*(\.\d{0,2})?$" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00"  name="max_weight[]"  value=""><span class="kg">Kg</span>
							</span>
							<!-- <span class="for-to">
							<button type="button" class="delete-btn float-right " data-toggle="modal" data-target="#deleteModalForRow" data-id="1"><i class="fas fa-trash-alt"></i> Delete</button>
							</span> -->
							</p>

					<?php  } ?>
					
							<div id="AddRowDiv"></div>

							<span class="product-variant-button text-left" id=""><button type="button" id="add_new_row"> + &nbsp;   Add Field</button> </span>
							
						</div><!-- boxed-shadow-part -->
					</div><!-- col-sm-7 --> 
					</div>

					</div>


					<!-- <div class="row">
						<div class="col-sm-7">
						<div class="boxed-shadow-part">
							<p><label class="checkbox"><input type="checkbox" name="" class="form-control" value=""><span class="checked"></span><b>Based on Product Category</b></label></p>
							<p><label class="display-b"> <span class="charge-text">  Select Category </span> 
							<select class="form-control col-sm-7"><option>Footware</option></select> </label> </p>
							<p class="ship-free mb-0"><label><span class="charge-text">  Shipping Charge </span> <span class="country-currency">₹</span> <input class="form-control charges-field" type="text" value="50"></label></p>
							<span class="product-variant-button text-left" id=""><button type="button"> + &nbsp;   Add another category</button> </span>
						</div> --><!-- boxed-shadow-part -->
						
					<!-- </div> --><!-- col-sm-4 -->

					<!-- </div> --><!-- row -->


						
					</div><!-- shipping-charges -->


			</div><!-- customize-add-section -->
		
		
		
			
			<div class="download-discard-small mar-top">
				<button class="white-btn" type="button" data-toggle="modal" data-target="#deleteModal" data-id="<?php if(isset($shipping_charges_master_by_id->id) && $shipping_charges_master_by_id->id !='') echo $shipping_charges_master_by_id->id; ?>">Delete</button>
				<button class="download-btn" name="save_new" value="Save" id="save_new" type="submit">Save</button>

			 </div><!-- download-discard-small  -->
			
        <!--end form-->
        </form>
    </div>
	
	

  </div>
        

    </main>
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form id="shippingChargeDeleteForm" method="POST" action="<?= base_url('ShippingChargesController/delete_shipping_charge')?>">
					<input type="hidden" name="shipping_charge_id" id="shipping_charge_id" value="<?php if(isset($shipping_charges_master_by_id->id) && $shipping_charges_master_by_id->id !='') echo $shipping_charges_master_by_id->id; ?>">
					<div class="modal-header">
						<h1 class="head-name">Delete Shipping Charge ?</h1>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-footer">
						<button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">No</button>
						<button type="submit" class="purple-btn">Delete</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade" id="deleteModalForRow" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form id="deleteModalForRowForm" method="POST" action="<?= base_url('ShippingChargesController/delete_cart_weight')?>">
					<input type="hidden" name="row_id" id="row_id" value="">
					<div class="modal-header">
						<h1 class="head-name">Delete This Row ?</h1>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-footer">
						<button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">No</button>
						<button type="submit" class="purple-btn">Delete</button>
					</div>
				</form>
			</div>
		</div>
	</div>

<script type="text/javascript">
	var currency_symbol = '<?php echo $currency_symbol; ?>';
	var row_count= <?php echo $row_count ; ?>;
	var i= row_count + 1;
	$("#add_new_row").on("click", () => {
        let appendRow = 

					'<p  class="charge-p">'+
					'<span class="charge-text"> Charge </span>'+
					'<span class="country-currency">'+currency_symbol+'</span>'+
					'<input type="hidden" name="row_count[]" value="'+i+'">'+
					'<input class="form-control charges-field" name="charge_on_cart_weight[]" id="charge_on_cart_weight" type="text" pattern="[0-9]+.?[0-9]?[0-9]?" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00" value="">'+ 
					'<span class="for-to">'+
					'<span class="align-center charge-text">for</span>'+
					'<input class="form-control charges-field" type="text"  name="min_weight[]" id="min_weight" pattern="[0-9]+.?[0-9]?[0-9]?" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00" value="">'+
					'<span class="kg">Kg</span>'+
					'</span>'+

					'<span class="for-to">'+
					'<span class="align-center charge-text"> To </span>'+
					'<input class="form-control charges-field" type="text" pattern="[0-9]+.?[0-9]?[0-9]?" step="0.01" title="Only Numbers Allowed. eg:-99 or 99.00" name="max_weight[]" id="max_weight" value=""><span class="kg">Kg</span>'+
					'</span>'+
					'</p>';


            
        $("#AddRowDiv").append(appendRow);
        i++;
        });
</script>
<script src="<?php echo SKIN_JS; ?>shipping_charges.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>