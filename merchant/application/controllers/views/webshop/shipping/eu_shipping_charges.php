<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<ul class="nav nav-pills">
    <li class="active"><a data-toggle="pill" href="#shipping-charges">Shipping Charges</a></li>
  </ul>

  <div class="tab-content">
    <div id="shipping-charges" class="tab-pane fade in active min-height-480  common-tab-section" style="opacity:1;">


	<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-0">
          <h1 class="head-name pad-bt-20">Add Bulk Shipping Rates </h1>
        </div><!-- d-flex -->

	  <div class="content-main form-dashboard">

		<div class="col-sm-12 pt-5 pb-5">
			<div class="row">
				<div class="buttons-upload-download">
					<button class="purple-btn" onclick="">Upload CSV</button>
					<button class="white-btn" onclick="">Download CSV</button>
				</div>
			</div>
		</div>



      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name">Shipping Method </h1>
  		  <div class="float-right">
          <button class="purple-btn" data-toggle="modal" data-target="#shipping-add">Create New</button>
  		  </div>
      </div>
        <!-- form -->
        <form>
              <div class="table-responsive text-center">
                <table class="table table-bordered table-style" id="shipping_charges_table">
                  <thead>
                    <tr>
                      <th>Shipping Id </th>
                      <th>Shipping Method Name </th>
                      <th>Status </th>
					            <th>Action </th>
					           <th>Details </th>
                    </tr>
                  </thead>
                  <tbody>

                  </tbody>
                </table>
              </div>

        </form>

        <!--end form-->
    </div>

  </div>

 </div>
    </main>
  <div class="modal fade" tabindex="-1" id="shipping-add" name="create_shipping_charge" aria-hidden="true" >
    <div class="modal-dialog change-pass-modal" role="document">
    <div class="modal-content">
      <div class="modal-header">
      <h4 class="head-name">Shipping Method</h4>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">x</span>
      </button>
      </div>
      <div class="modal-body">
      <div class="row width-100">
      <form id="eu_shipping_charges_form" name="eu_shipping_charges_form" method="post" data-toggle="" action="<?php echo base_url() ?>EuShippingChargesController/submit_shipping_charges" novalidate="novalidate">
        <div class="form-group row col-sm-12">
          <label for="" class="col-sm-12 col-form-label">Shipping Name <span class="required">*</span></label>
          <div class="col-sm-12">
           <input type="text" class="form-control" id="ship_method_name" name="ship_method_name" placeholder="" required="">
          <div class="error-msg"></div>
          </div>
        </div>

        <div class="form-group col-sm-12 shipping-status">
          <label>Shipping Status <span class="required">*</span></label>
          <div class="switch-onoff">
            <label class="checkbox"><input type="checkbox" name="status" id="status" autocomplete="off">
            <span class="checked"></span>
            </label>
          </div>
        </div>
      <div class="modal-footer col-sm-12 ">
        <button type="submit" name="submit" id="submit" class="purple-btn">ADD</button>
      </div>
      <!-- <div class="modal-footer col-sm-12 ">
        <button type="button" class="delete-btn" data-toggle="modal" data-target="#deleteModal"><i class="fas fa-trash-alt"></i> Delete</button>
        <button type="submit" name="" id="" class="purple-btn ml-0">SAVE</button>
      </div> -->
      </form>
    </div>
    </div>
  </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" id="shipping-update" name="" style="display: none;" aria-hidden="true">
    <div class="modal-dialog change-pass-modal" role="document">
    <div class="modal-content">
      <div class="modal-header">
      <h4 class="head-name">Shipping Method</h4>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">x</span>
      </button>
      </div>
      <div class="modal-body">
      <div class="row width-100">
      <form id="eu_shipping_charges_update_form" name="eu_shipping_charges_update_form" method="post" data-toggle="" action="<?php echo base_url() ?>EuShippingChargesController/update_shipping_charge" novalidate="novalidate">
        <div class="form-group row col-sm-12">
          <label for="" class="col-sm-12 col-form-label">Shipping Name <span class="required">*</span></label>
          <div class="col-sm-12">
           <input type="text" class="form-control" id="ship_method_name_update" name="ship_method_name" placeholder="" required="">
          <div class="error-msg"></div>
          </div>
        </div>

        <div class="form-group col-sm-12 shipping-status">
          <label>Shipping Status <span class="required">*</span></label>
          <div class="switch-onoff">
            <label class="checkbox"><input type="checkbox" name="status" id="status_update" autocomplete="off">
            <span class="checked"></span>
            </label>
          </div>
        </div>
     <!--  <div class="modal-footer col-sm-12 ">
        <button type="submit" name="submit" id="submit" class="purple-btn">ADD</button>
      </div> -->
      <input type="hidden" name="shipping_charge_id_hidden"  id="shipping_charge_id_hidden"  required>
      <div class="modal-footer col-sm-12 ">
        <button type="button" class="delete-btn" ><i class="fas fa-trash-alt"></i> Delete</button>
        <button type="submit" name="" id="" class="purple-btn ml-0">SAVE</button>
      </div>
      </form>
    </div>
    </div>
  </div>
  </div>
</div>

  <div class="modal fade" id="deleteModalForRow" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <form id="deleteModalForRowForm" method="POST" action="<?= base_url('EuShippingChargesController/delete_shipping_charge')?>">
              <input type="hidden" name="row_id" id="row_id" value="">
              <div class="modal-header">
                <h1 class="head-name">Delete This Shipping Charge Row ?</h1>
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

<script src="<?php echo SKIN_JS; ?>eu_shipping_charges.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
