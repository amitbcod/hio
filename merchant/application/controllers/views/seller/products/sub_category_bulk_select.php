<div class="main-inner ">
	<div class="variant-common-block variant-list">
		<h1 class="head-name pad-bottom-20">Download  CSV - Select Category Details</h1>
		<input type="hidden" class="" value="<?php echo $CategoryDetail->id; ?>" id="root_category_id"   name="root_category_id" >
		<input type="hidden" class="" value="<?php echo $SubCategoryDetail->id; ?>" id="sub_category"   name="sub_category" >
		<div class="form-group row">
			<label for="" class="col-sm-2 col-form-label font-500">Category Name</label>
			<div class="col-sm-3">
				<input type="text" class="form-control" value="<?php echo $CategoryDetail->cat_name; ?>" id="root_cat_name"   name="root_cat_name"  <?php echo (isset($CategoryDetail) && $CategoryDetail->created_by_type==0)?'readonly':'';  ?> >
			</div>
		</div>
		
		<div class="form-group row">
			<label for="" class="col-sm-2 col-form-label font-500">Sub Category</label>
			<div class="col-sm-3">
				<input type="text" class="form-control" value="<?php echo $SubCategoryDetail->cat_name; ?>" id="sub_cat_name"   name="sub_cat_name"  <?php echo (isset($SubCategoryDetail) && $SubCategoryDetail->created_by_type==0)?'readonly':'';  ?> >
			</div>
		</div>
		
		
		<div class="select-attributes full-list">
			<h3 class="sub-cat-head">Select Attributes &nbsp; <span  ondblclick="OpenAttributeListPopup(<?php echo $SubCategoryDetail->id; ?>);" >( Double Click to edit  )</span><a href="javascript:void(0);" onclick="OpenAttributeListPopup(<?php echo $SubCategoryDetail->id; ?>,'bulk-add','attributes');" class="link-purple float-right" >+  Add New Attribute</a></h3>
			
			<ul id="sc_attr_list">
			
			
			<?php 
			$SystemDefault=$this->EavAttributesModel->get_default_attributes();
			
			if(isset($SystemDefault) && count($SystemDefault)){
				foreach($SystemDefault as $attr){
				?>
				<li> <label class="checkbox"><input type="checkbox" name="sub_cat_attr[]"  class="sub_cat_attr"  value="<?php echo $attr['id']; ?>"  <?php echo (($attr['is_default']==1) || ($seller_attr==1))?'checked':'';  ?> <?php echo ($attr['is_default']==1)?'onclick="return false"':'';  ?>><?php echo $attr['attr_name']; ?> <span class="checked"></span></label> </li>
			<?php } 
			} 
			
			if(isset($DefaultAttrList) && count($DefaultAttrList)){
				foreach($DefaultAttrList as $attr){
				?>
				<li> <label class="checkbox"><input type="checkbox" name="sub_cat_attr[]"  class="sub_cat_attr"  value="<?php echo $attr['id']; ?>"  <?php echo (($attr['is_default']==1) || ($seller_attr==1))?'checked':'';  ?> <?php echo ($attr['is_default']==1)?'onclick="return false"':'';  ?>><?php echo $attr['attr_name']; ?> <span class="checked"></span></label> </li>
			<?php }  
			} ?>
				
			</ul>
		</div>
		<!-- select-attributes -->
		<div class="select-attributes full-list select-var">
			<h3 class="sub-cat-head">Select Variants &nbsp; <span ondblclick="OpenVariantsMaster(<?php echo $SubCategoryDetail->id; ?>,'bulk-add');">( Double Click to edit  )</span><a href="javascript:void(0);" onclick="OpenVariantsMaster(<?php echo $SubCategoryDetail->id; ?>,'bulk-add');" class="link-purple float-right">+  Add New Variant</a></h3>
			<ul id="sc_variant_list">
				<?php			
				if(isset($DefaultVariantList) && count($DefaultVariantList)){
				foreach($DefaultVariantList as $attr){
				?>
				<li> <label class="checkbox"><input type="checkbox" class="sub_cat_variant"  name="sub_cat_variant[]" value="<?php echo $attr['id']; ?>"  <?php echo (($attr['is_default']==1) || ($seller_attr==1))?'checked':'';  ?> <?php echo ($attr['is_default']==1)?'onclick="return false"':'';  ?>><?php echo $attr['attr_name']; ?> <span class="checked"></span></label> </li>
			<?php } 
			} ?>
			</ul>
		</div>
		<!-- select-attributes -->
	</div>
	<!-- variant-common-block -->
	<div class="download-discard-small ">
		<button class="white-btn" type="button" data-dismiss="modal">Discard</button>
		<button class="download-btn" type="button"   onClick="DownloadProductCSV();">Download</button>
		
		<a id="exportProductCSV"  class="d-none" href="<?php echo base_url(); ?>sellerproduct/downloadproductcsv?"><i class="fa fa-file-csv"></i> Export Products</a>
	</div>
	<!-- download-discard-small -->
</div>
