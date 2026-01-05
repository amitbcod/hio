<?php $this->load->view('common/fbc-user/header'); ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="generator" content="Jekyll v4.1.1">
    <title>Settings</title>


  <script type="text/javascript">
  $(document).ready(function() {
    const $valueSpan = $('.valueSpan');
    const $value = $('#slider11');
    $valueSpan.html($value.val());
    $value.on('input change', () => {
    $valueSpan.html($value.val());
    });
    $('#shipment_countries').multiselect({

  placeholder: 'Select Countries',

  search: true

});

  });

  $(document).ready(function() {
    const $valueSpan = $('.valueSpan2');
    const $value = $('#slider12');
    $valueSpan.html($value.val());
    $value.on('input change', () => {
    $valueSpan.html($value.val());
    });
  });

  $(document).ready(function(){
  $(".filter-section").hide();
    $(".filter button").click(function(){
    $(".filter-section").toggle();
    });
    $(".close-arrow").click(function(){
    $(".filter-section").hide();
    });
  });



  $( document ).ready(function() {
  $('.btn-slider').on('click', function() {
    $('.sliderPop').show();
    $('.ct-sliderPop-container').addClass('open');
    $('.sliderPop').addClass('flexslider');
    $('.sliderPop .ct-sliderPop-container').addClass('slides');

    $('.sliderPop').flexslider({
    selector: '.ct-sliderPop-container > .ct-sliderPop',
    slideshow: false,
    controlNav: false,
    controlsContainer: '.ct-sliderPop-container'
    });
  });

  $('.ct-sliderPop-close').on('click', function() {
    $('.sliderPop').hide();
    $('.ct-sliderPop-container').removeClass('open');
    $('.sliderPop').removeClass('flexslider');
    $('.sliderPop .ct-sliderPop-container').removeClass('slides');
  });
});


  </script>

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>
    <!-- Custom styles for this template -->
    <link href="<?php  echo base_url();?>public/css/style.css" rel="stylesheet">
    <link href="<?php  echo base_url();?>public/css/dashboard.css" rel="stylesheet">
    <link href="<?php  echo base_url();?>public/css/all.css" rel="stylesheet">

  <link href="<?php  echo base_url();?>public/css/flexslider.min.css" rel="stylesheet">
  <script src="<?php  echo base_url();?>public/js/jquery.flexslider-min.js"></script>

  </head>
  <body>


    <main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
