<?php $this->load->view('common/fbc-user/header'); ?>   
    <main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<ul class="nav nav-pills">
    <li class="active"><a data-toggle="pill" href="#shipping-charges">Shipping Charges</a></li>
    <li ><a href="<?php echo base_url() ?>webshop/createnew-shipping-charges">Create New</a></li>
  </ul>

  <div class="tab-content">
    <div id="shipping-charges" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">


	<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name pad-bt-20">Shipping Charges </h1> 
        </div><!-- d-flex -->
		
		
        <!-- form --> 
        <form id="shipping_charges_form" name="shipping_charges_form" action="<?php echo base_url() ?>ShippingChargesController/submit_shipping_charges" method="POST">
			<div class="customize-add-section">
				<div class="row">
				<div class="left-form-sec coupon-code-select">
					<div class="col-sm-6 customize-add-inner-sec">
						<label>Select tax type :</label>
						<select class="form-control" id="tax_type" name="tax_type">
						<option value="0">-- Please Select -- </option> 
						<option <?php if(isset($current_shipping_charge->shipping_tax_type) && $current_shipping_charge->shipping_tax_type ==1) echo 'selected'; ?> value="1">Highest percentage of in cart </option> 
						<option <?php if(isset($current_shipping_charge->shipping_tax_type) && $current_shipping_charge->shipping_tax_type ==2) echo 'selected'; ?> value="2">Lowest percentage of in cart </option> 
						<option <?php if(isset($current_shipping_charge->shipping_tax_type) && $current_shipping_charge->shipping_tax_type ==3) echo 'selected'; ?> value="3">Fix percentage on the cart  </option>
						</select>
					</div><!-- col-sm-6 -->
					<div class="col-sm-6 customize-add-inner-sec add-percentage "
          
         style='display:none;' 
            id="add_percentage_div">
						<label>Add percentage :</label>
						<input class="form-control" type="number" name="tax_fix_percentage"  id="tax_fix_percentage" value="<?php if(isset($current_shipping_charge->shipping_tax_fix_percentage) && $current_shipping_charge->shipping_tax_fix_percentage !=0.00) echo $current_shipping_charge->shipping_tax_fix_percentage; ?>" placeholder="Percentage ">&nbsp; <span class="Percentage-span"> % </span>
					</div><!-- col-sm-6 -->
					
				</div>
				<div class="col-sm-12">
		              <div class="download-discard-small">
		                <button class="download-btn pull-right"  value="save" name="save_shipping_charges" id="save_shipping_charges" type="submit">Save</button>
		              </div>
		            </div>
				</div><!-- row -->

			</div><!-- customize-add-section -->
		</form>





      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Shipping Charges </h1> 
        </div>

		
        <!-- form -->
        <div class="content-main form-dashboard">
            <form>

              <div class="table-responsive text-center">
                <table class="table table-bordered table-style" id="shipping_charges_table">
                  <thead>
                    <tr>
                      <th>Shipping Code </th>
                      <th>Customer Type </th>
                      <th>Created On </th>
          					  <th>Status </th>
          					  <th>Details </th>
                    </tr>
                  </thead>
                  <tbody>
            <?php if(isset($shipping_charges_info) && $shipping_charges_info!='')
            {
             foreach ($shipping_charges_info as  $value) { ?>
                    <tr>
                      <td><?php echo $value['shipping_code']; ?></td>
                      <td><?php $customer_type= $this->CustomerModel->get_single_customer_type_details($value['customer_type_id']);
                      print_r($customer_type['name']);
                      ?></td>
                      <td><?php echo date("d/m/Y" ,$value['created_at']);?></td>
                      <td><?php if($value['status'] ==1) echo "Active"; else echo "In Active" ; ?></td>
                      <td><a class="link-purple" href="<?php echo base_url(); ?>webshop/edit-shipping-charge/<?php echo $value['id'];?>">View</a></td>
                    </tr>
           
            <?php  }
             } ?>
                  </tbody>
                </table>
              </div>

            </form>
        </div>
        <!--end form-->
    </div>

  </div>
        

    </main>
<script src="<?php echo SKIN_JS; ?>shipping_charges.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>