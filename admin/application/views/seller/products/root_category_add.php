<div class="main-inner ">
	<div class="variant-common-block variant-list">
		<h1 class="head-name pad-bottom-20">Category - Add New</h1>
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Category Name</label>
			<div class="col-sm-4">
				<input type="text" class="form-control" value="" name="rc_name" id="rc_name">
				<span class="error" id="rc-error"></span>
			</div>
		</div>
		<!-- form-group -->
		<div class="form-group row">
			<label for="" class="col-sm-3 col-form-label font-500">Category Description</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" value=""  name="rc_description" id="rc_description">
			</div>
		</div>
		<!-- form-group -->
		
		<h3 class="sub-cat-head">SUB CATEGORIES <a href="javascript:void(0);" class="link-purple float-right"  onclick="CreateSubCategoryRow();">+  Add New Sub-Category</a></h3>
		<div class="sub-cat-container" id="sub-cat-container">
			<div class="form-group row sub-cat"  id="scr_1">
				<label for="" class="col-sm-3 col-form-label font-500">Category Name</label>
				<div class="col-sm-4">
				  <input type="text" class="form-control sub-cat-single" value="" name="level_one_category[]" >
				</div>
				<a href="javascript:void(0);" class="edit-purple link-purple" onclick="RemoveSubCatRow(1);"> Remove </a>
			</div>
		</div>
		
	</div>
	<!-- variant-common-block -->
	<div class="download-discard-small ">
		<button class="white-btn" type="button"  data-dismiss="modal">Discard</button>
		<button class="download-btn"  onClick="SaveRootCategory();">Save</button>
	</div>
	<!-- download-discard-small -->
</div>