<?php
// echo "<pre>";print_r($FBCUserData);die();
$coupon_code_discount = 0;
foreach($custom_variables as $key=>$val)
{
  if ($val['identifier'] == 'admin_email') {
        $admin_email = $val['value'];
    }

  if ($val['identifier'] == 'browse_by_gender_enabled') {
         $browse_by_gender_enabled = $val['value'];
    }
  if ($val['identifier'] == 'contact_us_email') {
        $contact_us_email = $val['value'];
    }
  if ($val['identifier'] == 'product_detail_page_max_qty') {
        $product_detail_page_max_qty = $val['value'];
    }
  if ($val['identifier'] == 'product_listing_get_show_records_list') {
        $product_listing_get_show_records_list = $val['value'];
    }
  if ($val['identifier'] == 'review_display_limit') {
        $review_display_limit = $val['value'];
    }
  if ($val['identifier'] == 'sales_admin_email') {
        $sales_admin_email = $val['value'];
    }
  if ($val['identifier'] == 'store_mobile') {
        $store_mobile = $val['value'];
    }

  if ($val['identifier'] == 'product_return_duration') {
         $product_return_duration = $val['value'];
    }
  if ($val['identifier'] == 'shipping_country') {
         $shipping_country = explode(",",$val['value']);
    }
  if ($val['identifier'] == 'footwear_category_id') {
         $footwear_category_id = $val['value'];
    }

  if ($val['identifier'] == 'invoice_prefix') {
         $invoice_prefix = $val['value'];
    }

  if ($val['identifier'] == 'invoice_next_no') {
         $invoice_next_no = $val['value'];
    }

  if ($val['identifier'] == 'request_for_invoice_default_webcust') {
         $request_for_invoice_default_webcust = $val['value'];
    }
  if ($val['identifier'] == 'pickinglist_show_cust_addr') {
         $pickinglist_show_cust_addr = $val['value'];
    }
 if ($val['identifier'] == 'general_log_zinapi') {
         $general_log_zinapi = $val['value'];
    }
if ($val['identifier'] == 'online_stripe_payment_refund') {

         $online_stripe_payment_refund = $val['value'];
    }
if ($val['identifier'] == 'captcha_check_flag') {

      $captcha_check_flag = $val['value'];
}
 if ($val['identifier'] == 'order_check_termsconditions') {
         $order_check_termsconditions = $val['value'];
    }

    if ($val['identifier'] == 'smtp_host') {
         $smtp_host = $val['value'];
    }
    if ($val['identifier'] == 'smtp_port') {
         $smtp_port = $val['value'];
    }
    if ($val['identifier'] == 'smtp_username') {
         $smtp_username = $val['value'];
    }
    if ($val['identifier'] == 'smtp_password') {
         $smtp_password = $val['value'];
    }
    if ($val['identifier'] == 'smtp_secure') {
         $smtp_secure = $val['value'];
    }

  if ($val['identifier'] == 'out_of_stock') {
         $out_of_stock = $val['value'];
    }

  if ($val['identifier'] == 'restricted_access') {
         $restricted_access = $val['value'];
    }

  if ($val['identifier'] == 'msg_for_customer') {
         $msg_for_customer  = $val['value'];
    }

  if($val['identifier']=='invoice_add_field1'){
    $invoice_add_field1_name=$val['name'];
    $invoice_add_field1_value=$val['value'];
  }

  if($val['identifier']=='invoice_add_field2'){
    $invoice_add_field2_name=$val['name'];
    $invoice_add_field2_value=$val['value'];

  }

  // new invoice
  if($val['identifier']=='invoice_logo'){
    $invoice_logo_name=$val['name'];
    $invoice_logo_value=$val['value'];

  }
  if($val['identifier']=='invoice_webshop_name'){
    $invoice_webshop_name_name=$val['name'];
    $invoice_webshop_name_value=$val['value'];

  }
  if($val['identifier']=='invoice_bottom_message'){
    $invoice_bottom_message_name=$val['name'];
    $invoice_bottom_message_value=$val['value'];

  }

  if($val['identifier']=='shipping_method_not_available'){
    $shipping_method_not_available_value=$val['value'];
  }

  if($val['identifier']=='review_contact_recipient'){
    $review_contact_recipient_value=$val['value'];
  }

  // edn new invoice
  if ($val['identifier'] == 'currency') {
    $currency = $val['value'];
}

if ($val['identifier'] == 'country') {
  $country = $val['value'];
}

if ($val['identifier'] == 'coupon_code_discount') {
  $coupon_code_discount = $val['value'];
}

}

$not_found_page='';
foreach($cms_pages as $key=>$val)
{
  if ($val['identifier'] == '404-not-found') {
         $not_found_page = $val['id'];
    }
}

