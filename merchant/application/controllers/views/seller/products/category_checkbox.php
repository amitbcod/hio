<?php 
if(isset($ProductData) && $ProductData->id!='')
{
	$product_id=$ProductData->id;
	$flag='edit';
	
	
}else{
	$product_id='';
	$flag='add';
}
?>

<div class="accordion custom-accordion " id="custom-accordion-one"> 
<ul class="common-list list-gc">
	<?php if(isset($CategoryTree) &&  count($CategoryTree)>0){
		foreach($CategoryTree as $parent_cat) {
		?>
		<li class="list-gc-item ">
			<div class="custom-control custom-checkbox"><label class="checkbox">
				<input type="checkbox" name="category[]" class="form-control" value="<?php echo $parent_cat['id']; ?>"  id="level_zero_cat_<?php echo $parent_cat['id']; ?>" onclick="LoadSubCategory('<?php echo $parent_cat['id']; ?>','<?php echo $flag; ?>','<?php echo $product_id; ?>');"  <?php echo (($flag=='edit') && count($cat_level_zero_selected)>0 &&(in_array($parent_cat['id'],$cat_level_zero_selected)))?'checked':''; ?>   <?php echo (isset($side_menu) && $side_menu=='product_view')?'readonly':''; ?> disabled>
				<span class="checked" ></span>
				<span class="sis-cat-name"><?php echo $parent_cat['cat_name']; ?></span>
				<a class="custom-accordion-title d-block py-1"
				data-toggle="collapse" href="#subCatOuter_<?php echo $parent_cat['id']; ?>"
				aria-expanded="false" aria-controls="subCatOuter_<?php echo $parent_cat['id']; ?>"  >&nbsp;<i
					class="accordion-arrow fa fa-angle-down">&nbsp;</i>
			</a></label>
		
			</div>
			
			
			<div  id="subCatOuter_<?php echo $parent_cat['id']; ?>" class="collapse <?php echo (($flag=='edit') && (count($cat_level_zero_selected)>0 &&(in_array($parent_cat['id'],$cat_level_zero_selected))))?'show':''; ?>"  data-parent="#custom-accordion-one">
			<ul class="common-list list-gc1">
				<?php if(isset($parent_cat['sub_category']) && count($parent_cat['sub_category'])>0){ ?>
				
					<?php foreach($parent_cat['sub_category'] as $sub_cat) { ?>
					<li class="list-gc-item ">
						<div class="custom-control custom-checkbox"><label class="checkbox"><input type="checkbox" name="sub_category[]" class="form-control" value="<?php echo $sub_cat['id']; ?>"  id="level_one_cat_<?php echo $sub_cat['id']; ?>"  <?php echo (($flag=='edit') && count($cat_level_one_selected)>0 &&(in_array($sub_cat['id'],$cat_level_one_selected)))?'checked':''; ?>   <?php echo (isset($side_menu) && $side_menu=='product_view')?'readonly':''; ?>  onclick="SelectParentCategory(this,<?php echo $parent_cat['id']; ?>,<?php echo $sub_cat['id']; ?>,'');" disabled><span class="checked"></span><span class="sis-cat-name"><?php echo $sub_cat['cat_name']; ?></span></label>
									
						</div>
						
						
						<div class="tags-category"  id="tagsCatOuter_<?php echo $parent_cat['id']; ?>_<?php echo $sub_cat['id']; ?>">
							<?php if(isset($parent_cat['sub_category']) && count($parent_cat['sub_category'])>0){ ?>
							<ul class=" common-list list-gc2">
								<?php foreach($sub_cat['sub_category'] as $tag_cat) { ?>
								<li class="list-gcc-item">
									<div class="custom-control custom-checkbox"><label class="checkbox"><input type="checkbox" name="child_category[]" class="form-control" value="<?php echo $tag_cat['id']; ?>"  id="level_two_cat_<?php echo $sub_cat['id']; ?>_<?php echo $tag_cat['id']; ?>"  <?php echo (($flag=='edit') && count($cat_level_two_selected)>0 &&(in_array($tag_cat['id'],$cat_level_two_selected)))?'checked':''; ?>   <?php echo (isset($side_menu) && $side_menu=='product_view')?'d-none':''; ?>    onclick="SelectParentCategory(this,<?php echo $parent_cat['id']; ?>,<?php echo $sub_cat['id']; ?>,<?php echo $tag_cat['id']; ?>);" disabled><span class="checked"></span><span class="sis-cat-name"><?php echo $tag_cat['cat_name']; ?></span></label>
								
					
									</div>
									
								</li>
								
								<?php } ?>
							
							</ul>
							
							<?php }	?>
							</div>
						
					</li>
					
					
					<?php } ?>
					
				
				<?php }	?>
				</ul>
				</div>
				
		</li>
	<?php 
		
		}
	} ?>
