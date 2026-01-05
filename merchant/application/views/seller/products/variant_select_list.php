<div class="main-inner">
	<div class="variant-common-block variant-list">
		<h1 class="head-name">Variant List </h1>
		<div class="select-attributes" id="variantlist-block-new">
			<ul>
			<?php 
			
			if(isset($VariantMaster) && count($VariantMaster)>0){
					foreach($VariantMaster as $val){ ?>
					<li> 
						<label class="checkbox" for="variant_<?php echo $val['id']; ?>"><label class="checkbox"><input type="checkbox"  value="<?php echo $val['id']; ?>" class="select_attr_main" name="select_attr_main[]"  <?php echo ($val['is_default']==1)?'checked':'';  ?> <?php echo ($val['is_default']==1)?'onclick="return false"':'';  ?>><?php echo $val['attr_name']; ?><span class="checked"></span></label>  
						<a href="javascript:void(0);" class="link-red"  onclick="EditVariant(<?php echo $val['id']; ?>,'<?php echo $CategoryDetail->id; ?>');">Edit</a>
					</li>
					<?php }
					}
			?>
			
				<li>
					<p><a href="javascript:void(0);" class="link-purple"  onclick="OpenCreateVariantPopup('<?php echo $CategoryDetail->id; ?>');">+  Add New Variant</a></p>
				</li>
			
			</ul>
			<div class="download-discard-small">
			<?php if(isset($flag) && $flag=='bulk-add'){?>
			
				<button class="white-btn"  type="button" data-dismiss="modal">Discard</button>
			<?php }else{?>
				<button class="white-btn"  type="button" onclick="EditSubCatRow('<?php echo $CategoryDetail->id; ?>');">Discard</button>
			<?php } ?>
				<button class="download-btn"  type="button" onclick="SaveVariantForCategory('<?php echo $CategoryDetail->id; ?>','variants');">Save</button>
			
			</div>
			<!-- download-discard-small -->
		</div>
		<!-- select-attributes -->
	</div>
	
</div>

<script>
$(document).ready(function(){
	

	if($('#added_variant').length){
		var added_variant=$('#added_variant').val();
		var temp = new Array();
		
		temp = added_variant.split(",");

		if(added_variant!=''){
			
			$('.simple-check').attr('checked',false);
			$('.simple-check').attr('readonly',true);
			$('.simple-check').attr('onclick','return false;');
			
			$('.variant-single').each(function() {
				
				let cur_val=$(this).val();
				if(inArray(cur_val,temp)){
					
					$(this).attr('checked',true);
				}else{
					$(this).attr('checked',false);
				}
				
			});
			
		}
	}
	
});



	
</script>