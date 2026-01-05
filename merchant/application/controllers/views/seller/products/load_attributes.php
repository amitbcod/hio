<?php
$fbc_user_id	=	$this->session->userdata('LoginID');
if(isset($AttributesList) && count($AttributesList)>0){ ?>
  <?php foreach($AttributesList as $attr){
	  $attr_properties=$attr['attr_properties'];
	  
	  if($attr['is_default']==1){
		  continue;
	  }
	  
	  ?>
	<tr id="attr_row_<?php echo $attr['id']; ?>"  class="at-row">
	  <td><?php echo $attr['attr_name']; ?></td>
	  <td>
	  
	 <?php  if($attr_properties==1 || $attr_properties==3){
			if($attr_properties==3){
				$extra_class='sis-datepicker';
			}else{
				$extra_class='';
			}

		 ?>
			  <input type="text" name="attributes[<?php echo $attr['id']; ?>]" <?php echo ($attr_properties==3)?'readonly':''; ?> class="form-control required-field <?php echo $extra_class; ?>" id="attr_tf_<?php echo $attr['id']; ?>" value="" placeholder="Enter value">
		 <?php  }else if($attr_properties==2){ ?>
		 
		 	  <textarea name="attributes[<?php echo $attr['id']; ?>]" class="form-control" id="attr_tf_<?php echo $attr['id']; ?>" rows="5" cols="3"  placeholder="Enter value"></textarea>
		 
		   <?php  }else if($attr_properties==4){ ?>
			
			<div class="radio">
				  <label><input type="radio"  name="attributes[<?php echo $attr['id']; ?>]" checked value="Yes">Yes <span class="checkmark"></span></label>
				</div><!-- radio -->
				<div class="radio">
				  <label><input type="radio"  name="attributes[<?php echo $attr['id']; ?>]" value="No">No <span class="checkmark"></span></label>
				</div><!-- radio -->
			 </div><!-- col-md-6 -->
		    <?php  }else if($attr_properties==5){ 
			//$OptionList=$this->CommonModel->GetDropDownOptions($attr['id']);
			$OptionList= $this->EavAttributesModel->get_attributes_options_by_seller($attr['id']);
			
			?>
			<select class="form-control required-field" name="attributes[<?php echo $attr['id']; ?>]">
				<option value="">Select</option>
				<?php if(isset($OptionList) && count($OptionList)>0){
					foreach($OptionList as $option){
					?>
				<option value="<?php echo $option['id']; ?>"><?php echo $option['attr_options_name']; ?></option>
				
			<?php } } ?>
			</select>
			<?php  }else if($attr_properties==6){ 
			//$OptionList=$this->CommonModel->GetDropDownOptions($attr['id']);
			$OptionList= $this->EavAttributesModel->get_attributes_options_by_seller($attr['id']);
			?>
			<select class="form-control required-field multiple-selection" name="attributes[<?php echo $attr['id']; ?>][]"  multiple>
				<option value="">Select</option>
				<?php if(isset($OptionList) && count($OptionList)>0){
					foreach($OptionList as $option){
					?>
				<option value="<?php echo $option['id']; ?>"><?php echo $option['attr_options_name']; ?></option>
				
			<?php } } ?>
			</select>
			<?php } ?>
	 </td>			  
	  <td><a class="link-red" href="javascript:void(0);" onClick="removeAttrRow(<?php echo $attr['id']; ?>)">Delete</a></td>
	</tr>
	 
  <?php } ?>
  <?php } ?>
  
  
  
<script>
$(document).ready(function(){

	$(".sis-datepicker").datepicker({ 
			autoclose: true, 
			todayHighlight: true,
			format:'dd-mm-yyyy',
	  });
	  
	
	$('.required-field').each(function() {
        $(this).rules("add", 
            {
                required: true,
                messages: {
                    required: "Field is required",
                }
            });
    });
	
});
	
	
</script>
