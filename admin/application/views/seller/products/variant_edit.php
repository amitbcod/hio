<div class="main-inner ">

        <form id="variantform" method="POST">
		<input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
            <input type="hidden" name="attribute_id" id="attribute_id" value="<?php echo  $attribute->id ?>">
			
			<input type="hidden" name="attribute_properties" value="5">
            <div class="variant-common-block variant-list" id="variant-add-new">
                <h1 class="head-name pad-bottom-20">Variant Details</h1>
                    <div class="form-group row">
                        <label for="attribute_name" class="col-sm-3 col-form-label font-500">Variant Name <span class="required">*</span></label>
                        <div class="col-sm-4">
                          <input type="text" class="form-control" name="attribute_name" id="attribute_name" value="<?php echo $attribute->attr_name ?>" required <?php echo ($attribute->created_by==$this->session->userdata('LoginID'))?'':'readonly'; ?>>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label font-500">Variant Code <span class="required">*</span></label>
                        <div class="col-sm-4">
                          <input type="text" class="form-control" name="attribute_code" id="attribute_code" value="<?php echo $attribute->attr_code ?>" required <?php echo ($attribute->created_by==$this->session->userdata('LoginID'))?'':'readonly'; ?>>
                        </div>
                  </div><!-- form-group -->

                  <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label font-500">Variant Description</label>
                        <div class="col-sm-7">
                            <textarea class="form-control" name="attribute_description" id="attribute_description" <?php echo ($attribute->created_by==$this->session->userdata('LoginID'))?'':'readonly'; ?>><?php echo $attribute->attr_description ?></textarea>
                        </div>
                  </div><!-- form-group -->
                 
                  <?php 
				  $fbc_user_id	=	$this->session->userdata('LoginID');
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
                    <label for="" class="col-sm-3 col-form-label font-500">Variant Values <span class="required">*</span></label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" data-role="tagsinput" name="tagsValues" id="tagsValues"  value="<?php echo $opt_str; ?>">
                    </div>
                  </div>
              
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
						<?php if(isset($flag) && $flag=='edit'){?>
						 <button class="white-btn" type="button"  data-dismiss="modal">Discard</button>
						<?php }else { ?>
                        <button class="white-btn" type="button" onclick="OpenVariantsListbyCategory('<?php echo $category_id; ?>');">Discard</button>
						<?php } ?>
                        <button type="submit" class="download-btn" id="variant_save_btn" >Save</button>
                  </div><!-- download-discard-small -->
            </div><!-- -common-block -->
        </form>

    </div><!-- add new tab -->
	<script type="text/javascript" src="<?php echo SKIN_JS; ?>attribute.js"></script>
	
	