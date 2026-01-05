<div class="main-inner ">
	<div class="variant-common-block variant-list">
		<h1 class="head-name pad-bottom-20">Category Details</h1>
		<div class="form-group row">
			<label for="" class="col-sm-2 col-form-label font-500">Category Name</label>
			<div class="col-sm-3">
				<input type="text" class="form-control" value="" id="sub_cat_name"   name="sub_cat_name" >
			</div>
		</div>
		<!-- form-group -->
		<div class="form-group row">
			<label for="" class="col-sm-2 col-form-label font-500">Catalogues Included</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" value="" id="sub_cat_des"   name="sub_cat_des">
			</div>
		</div>
		<!-- form-group -->
		
		<div class="select-attributes full-list">
			<h3 class="sub-cat-head">Select Attributes &nbsp; <span ondblclick="CreateAttribute();">( Double Click to edit  )</span><a href="javascript:void(0);" onclick="CreateAttribute();" class="link-purple float-right" >+  Add New Attribute</a></h3>
			
			<ul>
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
				<li> <label class="checkbox"><input type="checkbox"  value="<?php echo $attr['id']; ?>"  <?php echo ($attr['is_default']==1)?'checked':'';  ?> <?php echo ($attr['is_default']==1)?'onclick="return false"':'';  ?>><?php echo $attr['attr_name']; ?> <span class="checked"></span></label> </li>
			<?php } 
			} ?>
				
			</ul>
		</div>
		<!-- select-attributes -->
		<div class="select-attributes full-list select-var">
			<h3 class="sub-cat-head">Select Variants &nbsp; <span ondblclick="CreateVariant();">( Double Click to edit  )</span><a href="javascript:void(0);" onclick="CreateVariant();" class="link-purple float-right">+  Add New Variant</a></h3>
			<ul>
				<?php if(isset($DefaultVariantList) && count($DefaultVariantList)){
				foreach($DefaultVariantList as $attr){
				?>
				<li> <label class="checkbox"><input type="checkbox"  value="<?php echo $attr['id']; ?>"  <?php echo ($attr['is_default']==1)?'checked':'';  ?> <?php echo ($attr['is_default']==1)?'onclick="return false"':'';  ?>><?php echo $attr['attr_name']; ?> <span class="checked"></span></label> </li>
			<?php } 
			} ?>
			</ul>
		</div>
		<!-- select-attributes -->
	</div>
	<!-- variant-common-block -->
	<div class="download-discard-small ">
		<button class="white-btn" type="button"  data-dismiss="modal">Discard</button>
		<button class="download-btn"  onClick="SaveSubCategory();">Save</button>
	</div>
	<!-- download-discard-small -->
</div>