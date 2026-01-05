<?php $this->load->view('common/fbc-user/header');
$use_advanced_warehouse=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value');
 ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('webshop/shipment-status/breadcrums'); ?>

	<div class="tab-content" >
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Webshop  Orders Shipments Status</h1>
		 	<div class="float-right product-filter-div ">
					<div class="search-div d-none"  id="pro-search-div">
						<input class="form-control form-control-dark top-search" id="custome-filter" type="text" placeholder="Search" aria-label="Search">
						<button type="button" class="btn btn-sm search-icon" onclick="FilterProductDataTable();"><i class="fas fa-search"></i></button>
					</div>
				</div>
        </div>
        <!-- form -->
        <div class="content-main form-dashboard">
           <input type="hidden" id="current_tab" name="current_tab"  value="<?php echo $current_tab; ?>">
           <input type="hidden" id="is_warehouse" name="is_warehouse" value="<?php if($use_advanced_warehouse->value=="yes"){echo 1;}else{echo 0;}  ?>">
              <div class="table-responsive text-center">
                <table class="table table-bordered table-style" id="DataTables_Table_WebshopOrder_Shipmentstatus">
                  <thead>
                    <tr>
						<th>Order Number </th>
						<th>Shipment Detail </th>
						<th>Tracker Vendor </th>
						<th>response </th>
						<th>Created </th>
						<th>Updated </th>
						<th>Status </th>
                    </tr>
                  </thead>
                  <tbody>
				  </tbody>
                </table>
              </div>


        </div>
        <!--end form-->
    </div>

	</div>
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_shipment_status_list.js"></script>


<?php $this->load->view('common/fbc-user/footer'); ?>
