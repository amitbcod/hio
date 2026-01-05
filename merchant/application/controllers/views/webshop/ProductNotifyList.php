<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	
<div class="main-inner min-height-480">
	<div class="tab-content">
    <div id="attribute" class="tab-pane fade in active show admin-shop-details-table" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Coming Soon Products List </h1> 
      </div>
		

        <div class="content-main form-dashboard">
            <form>
              <div class="table-responsive text-center">
                <table class="table table-bordered table-style" name="coming_products_table" id="coming_products_table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Email Address</th>
					            <th>Customer Name</th>
                      <th>Product Name</th>
                      <th>Notify Count</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>

                  </tbody>
                </table>
              </div>
            </form>
        </div>
    
    </div>
    </div>
</div>	
</main>	
<?php $this->load->view('common/fbc-user/footer'); ?>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>coming_products.js"></script>