?>

  <div class="tab-content">
    <div  class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Warehouse Settings</h1> <?php //echo $this->session->flashdata('item'); ?>

        </div><!-- d-flex -->
        <!-- form -->
        <form action="<?php echo base_url(); ?>UserController/update_settings" method="post" id="settings-form">
          <!-- <input type="hidden" name="user_id" id="user_id" value="<?php echo $FBCUserData->fbc_user_id; ?>"> -->

        <div class="content-main form-dashboard warehouse-setting">
          <div class="customize-add-section">
            <div class="row">
                <div class="right-form-sec">
                  <div class="col-sm-6 customize-add-inner-sec">
                    <label for="currency" class="">Currency </label>
                    <select name = "currency"  id="currency" class="currency form-control "  <?php echo (isset($ProductCount) && $ProductCount>0)?'readonly ':''; ?>>
                      <option value="" >Select Currency</option>
                       <?php if(isset($currencyList) && count($currencyList) > 0) {
                          foreach($currencyList as $data) { 
                            $selected = '';
                            if($data['currency_code']== $currency){
                              $selected = "selected";
                            }else{
                              $selected = '-';
                            }
                            ?>
                            <option  <?php echo $selected;  ?> value="<?php echo $data['currency_code']/*.'/'.$data['currency']*/; ?>" ><?php echo $data['currency_name']; ?></option>
                        <?php } } ?>
                    </select>
          </div>
                  <div class="col-sm-6 customize-add-inner-sec">
                    <label>Total Products in Warehouse</label>
                    <span class="total-warehouse"><?php echo (isset($ProductCount) && $ProductCount>0)?$ProductCount:'-'; ?></span>
                  </div>
          <!-- <div class="col-sm-6 customize-add-inner-sec">
            <label>Browse by gender</label>
                            <div class="switch-onoff">
                <label class="checkbox">
                <input type="checkbox" name="browse_by_gender_enabled" id="browse_by_gender_enabled" autocomplete="off"  <?php echo ($browse_by_gender_enabled == "no" || $browse_by_gender_enabled == NULL ) ? '' : 'checked' ;?>>
                <span class="checked"></span>
                </label>
                            </div>
                  </div> -->

         <div class="col-sm-6 customize-add-inner-sec ">
                    <label> Contact us recipient email address</label>
                    <input class="form-control" type="email" id="contact_us_email"  title="Please enter valid Admin Email."  name="contact_us_email" value="<?php if(isset($contact_us_email)) { echo $contact_us_email ; }   ?>" placeholder="">
                  </div>

          <div class="col-sm-6 customize-add-inner-sec ">
                    <label>Product Listing Get Show Records List </label>
                    <input class="form-control" type="text" id="product_listing_get_show_records_list "  title="Please enter valid Admin Email."  name="product_listing_get_show_records_list" value="<?php if(isset($product_listing_get_show_records_list )) { echo $product_listing_get_show_records_list  ; }   ?>" placeholder="">
                  </div>
          <div class="col-sm-6 customize-add-inner-sec ">
                    <label>Sales admin email</label>
                    <input class="form-control" type="email" id="sales_admin_email"  title="Please enter valid Admin Email."  name="sales_admin_email" value="<?php if(isset($sales_admin_email )) { echo $sales_admin_email  ; }   ?>" placeholder="">
                  </div>
                  <div class="col-sm-6 customize-add-inner-sec ">
                    <label>Review contact email</label>
                    <input class="form-control" type="text" id="review_contact_recipient"  title="Please enter valid Admin Email."  name="review_contact_recipient" value="<?php if(isset($review_contact_recipient_value )) { echo $review_contact_recipient_value  ; }   ?>" placeholder="">
                  </div>
          <div class="col-sm-6 customize-add-inner-sec ">
                    <label>Store contact number</label>
                    <input class="form-control" type="text" id="store_mobile"  title="Please enter valid Admin Email."  name="store_mobile" value="<?php if(isset($store_mobile )) { echo $store_mobile  ; }   ?>" placeholder="">
                  </div>
        <div class="col-sm-6 customize-add-inner-sec shipping-countries">
          <label>Shipping Countries</label>
            <select class="form-control" id="shipment_countries" name="shipment_countries[]" multiple>
<?php
            if(isset($country_master) && $country_master != '')
            {
              foreach($country_master as $key_country=>$value_country)
              {
                $selected = '';
                if(isset($shipping_country))
                {

                  if(in_array($value_country['country_code'], $shipping_country)) {

                    $selected = 'selected="selected"';

                  }

              }
?>
                 <option value="<?php echo $value_country['country_code'] ?>" <?php echo $selected; ?>><?php echo $value_country['country_name']?></option>
<?php           }
            }
            else
            {
?>
              <option value="1">One</option>
<?php           }


?>
          </select>
         </div>
<?php

