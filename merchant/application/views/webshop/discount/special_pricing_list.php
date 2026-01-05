<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php $this->load->view('webshop/discount/breadcrums');?>

  <div class="tab-content">
    <form method="POST" id="sp_listing_Form">
    <div id="dropshipping-products" class="tab-pane fade admin-shop-details-table" style="opacity:1; display:block;">

		<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

			<div class="special-add-btn">
			<!-- <button class="purple-btn" onclick="gotoLocation('<?= $add_special_pricing_link; ?>');">Add New</button> -->
      <!-- <button class="white-btn" onclick="gotoLocation('<?= $bulk_add_special_pricing_link; ?>');">Import CSV</button> -->
       <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/discounts/write',$this->session->userdata('userPermission'))){ ?>
        <a class="purple-btn delete-all-btn" href="<?= $add_special_pricing_link; ?>">Add New</a>
      <?php } ?>
				<a class="white-btn delete-all-btn" href="<?= $bulk_add_special_pricing_link; ?>">Import CSV</a>
      <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/discounts/write',$this->session->userdata('userPermission'))){ ?>
        <a class="white-btn delete-all-btn d-none" id="deleteall" data-toggle="modal" data-target="#deleteALLModal">Delete ALL</a>
      <?php } ?>
        <a class="white-btn delete-all-btn showall" id="showall" >Show ALL</a>
        <a class="white-btn delete-all-btn hideall d-none" id="hideall" >Hide ALL</a>
			</div>

        </div>
        <!-- form -->
        <div class="content-main form-dashboard d-none" id="table_content">
            <form>

              <div class="table-responsive text-center make-virtual-table">
                  <table class="table table-bordered table-style" id="Datatable_special_pricing">
                  <thead>
                    <tr>
                      <th>
                         <input type="checkbox" id="ckbCheckAllSP"> Select All
                      </th>
                      <th>SKU </th>
                      <th>Product Name  </th>
                      <th>Variant  </th>
                      <th>Webshop <br>Price </th>
                      <th>Special Price </th>
                      <th>Start Date </th>
                      <th>End Date</th>
                      <th>Status </th>
                      <th>Action </th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>



            </form>
        </div>
        <!--end form-->
    </div> <!-- dropshipping-products -->
</form>
  </div>


    </main>
      <div class="modal fade" id="deleteModalForRow" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <form id="deleteModalForRowForm" method="POST" action="<?= base_url('WebshopController/delete_special_pricing')?>">
              <input type="hidden" name="row_id" id="row_id" value="">
              <div class="modal-header">
                <h1 class="head-name">Delete This Special Pricing Row ?</h1>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-footer">
                <button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">No</button>
                <button type="submit" class="purple-btn">Delete</button>
              </div>
            </form>
          </div>
        </div>
      </div>

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
<script src="<?php echo SKIN_JS; ?>special_pricing.js"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>
