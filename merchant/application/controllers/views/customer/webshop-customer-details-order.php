<?php $this->load->view('common/fbc-user/header'); ?>

<?php

    $last=  (count($customer_details) -1);
    if(isset($org_website_address) && $org_website_address != ''){
        $url = $org_website_address."/CustomerController/autologin?email=".base64_encode($customer_details[$last]['email_id']);
    }else{
       // $shop_id =	$this->session->userdata('ShopID');
        $baseurl= base_url();
      //  $webshop_address = getWebsiteUrl($shop_id,$baseurl);
        $url = "/CustomerController/autologin?email=".base64_encode($customer_details[$last]['email_id']);
    }
?>

  <main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <ul class="nav nav-pills">
    <li class="active"><a  href="<?php echo base_url(); ?>customers">Customer Listing</a></li>
  
    <li class=""><a href="<?php echo base_url(); ?>customertype">Customer Type</a></li>
 
  </ul>
        <div class="profile-details busniess-details customer-details">
          <div class="row">
            <div class="col-md-12">
              <h2>Customer  Details - Customer Id : <?php echo $customer_id; ?>

             
                <?php if(isset($customer_details) && $customer_details[$last]['status'] == 1) {?>
                    <a href="<?php echo $url ?>" class="white-btn float-right" target="_blank">Login as Customer</a>
                <?php } ?>
          
                    <button class="white-btn float-right" type="button" onclick="OpenEditPersonalInfoPopup(<?php echo  $customer_id ?>);">Edit Personal Info</button></h2>
            
			  </div>
			  <div class="col-md-12">
              <div class="barcode-qty-box row order-details-sec-top">
                <div class="col-sm-6 order-id"><?php $lastrow=  (count($customer_details) -1); ?>
                  <p><span>Customer Name :</span> <?php
         if(isset($customer_details[$lastrow]['first_name']) && $customer_details[$lastrow]['first_name'] !='' && isset($customer_details[$lastrow]['last_name']) && $customer_details[$lastrow]['last_name'] !='')
          { echo $customer_details[$lastrow]['first_name']. ' '.$customer_details[$lastrow]['last_name']; } ?></p>
                  <p><span>Last Purchase Date :</span><?php
                  if(isset($customer_details[$lastrow]['created_at']) && $customer_details[$lastrow]['created_at'] !='')
          { echo date("d/m/Y" ,$customer_details[$lastrow]['created_at']); } ?></p>
                  <p><span>Customer Gender :</span><?php
    if(isset($customer_details[$lastrow]['gender']) && $customer_details[$lastrow]['gender'] !='')
          {       if($customer_details[$lastrow]['gender'])
                  { echo $customer_details[$lastrow]['gender'];}  }
                  else {echo "--";} ?></p>
                  <p><span>Customer DOB :</span><?php
 if(isset($customer_details[$lastrow]['dob']) && $customer_details[$lastrow]['dob'] !='')
          {
                   if($customer_details[$lastrow]['dob'])
                  { echo $customer_details[$lastrow]['dob'];} }
                  else{echo "--";}?></p>
                  <?php if($restricted_access == 'yes') { ?>
                  <p><span>GST No. :</span>
                   <?php
                    if(isset($customer_details[$lastrow]['gst_no']) && $customer_details[$lastrow]['gst_no'] !='')
                    {
                       echo $customer_details[$lastrow]['gst_no'];
                    }
                    else {echo "--";} ?>
                  </p>
                <?php } ?>
                  <p><span>Total purchase Amt :</span> <?php $total_amount=0;
                  for ($i=0; $i <count($customer_order_details) ; $i++)
                  {
                    $total_amount += $customer_order_details[$i]['subtotal'];
                  }echo $currency_symbol.' '.number_format($total_amount);  ?></p>


                </div>
                <div class="col-sm-6 order-id">
                  <p><span>Mobile Number :</span><?php if(isset($customer_details[$lastrow]['mobile_no']) && $customer_details[$lastrow]['mobile_no'] !='')
          {
                    echo $customer_details[$lastrow]['mobile_no'];
                  }else{ echo '--';}?></p>
                  <p><span>Email ID :</span> <?php
                  if(isset($customer_details[$lastrow]['email_id']) && $customer_details[$lastrow]['email_id'] !='')
          { echo lcfirst($customer_details[$lastrow]['email_id']);  } ?> </p>

                  <p><span>Customer Country :</span> <?php if(isset($customer_country) && $customer_country !='')
                  { echo $customer_country;}
                  else{echo "--";}?></p>
        <form  action="<?php echo base_url(); ?>CustomerController/update_customer_detail" method="post">
                  <p class="input-label-field">
          <input type="hidden" name="text_hidden" value="<?php if(isset($customer_details[$lastrow]['id']) && $customer_details[$lastrow]['id'] !='')
                  { echo $customer_details[$lastrow]['id'] ; }?>">
                    <span>Customer Type :</span>
                    <select class="form-control" name="customer_type">
                      <?php foreach ($customer_types as  $customer_type) { ?>
                       <option value="<?php echo $customer_type['id'];  ?>"
                        <?php
                        if(isset($customer_details[$lastrow]['customer_type_id']) && $customer_details[$lastrow]['customer_type_id'] !='')
                  {
                    if($customer_details[$lastrow]['customer_type_id'] == $customer_type['id'] ) echo "selected";  }
                        if($customer_type['id'] == 1 ) echo "disabled";
                          ?>>
                        <?php echo $customer_type['name']; ?></option>
                    <?php  } ?>

                    </select>
                  </p>

                
                  <?php if($restricted_access == 'yes') { ?>
                  <p><span>Company Name :</span>
                   <?php
                    if(isset($customer_details[$lastrow]['company_name']) && $customer_details[$lastrow]['company_name'] !='')
                    {
                       echo $customer_details[$lastrow]['company_name'];
                    }
                    else {echo "--";} ?>
                  </p>
                <?php } ?>
                </div>

                <?php $checked_access = ($customer_details[$lastrow]['access_prelanch_product'])?'checked':''; ?>
				<div class="row col-sm-12 pad-zero">
                    <div class="col-sm-10 pad-zero checkbox-label">
                        <label class="checkbox">
                            <input <?php echo $checked_access;?> type="checkbox" id="access_prelaunch" name="access_prelaunch"> Allow exclusive display and prebooking for Pre-Launch Products <span class="checked" style="width:12px;"></span>
                        </label>
                    </div>
                </div>

        <?php //$checked_catlog_builder = ($customer_details[$lastrow]['allow_catlog_builder'])?'checked':''; ?>
          <div class="row col-sm-12 pad-zero">
            <div class="col-sm-10 pad-zero checkbox-label">
              <label class="checkbox">
                <input <?php  //echo $checked_catlog_builder;?> type="checkbox" id="allow_catlog_builder" name="allow_catlog_builder"> Allow Catlog Builder <span class="checked" style="width:12px;"></span>
              </label>
            </div>
          </div>

				 <div class="col-sm-12 billing-new-add">
					<p><span>Address :</span> </p>
					<div class="billing-new-add-inner row">

            <?php for ($i=0; $i <count($customer_address_book) ; $i++) { ?>
                <div class="col-sm-4">
                    <span class="name">
                        <?php echo $customer_address_book[$i]['first_name']. ' '.$customer_address_book[$i]['last_name'];
                            if($customer_address_book[$i]['is_default'] == 1 ) {?>
                                    <small>&nbsp; (Default) </small>
                        <?php } ?>

                  <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/customers/write',$this->session->userdata('userPermission'))){ ?>
                         <a class="edit-address" onclick="OpenEditAddressPopup(<?php echo  $customer_id ?>,<?php echo  $customer_address_book[$i]['id'] ?>)"><i class="icon-edit"></i> Edit</a>
                         <?php } ?>
                             <!-- <a class="edit-address"  onclick="openAddressPopup()"><i class="icon-edit"></i> Delete</a> -->
                    </span>
                    <p>
                        <span><?php echo $customer_address_book[$i]['mobile_no']; ?></span><br>
                        <?php echo $customer_address_book[$i]['address_line1'].", <br>"; ?>
                        <?php if($customer_address_book[$i]['address_line2'] != NULL){
                            echo $customer_address_book[$i]['address_line2'].", <br>";
                        } ?>
                        <?php echo $customer_address_book[$i]['city']; ?>,<?php echo $customer_address_book[$i]['state']; ?>, <br>
                        <?php echo $customer_address_book[$i]['country_name']; ?> - <?php echo $customer_address_book[$i]['pincode']; ?>
                    </p>
                </div>

           <?php  }  ?>

			
                <div class="save-discard-btn  pd-top-10"> <button class="purple-btn" name="submit" value="submit"> Save </button>
                </div>
           
        </form>



              </div>

              <div class="row sub-tab-style">
                <ul class="nav nav-pills">
                  <!-- <li class="active"> <a data-toggle="pill" href="#b2b-order-and-details">Orders</a></li> -->
                  <li class="active orderClass"><a data-toggle="pill" class="order" id="order" href="#b2b-order-and-details">Orders </a></li>

                  <li class="invoiceClass"><a  class="invoice" id="invoice" href="javascript:void(0);">Invoices</a></li>
          
                  <li class="returnClass"><a  class="return-order" id="return-order" href="#return-order-details">Returns</a></li>

                </ul>
                <div class="tab-content sub-tab-contant-style">
                  <div id="b2b-order-and-details" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">

                    <!-- <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 ">
                      <label>
                        Show
                        <select>
                          <option>7</option>
                        </select>
                      </label>
                      <div class="float-right product-filter-div inner-search">
                        <div class="search-div">				  <input class="form-control form-control-dark top-search" type="text" placeholder="Search or Scan barcode" aria-label="Search">				  <button type="button" class="btn btn-sm search-icon"><i class="fas fa-search"></i></button>			 </div>
                      </div>
                    </div> -->
                    <!-- d-flex -->
                    <div class="table-responsive text-center">
                      <?php include('webshop_customer_order_list.php');?>
                    </div>

                  </div>

                  <div id="return-order-details" class="tab-pane fade common-tab-section d-none" style="opacity:1; display:block;">

                      <div class="table-responsive text-center">
                        <?php include('webshop_customer_return_list.php');?>
                      </div>

                  </div>

                  <?php
                    // if(isset($this->CommonModel->page_access()->acc_inv_flag) && $this->CommonModel->page_access()->acc_inv_flag==1){

                    //   if(isset($InvoiceList)){
                    //     $invoice_type=$InvoiceList->invoice_type;
                    //     $inv_daily_max_inv_amt=$InvoiceList->inv_daily_max_inv_amt;
                    //     $inv_weekly_max_inv_amt=$InvoiceList->inv_weekly_max_inv_amt;
                    //     $inv_monthly_max_inv_amt=$InvoiceList->inv_monthly_max_inv_amt;
                    //     $invoice_to=$InvoiceList->invoice_to_type;
                    //     $alternative_email_id=$InvoiceList->alternative_email_id;
                    //     $payment_term=$InvoiceList->payment_term;
                    //   }else{
                        $invoice_type='1';
                        $invoice_to='1';
                        $inv_daily_max_inv_amt='';
                        $inv_weekly_max_inv_amt='';
                        $inv_monthly_max_inv_amt='';
                        $inv_monthly_max_inv_amt='';
                        $alternative_email_id='';
                        $payment_term=0;
                    // }

                    // if(empty($alternative_email_id)|| $alternative_email_id==''){
                    //   $alternative_email_id=$webshopcust_def_inv_altemail->value;
                    // }

                  ?>
                  <!-- Invoice -->
                  <div id="b2b-order-and-invoices" class="tab-pane fade common-tab-section d-none" style="opacity:1; display:block;">
                      <form  id="WebCustomerInvoiceForm" method="POST" action="<?php echo base_url('CustomerController/postwebshopCustomerInvoice') ?>">
                        <div class="row b2b-invoicing-sec">
                          <div class="order-listing-head"><h2 class="bank-head no-underline">Invoicing Options</h2></div>   <div class="col-sm-6">
                            <div class="radio">
                              <label><input type="radio" name="invoice" <?php echo ($invoice_type=='1') ? 'checked="checked"':'';?> value="1">Invoice per Order <span class="checkmark"></span></label>
                            </div>
                          </div>
                          <div class="col-sm-6 b2b-invoicing-sec-sub">
                            <div class="radio">
                              <label><input type="radio" name="invoice" value="2" <?php echo ($invoice_type=='2') ? 'checked="checked"':'';?>>Invoice Daily <span class="checkmark"></span></label>
                            </div>
                            <div class="profile-inside-box">
                              <label>Maximum Invoicing Amount :</label><input class="form-control" type="text" name="invDailyAmt" onkeypress="return isNumberKey(event);" value="<?php echo ($inv_daily_max_inv_amt >'0') ? $inv_daily_max_inv_amt:'';?>" placeholder="">
                            </div>
                           </div>
                           <div class="col-sm-6 b2b-invoicing-sec-sub">
                              <div class="radio">
                                <label><input type="radio" name="invoice" value="3" <?php echo ($invoice_type=='3') ? 'checked="checked"':'';?>>Invoice Weekly <span class="checkmark"></span></label>
                              </div>
                              <div class="profile-inside-box">
                                <label>Maximum Invoicing Amount :</label><input class="form-control" type="text" name="invWeeklyAmt" onkeypress="return isNumberKey(event);" value="<?php echo ($inv_weekly_max_inv_amt >'0') ? $inv_weekly_max_inv_amt:'';?>" placeholder="">
                              </div>
                           </div>
                           <div class="col-sm-6 b2b-invoicing-sec-sub">
                              <div class="radio">
                                <label><input type="radio" name="invoice" value="4" <?php echo ($invoice_type=='4') ? 'checked="checked"':'';?>>Invoice Monthly <span class="checkmark"></span></label>
                              </div>
                              <div class="profile-inside-box">
                                <label>Maximum Invoicing Amount :</label>
                                <input class="form-control" type="text" name="invMonthlyAmt" onkeypress="return isNumberKey(event);" value="<?php echo ($inv_monthly_max_inv_amt >'0') ? $inv_monthly_max_inv_amt:'';?>" placeholder="">
                              </div>
                            </div>
                        </div>
                      <div class="row b2b-invoicing-sec">
                        <div class="order-listing-head">
                          <h2 class="bank-head no-underline">Invoice to :</h2>
                        </div>
                        <div class="col-sm-2">
                          <div class="radio">
                            <label><input type="radio" name="invoice_to" value="0" id="invoice_to" <?php echo ($invoice_to=='0') ? 'checked="checked"':'';?>>Self <span class="checkmark"></span></label>
                          </div>
                        </div>
                        <div class="col-sm-6 b2b-invoicing-sec-sub">
                          <div class="radio">
                            <label><input type="radio" name="invoice_to" id="invoice_to" value="1" <?php echo ($invoice_to=='1') ? 'checked="checked"':'';?>> <span class="checkmark"></span> Alternate Customer Email ID :</label>
                            <!-- <input class="form-control col-sm-5" type="text" value="nick@gmail.com" placeholder="" style=" position: relative; left: 10px;  top: -10px;">alternative_email_id -->
                            <div class="alternateEmail">
                              <select name = "alternate_email"  id="alternate_email" class="form-control ">
                                  <option value="" >Please Select Email</option>
                                   <?php if(isset($customers_info) && count($customers_info) > 0) {
                                      foreach($customers_info as $data) { ?>
                                        <option value="<?php echo $data['id']; ?>"  <?php if(isset($alternative_email_id)){
                                          echo ($alternative_email_id == $data['id']) ? "selected='selected'" : '';
                                        } ?>><?php echo $data['email_id'].'('.$data['first_name'].' '.$data['last_name'].')'; ?></option>
                                    <?php } } ?>
                                </select>
                            </div>
                            <span class="text-danger"></span>
                          </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group row">
                              <label for="PaymentTerm" class="col-sm-2 col-form-label">Payment Term :</label>
                              <div class="col-sm-1">
                                <input class="form-control" type="text" name="payment_term" onkeypress="return isNumberKey(event);" value="<?php echo ($payment_term >'0') ? $payment_term:$payment_term;?>" placeholder="">
                              </div> Days
                            </div>

                        </div>
                        <div class="save-discard-btn">
                          <input type="hidden" name="customerId" value="<?=$customerId?>" class="purple-btn">
           <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/customers/write',$this->session->userdata('userPermission'))){ ?>
                          <input type="submit" value="Save" class="purple-btn">
           <?php// } ?>
                        </div>

                      </div>
                      <!-- <div class="row b2b-invoicing-sec">
                          <div class="order-listing-head">
                            <h2 class="bank-head no-underline">Payment Term</h2>
                            <label><input class="form-control" type="text" name="payment_term" onkeypress="return isNumberKey(event);" value="<?php echo ($payment_term >'0') ? $payment_term:$payment_term;?>" placeholder=""> Days</label>

                          </div>
                        </div>  -->

                    </form>

                      <div class="order-listing-head">
                        <h2 class="bank-head no-underline">Invoice Listing</h2>
                      </div>
                      <div class="table-responsive text-center">
                        <?php include('webshop_invoice_list.php');?>
                      </div>

                  </div>
                    <?php } ?>
                </div>

              </div>
            </div>
            <!-- row -->
          </div>
          <!-- profile-details-block -->
		  </div>
      </main>

      <script>
        $("#access_prelaunch").change(function(){   // 1st

            var checked = $(this).is(':checked');
            if(checked) {

                swal({
                title: "Are you sure ??",
                text: "You want to update access for prelaunch?",
                icon: "warning",
                buttons: true,
                className: 'swal-height',
                dangerMode: true,
                showCancelButton: true,
                },function(isConfirm){
                    if (isConfirm) {
                        $("#access_prelaunch").prop("checked", true);
                    } else {
                        $('#access_prelaunch').removeAttr('checked');
                    }
                });
            }else{
                swal({
                title: "Are you sure ??",
                text: "You want to update access for prelaunch?",
                icon: "warning",
                buttons: true,
                className: 'swal-height',
                dangerMode: true,
                showCancelButton: true,
                },function(isConfirm){
                    if (isConfirm) {
                        $('#access_prelaunch').removeAttr('checked');
                    } else {
                        $("#access_prelaunch").prop("checked", true);
                    }
                });


            }

        });

      </script>

  <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script type="text/javascript">

  $("#alternate_email").select2();

  </script>
 <script type="text/javascript" src="<?php echo SKIN_JS; ?>single_customer_order_list.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
