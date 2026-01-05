<?php $this->load->view('common/fbc-user/header'); ?>
 
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
          echo "<b>Inbound approved at :</b> ".$inboundData['approved_at'];}
       else{ ?>
        <?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
         <button class="purple-btn small-button" onclick="OpenApproveInbounndPopup(<?php echo $inboundData['id']; ?>);">Approve Inbound</button>
        <?php } ?>
     <?php }} ?>

			<a target="_blank" href="<?php echo base_url() ?>seller/inbound/print/<?php echo  $inboundData['id'];?>"><button class="white-btn">Print</button></a>
			<div class="export-colum a-right col-sm-4 order-id export-new-inbound">
				<span>Export to:</span> 
				<form method="post" action="<?php echo base_url() ?>InboundController/exportCSVOrdered">
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
        <input type="hidden" id="order_id" value="<?php echo $inboundData['id'];?>">
        <div class="content-main form-dashboard">
			<div class="barcode-qty-box row">
				<div class="col-sm-4 order-id">
					<p><span class="huge-name">Date :</span> <?php echo date('d/m/Y | h:i A',$inboundData['updated_at']) ?> </p>
          <p><span class="huge-name">Name :</span> <?php echo $inboundData['name'];?></p>
        <?php if (isset($advance_warehouse_flag) && $advance_warehouse_flag->value=='yes') {?> 
          <p><span class="huge-name">Warehouse Eta :</span> <?php echo $inboundData['warehouse_eta'];?></p>
          <p><span class="huge-name">Carrier Refrence :</span> <?php echo $inboundData['carrier_reference'];?></p>
        <?php } ?>
				</div>
				<div class="col-sm-4 order-id">
					<p><span class="huge-name">Order ID :</span> <?php echo $inboundData['id'];?> </p>
          <p><span class="huge-name">Total Products :</span> <?php echo $inboundData['total_products'];?></p>
        <?php if (isset($advance_warehouse_flag) && $advance_warehouse_flag->value=='yes') {?> 
          <p><span class="huge-name">Carrier :</span> <?php echo $inboundData['carrier'];?></p>
          <p><span class="huge-name">Carton Count :</span> <?php echo $inboundData['carton_count'];?></p>
        <?php } ?>
				</div>

				<div class="col-sm-4 order-id">
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
					<p><span class="huge-name">Total Price :</span> <?php echo $currency_code." ".$inboundData['total_price'];?> </p>
       <?php if (isset($advance_warehouse_flag) && $advance_warehouse_flag->value=='yes') {?> 
          <p><span class="huge-name">Po Refrence :</span> <?php echo $inboundData['po_reference'];?></p>
        <?php } ?>
				</div>

			</div><!-- barcode-qty-box -->
			
              <div class="table-responsive text-center">
                <table class="table table-bordered table-style" id="datatableInboundProductList">
                  <thead>
                    <tr>
					   <th>Inbound Id</th>
                      <th>PRODUCT NAME </th>
                      <th>SKU</th>
                      <th>Variant  </th>
                      <th>Qty Scanned </th>
                      <th>Price</th>
                      <th>Total Price </th>
                      <th>Location </th>
                      <th>Restock </th>
                      <th>Type </th>
                    </tr>
                  </thead>
                 
                </table>
              </div>

			  <div class="download-discard-small inbound-box">
					<a href="<?php echo base_url() ?>seller/inbound/"><button class="white-btn">Back </button></a>
				 </div>

            
        </div>
        <!--end form-->

       

    </div> <!-- dropshipping-products -->

  </div>
</main> 

<script>
  $(document).ready(function(){
    order_id = $('#order_id').val();
    FilterInboundOrderedProductDataTable(order_id);

   

  });
</script>

<script type="text/javascript" src="<?php echo SKIN_JS; ?>seller-inbound-process.js"></script> 


<?php $this->load->view('common/fbc-user/footer'); ?>