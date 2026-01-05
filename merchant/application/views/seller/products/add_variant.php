<div class="main-inner">
	<div class="variant-common-block variant-list  " id="variant-add-new">
		<h1 class="head-name pad-bottom-20">Variant Details</h1>
		<form id="variantform" method="POST">
		<input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
            <input type="hidden" name="attribute_id" id="attribute_id" value="">
		<div class="form-group row">
			<label for="" class="col-sm-2 col-form-label font-500">Variant Name <span class="required">*</span></label>
			<div class="col-sm-3">
				<input type="text" class="form-control" value="" name="attribute_name" id="attribute_name">
			</div>
		</div>
		<!-- form-group -->
	 <div class="form-group row">
			<label for="" class="col-sm-2 col-form-label font-500">Variant Code <span class="required">*</span></label>
			<div class="col-sm-3">
			  <input type="text" class="form-control" name="attribute_code" id="attribute_code" value="" required >
			</div>
	  </div><!-- form-group -->
		<div class="form-group row">
			<label for="" class="col-sm-2 col-form-label font-500">Variant Description</label>
			<div class="col-sm-7">
				<textarea class="form-control" name="attribute_description" id="attribute_description"></textarea>
			</div>
		</div>
		<!-- form-group -->
		<div class="form-group row">
			<label for="" class="col-sm-2 col-form-label font-500">Variant Values <span class="required">*</span></label>
			<div class="col-sm-3">
				<input type="text" class="form-control" value="" data-role="tagsinput" name="tagsValues" id="tagsValues" >
			</div>
		</div>
		<!-- form-group -->
		
		 <div class=" switch-onoff">
					  <div class="form-group row">
						<label for="" class="col-sm-2 col-form-label font-500">Display On Frontend</label>
							<label class="checkbox">
								<input type="checkbox" name="display_on_frontend" value="1" autocomplete="off"    > 
								<span class="checked"></span>
					</div>	
					
					 <div class="form-group row">
						<label for="" class="col-sm-2 col-form-label font-500">Filterable With Result</label>
							<label class="checkbox">
								<input type="checkbox" name="filterable_with_results" value="1" autocomplete="off"  > 
								<span class="checked"></span>
					</div>	
				</div><!-- bs-example -->
                 
				 
		<div class="download-discard-small">
			<button class="white-btn" type="button" onclick="OpenVariantsListbyCategory('<?php echo $category_id?>','add');">Discard</button>
			<button class="download-btn" type="submit"  id="variant_save_btn">Save</button>
		</div>
		</form>
		<!-- download-discard-small -->
	</div>
	<!-- variant-common-block -->
</div>

<script type="text/javascript" src="<?php echo SKIN_JS; ?>attribute.js"></script>