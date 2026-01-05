<div class="main-inner ">

        <form id="attributeForm" method="POST">
		<input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
            <input type="hidden" name="attribute_id" id="attribute_id" value="<?php echo  $attribute->id ?>">
            <div class="variant-common-block variant-list">
                <h1 class="head-name pad-bottom-20">Attribute</h1>
                    <div class="form-group row">
                        <label for="attribute_name" class="col-sm-3 col-form-label font-500">Attribute Name <span class="required">*</span></label>
                        <div class="col-sm-4">
                          <input type="text" class="form-control" name="attribute_name" id="attribute_name" value="<?php echo $attribute->attr_name ?>" required <?php echo ($attribute->created_by==$this->session->userdata('LoginID'))?'':'readonly'; ?> >
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label font-500">Attribute Code <span class="required">*</span></label>
                        <div class="col-sm-4">
                          <input type="text" class="form-control" name="attribute_code" id="attribute_code" value="<?php echo $attribute->attr_code ?>" <?php echo ($attribute->created_by==$this->session->userdata('LoginID'))?'':'readonly'; ?> required >
                        </div>
                  </div><!-- form-group -->

                  <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label font-500">Attribute Description</label>
                        <div class="col-sm-7">
                            <textarea class="form-control" name="attribute_description" id="attribute_description" <?php echo ($attribute->created_by==$this->session->userdata('LoginID'))?'':'readonly'; ?>><?php echo $attribute->attr_description ?></textarea>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row read-only">
                        <label for="" class="col-sm-3 col-form-label font-500">Attribute Properties <span class="required">*</span></label>
                        <div class="col-sm-4 read-only">
                            <?php $seValue = $attribute->attr_properties ?>
                            <select class="form-control" name="attribute_properties" id="attribute_properties" readonly>
                                <option value="">Select Attribute Properties</option>
                                <option value="1" <?php if($seValue == 1) echo "selected";?>>Text Field</option>
                                <option value="2" <?php if($seValue == 2) echo "selected";?>>Text Area</option>
                                <option value="3" <?php if($seValue == 3) echo "selected";?>>Date</option>
                                <option value="4" <?php if($seValue == 4) echo "selected";?>>Yes/No</option>
                                <option value="5" <?php if($seValue == 5) echo "selected";?>>Dropdown</option>
                                <option value="6" <?php if($seValue == 6) echo "selected";?>>Multiselect</option>

                            </select>
                        </div>
                  </div><!-- form-group -->
                  <?php if($seValue == 5 || $seValue == 6) {
					 $shop_id	=	$this->session->userdata('ShopID');
					 $options_arr= $this->EavAttributesModel->get_attributes_options_by_seller($shop_id,$attribute->id);
					 $options_arr_selected=array();
					  if(isset($options_arr) && count($options_arr)>0){
						 foreach($options_arr as $option){
							 $options_arr_selected[]=$option['attr_options_name'];
						 }
					  }
					 
					 $opt_str=implode(',',$options_arr_selected);
					  ?>
                  <div class="form-group row" id="slectvalue">
                    <label for="" class="col-sm-3 col-form-label font-500">Attribute Values <span class="required">*</span></label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" data-role="tagsinput" name="tagsValues" id="tagsValues"  value="<?php echo $opt_str; ?>">
                    </div>
                  </div>
              <?php } ?>
                 
				  
				   <?php 
				   $display_on_frontend = (isset($attribute_display->display_on_frontend) && $attribute_display->display_on_frontend==1)?1:'';
				   $filterable_with_results = (isset($attribute_display->filterable_with_results) && $attribute_display->filterable_with_results==1)?1:'';
				   ?>
				   <div class=" switch-onoff">
					  <div class="form-group row">
						<label for="" class="col-sm-3 col-form-label font-500">Display On Frontend</label>
							<div class="col-sm-4">
							<label class="checkbox">
								<input type="checkbox" name="display_on_frontend" value="1" autocomplete="off"   <?php if($display_on_frontend == 1){echo "checked";} ?>  > 
								<span class="checked"></span>
								</label>
							</div>
					</div>	
					
					 <div class="form-group row">
						<label for="" class="col-sm-3 col-form-label font-500">Filterable With Result</label>
						<div class="col-sm-4">
							<label class="checkbox">
								<input type="checkbox" name="filterable_with_results" value="1" autocomplete="off"   <?php if($filterable_with_results == 1){echo "checked";} ?>> 
								<span class="checked"></span>
								</label>
							</div>
					</div>	
				</div><!-- bs-example -->
				
                  <div class="download-discard-small ">
                        <button class="white-btn" type="button" onclick="OpenAttributeListPopup('<?php echo (isset($category_id) && $category_id!='')?$category_id:0; ?>','add_attr');">Discard</button>
                        <button type="submit" class="download-btn" id="attr_save_btn">Save</button>
                  </div><!-- download-discard-small -->
            </div><!-- -common-block -->
        </form>

    </div><!-- add new tab -->
	<script type="text/javascript" src="<?php echo SKIN_JS; ?>attribute.js"></script>
	