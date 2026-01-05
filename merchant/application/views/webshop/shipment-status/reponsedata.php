<div class="tab-content">
  <div  class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
      <h1 class="head-name">Shiping Status Response</h1> <?php //echo $this->session->flashdata('item'); ?>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
    </div><!-- d-flex -->
        <!-- form -->
     <div class="content-main order-shipment-status">
          <div class="customize-add-section">
            <div class="row">
				<div class="col-sm-12 ">
					<textarea class="form-control shipment-status-response"><?php if(isset($response) && !empty($response)){ echo $response; } ?></textarea>
				</div>
            </div><!-- row -->
          </div>
		</div>
        <!--end form-->
  </div>
</div>


