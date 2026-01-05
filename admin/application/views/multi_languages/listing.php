<?php $this->load->view('common/fbc-user/header'); ?>
<style type="text/css">
	.switch-onoff span {
    margin-left: 72px;
}
</style>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <ul class="nav nav-pills">
        <li class="active"><a data-toggle="pill" href="#multi_languages-setting">Multi Languages Settings</a></li>
    </ul>


    <div class="tab-content">
        <div id="shipping-charges" class="tab-pane fade in active min-height-480  common-tab-section" style="opacity:1;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-0">
                <h1 class="head-name pad-bt-20">Multi Languages Settings </h1>
            </div><!-- d-flex -->

            <div class="content-main form-dashboard admin-shop-details-table new-height">
            <?php if(empty($this->session->userdata('userPermission')) || in_array('system/multi_languages/write',$this->session->userdata('userPermission'))){  ?>
                <p><button class="white-btn ml-0" data-toggle="modal" data-target="#multi_languages"> + Create New</button></p>
            <?php } ?>
                <!-- form -->


                <form>
                    <div class="table-responsive text-center pt-3">
                        <table id="vat_setting_list" class="table table-bordered table-style">
                        <thead>
                            <tr>
                            <th>ID </th>
                            <th>Name </th>
                            <th>Display Name </th>
                            <th>Code </th>
                            <th>Default Languages </th>
                            <th>Communication Languages</th> 
                            <th>Enabled </th>
                            <th>Action </th>
                            <th>Details </th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($languagesListing as  $item) {

                        ?>
                         <tr>
                            <td><?php echo $item['id'] ?></td>
                            <td><?php echo $item['name'] ?></td>
                            <td><?php echo $item['display_name'] ?></td>
                            <td><?php echo $item['code'] ?></td>
                            <td><?php echo ($item['is_default_language'] == 0?'No':'Yes') ?></td>
                            <td><?php echo ($item['is_communication_language'] == 0?'No':'Yes') ?></td> 
                            <td><?php echo ($item['status'] == 0?'No':'Yes') ?></td>
                            <?php if ($item['is_default_language']!=1) {
                            ?>
                            <td>
                            <?php if(empty($this->session->userdata('userPermission')) || in_array('system/multi_languages/write',$this->session->userdata('userPermission'))){  ?>
                            	<a class="link-purple" href="javascript:void(0);" onclick="deleteLanguage(<?php echo $item['id'];  ?>);">Delete</a>
                            <?php } else { echo "-"; } ?>
                            </td>
                            <?php
                            }else{
                            ?>
                            <td> </td>
                            <?php
                            } ?>

                           <td><a class="link-purple" href="javascript:void(0);" onclick="updateLang(<?php echo $item['id'];  ?>);">View</a></td>
                            

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


<div class="modal fade" tabindex="-1" id="multi_languages" name="" style="display: none;" aria-hidden="true">
	  <div class="modal-dialog change-pass-modal" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h4 class="head-name">Multi Languages</h4>
			<button id="close-btn" type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">×</span>
			</button>
		  </div>
		  <div class="modal-body">
			<div class="row width-100">
			<form id="vat_create_form" name="vat_create_form" method="post" data-toggle="" action="<?php echo base_url()."Multi_Languages_Controller/create_multi_languages"?>" novalidate="novalidate">

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Language Name <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="name" name="name" class="form-control" required>

					</div>
				</div>

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Language Display Name <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="display_name" name="display_name" class="form-control" required>

					</div>
				</div>

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Language code <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="code" name="code" maxlength="3" class="form-control" required>

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

				<div class="form-group col-sm-12 shipping-status">            
				  <label>Is Communication Language</label>
					<div class="switch-onoff">
					  <label class="checkbox"><input type="checkbox" name="is_communication_language" id="is_communication_language" autocomplete="off"> 
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



<div class="modal fade" tabindex="-1" id="language_update" name="" style="display: none;" aria-hidden="true">
	  <div class="modal-dialog change-pass-modal" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h4 class="head-name">Multi Languages</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">×</span>
			</button>
		  </div>
		  <div class="modal-body">
			<div class="row width-100">
			<form id="language_update_form" name="language_update_form" method="post" data-toggle="" action="<?php echo base_url()."Multi_Languages_Controller/update_language"?>" novalidate="novalidate">

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Language Name <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="name_update" name="name" class="form-control" required>

					</div>
				</div>

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Language Display Name <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="display_name_update" name="display_name" class="form-control" required>

					</div>
				</div>

				<div class="form-group row col-sm-12">
					<label for="" class="col-sm-12 col-form-label">Language code <span class="required">*</span></label>
					<div class="col-sm-12">
						<input type="text" id="code_update" name="code" maxlength="3" class="form-control" required>

					</div>
				</div>

				<div class="form-group col-sm-12 shipping-status">
				  <label>Is Default Language</label>
					<div class="switch-onoff">
					  <label class="checkbox"><input type="checkbox" name="is_default_language_update" id="is_default_language_update" autocomplete="off">
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

				<div class="form-group col-sm-12 shipping-status">            
				  <label>Is Communication Language</label>
					<div class="switch-onoff">
					  <label class="checkbox"><input type="checkbox" name="is_communication_language" id="is_communication_language_upd" autocomplete="off"> 
						<span class="checked"></span>
					  </label>
					</div>        
				</div>



            <input type="hidden" name="language_id_hidden"  id="language_id_hidden">

        <?php if(empty($this->session->userdata('userPermission')) || in_array('system/multi_languages/write',$this->session->userdata('userPermission'))){  ?>
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


<script type="text/javascript" src="<?php echo SKIN_JS; ?>Multi_Languages_setting.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>
