	<div class="main-inner">
	<div class="add-bulk-inner2  ">
		<h1 class="head-name">Download CSV - Select Category<a class="float-right" href="<?php echo SKIN_URL; ?>uploads/sample/SIS_sample_product_import.csv"  target="_blank">Sample CSV</a></h1>
		
		<?php $ParentCategory=$this->CommonModel->get_category_for_seller($this->session->userdata('ShopID')); ?>
			<div class="add-bulk-inner2-form">
			   <div class="col-md-12 pr-5">
                     <div class="form-group row">
                        <label for="" class="col-sm-4 col-form-label font-500">Category</label>
                        <div class="col-sm-8">
                          <select class="form-control" onchange="GetSellerSubCategoryAjax(this.value);" name="category" id="category" >
							<option value="">Select Category</option>
							<?php if(isset($ParentCategory) && count($ParentCategory)>0){
								foreach($ParentCategory as $parent_cat){
								?>
								<option value="<?php echo $parent_cat['id']; ?>"><?php echo $parent_cat['cat_name']; ?></option>
							<?php } 
							} ?>
							<option value="new">Add New</option>
						</select>
                        </div>
                      </div>
                </div>
				 <div class="col-md-12 pr-5">
                     <div class="form-group row">
                        <label for="" class="col-sm-4 col-form-label font-500">Sub-Category</label>
                        <div class="col-sm-8">
                           <select class="form-control" name="sub_category" id="SubCategoryOptions" onchange="BulkCategoryChange(this.value,'import');">
									<option value="">Select Sub Category</option>
							</select>
                        </div>
                      </div>
                </div>
				
			</div>
		 
		
		 <div class="download-discard-small">
			<button class="white-btn" type="button"data-dismiss="modal">Discard</button>
			<button class="download-btn"  type="button" name="bulk_cat_save" id="bulk_cat_save" disabled="" onclick="SaveDownloadCSVCat();">Download</button>
		 </div>
		 </div>
		 <!-- add-bulk-inner2 -->
		 </div>