<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <ul class="nav nav-pills">
        <li ><a href="<?= base_url('variants') ?>">Variants</a></li>
        <li class="active"><a>Add New</a></li>
    </ul>
    <div class="main-inner min-height-480">
    <form id="variantForm" method="POST" action="<?php echo base_url('VariantsController/submitVariant'); ?>">
    <input type="hidden" name="attribute_id" value="">
            <input type="hidden" name="type" value="1">
            <input type="hidden" name="attribute_properties" value="<?= '5' ?>">
            <div class="variant-common-block variant-list">
                <h1 class="head-name pad-bottom-20">Variants</h1>
                    <div class="form-group row">
                        <label for="variant_name" class="col-sm-2 col-form-label font-500">Variant Name<span class="required">*</span></label>
                        <div class="col-sm-3">
                        <?php echo form_input(['class'=> 'form-control','placeholder'=>'Enter variant name','id'=>"attribute_name",'name'=>'attribute_name', 'value'=>set_value('attribute_name'),'required'=>'true', 'onkeypress'=>'return /^[a-zA-Z\s]+$/i.test(event.key)','maxlength' => '30']); ?> 
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Variant Code<span class="required">*</span></label>
                        <div class="col-sm-3">
                        <?php echo form_input(['class'=> 'form-control','placeholder'=>'Enter variant code','id'=>"attribute_code",'name'=>'attribute_code','maxlength' => '30', 'value'=>set_value('attribute_code'),'required'=>'true']); ?>
                        </div>
                  </div><!-- form-group -->

                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Variant Description</label>
                        <div class="col-sm-3">
                        <?php echo form_textarea(['class'=> 'form-control','placeholder'=>'Enter description','name'=> 'attribute_description', 'maxlength' => '250','value'=>set_value('attribute_description')]); ?>
                        </div>
                  </div><!-- form-group -->
                                    
                  <div class="form-group row" style="display: show;" id="slectvalue">
                    <label for="" class="col-sm-2 col-form-label font-500">Variant Values<span class="required">*</span></label>
                    <div class="col-sm-3">
                    <?php echo form_input(['class'=> 'form-control','placeholder'=>'Enter variant code','id'=>"tagsValues",'name'=>'tagsValues', 'value'=>set_value('tagsValues'),'data-role'=>"tagsinput",'required'=>'true']); ?>
                    </div>
                  </div>
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
                  <button class="white-btn" type="button"data-dismiss="modal" onclick="window.location.href='<?= base_url('variants') ?>';">Discard</button> 
                        <button type="submit" id="variantbtn" class="download-btn">Save</button>
                  </div><!-- download-discard-small -->
            </div><!-- -common-block -->
    </div><!-- add new tab -->
</div>
  </div>
</main>
<?php $this->load->view('common/fbc-user/footer'); ?>
<script src="<?php echo SKIN_JS; ?>variants.js"></script>
