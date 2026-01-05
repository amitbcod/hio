<div class="main-inner">
	<div class="variant-common-block variant-list">
		<h1 class="head-name">Variant List </h1>
		<div class="select-attributes" id="variantlist-block-new">
			<ul>

			<input type="hidden"  name="Rounded_price_flag" id="Rounded_price_flag" value="<?php echo $Rounded_price_flag ?>" >
			
			<?php if($flag=='edit_variant'){
				
					if(isset($VariantMaster) && count($VariantMaster)>0){
					foreach($VariantMaster as $val){ ?>
					<li> 
						<label class="checkbox" for="variant_<?php echo $val['attr_id']; ?>"><?php echo $val['attr_name']; ?></label>  
					</li>
					<?php }
					} 
				}else { ?>
				<li> <label class="checkbox"><input type="checkbox"  name="simple_product" id="simple_product" value="1"  class="simple-check"  onclick=""> Simple Product <span class="checked" id="simple_product_sp"></span></label> </li>
				<?php
				if(isset($VariantList) && count($VariantList)>0){
				foreach($VariantList as $val){ ?>
				<li> <label class="checkbox" for="variant_<?php echo $val['id']; ?>"><input type="checkbox" name="variant_option[]" id="variant_<?php echo $val['id']; ?>" class="variant-single" value="<?php echo $val['id']; ?>" > <?php echo $val['attr_name']; ?> <span class="checked"></span></label>  
					
				</li>
				<?php }
				} 
				if(isset($VariantsBySeller) && count($VariantsBySeller)){
				foreach($VariantsBySeller as $attr){
				?>
				<li> <label class="checkbox"><input type="checkbox"  value="<?php echo $attr['id']; ?>" class="variant-single" name="variant_option[]"  id="variant_<?php echo $attr['id']; ?>" <?php echo ((isset($attr['is_default']) && $attr['is_default']==1))?'checked':''; ?>><?php echo $attr['attr_name']; ?> <span class="checked"></span></label>
			<?php } 
			} ?>
			
				
			<?php } ?>
			</ul>
			<div class="download-discard-small">
			<?php if($flag=='edit_variant'){ ?>
				<button class="white-btn" type="button" onclick="location.reload();">Discard</button>
			<?php }else { ?>
				<button class="white-btn" type="button" data-dismiss="modal">Discard</button>
				<button class="download-btn"   type="button" onclick="SaveProductType();">Save</button>
			<?php } ?>
			</div>
			<!-- download-discard-small -->
		</div>
		<!-- select-attributes -->
	</div>
	
</div>

<script>
$(document).ready(function(){
	
	
	$('.variant-single').each(function() {
		if ($(this).is(':checked')) {
			
				$('.simple-check').attr('checked',false);
				$('.simple-check').attr('readonly',true);
				$('.simple-check').attr('onclick','return false;');
				$('#product_type').val('configurable'); 
		}else{
			$('.simple-check').attr('checked',true);
			$('.simple-check').attr('readonly',false);
			$('#product_type').val('simple');
			$('.simple-check').attr('onclick','');
		}	
	});
			

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
	
	
	$(document).on("click",".variant-single",function(){
		var vchecked = $(".variant-single:checked").length;
		
		if(vchecked > 0){
			$('.simple-check').attr('checked',false);
			$('.simple-check').attr('readonly',true);
			$('.simple-check').attr('onclick','return false;');
			$('#product_type').val('configurable');
		   
		} else{
			$('.simple-check').attr('checked',true);
			$('.simple-check').attr('readonly',false);
			$('#product_type').val('simple');
			$('.simple-check').attr('onclick','');
			
			
		}	   
	});
	
	$(document).on("click",".simple-check",function(){
		var vchecked = $(".simple-check:checked").length;
		if(vchecked > 0){
			$('.variant-single').attr('checked',false);
			//$('.variant-single').attr('checked',false);
			$('#product_type').val('simple'); 
			$('.simple-check').attr('onclick','');
		}else{
			$('#product_type').val('configurable'); 
			$('.simple-check').attr('onclick','return false;');
		}
	});
	
});



	
</script>