?>
         <div class="col-sm-6 customize-add-inner-sec">
                    <label for="currency" class="">API customer type </label>
                    <select name="zin_customer_type_id"  id="zin_customer_type_id" class="currency form-control "  >
                      <option value="" >Select Customer type</option>
                       <?php if(isset($customer_types) && count($customer_types) > 0) {
                          foreach($customer_types as $data) { ?>
                            <option value="<?php echo $data['name'].'|'.$data['id']; ?>"
                              <?php echo (isset($zin_customer_type_id) && $zin_customer_type_id->value == $data['id'])?'selected':''?>><?php echo $data['name']; ?></option>
                        <?php } } ?>
                    </select>
          </div>
       

           <div class="col-sm-6 customize-add-inner-sec">
                    <label for="currency" class="">Webshop customers default Invoice needs to be send to </label>
                    <select name = "customer_id"  id="customer_id" class=" form-control">
                      <option value="" >Select Email</option>
                       <?php if(isset($customers_info) && count($customers_info) > 0) {
                          foreach($customers_info as $data) { ?>
                            <option value="<?php echo $data['id']; ?>"  <?php if(isset($webshopcust_def_inv_altemail)){
                              echo ($webshopcust_def_inv_altemail->value == $data['id']) ? "selected='selected'" : '';
                            } ?>><?php echo $data['email_id'].'('.$data['first_name'].' '.$data['last_name'].')'; ?></option>
                        <?php } } ?>
                    </select>
          </div>
          <div class="col-sm-12">
            <label for="currency" class="">Invoicing - Additional Details</label>
          </div>
          <div class="col-sm-6 customize-add-inner-sec ">
            <label>
              <input class="form-control" type="text" id="invoice_add_field1_name" name="invoice_add_field1_name" value="<?php if(isset($invoice_add_field1_name)) { echo $invoice_add_field1_name ; }   ?>" placeholder="Field1 Name">
            </label>
            <input class="form-control" type="text" id="invoice_add_field1_value" name="invoice_add_field1_value" value="<?php if(isset($invoice_add_field1_value)) { echo $invoice_add_field1_value ; }   ?>" placeholder="Field1 Value" >
          </div>

          <div class="col-sm-6 customize-add-inner-sec ">
            <label>
              <input class="form-control" type="text" id="invoice_add_field2_name" name="invoice_add_field2_name" value="<?php if(isset($invoice_add_field2_name)) { echo $invoice_add_field2_name ; }   ?>" placeholder="Field2 Name">
            </label>
            <input class="form-control" type="text" id="invoice_add_field2_value" name="invoice_add_field2_value" value="<?php if(isset($invoice_add_field2_value)) { echo $invoice_add_field2_value ; }   ?>" placeholder="Field2 Value">
          </div>

          <!-- start invoice new -->
          <?php if(isset($invoice_logo_name)){?>
          <div class="col-sm-6 customize-add-inner-sec">
            <label><?=$invoice_logo_name?></label>
              <div class="switch-onoff">
                <label class="checkbox">
                  <input type="checkbox" name="invoice_logo" id="invoice_logo" autocomplete="off"  <?php echo ($invoice_logo_value == "no" || $invoice_logo_value == NULL ) ? '' : 'checked' ;?>>
                  <span class="checked"></span>
                </label>
              </div>
          </div>
        <?php
          }
          if(isset($invoice_webshop_name_name)){
        ?>
          <div class="col-sm-6 customize-add-inner-sec">
            <label><?=$invoice_webshop_name_name?></label>
              <div class="switch-onoff">
                <label class="checkbox">
                  <input type="checkbox" name="invoice_webshop_name" id="invoice_webshop_name" autocomplete="off"  <?php echo ($invoice_webshop_name_value == "no" || $invoice_webshop_name_value == NULL ) ? '' : 'checked' ;?>>
                  <span class="checked"></span>
                </label>
              </div>
          </div>
          <?php
            }
            if(isset($invoice_bottom_message_name)){
          ?>
          <div class="col-sm-6 customize-add-inner-sec msg_sec">
            <label><?=$invoice_bottom_message_name?></label>
              <textarea class="form-control" type="textarea" id="invoice_bottom_message" name="invoice_bottom_message" placeholder="Thank you message at bottom of the invoice"><?php if(isset($invoice_bottom_message_value)) { echo $invoice_bottom_message_value ; }?></textarea>
              <!-- value=""    -->
          </div>
        <?php } ?>

        <div class="col-sm-6 customize-add-inner-sec msg_sec">
            <label>Shipping Method Message</label>
              <textarea class="form-control" type="textarea" id="shipping_method_not_available" name="shipping_method_not_available" placeholder="Your message here"><?php if(isset($shipping_method_not_available_value)) { echo $shipping_method_not_available_value ; }?></textarea>
              <!-- value=""    -->
          </div>

          <div class="col-sm-6 customize-add-inner-sec ">
              <label>Smtp Host</label>
              <input class="form-control" type="text" id="smtp_host"  title="Please enter Smtp Host."  name="smtp_host" value="<?php if(isset($smtp_host )) { echo $smtp_host  ; }   ?>" placeholder="">
          </div>

          <div class="col-sm-6 customize-add-inner-sec ">
            <label>Smtp Port</label>
            <input class="form-control" type="text" id="smtp_port"  title="Please enter Smtp Port."  name="smtp_port" value="<?php if(isset($smtp_port )) { echo $smtp_port  ; }   ?>" placeholder="">
          </div>

          <div class="col-sm-6 customize-add-inner-sec ">
            <label>Smtp Username</label>
            <input class="form-control" type="text" id="smtp_username"  title="Please enter Smtp Username."  name="smtp_username" value="<?php if(isset($smtp_username )) { echo $smtp_username  ; }   ?>" placeholder="">
          </div>

          <div class="col-sm-6 customize-add-inner-sec ">
            <label>Smtp Password</label>
            <input class="form-control" type="text" id="smtp_password"  title="Please enter Smtp Password."  name="smtp_password" value="<?php if(isset($smtp_password )) { echo $smtp_password  ; }   ?>" placeholder="">
          </div>

          <div class="col-sm-6 customize-add-inner-sec ">
            <label>Smtp Secure</label>
            <input class="form-control" type="text" id="smtp_secure"  title="Please enter Smtp Secure."  name="smtp_secure" value="<?php if(isset($smtp_secure )) { echo $smtp_secure  ; }   ?>" placeholder="ssl">
          </div>
          <!-- end invoice new -->


        </div>

                <div class="left-form-sec">
          <div class="col-sm-6 customize-add-inner-sec days">
                  <label>Country </label>
                    <select name="country" id="country" class="country form-control " aria-invalid="false" <?php echo (isset($ProductCount) && $ProductCount>0)?'readonly ':''; ?>>
                     <option value="">Select Country</option>
                      <?php if(isset($countryList) && count($countryList) > 0) {
                          foreach($countryList as $data) { 
                            $selected = '';
                            if($data['country_code']== $country){
                              $selected = "selected";
                            }else{
                              $selected = '-';
                            }
                            ?>

                        <option <?php echo $selected; ?> value="<?php echo $data['country_code']; ?>" ><?php echo $data['country_name']; ?></option>
                        <?php } } ?>
                    </select>


                </div>
                  <div class="col-sm-6 customize-add-inner-sec days">
                    <label>Product Return Duration</label>
                    <input class="form-control" type="text" id="product_return_duration"  title="Please enter valid Product Return Duration."  name="product_return_duration" value="<?php if(isset($product_return_duration)) { echo $product_return_duration; } ?>" placeholder="">
                      <span class="days-span">Days</span>
                  </div>
                  <div class="col-sm-6 customize-add-inner-sec days">
                    <label>Product Delivery Duration</label>
                    <input class="form-control" type="number" id="product_delivery_duration"  title="Please enter valid Product Delivery Duration."  name="product_delivery_duration" value="<?php if(isset($product_delivery_duration)) { echo $product_delivery_duration->value ; } ?>" placeholder="">  <span class="days-span">Days</span>
                  </div>

                  <div class="col-sm-6 customize-add-inner-sec days">
                    <label>Delay Warehouse Duration</label>
                    <input class="form-control" type="number" id="delay_warehouse"  title="Please enter valid Delay Warehouse Duration."  name="delay_warehouse" value="<?php if(isset($delay_warehouse)) { echo $delay_warehouse->value ; } ?>" placeholder="">  <span class="days-span">Days</span>
                  </div>

      
                  <div class="col-sm-6 customize-add-inner-sec days">
                    <label>VIES Checker Duration</label>
                    <input class="form-control" type="number" id="vies_checker_time_in_hr"  title="Please enter VIES Duration."  name="vies_checker_time_in_hr" value="<?php if(isset($vies_checker_time_in_hr)) { echo $vies_checker_time_in_hr->value ; } ?>" placeholder="">  <span class="days-span">Hours</span>
                  </div>
    
         <div class="col-sm-6 customize-add-inner-sec ">
                    <label>Admin Email</label>
                    <input class="form-control" type="email" id="admin_email"  title="Please enter valid Admin Email."  name="admin_email" value="<?php if(isset($admin_email)) { echo $admin_email ; }   ?>" placeholder="">
                  </div>
         <div class="col-sm-6 customize-add-inner-sec ">
                    <label>Product detail page max quantity </label>
                    <input class="form-control" type="number" id="product_detail_page_max_qty"  title="Please enter valid Admin Email."  name="product_detail_page_max_qty" value="<?php if(isset($product_detail_page_max_qty )) { echo $product_detail_page_max_qty  ; }   ?>" placeholder="">
                  </div>
          <div class="col-sm-6 customize-add-inner-sec ">
                    <label>Review dispaly limit </label>
                    <input class="form-control" type="number" id="review_display_limit"  title="Please enter valid Admin Email."  name="review_display_limit" value="<?php if(isset($review_display_limit )) { echo $review_display_limit  ; }   ?>" placeholder="">
                  </div>

            <div class="col-sm-6 customize-add-inner-sec">
            <label>Custom Error pages</label>
          </div>
          <div class="customize-add-radio-section row setting-page-change">
              <div class="radio col-sm-5">
                <label><input type="radio" name="custom_error_pages" checked="" value="0">Custom 404 page<span class="checkmark"></span></label>
                <span>Please <a href="<?php echo base_url()?>webshop/pages/edit/<?php echo $not_found_page?>" target="_blank"> "click here"</a> to update the contents if required. </span>
              </div><!-- radio -->
              <div class="radio col-sm-5">
                <label><input type="radio" name="custom_error_pages" value="1">Redirect to Homepage<span class="checkmark"></span></label>
              </div><!-- radio -->


            </div><!-- customize-add-radio-section -->
