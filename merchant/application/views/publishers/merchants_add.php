<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <ul class="nav nav-pills">
        <li ><a href="<?= base_url('merchants') ?>">Merchants</a></li>
        <li class="active"><a>Add New</a></li>
    </ul>
    <div class="main-inner min-height-480">
    <form id="publisherForm" method="POST" action="<?php echo base_url('PublisherController/submitMerchant'); ?>">
    <input type="hidden" name="publisher_id" value="">
            <input type="hidden" name="type" value="1">
            <div class="variant-common-block variant-list">
                <h1 class="head-name pad-bottom-20">Merchants</h1>
                    <div class="form-group row">
                        <label for="variant_name" class="col-sm-2 col-form-label font-500">Email*</label>
                        <div class="col-sm-3">
                        <?php echo form_input(['class'=> 'form-control','placeholder'=>'Enter email id','id'=>"email",'name'=>'email', 'value'=>set_value('email'),'required'=>'true','validateEmail'=>true]); ?>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Password*</label>
                        <div class="col-sm-3">
                        <?php echo form_password(['class'=> 'form-control','placeholder'=>'Enter password','id'=>"password",'name'=>'password', 'value'=>set_value('password'),'required'=>'true', 'minlength'=>8]); ?>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Merchant Name*</label>
                        <div class="col-sm-3">
                        <?php echo form_input(['class'=> 'form-control','placeholder'=>'Enter publication name','id'=>"publication_name",'name'=>'publication_name', 'value'=>set_value('publication_name'),'required'=>'true']); ?>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Vendor Name*</label>
                        <div class="col-sm-3">
                        <?php echo form_input(['class'=> 'form-control','placeholder'=>'Enter vendor name','id'=>"vendor_name",'name'=>'vendor_name', 'value'=>set_value('vendor_name'),'required'=>'true']); ?>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Commission % *</label>
                        <div class="col-sm-3">
                        <?php echo form_input(['class'=> 'form-control','type'=>'number' ,'placeholder'=>'Enter commission','id'=>"commision_percent",'name'=>'commision_percent', 'value'=>set_value('commision_percent'),'required'=>'true']); ?>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Phone No*</label>
                        <div class="col-sm-3">
                        <?php echo form_input(['class'=> 'form-control','type'=>'tel','pattern'=>"[0-9]{3}[0-9]{3}[0-9]{4}", 'maxlength'=>"12" ,'placeholder'=>'Enter phone no','id'=>"phone_no",'name'=>'phone_no', 'value'=>set_value('phone_no'),'required'=>'true']); ?>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Description</label>
                        <div class="col-sm-3">
                        <?php echo form_textarea(['class'=> 'form-control','placeholder'=>'Enter description','name'=> 'description', 'value'=>set_value('description')]); ?>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Status</label>
                        <div class="col-sm-3">
                            <div class="radio col-sm-12">
						<label>
							<input type="radio" name="status" value="1" checked="">Active <span class="checkmark"></span>
						</label>
						</div><!-- radio -->
				      		<div class="radio col-sm-12">
								  <label>
								  	<input type="radio" name="status" value="0">In Active <span class="checkmark"></span>
								</label>
							</div><!-- radio -->
                        </div>
                  </div><!-- form-group -->
                  <div class="download-discard-small pos-ab-bottom">
                  <button class="white-btn" type="button"data-dismiss="modal" onclick="window.location.href='<?= base_url('publishers') ?>';">Discard</button> 
                        <button type="submit" class="download-btn">Save</button>
                  </div><!-- download-discard-small -->
            </div><!-- -common-block -->
    </div><!-- add new tab -->
</div>
  </div>
</main>
<?php $this->load->view('common/fbc-user/footer'); ?>
<script src="<?php echo SKIN_JS; ?>publisher.js"></script>
