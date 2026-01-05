<?php 
$selected_attributes=array();

 if(isset($side_menu) && $side_menu=='product_view'){	
	$shop_id		=	$shop_id;
 }else{
	$shop_id		=	$this->session->userdata('ShopID');
 }
if(isset($AttributesList) && count($AttributesList)>0){ ?>
  <?php foreach($AttributesList as $attr){
	  $AttrInfo=$this->CommonModel->getSingleDataByID('eav_attributes',array('id'=>$attr->attr_id),'id,attr_name,attr_properties');
	  if(isset($AttrInfo) && $AttrInfo->id!=''){
		  
	  }else{
		  continue;
	  }
	  
	  $attr_properties=$AttrInfo->attr_properties;
	  $attr_name=trim($AttrInfo->attr_name);
	  $attr_value=$attr->attr_value;
	  $attr_id=$attr->attr_id;
	  
	  $selected_attributes[]=$attr_id;
	  
	  
	  ?>
	<tr id="attr_row_<?php echo $attr_id; ?>"  class="at-row">
	  <td><?php echo $attr_name; ?></td>
	  <td>
	  
	 <?php  if($attr_properties==1 || $attr_properties==3){
			if($attr_properties==3){
				$extra_class='sis-datepicker';
				//$attr_value=(isset($attr_value) && $attr_value!='0')?date('d-m-Y',$attr_value):'';
			}else{
				$extra_class='';
			}

		 ?>
			  <input type="text" name="attributes[<?php echo $attr_id; ?>]" <?php echo ($attr_properties==3)?'readonly':''; ?>  class="form-control required-field <?php echo $extra_class; ?>" id="attr_tf_<?php echo $attr_id; ?>" value="<?php echo $attr_value; ?>" placeholder="Enter value">
		 <?php  }else if($attr_properties==2){ ?>
		 
		 	  <textarea name="attributes[<?php echo $attr_id; ?>]" class="form-control" id="attr_tf_<?php echo $attr_id; ?>" rows="5" cols="3"  placeholder="Enter value"><?php echo $attr_value; ?></textarea>
		 
		   <?php  }else if($attr_properties==4){ ?>
		   
			
			<div class="radio">
				  <label><input type="radio" name="attributes[<?php echo $attr_id; ?>]" <?php echo (isset($attr_value) && $attr_value=='Yes')?'checked':''; ?> value="Yes">Yes <span class="checkmark"></span></label>
				</div><!-- radio -->
				<div class="radio">
				  <label><input type="radio" name="attributes[<?php echo $attr_id; ?>]" <?php echo (isset($attr_value) && $attr_value=='No')?'checked':''; ?> value="No">No <span class="checkmark"></span></label>
				</div><!-- radio -->
			 </div><!-- col-md-6 -->
			 
		    <?php  }else if($attr_properties==5){ 
			//$OptionList=$this->CommonModel->GetDropDownOptions($attr_id);
			$OptionList= $this->EavAttributesModel->get_attributes_options_by_seller($attr_id);
			?>
			<select class="form-control required-field" name="attributes[<?php echo $attr_id; ?>]" disabled='disabled'>
				<option value="">Select</option>
				<?php if(isset($OptionList) && count($OptionList)>0){
					foreach($OptionList as $option){
					?>
				<option value="<?php echo $option['id']; ?>"  <?php echo (isset($attr_value) && $attr_value==$option['id'])?'selected':''; ?>><?php echo $option['attr_options_name']; ?></option>
				
			<?php } } ?>
			</select>
			<?php  }else if($attr_properties==6){ 
			//$OptionList=$this->CommonModel->GetDropDownOptions($attr_id);
			$OptionList= $this->EavAttributesModel->get_attributes_options_by_seller($attr_id);
			
			$selected_values=array();
			$selected_values=explode(',',$attr_value);
			array_filter($selected_values);
			?>
			<select class="form-control required-field multiple-selection" name="attributes[<?php echo $attr_id; ?>][]"  multiple>
				<option value="">Select</option>
				<?php if(isset($OptionList) && count($OptionList)>0){
					foreach($OptionList as $option){
					?>
				<option value="<?php echo $option['id']; ?>" <?php echo (is_array($selected_values) && count($selected_values)>0 &&  in_array($option['id'],$selected_values))?'selected':''; ?>><?php echo $option['attr_options_name']; ?></option>
				
			<?php } } ?>
			</select>
			<?php } ?>
	 </td>			  
	  <td  class="<?php echo ((isset($side_menu) && $side_menu=='product_view'))?'d-none':''; ?>">
	  <?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
	  	<!-- <a class="link-red" href="javascript:void(0);" onClick="removeAttrRow(<?php echo $attr_id; ?>)">Delete</a></td> -->
	  <?php } ?>
	</tr>
	 
  <?php } ?>
  <?php } ?>
  
  
  

