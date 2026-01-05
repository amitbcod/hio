<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
<?php  $this->load->view('seller/products/breadcrums'); ?>
   <div class="tab-content">

    <div id="customer-listing-tab" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">

      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

          <h1 class="head-name">Products Inventory Listing</h1> 
       
        
      </div>
    
        <!-- form -->

        <div class="content-main form-dashboard">
              <div class="table-responsive text-center">
               <table id="DataTables_Product_Inventory_list" class="table table-bordered table-style">

                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>SKU</th>
                      <th>Product Name</th>
                      <th >Type</th>
                      <th >Adjustment</th>
                      <th>processed</th>
                      <th >Source</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>

                </table>

              </div>
        </div>
    </div>
  </div>
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>product-overview.js"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>

<script type="text/javascript">
  
</script>


