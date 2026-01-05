<div class="main-inner ">
	<div class="variant-common-block variant-list">
		<h1 class="head-name pad-bottom-20">Sub Category Details</h1>
		<input type="hidden" class="" value="<?php echo $CategoryDetail->id; ?>" id="hidden_sc_id"   name="hidden_sc_id" >
		<div class="form-group row">
			<label for="" class="col-sm-2 col-form-label font-500">Category Name</label>
			<div class="col-sm-3">
				<input type="text" class="form-control" value="<?php echo $CategoryDetail->cat_name; ?>" id="sub_cat_name"   name="sub_cat_name"  <?php echo (isset($CategoryDetail) && $CategoryDetail->created_by_type==0)?'readonly':'';  ?> >
			</div>
		</div>
		<!-- form-group -->
		<div class="form-group row">
			<label for="" class="col-sm-2 col-form-label font-500">Catalogues Included</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" value="<?php echo (isset($category_tags) && $category_tags!='')?$category_tags:''; ?>" id="sub_child_ids"   name="sub_child_ids" data-role="tagsinput">
			</div>
		</div>
		<!-- form-group -->
		
		<div class="select-attributes full-list">
			<h3 class="sub-cat-head">Select Attributes &nbsp; <span  ondblclick="OpenAttributeListPopup(<?php echo $CategoryDetail->id; ?>);" >( Double Click to edit  )</span><a href="javascript:void(0);" onclick="OpenAttributeListPopup(<?php echo $CategoryDetail->id; ?>,'add','attributes');" class="link-purple float-right" >+  Add New Attribute</a></h3>
			
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
			<h3 class="sub-cat-head">Select Variants &nbsp; <span ondblclick="OpenVariantsMaster(<?php echo $CategoryDetail->id; ?>,'add');">( Double Click to edit  )</span><a href="javascript:void(0);" onclick="OpenVariantsMaster(<?php echo $CategoryDetail->id; ?>,'add');" class="link-purple float-right">+  Add New Variant</a></h3>
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
		<button class="white-btn" type="button" onclick="OpenEditCategory(<?php echo $CategoryDetail->parent_id; ?>)">Discard</button>
		<button class="download-btn" type="button"   onClick="SaveSubCategory();">Save</button>
	</div>
	<!-- download-discard-small -->
</div>

<script>
$(document).ready(function(){
// Destroy all previous bootstrap tags inputs (optional)
        $('input[data-role="tagsinput"]').tagsinput('destroy');
        // Create the bootstrap tag input UI
        $('input[data-role="tagsinput"]').tagsinput('items');
});
</script>