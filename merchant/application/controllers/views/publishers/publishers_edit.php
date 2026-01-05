<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
      <ul class="nav nav-pills">
            <li><a href="<?= base_url('publishers') ?>">Publishers</a></li>
            <li class="active"><a>Edit Publisher</a></li>
      </ul>
      <div class="main-inner min-height-480">
            <form id="publisherForm" method="POST" action="<?php echo base_url('PublisherController/submitPublisher'); ?>">
                  <input type="hidden" name="publisher_id" value="<?= $publisher->id ?>">
                  <input type="hidden" name="passwordCheck" value="">

                  <input type="hidden" name="type" value="1">

                  <div class="variant-common-block variant-list">
                  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
					<h1 class="head-name pad-bottom-20">Publishers</h1>
					<div class="float-right">
						<?php if ($_SESSION["LoginID"]) { ?> <!-- <button class="purple-btn" data-toggle="modal" data-target="#change_pass_modal" type="button">Change Password</button> -->
							<button class="purple-btn" type="button" onclick="OpenPasswordChangePopup();">Change Password</button>
						<?php } ?>
					</div>

				</div>
                        <div class="form-group row">
                              <label for="variant_name" class="col-sm-2 col-form-label font-500">Email</label>
                              <div class="col-sm-3">
                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter email id', 'id' => "email", 'name' => 'email', 'value' => "$publisher->email", 'required' => 'true', 'readonly' => 'true']);  ?>
                              </div>
                        </div><!-- form-group -->
                        <div class="form-group row">
                              <!-- <label for="" class="col-sm-2 col-form-label font-500">Edit Password</label>
                        <div class="col-sm-6">
                        <label class="checkbox">
						<input type="checkbox" id="passwordCheck"  name="passwordCheck" value="check" >  <span class="checked"></span>
						</label>
                        </div> -->
                        </div><!-- form-group -->
                        <div class="form-group row">
                              <!-- <label for="" class="col-sm-2 col-form-label font-500"></label>
                        <div class="col-sm-3">
                        <?php echo form_password(['class' => 'form-control', 'placeholder' => 'Enter password', 'id' => "password", 'name' => 'password', 'value' => "$publisher->password", 'required' => 'true', 'disabled' => 'true']); ?>
                        </div> -->
                        </div><!-- form-group -->

                        <div class="form-group row">
                              <label for="" class="col-sm-2 col-form-label font-500">Publication Name*</label>
                              <div class="col-sm-3">
                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter publication name', 'id' => "publication_name", 'name' => 'publication_name', 'value' => "$publisher->publication_name", 'required' => 'true']); ?>
                              </div>
                        </div><!-- form-group -->

                        <div class="form-group row">
                              <label for="" class="col-sm-2 col-form-label font-500">Vendor Name*</label>
                              <div class="col-sm-3">
                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter vendor name', 'id' => "vendor_name", 'name' => 'vendor_name', 'value' => "$publisher->vendor_name", 'required' => 'true']); ?>
                              </div>
                        </div><!-- form-group -->
                        <div class="form-group row">
                              <label for="" class="col-sm-2 col-form-label font-500">Commission % *</label>
                              <div class="col-sm-3">
                                    <?php echo form_input(['class' => 'form-control', 'type' => 'number', 'placeholder' => 'Enter commission', 'id' => "commision_percent", 'name' => 'commision_percent', 'value' => "$publisher->commision_percent", 'required' => 'true', 'readonly' => 'true']); ?>
                              </div>
                        </div><!-- form-group -->
                        <div class="form-group row">
                              <label for="" class="col-sm-2 col-form-label font-500">Phone No*</label>
                              <div class="col-sm-3">
                                    <?php echo form_input(['class' => 'form-control', 'type' => 'tel', 'pattern' => "[0-9]{3}[0-9]{3}[0-9]{4}", 'maxlength' => "12", 'placeholder' => 'Enter phone no', 'id' => "phone_no", 'name' => 'phone_no', 'value' => "$publisher->phone_no", 'required' => 'true']); ?>
                              </div>
                        </div><!-- form-group -->
                        <div class="form-group row">
                              <label for="" class="col-sm-2 col-form-label font-500">Description</label>
                              <div class="col-sm-3">
                                    <?php echo form_textarea(['class' => 'form-control', 'placeholder' => 'Enter description', 'name' => 'description', 'value' => "$publisher->description"]); ?>
                              </div>
                        </div><!-- form-group -->
                        <div class="form-group row">
                              <!-- <label for="" class="col-sm-2 col-form-label font-500">Status</label> -->
                              <div class="col-sm-3">
                                    <?php  //$status = $publisher->status; 
                                    ?>
                                    <input type="hidden" name="status" value="1">
                                    <!-- <label>Active</label><br>
                            <input type="radio" name="status" value="0" <?php if ($status == 0) {
                                                                              echo "checked";
                                                                        } ?>>
                            <label>In Active</label> -->
                              </div>
                        </div><!-- form-group -->
                        <div class="download-discard-small pos-ab-bottom">
                              <button class="white-btn" type="button" data-dismiss="modal" onclick="window.location.href='<?= base_url('DashboardController/index') ?>';">Discard</button>
                              <button type="submit" class="download-btn">Update</button>
                        </div><!-- download-discard-small -->
                  </div><!-- -common-block -->
      </div><!-- add new tab -->
      </div>
      </div>
</main>
<?php $this->load->view('common/fbc-user/footer'); ?>
<script src="<?php echo SKIN_JS; ?>publisher.js"></script>