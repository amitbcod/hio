<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <div class="tab-content">
    <div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
	<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">B2B  Customers </h1>
				<!-- product filter div -->
        </div>
        <!-- form -->
        <div class="content-main form-dashboard">
              <div class="table-responsive text-center">
					<table class="table table-bordered table-style" id="DataTables_Table_B2BCustomerList">
						<thead>
								<tr>
								  <th>Webshop Name </th>
								  <th>Owner Name </th>
								  <th>Address  </th>
								  <th>Email ID </th>
								  <th>Taxes Exempted</th>
								  <th>Last Purchased On </th>
								  <th>Total Purchase </th>
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
  <script type="text/javascript" src="<?php echo SKIN_JS; ?>b2b_customer_dt.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
