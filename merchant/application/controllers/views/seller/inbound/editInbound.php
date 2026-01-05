<?php $this->load->view('common/fbc-user/header'); ?>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.3/themes/ui-lightness/jquery-ui.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('seller/products/breadcrums'); ?>

    <div class="tab-content">
    
    <div id="inbound" class="tab-pane fade common-tab-section admin-shop-details-table" style="opacity:1; display:block;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Inbound - (ID: <?php echo $inboundData['inbound_no']; ?>)</h1> 
            <div class="float-right">
              <?php $use_advanced_warehouse=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value'); 
              if ($use_advanced_warehouse->value=="yes") {
                 if($inboundData['approved_at'] !=''){
                    echo "<span class='inbound-approve-text'><b>Inbound approved at :</b> ".$inboundData['approved_at'].'</span>';}
                 else{ ?>
                    <?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
                        <button class="purple-btn small-button" onclick="OpenApproveInbounndPopup(<?php echo $inboundData['id']; ?>);">Approve Inbound</button>
                    <?php } ?>

              <?php }} ?>
				<div class="export-colum a-right col-sm-4 order-id export-new-inbound">
					<span>Export to:</span> 
					<form method="post" action="<?php echo base_url() ?>InboundController/exportCSV">
					<select name="inbound_export" id="inbound_export" class="form-control" >
						<option value="csv">CSV</option>
						<option value="excel">Excel</option>
					</select>
					<button class="purple-btn pos-top-25">Export</button>
					<input type="hidden" value="<?php echo $inboundData['id'] ?>" name="order_inbound_id" id="order_inbound_id">
					
					</form>
				</div>
            </div>
    </div>


		
		
        <!-- form -->
        <div class="content-main form-dashboard"> 
            <form method="post" action="<?php echo BASE_URL ?>/InboundController/submitAction">
			<input type="hidden" id='order_id' name='order_id' value="<?php echo $inboundData['id'] ?>">
			<div class="barcode-qty-box row">
				<div class="col-sm-4 order-id">
					<p><span class="huge-name">Name : </span> <input name="name" id="name" type="text" value="<?php echo $inboundData['name']?>" placeholder=""></p>
					
				</div>
				<div class="col-sm-4 order-id">
					<p><span class="huge-name">Total Products :</span> <span class="inbound-count-bold" id="total-products"><?php echo $inboundData['total_products']?></span>
					
				</p></div>

				<div class="col-sm-4 order-id">
					<p><span>Total Price :</span> <?php echo $currency_code?> <span class="inbound-count-bold" id="total-price"><?php echo $inboundData['total_price']?></span></p>
				</div>
        <?php $customVarData = $this->InboundModel->getCustomData();
        if(isset($customVarData) && $customVarData->value == 'yes') { ?>
          <div class="col-sm-4 order-id date pd-t">
           <p><span class="huge-name">Warehouse Eta : </span><input type="date" class="form-control" id="warehouse_eta"  name="warehouse_eta" value="<?php echo $inboundData['warehouse_eta']?>"></p>          
          </div> 
          <div class="col-sm-4 order-id pd-t">
              <p><span class="huge-name">Carrier : </span> <input type="text" name="carrier" id="carrier" value="<?php echo $inboundData['carrier']?>" placeholder=""></p>
              
          </div>
          <div class="col-sm-4 order-id pd-t">
              <p><span class="huge-name">Carrier Refrence : </span> <input type="text" name="carrier_reference" id="carrier_reference" value="<?php echo $inboundData['carrier_reference']?>" placeholder=""></p>
              
          </div>
          <div class="col-sm-4 order-id pd-b">
              <p><span class="huge-name">Carton Count : </span> <input onkeypress="return onlyNumberKey(event)" type="text" name="carton_count" id="carton_count" value="<?php echo $inboundData['carton_count']?>" placeholder=""></p>
              
          </div>
          <div class="col-sm-4 order-id pd-b">
              <p><span class="huge-name">Po Refrence : </span> <input type="text" name="po_reference" id="po_reference" value="<?php echo $inboundData['po_reference']?>" placeholder=""></p>
              
          </div>
          <div class="col-sm-4 order-id pd-b">
                <?php 
                    $use_advanced_warehouse=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value');

                    if ($use_advanced_warehouse->value=="yes") {
                      $warehouse_status=$inboundData['warehouse_status'];
                      $warehouse_status_name=$this->CommonModel->getWarehouse_status_name($warehouse_status);

                    ?>
                      <p><span class="huge-name">Warehouse Status :</span> <?php echo $warehouse_status_name; ?></p>
                    <?php 
                    }
                  ?>
                
          </div>
    <?php } ?>
				<div class="row col-sm-12 checkbox-label print-box">
					<div class="col-sm-8 pad-zero"><label class="checkbox"><input id="product_label" name="product_label" value="<?php echo $inboundData['print_pro_lables'] ?>" <?php echo ($inboundData['print_pro_lables'] == 1)?'checked':'' ?> type="checkbox"> Print inbound product labels <span class="checked"></span></label></div>
				</div>

        <div class="row col-sm-12 dropdown-label import-month">                    
            <div class="col-sm-2 pad-zero">            
                <select class="form-control" name="select_month" id="select_month">
                    <option value="">Select Month</option>
                    
                    <?php
                   
                        for($m=1; $m<=12; $m++){

                           $monthList = date('F', mktime(0, 0, 0, $m));

                           if($inboundData['month'] == $monthList){
                              $month_selected = 'selected';
                           }else{
                              $month_selected = '';
                           }

                           //echo '<option value="'.$monthList.'" '.$month_selected.'>'.$monthList.'</option>';
                           echo "<option value=\"$monthList\" $month_selected>$monthList</option> \n"; 
                        }
                    ?>
                </select>
                <br>
            </div>

            <div class="col-sm-2 pad-zero">
                <select class="form-control" name="select_year" id="select_year">
                    <option value="">Select Year</option>
                    <?php

                        $date_future = date("Y", strtotime('+20 year'));
                        $date_year = date("Y");
                        for($i=$date_year;$i<$date_future;$i++){

                           if($inboundData['year'] == $i){
                              $year_selected = 'selected';
                           }else{
                              $year_selected = '';
                           }

                           //echo '<option value="'.$i.'" '.$year_selected.'>'.$i.'</option>';
                            echo "<option value=\"$i\" $year_selected>$i</option> \n"; 
                    
                        }


                    ?>
                </select>   
            </div>

            <div class="col-sm-2 pad-zero">
                 <select class="form-control" name="label_size" id="label_size">
                     
                    <option value="">Select Size</option>
                    <option value="10x15" <?php echo ($inboundData['label_size'] == '10x15') ?'selected':'' ?> >10 cm x 15 cm</option>
                    <option value="10x7.5" <?php echo ($inboundData['label_size'] == '10x7.5') ?'selected':'' ?>>10 cm x 7.5 cm</option>
                </select>              
            </div>

            
        </div>
				
				<div class="row col-sm-12">
					<div class="col-sm-3 pad-zero">
					   <input type="text" id="barcode_item" name="barcode_item" class="form-control" placeholder="Barcode" onmouseover="this.focus();" autofocus><br>
				
                   <input type="text" id="sku" name="sku" class="form-control" placeholder="Product Name - SKU">
					</div>
					<div class="col-sm-3 pad-zero"><input name="qty" id="qty" value="1" type="text" class="form-control pos-top-25" placeholder="Quantity"></div>
                <?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
					<div class="col-sm-3 pad-zero"><button onclick="ScanInboundProductsManually(); return false;" class="purple-btn pos-top-25">Enter</button></div>
                <?php } ?>
                <label class="error" id="barcode-error"></label>
            </div>
			</div><!-- barcode-qty-box -->


			
              <div class="table-responsive text-center">
                <table class="table table-bordered table-style" id="datatableInboundProductList">
                  <thead>
                    <tr>
                      <th>PRODUCT NAME </th>
                      <th>SKU</th>
                      <th>Variant  </th>
                      <th>Qty Scanned </th>
                      <th>Price </th>
                      <th>Total Price </th>
                      <th>Location</th>
                      <th>Restock </th>
                      <th>Type </th>
                      <th>Action </th>
                    </tr>
                  </thead>
                  
                </table>
              </div>
            <?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
			    <div class="download-discard-small inbound-box">	
                    <button name="btn" class="white-btn" value="0">Save Draft</button>
          	        <button name="btn" class="download-btn auto-width" value="1" >Create Inbound</button>
				</div>
            <?php } ?>
            </form>
        </div>
        <!--end form-->

        

    </div> <!-- dropshipping-products -->

  </div>


</main>


<script>
  $(document).ready(function(){
    FilterInboundProductDataTable();

    $('#sku').autocomplete({
      minLength: 3,
      source: function(request, response) {
         $.getJSON(BASE_URL+"InboundController/getProductSku", {
               term: request.term
         }, function(data) {    
               var array = data.error ? [] : $.map(data, function(m) {
         
                  return {
                     label: m.name+" - "+m.sku,
                     value: m.sku,
                     
                  };
               });
      
               response(array);
            });
      },
      select: function (event, ui) {
         
         $('#sku').val(ui.item.value); // save selected id to hidden input
         return false;
      },
      focus: function( event, ui ) {

         $('#sku').val(ui.item.label);
         return false;
      },	
      change: function( event, ui ) {
         $( "#sku" ).val( ui.item? ui.item.value : "" );
      }
   });


  });

 
</script>

<script type="text/javascript" src="<?php echo SKIN_JS; ?>seller-inbound-process.js"></script> 


<?php $this->load->view('common/fbc-user/footer'); ?>