</ul>
</div>


<script type="text/javascript">


function LoadSubCategory(parent_id,flag,product_id='')
{
	$('#subCatOuter_'+parent_id).collapse('show');
	return false;
	
	//console.log($('#school_permisssion_'+school_id).is(':empty'));
	/*
	if(($('#level_zero_cat_'+parent_id).is(":checked")) || ($('#subCatOuter_'+parent_id).is(':empty'))){
		if(parent_id!='' && flag!='')
		{
			
			$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL+"sellerproduct/loadsubcategory/",
				data: {parent_id:parent_id,flag:flag,product_id:product_id},				
				beforeSend: function () { 
					$('#ajax-spinner').show();
				},			
				success: function(response) {
					$('#subCatOuter_'+parent_id).html(response);
					$('#subCategory_collapsediv'+parent_id).collapse('show');
					$('#ajax-spinner').hide();
					
				}
			});
		}else{
			$('#ajax-spinner').hide();
			return false;
		}
	}else{
		// $('#school_permisssion_'+school_id).html('');
		$('#ajax-spinner').hide();
		return false;
	}
	
	*/
}

function SelectParentCategory(elem,level_zero,level_one,level_two=''){
	if($(elem).is(':checked')){
		if(level_two!=''){
			$('#level_zero_cat_'+level_zero).prop('checked',true);
			$('#level_one_cat_'+level_one).prop('checked',true);
			
		}else{
			$('#level_zero_cat_'+level_zero).prop('checked',true);
		}
	}
	
}

function ConfirmCategoryDelete(id,cat_level,flag=''){
	
	var pcount=0;
	if(id!=''){
		
		$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL+"sellerproduct/getcatproductcount/",
				data: {id:id,cat_level:cat_level,flag:flag},				
				beforeSend: function () { 
					$('#ajax-spinner').show();
				},			
				success: function(response) {
					$('#ajax-spinner').hide();
					if(response !='error'){
						pcount=response;
						if(pcount>0){

							var conf_message="There are some products already assigned to this category, Still you want to delete this category? You won't be able to revert this.";
						}else{
							var conf_message="You won't be able to revert this!";
							
						}
						
						
						swal({
							title: "Are you sure? ",
							text: conf_message,
							type: "warning",
							showCancelButton: true,
							confirmButtonColor: "#3085d6",
							 cancelButtonColor: '#d33',
							confirmButtonText: "Yes, delete it!",
							cancelButtonText: "Cancel",
							closeOnConfirm: false,
							closeOnCancel: false
						}, function(isConfirm) {
							if (isConfirm) {
								
								DeleteCategory(id,cat_level);
								
							} else {
								swal.close();
							}
						});
						
					}else{
						swal('Error','Something went wrong!','error');
					}
					
					
				}
			});
	}
}

function DeleteCategory(id,cat_level){
	if(id!=''){
		
			$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL+"sellerproduct/deletecategory/",
				data: {id:id,cat_level:cat_level},				
				beforeSend: function () { 
					$('#ajax-spinner').show();
				},			
				success: function(response) {
					$('#ajax-spinner').hide();
					
					if(response=='success'){
						RefreshCategoryTree();

						swal('Success','Category deleted successfully!','success');
						
					}else{
						return false;
					}
				}
			});
	}else{
		return false;
	}
					
	
}
</script>