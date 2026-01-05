<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('webshop/returns/ref_breadcrums');?>
	
	<div class="tab-content"  >
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Request Escalations Complated Orders </h1> 
		 	<div class="float-right product-filter-div  d-none">
					<div class="search-div d-none"  id="pro-search-div">
						<input class="form-control form-control-dark top-search" id="custome-filter" type="text" placeholder="Search" aria-label="Search">
						<button type="button" class="btn btn-sm search-icon" onclick="FilterProductDataTable();"><i class="fas fa-search"></i></button>
					</div>
					<!-- filter section start -->
					<div class="filter">
						<button>
							<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"/>
							</svg>
							Filter
						</button>
					</div>
					<div class="filter-section">
					<span class="reset-arrow"><a  href="javascript:void(0);" onclick="location.reload();">Reset</a></span>
						<div class="close-arrow"> <i class="fa fa-angle-left"></i> </div>
						
						<div class="filter filter-inside">
							<button>
								<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd" d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"></path>
								</svg>
								Filter
							</button>
						</div>
						
						<div class="justify-content-center my-4 status-box">
							<h3>Order Status</h3>
							<div class="col-md-12">
								<select class="form-control"  name="order_status" id="order_status" >
									<option value="">--Select--</option>
									<?php if($current_tab=='shipped-orders'){?>
									<option value="4">Tracking Missing</option>
									<option value="5">Tracking Incomplete</option>
									<option value="6">Tracking Complete</option>
									<?php } else{ ?>
									<option value="0">To be processed</option>
									<option value="1">Processing</option>
									<option value="2">Complete</option>
									<option value="3">Cancelled</option>
									<?php } ?>
								</select>
								</div>
						</div>
						
						<div class="justify-content-center my-4 price-range">
							<h3>Grand Total Price Range</h3>
							<form class="range-field w-100">
								<input id="slider11" class="border-0"  value="0" type="range" min="0" max="100000" />
							</form>
							<span class="zero-value">0</span>
							<span class="font-weight-bold text-primary ml-2 mt-1 valueSpan"></span>
						</div>
						
						<!-- range-box -->
						<div class="justify-content-center my-4 supplier-box d-none">
							<h3>Shipment Type</h3>
							<div class="col-md-6"><label class="checkbox"><input type="checkbox" class="form-control" name="shipment_type[]" value="1"><span class="checked"></span> Buy In</label></div>
							<div class="col-md-6"><label class="checkbox"><input type="checkbox" class="form-control"  name="shipment_type[]" value="2"><span class="checked"></span> Dropship</label></div>
						</div>
						<!-- range-box -->
						<div class="justify-content-center my-4 last-updated">
							<h3>Last Updated</h3>
							<div class="col-md-5"><input type="text" class="form-control"  id="from_date"></div>
							<div class="col-md-2">To</div>
							<div class="col-md-5"><input type="text" class="form-control"  id="to_date"></div>
						</div>
						<!-- range-box -->
						<div class="filter-btn-box">
							<button class="filter-btn" onclick="FilterOrdersDataTable();">Filter</button>
						</div>
					</div>
					<!-- filter section -->
					<!-- filter section close -->
				</div>
				<!-- product filter div -->
        </div>
        <!-- form -->
        <div class="content-main form-dashboard">
           <input type="hidden" id="current_tab" name="current_tab"  value="<?php echo $current_tab; ?>">
              <div class="table-responsive text-center">
                <table class="table table-bordered table-style" id="DataTables_Table_WebshopEscalationsOrder">
                  <thead>
                    <tr>
                      <th>Order Number </th>
                      <th>Purchased On </th>
                      <th>Customer Name </th>
                      
					  <th>Requested On  </th>
					  <th>Status</th>
					  <th>Refund Status</th>
					  <th>Payment Method </th>
					  <th>Details </th>
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
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_escalations_requested_order_list.js"></script>
		
  
<?php $this->load->view('common/fbc-user/footer'); ?>