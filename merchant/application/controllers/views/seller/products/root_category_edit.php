<div class="main-inner ">
	<div class="variant-common-block variant-list">
		<input type="hidden" id="hidden_rc_id" value="<?php echo $CategoryDetail->id; ?>">
		<h1 class="head-name pad-bottom-20">Category</h1>
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Category Name</label>
			<div class="col-sm-4">
				<input type="text" class="form-control" value="<?php echo $CategoryDetail->cat_name; ?>" name="rc_name" id="rc_name"  <?php echo (isset($CategoryDetail) && $CategoryDetail->created_by_type==0)?'readonly':'';  ?>>
				<span class="error" id="rc-error"></span>
			</div>
		</div>
		<!-- form-group -->
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Category Description</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" value="<?php echo (isset($CategoryDetail->cat_description))?$CategoryDetail->cat_description:''; ?>"  name="rc_description" id="rc_description" <?php echo (isset($CategoryDetail) && $CategoryDetail->created_by_type==0)?'readonly':'';  ?>>
			</div>
		</div>
		<!-- form-group -->
		
		<h3 class="sub-cat-head">SUB CATEGORIES <a href="javascript:void(0);" class="link-purple float-right"  onclick="CreateSubCategoryRow();">+  Add New Sub-Category</a></h3>
		<div class="sub-cat-container" id="sub-cat-container">
			<?php if(isset($SubCategoryList)  && count($SubCategoryList)>0){
				foreach($SubCategoryList as $val){
					$CateInfo=$this->CommonModel->getSingleDataByID('category',array('id'=>$val['id']),'id,created_by_type');
				?>
			<div class="form-group row sub-cat"  id="scr_<?php echo $val['cat_name']; ?>">
				<label for="" class="col-sm-3 col-form-label font-500">Category Name</label>
				<div class="col-sm-4">
				  <input type="text" class="form-control" value="<?php echo $val['cat_name']; ?>"  name="level_one_category[<?php echo $val['id']; ?>]" <?php echo (isset($CateInfo) && $CateInfo->created_by_type==0)?'readonly':'';  ?> >
				  <input type="hidden" class="form-control" value="<?php echo $val['id']; ?>" name="hidden_sub_cat[<?php echo $val['id']; ?>]" >
				</div>
				<a href="javascript:void(0);" class="edit-purple link-purple" onclick="EditSubCatRow(<?php echo $val['id']?>);"> Edit </a>
			</div>
			<?php }
			} ?>
		</div>
		
	</div>
	<!-- variant-common-block -->
	<div class="download-discard-small ">
		<button class="white-btn" type="button"  data-dismiss="modal">Discard</button>
		<button class="download-btn" type="button"  onClick="SaveExistingCategory();">Save</button>
	</div>
	<!-- download-discard-small -->
</div>