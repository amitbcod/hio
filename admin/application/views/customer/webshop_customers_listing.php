<?php $this->load->view('common/fbc-user/header'); ?> 

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

<ul class="nav nav-pills">
  <li class="active"><a data-toggle="pill" href="#customer-listing-tab">Customer Listing</a></li>
  <li><a href="<?php  echo base_url();?>customertype">Customer Type</a></li>


</ul>
   <div class="tab-content">

    <div id="customer-listing-tab" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">

      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

          <h1 class="head-name">Customer Listing</h1> 
         <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/customers/write',$this->session->userdata('userPermission'))){ ?>
          <div class="float-right product-filter-div">
            <button class="white-btn" onclick="window.location.href='<?php echo base_url(); ?>add-customer';"> +  Add New</button>
          </div><!-- product filter div -->
          <?php } ?> 
      </div>
		  <!-- <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center  pb-2">
        <h1 class="head-name"></h1> 
          <div class="float-right product-filter-div">
           <div class="search-div">
              <input class="form-control form-control-dark top-search" id="search_txt" type="search" placeholder="Search " aria-label="Search">
              <button type="button" class="btn btn-sm search-icon"><i class="fas fa-search"></i></button> 
          </div>
          </div>
      </div> -->


        <!-- form -->

        <div class="content-main form-dashboard">
              <div class="table-responsive text-center">
                <?php include('webshopcustomerlist.php');?>
              </div>
        </div>
    </div>
  </div>
</main>


<script type="text/javascript" src="<?php echo SKIN_JS; ?>customer_order_list_new.js"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>


