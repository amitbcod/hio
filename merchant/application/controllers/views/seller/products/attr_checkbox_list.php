<?php
if($type=='attributes'){

if(isset($AttributesList) && count($AttributesList)){
				foreach($AttributesList as $attr){
				?>
				<li> <label class="checkbox"><input type="checkbox" class="sub_cat_attr" name="sub_cat_attr[]" value="<?php echo $attr['id']; ?>"  checked <?php echo ($attr['is_default']==1)?'onclick="return false"':'';  ?>><?php echo $attr['attr_name']; ?> <span class="checked"></span></label> </li>
			<?php } 
			} 
			
			
}else if($type=='variants'){ 
if(isset($VariantList) && count($VariantList)){
				foreach($VariantList as $attr){
				?>
				<li> <label class="checkbox"><input type="checkbox" class="sub_cat_variant" name="sub_cat_variant[]" value="<?php echo $attr['id']; ?>"  checked <?php echo ($attr['is_default']==1)?'onclick="return false"':'';  ?>><?php echo $attr['attr_name']; ?> <span class="checked"></span></label> </li>
			<?php } 
			} 
}
			
			?>