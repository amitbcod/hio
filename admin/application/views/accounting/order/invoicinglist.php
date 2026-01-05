<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('accounting/order/breadcrums'); ?>

	<div class="tab-content"  >
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <h1 class="head-name">Invoicing</h1>
            <div class="float-left">
                <button class="purple-btn" type="button" onclick="exportAllInvoice('exportAll');">Export All</button>

            </div>
        </div>
        <!-- form -->
        <div class="content-main form-dashboard">
           <input type="hidden" id="current_tab" name="current_tab"  value="<?php echo $current_tab; ?>">
              <div class="table-responsive text-center">
                <table class="table table-bordered table-style" id="DataTables_Table_AccountingInvoicingOrders">
                  <thead>
                    <tr>
                      <th>Invoice Number </th>
                      <th>Invoice Date </th>
                      <th>Invoice Name </th>
                      <th>Order Type </th>
                      <th>Amount  </th>
                      <!-- <th>Status </th> -->
                      <th>Resend </th>
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
<div id="InvoicingCommonModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true"  data-backdrop="static" data-keyboard="false" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="fullWidthModalLabel">Modal Heading</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <center><div class="spinner-border text-primary" role="status"></div></center>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div><!-- /.modal-content -->
        <input type="hidden" name="booklist_item_id" id="booklist_item_id" value="">
        <input type="hidden" name="subject_id" id="subject_id" value="">
    </div><!-- /.modal-dialog -->
</div>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>accounting_webshop_order_list.js"></script>


<?php $this->load->view('common/fbc-user/footer'); ?>

