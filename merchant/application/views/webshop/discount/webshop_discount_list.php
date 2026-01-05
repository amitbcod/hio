<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <?php $this->load->view('webshop/discount/breadcrums');?>

  <div class="tab-content">
    <div id="catalogue-discounts-tab" class="tab-pane fade in active common-tab-section  min-height-480 admin-shop-details-table" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name">Discounts List</h1>
      <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/discounts/write',$this->session->userdata('userPermission'))){ ?>
  		  <div class="float-right">
          <button class="purple-btn" onclick="gotoLocation('<?= $add_discount_link; ?>');">Create New</button>
  		  </div>
      <?php } ?>
      </div><!-- d-flex -->

		 <!--  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 ">
        <label>Show <select><option>6</option></select></label>

  		  <div class="float-right product-filter-div inner-search">
  			  <div class="search-div">
  				  <input class="form-control form-control-dark top-search" type="text" placeholder="Search Ticket Number" aria-label="Search">
  				  <button type="button" class="btn btn-sm search-icon"><i class="fas fa-search"></i></button>
  			 </div>
        </div>
      </div> --><!-- d-flex -->

      <!-- form -->
      <div class="content-main form-dashboard admin-shop-details-table new-height">
		<input type="hidden" name="discount_type" id="discount_type" value="<?php echo $discount_type; ?>">
        <div class="table-responsive text-center">
          <table class="table table-bordered table-style" id="discountTableList">
            <thead>
              <tr>
                <?php if($current_tab == 'cpCode' || $current_tab == 'emlCoupon') { ?>
                  <th>Coupon Code </th>
                <?php }else { ?>
                <th>Discount Code </th>
              <?php } ?>
                <th>Discount name </th>
                <th>Coupon Type </th>
                <th>Start Date </th>
                <th>End Date </th>
                <th>Status </th>
                <th>Details </th>
              </tr>
            </thead>
            <tbody>

            </tbody>
          </table>
        </div>
			</div><!--end form-->
    </div>
	</div>
</main>

<script src="<?php echo SKIN_JS; ?>discounts.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>
