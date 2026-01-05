<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <ul class="nav nav-pills">
    <li class="active"><a href="<?= base_url('webshop/catalogue-discounts') ?>">Catalogue Discounts</a></li>
    <li><a href="#product-discounts-tab">Product Discounts</a></li>
    <li><a href="#coupon-code-tab">Coupon Code</a></li>
    <li><a href="#email-coupon-tab">Email Coupon</a></li>
  </ul>

  <div class="tab-content">
    <div id="catalogue-discounts-tab" class="tab-pane fade in active common-tab-section  min-height-480  admin-shop-details-table" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name">Discounts List</h1> 
  		  <div class="float-right">
          <button class="purple-btn" onclick="gotoLocation('<?= base_url('webshop/catalogue-discounts/add') ?>');">Create New</button>
  		  </div>
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
      <div class="content-main form-dashboard">
        <div class="table-responsive text-center">
          <table class="table table-bordered table-style" id="discountTableList"> 
            <thead>
              <tr>
                <th>Coupon Code <i class="float-right fa fa-fw fa-sort"></i></th>
                <th>Discount name <i class="float-right fa fa-fw fa-sort"></i></th>
                <th>Start Date </th>
                <th>End Date </th>
                <th>Status </th>
                <th>Details </th>
              </tr>
            </thead>
            <tbody>
              <?php if(isset($catalogueDiscountList) && $catalogueDiscountList!='') { ?>
              <?php foreach($catalogueDiscountList as $discount_list){ ?>
              <tr>
                <td><?php echo $discount_list->coupon_code;?></td>
                <td><?php echo $discount_list->name;?></td>
                <td><?php echo date('d/m/y', strtotime($discount_list->start_date));?></td>
				        <td><?php echo date('d/m/y', strtotime($discount_list->end_date));?></td>
                <td><?php echo (isset($discount_list->status) && $discount_list->status==1) ? 'Active':'Inactive';?></td>
                <td><a class="link-purple" href="<?php echo BASE_URL ?>webshop/catalogue-discounts/edit/<?php echo $discount_list->rule_id;?>">View</a></td>
              </tr>
              <?php } } ?>
            </tbody>
          </table>
        </div>
			</div><!--end form-->
    </div>
	</div>
</main>

<script src="<?php echo SKIN_JS; ?>discounts.js"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>