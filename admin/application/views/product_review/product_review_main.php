<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

<!-- <ul class="nav nav-pills">

    <li class="active"><a data-toggle="pill" href="#customer-listing-tab">Customer Listing</a></li>

	<li><a data-toggle="pill" href="#customer-type-tab">Customer Type</a></li>

  </ul> -->

   <div class="tab-content">

    <div id="customer-listing-tab" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">

      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Product Reviews</h1>

          <!-- <div class="float-right product-filter-div">
            <button class="white-btn" onclick="window.location.href='<?php //echo base_url(); ?>add-customer';"> +  Add New</button>
          </div> -->
          <!-- product filter div -->
      </div>

      <form method="POST" id="pr_listing_Form">
        <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/product_reviews/write',$this->session->userdata('userPermission'))){ ?>
        <div class="float-right product-filter-div">
          <a class="white-btn delete-all-btn" id="deleteall" data-toggle="modal" data-target="#deleteALLModal">Delete ALL</a>
        </div>
      <?php } ?>
        <!-- form -->
        <div class="content-main form-dashboard">
          <div class="table-responsive text-center"  id="reviewListBlock">
            <?php include('product_review_list.php');?>
          </div>
        </div>
      </form>
    </div>
  </div>
</main>

<div class="modal fade" id="deleteALLModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <!-- <form id="deleteAllModalForm" method="POST" > -->
            <!-- <input type="hidden" name="row_id" id="row_id" value=""> -->
            <div class="modal-header">
            <h1 class="head-name">Are you sure you want to delete selected special pricings ?</h1>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-footer">
            <button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">No</button>
            <button type="button" class="purple-btn" onclick="OpenBulkDeletePopup(event);">Delete</button>
            </div>
        <!-- </form> -->
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>project_review.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>
