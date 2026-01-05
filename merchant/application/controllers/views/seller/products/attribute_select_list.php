<div class="main-inner">
	<div class="variant-common-block variant-list"  id="sc_attr_list_inner">
		<h1 class="head-name">Attributes List </h1>
		<div class="select-attributes">
			<ul>
			<?php if($flag=='edit_attr'){
				
				if(isset($AttributesList) && count($AttributesList)){
					foreach($AttributesList as $attr){
					?>
					<!--<li> <label class="checkbox"><?php echo $attr['attr_name']; ?></li>-->
				<?php } 
				} 
				//print_r_custom($AttributesBySeller);
				if(isset($AttributesBySeller) && count($AttributesBySeller)){
					foreach($AttributesBySeller as $attr){
						//echo (isset($attr['is_default']) && $attr['is_default']==1)?'checked--------':'';
					?>
					
					<li> <label class="checkbox"><input type="checkbox" data-id="<?php echo $attr['id']; ?>"  value="<?php echo $attr['id']; ?>" class="select_attr_main" name="select_attr_main[]"  <?php echo (isset($attr['is_default']) && $attr['is_default']==1)?'onclick="return false"':'';  ?>    <?php echo (isset($attr['is_default']) && $attr['is_default']==1)?'checked':'';  ?>><?php echo $attr['attr_name']; ?> <span class="checked"></span></label>
					<?php if($attr['is_default']!=1){ ?>
					<?php } ?> </li>
				<?php } 
				} 

			} else{?> 
			<?php if(isset($AttributesList) && count($AttributesList)){
				foreach($AttributesList as $attr){
				?>
				<li> <label class="checkbox"><input type="checkbox"  value="<?php echo $attr['id']; ?>" class="select_attr_main" name="select_attr_main[]"  <?php echo ($attr['is_default']==1)?'checked':'';  ?> <?php echo ($attr['is_default']==1)?'onclick="return false"':'';  ?>><?php echo $attr['attr_name']; ?> <span class="checked"></span></label> 
				<?php if($attr['is_default']!=1){ ?>
				<?php } ?>

				<?php if($attr['created_by']==$this->session->userdata('LoginID')){ ?><?php } ?>
				</li>
			<?php } 
			} 
			if(isset($AttributesBySeller) && count($AttributesBySeller)){
				foreach($AttributesBySeller as $attr){
				?>
				<li> <label class="checkbox"><input type="checkbox"  value="<?php echo $attr['id']; ?>" class="select_attr_main" name="select_attr_main[]"  <?php echo ($attr['is_default']==1)?'checked':'';  ?> <?php echo ($attr['is_default']==1)?'onclick="return false"':'';  ?>><?php echo $attr['attr_name']; ?> <span class="checked"></span></label>
				<?php if($attr['is_default']!=1){ ?>
				
				<?php } ?>
				</li>
			<?php } 
			} ?>
			
			
			<?php } ?>
			
			</ul>

			<div class="download-discard-small">
				
				<?php


				if(isset($flag) && $flag=='add_attr'){?>
				<button class="white-btn"  type="button" data-dismiss="modal">Discard</button>
				<button class="download-btn"  type="button" onclick="LoadExtraAttribute('<?php //echo $CategoryDetail->id; ?>');">Save</button>
				<?php }else if(isset($flag) && $flag=='edit_attr'){?>
				<button class="white-btn"  type="button" data-dismiss="modal">Discard</button>
				<button class="download-btn"  type="button" onclick="LoadExtraAttribute('<?php //echo $CategoryDetail->id; ?>');">Save</button>
				
				<?php }else if(isset($flag) && $flag=='bulk-add'){?>
				<button class="white-btn"  type="button" data-dismiss="modal">Discard</button>
				<button class="download-btn"  type="button" onclick="SaveAttributeForCategory('<?php echo $CategoryDetail->id; ?>','attributes');">Save</button>
				
				<?php }else{ ?>
				<button class="white-btn"  type="button" onclick="EditSubCatRow(<?php echo $CategoryDetail->id; ?>);">Discard</button>
				<button class="download-btn" type="button" onclick="SaveAttributeForCategory('<?php echo $CategoryDetail->id; ?>','attributes');">Save</button>
				<?php } ?>
				
			 </div><!-- download-discard-small -->
		</div><!-- select-attributes -->
	</div><!-- variant-common-block -->

	

</div>
<script>
$(document).ready(function(){
	

	if($('#added_attr').length){
		var added_attr=$('#added_attr').val();
		var temp = new Array();
		
		temp = added_attr.split(",");

		if(added_attr!=''){
			
			
			$('.select_attr_main').each(function() {
				
				let cur_val=$(this).val();
				if(inArray(cur_val,temp)){
					
					$(this).attr('checked',true);
				}else{
					//$(this).attr('checked',false);
				}
				
			});
			
		}
	}
	
});
</script>