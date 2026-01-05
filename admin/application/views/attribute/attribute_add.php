<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <ul class="nav nav-pills">
        <li ><a href="<?= base_url('attribute') ?>">Attribute</a></li>
        <li class="active"><a>Add New</a></li>
    </ul>
    <div class="main-inner min-height-480">
    <form id="attributeForm" method="POST" action="<?php echo base_url('AttributeController/submitAttribute'); ?>">
            <input type="hidden" name="attribute_id" value="">
            <input type="hidden" name="type" value="1">
            <div class="variant-common-block variant-list">
                <h1 class="head-name pad-bottom-20">Attribute</h1>
                    <div class="form-group row">
                        <label for="attribute_name" class="col-sm-2 col-form-label font-500">Attribute Name<span class="required">*</span></label>
                        <div class="col-sm-3">
                        <?php echo form_input(['class'=> 'form-control','placeholder'=>'Enter attribute name','id'=>"attribute_name",'name'=>'attribute_name', 'value'=>set_value('attribute_name'),'required'=>'true','onkeypress'=>'return /^[a-zA-Z\s]+$/i.test(event.key)','maxlength' => '30'] ); ?>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Attribute Code<span class="required">*</span></label>
                        <div class="col-sm-3">
                        <?php echo form_input(['class'=> 'form-control','placeholder'=>'Enter attribute code','id'=>"attribute_code",'name'=>'attribute_code', 'value'=>set_value('attribute_code'),'required'=>'true','maxlength' => '30']); ?>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Attribute Description</label>
                        <div class="col-sm-3">
                        <?php echo form_textarea(['class'=> 'form-control','placeholder'=>'Enter description','name'=> 'attribute_description','maxlength' => '250', 'value'=>set_value('attribute_description')]); ?>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Attribute Properties<span class="required">*</span></label>
                        <div class="col-sm-3">
                        <?php
                            $options = array(
                                ''         => 'Select Attribute Properties',
                                '1'         => 'Text Field',
                                '2'           => 'Text Area',
                                '3'         => 'Date',
                                '4'        => 'Yes/No',
                                '5'        => 'Dropdown',
                                '6'        => 'Multiselect'
                                );
                             ?>
                            <select class="form-control" name="attribute_properties" id="attribute_properties" >
                                <option value="">Select Attribute Properties</option>
                                <option value="1">Text Field</option>
                                <option value="2">Text Area</option>
                                <option value="3">Date</option>
                                <option value="4">Yes/No</option>
                                <option value="5">Dropdown</option>
                                <option value="6">Multiselect</option>
                            </select>
                        </div>
                  </div><!-- form-group -->
                  
                  <div class="form-group row" style="display: none;" id="slectvalue">
                    <label for="" class="col-sm-2 col-form-label font-500">Attribute Values<span class="required">*</span></label>
                    <div class="col-sm-3">
                        <?php echo form_input(['class'=> 'form-control','placeholder'=>'Enter value','name'=>'tagsValues', 'value'=>set_value('tagsValues'),'data-role'=>"tagsinput"]); ?>
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
                        <button class="white-btn" type="button"data-dismiss="modal" onclick="window.location.href='<?= base_url('attribute') ?>';">Discard</button> 

                        <button type="submit" name="attributebtn" id="attributebtn" class="download-btn">Save</button>
                  </div><!-- download-discard-small -->
            </div><!-- -common-block -->
    </div><!-- add new tab -->
</div>
  </div>
</main>
<script src="<?php echo SKIN_JS; ?>attribute.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
