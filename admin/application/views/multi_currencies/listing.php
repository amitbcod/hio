<?php $this->load->view('common/fbc-user/header'); ?>



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <ul class="nav nav-pills">
        <li class="active"><a data-toggle="pill" href="#multi_currencies-setting">Multi Currencies Settings</a></li>
    </ul>


    <div class="tab-content">
        <div id="shipping-charges" class="tab-pane fade in active min-height-480  common-tab-section" style="opacity:1;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-0">
                <h1 class="head-name pad-bt-20">Multi Currencies Settings </h1>
            </div><!-- d-flex -->

            <div class="content-main form-dashboard admin-shop-details-table new-height">
            <?php if(empty($this->session->userdata('userPermission')) || in_array('system/multi_currencies/write',$this->session->userdata('userPermission'))){  ?>
                <p><button class="white-btn ml-0" data-toggle="modal" data-target="#multi_currencies"> + Create New</button></p>
            <?php } ?>
                <!-- form -->


                <form>
                    <div class="table-responsive text-center pt-3">
                        <table id="vat_setting_list" class="table table-bordered table-style">
                        <thead>
                            <tr>
                            <th>ID </th>
                            <th>Name </th>
                            <th>Code </th>
                            <th>Default Currency </th>
                            <th>Conversion Rate </th>
                            <th>Actual Conversion Rate </th>
                            <th>Symbol </th>
                            <th>Enabled </th>
                            <th>Action </th>
                            <th>Details </th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($currenciesListing as  $item) {
                        	 $Amount = 1;
								$online_converted_rate=sis_convert_currency('EUR',$item['code'],$Amount);

                        // $deduct_vat = ($item['deduct_vat'] == 2) ? 'Not Relevent' : (($item['deduct_vat'] == 1) ? 'Yes' : 'No');

                        ?>
                         <tr>
                            <td><?php echo $item['id'] ?></td>
                            <td><?php echo $item['name'] ?></td>
                            <td><?php echo $item['code'] ?></td>
                            <td><?php echo ($item['is_default_currency'] == 0?'No':'Yes') ?></td>
                            <td><?php echo $item['conversion_rate']; ?></td>
                            <td><?php echo $online_converted_rate; ?></td>
                            <td><?php echo $item['symbol']; ?></td>
                            <td><?php echo ($item['status'] == 0?'No':'Yes') ?></td>
                            <?php if ($item['is_default_currency']!=1) {
                            ?>
                            <td>
                            <?php if(empty($this->session->userdata('userPermission')) || in_array('system/multi_currencies/write',$this->session->userdata('userPermission'))){  ?>
                            	<a class="link-purple" href="javascript:void(0);" onclick="deleteCurrency(<?php echo $item['id'];  ?>);">Delete</a>
                            <?php } else { echo "-"; } ?>
                            </td>
                            <?php }else{ ?>
                            <td> </td>
                            <?php } ?>
                           <td><a class="link-purple" href="javascript:void(0);" onclick="updateCurr(<?php echo $item['id'];  ?>);">View</a></td>
                           

                    </tr>

					   <?php } ?>

                        </tbody>
                        </table>
                    </div>
                </form>
                <!--end form-->
            </div>
        </div>
    </div>
</main>


<div class="modal fade" tabindex="-1" id="multi_currencies" name="" style="display: none;" aria-hidden="true">
	  <div class="modal-dialog change-pass-modal" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h4 class="head-name">Multi Currencies</h4>
			<button id="close-btn" type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">×</span>
			</button>
		  </div>
		  <div class="modal-body">
			<div class="row width-100">
			<form id="vat_create_form" name="vat_create_form" method="post" data-toggle="" action="<?php echo base_url()."Multi_Currencies_Controller/create_multi_currencies"?>" novalidate="novalidate">

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Currency Name <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="currency_name" name="currency_name" class="form-control" required>

					</div>
				</div>

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Currency code <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="currency_code" name="currency_code" maxlength="3" class="form-control" required>

					</div>
				</div>

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Currency conversion rate <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="currency_conversion_rate" name="currency_conversion_rate" class="form-control" required>

					</div>
				</div>

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Symbol <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="currency_symbol" name="currency_symbol" class="form-control" required>

					</div>
				</div>

				<!-- <div class="form-group col-sm-12 shipping-status">
				  <label>Is Default Currency</label>
					<div class="switch-onoff">
					  <label class="checkbox"><input type="checkbox" name="is_default_currency" id="is_default_currency" autocomplete="off">
						<span class="checked"></span>
					  </label>
					</div>
				</div> -->

				<div class="form-group col-sm-12 shipping-status">
				  <label>Status</label>
					<div class="switch-onoff">
					  <label class="checkbox"><input type="checkbox" name="status" id="status" autocomplete="off">
						<span class="checked"></span>
					  </label>
					</div>
				</div>






			<div class="modal-footer col-sm-12 ">
				<button type="submit" name="" id="" class="purple-btn">ADD</button>
			</div>
		  </form>
		</div>
	  </div>
	</div>
	</div>
</div>



<div class="modal fade" tabindex="-1" id="currency_update" name="" style="display: none;" aria-hidden="true">
	  <div class="modal-dialog change-pass-modal" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h4 class="head-name">Multi Currencies</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">×</span>
			</button>
		  </div>
		  <div class="modal-body">
			<div class="row width-100">
			<form id="currency_update_form" name="currency_update_form" method="post" data-toggle="" action="<?php echo base_url()."Multi_Currencies_Controller/update_currency"?>" novalidate="novalidate">

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Currency Name <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="currency_name_update" name="currency_name_update" class="form-control" required>

					</div>
				</div>

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Currency code <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="currency_code_update" name="currency_code_update" maxlength="3" class="form-control" required>

					</div>
				</div>

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Currency conversion rate <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="currency_conversion_rate_update" name="currency_conversion_rate_update" class="form-control" required>

					</div>
				</div>

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Symbol <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="currency_symbol_update" name="currency_symbol_update" class="form-control" required>

					</div>
				</div>

				<div class="form-group col-sm-12 shipping-status">
				  <label>Is Default Currency</label>
					<div class="switch-onoff">
					  <label class="checkbox"><input type="checkbox" name="is_default_currency_update" id="is_default_currency_update" autocomplete="off">
						<span class="checked"></span>
					  </label>
					</div>
				</div>

				<div class="form-group col-sm-12 shipping-status">
				  <label>Status</label>
					<div class="switch-onoff">
					  <label class="checkbox"><input type="checkbox" name="status_update" id="status_update" autocomplete="off">
						<span class="checked"></span>
					  </label>
					</div>
				</div>



            <input type="hidden" name="currency_id_hidden"  id="currency_id_hidden"  required>

        <?php if(empty($this->session->userdata('userPermission')) || in_array('system/multi_currencies/write',$this->session->userdata('userPermission'))){  ?>
            <div class="modal-footer col-sm-12 ">
				<button type="button" class="delete-btn" data-toggle="modal" data-target="#deleteModal"><i class="fas fa-trash-alt"></i> Delete</button>
				<button type="submit" name="" id="" class="purple-btn ml-0">SAVE</button>
			</div>
		<?php } ?>
		  </form>
		</div>
	  </div>
	</div>
	</div>
</div>


<script type="text/javascript" src="<?php echo SKIN_JS; ?>Multi_Currencies_setting.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>