<?php
                


?>
        <div class="col-sm-6 customize-add-inner-sec days">
                    <label>Footwear category id</label>
                    <input class="form-control" type="text" id="footwear_category_id"  title="Please enter valid Product Delivery Duration."  name="footwear_category_id" value="<?php if(isset($footwear_category_id)) { echo $footwear_category_id ; } ?>"   oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"  placeholder="" >
        </div>
 
            <div class="col-sm-6 customize-add-inner-sec ">
              <label> What is the prefix of Invoice</label>
              <input class="form-control" type="text" id="invoice_prefix"  name="invoice_prefix" value="<?php if(isset($invoice_prefix)) { echo $invoice_prefix ; }   ?>" placeholder="">
            </div>
            <div class="col-sm-6 customize-add-inner-sec ">
              <label>What is your next Invoice Number</label>
              <input class="form-control" type="text" id="invoice_next_no"   name="invoice_next_no" value="<?php if(isset($invoice_next_no)) { echo $invoice_next_no ; }   ?>" placeholder="" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" >
            </div>

            <div class="col-sm-6 customize-add-inner-sec">
              <label>Request for Invoice (Default) for Webshop Customers</label>
                <div class="switch-onoff">
                  <label class="checkbox">
                    <input type="checkbox" name="request_for_invoice_default_webcust" id="request_for_invoice_default_webcust" autocomplete="off"  <?php echo ($request_for_invoice_default_webcust == "no" || $request_for_invoice_default_webcust == NULL ) ? '' : 'checked' ;?>>
                    <span class="checked"></span>
                  </label>
                </div>
            </div>

            <div class="col-sm-6 customize-add-inner-sec">
              <label>Do you want to show out of stock products on frontend</label>
                <div class="switch-onoff">
                  <label class="checkbox">
                    <input type="checkbox" name="out_of_stock" id="out_of_stock" autocomplete="off"  <?php echo ($out_of_stock == "no" || $out_of_stock == NULL ) ? '' : 'checked' ;?>>
                    <span class="checked"></span>
                  </label>
                </div>
            </div>

             <div class="col-sm-6 customize-add-inner-sec">
              <label>Restricted/Limited access to website</label>
                <div class="switch-onoff">
                  <label class="checkbox">
                    <?php
                      if($restricted_access == 'yes'){
                          $restricted_access = 'checked';
                          $display_msg = '';
                          $required='required';
                      }else
                      {
                        $restricted_access = '';
                        $display_msg = 'style="display: none;"';
                         $required='';
                      }

                    ?>
                    <input type="checkbox" name="restricted_access" id="restricted_access" autocomplete="off"  <?php echo $restricted_access ?>>
                    <span class="checked"></span>
                  </label>
                </div>
            </div>

            <div class="col-sm-6 customize-add-inner-sec">
              <label>Rounded Webshop Prices</label>
                <div class="switch-onoff">
                  <label class="checkbox">
                    <?php

                      if($rounded_webshop_prices->value  == '1'){
                          $rounded_webshop_prices  = 'checked';

                      }else
                      {
                        $rounded_webshop_prices  = '';

                      }

                    ?>
                    <input type="checkbox" name="rounded_webshop_prices" id="rounded_webshop_prices" autocomplete="off"  <?php echo $rounded_webshop_prices  ?>>
                    <span class="checked"></span>
                  </label>
                </div>
            </div>

            <div class="col-sm-6 customize-add-inner-sec">
              <label>Show customer address details on picking list</label>
                <div class="switch-onoff">
                  <label class="checkbox">
                    <input type="checkbox" name="pickinglist_show_cust_addr" id="pickinglist_show_cust_addr" autocomplete="off"  <?php echo ($pickinglist_show_cust_addr == "no" || $pickinglist_show_cust_addr == NULL ) ? '' : 'checked' ;?>>
                    <span class="checked"></span>
                  </label>
                </div>
            </div>

            <div class="col-sm-6 customize-add-inner-sec">
              <label>Add "Agree to Terms and conditions" as checkbox at the end of checkout process</label>
                <div class="switch-onoff">
                  <label class="checkbox">
                    <input type="checkbox" name="order_check_termsconditions" id="order_check_termsconditions" autocomplete="off"  <?php echo ($order_check_termsconditions == "no" || $order_check_termsconditions == NULL ) ? '' : 'checked' ;?>>
                    <span class="checked"></span>
                  </label>
                </div>
            </div>

            <!-- <div class="col-sm-6 customize-add-inner-sec">
              <label>Enable Zumba API Log</label>
                <div class="switch-onoff">
                  <label class="checkbox">
                    <input type="checkbox" name="general_log_zinapi" id="general_log_zinapi" autocomplete="off"  <?php echo ($general_log_zinapi == "no" || $general_log_zinapi == NULL ) ? '' : 'checked' ;?>>
                    <span class="checked"></span>
                  </label>
                </div>
            </div> -->

			<div class="col-sm-6 customize-add-inner-sec">
              <label>Stripe Payment - Refund the same way the payment was made</label>
                <div class="switch-onoff">
                  <label class="checkbox">
                    <input type="checkbox" name="online_stripe_payment_refund" id="online_stripe_payment_refund" autocomplete="off"  <?php echo ($online_stripe_payment_refund == "no" || $online_stripe_payment_refund == NULL ) ? '' : 'checked' ;?>>
                    <span class="checked"></span>
                  </label>
                </div>
            </div>

			<div class="col-sm-6 customize-add-inner-sec">
              <label>Enable Captcha </label>
                <div class="switch-onoff">
                  <label class="checkbox">
                    <input type="checkbox" name="captcha_check_flag" id="captcha_check_flag" autocomplete="off"  <?php echo ($captcha_check_flag == "no" || $captcha_check_flag == NULL ) ? '' : 'checked' ;?>>
                    <span class="checked"></span>
                  </label>
                </div>
            </div>

            <div class="col-sm-6 customize-add-inner-sec">
              <label>Use Advanced Warehouse</label>
                <div class="switch-onoff">
                  <label class="checkbox">
                    <input type="checkbox" name="use_advanced_warehouse" id="use_advanced_warehouse" autocomplete="off"  <?php echo ($use_advanced_warehouse->value == "no" || $use_advanced_warehouse->value == NULL ) ? '' : 'checked' ;?>>
                    <span class="checked"></span>
                  </label>
                </div>
            </div>

            <div class="col-sm-6 customize-add-inner-sec">
              <label>Use Base Colors</label>
                <div class="switch-onoff">
                  <label class="checkbox">
                    <input type="checkbox" name="use_base_colors" id="use_base_colors" autocomplete="off"  <?php echo ($use_base_colors->value == "no" || $use_base_colors->value == NULL ) ? '' : 'checked' ;?>>
                    <span class="checked"></span>
                  </label>
                </div>
            </div>

            <div class="col-sm-6 customize-add-inner-sec">
              <label>Enable Auto Coupon code </label>
                <div class="switch-onoff">
                  <label class="checkbox">
                    <input type="checkbox" name="coupon_code_discount" id="coupon_code_discount" autocomplete="off"  <?php echo ($coupon_code_discount == "0" || $coupon_code_discount == NULL ) ? '' : 'checked' ;?>>
                    <span class="checked"></span>
                  </label>
                </div>
            </div>

            <div class="col-sm-6 customize-add-inner-sec msg_sec" <?php echo $display_msg; ?>>
              <label>Message for customers</label>
                <textarea class="form-control" type="textarea" <?= $required; ?> id="msg_for_customer" name="msg_for_customer" placeholder="Message for customers"><?php if(isset($msg_for_customer)) { echo $msg_for_customer ; }?></textarea>
                <!-- value=""    -->
            </div>

                </div>
             </div><!-- row -->
            <?php if(empty($this->session->userdata('userPermission')) || in_array('system/settings/write',$this->session->userdata('userPermission'))){  ?>
                <div class="download-discard-small ">
                  <input type="submit" value="Save" class="purple-btn">
                </div>
            <?php } ?>
          </div>
        </div>

        </form>
        <!--end form-->
    </div>
  </div>


    </main>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {

   $('#form').validate({
    rules: {

        smtp_host: {
          required:{
            depends: function(element) {
                return ($('#smtp_port').val() !== "" || $('#smtp_username').val() !== "" || $('#smtp_password').val() !== "" || $('#smtp_secure').val() !="")
            }
          }
        },

        smtp_port: {
          required:{
            depends: function(element) {
                return ($('#smtp_password').val() !== "" || $('#smtp_username').val() !== "" || $('#smtp_host').val() !== "" || $('#smtp_secure').val() !="")
            }
          }
        },


        smtp_username: {
          required:{
            depends: function(element) {
                return ($('#smtp_password').val() !== "" || $('#smtp_port').val() !== "" || $('#smtp_host').val() !== "" || $('#smtp_secure').val() !="")
            }
          }
        },

        smtp_password: {
          required:{
            depends: function(element) {
                return ($('#smtp_username').val() !== "" || $('#smtp_port').val() !== "" || $('#smtp_host').val() !== "" || $('#smtp_secure').val() !="")
            }
          }
        },

        smtp_secure: {
          required:{
            depends: function(element) {
                return ($('#smtp_username').val() !== "" || $('#smtp_port').val() !== "" || $('#smtp_host').val() !== "" || $('#smtp_password').val() !="")
            }
          }
        },
    }
  });

});
  </script>

  <script type="text/javascript">

$("#customer_id").select2();

$(function() {
      $("#restricted_access").on("click",function() {
        $(".msg_sec").toggle(this.checked);
      });
  });

     $(function() {
        $("#restricted_access").on("change",function() {
             $(".msg_sec").toggle(this.checked);
            var msg_for_customer = $('#msg_for_customer');
           // coup.toggle();
            if (msg_for_customer.prop('required')) {
                msg_for_customer.prop('required', false);
            } else {
                msg_for_customer.prop('required', true);
            }
         });
    });


</script>




<?php $this->load->view('common/fbc-user/footer'); ?>
