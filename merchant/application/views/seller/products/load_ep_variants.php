<?php
$conf_simple=array();
$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag();

$giftsTo = $this->SellerProductModel->get_gifts_data();

if(isset($VariantProducts) && count($VariantProducts)>0){
	foreach($VariantProducts as $variant){
		
			 $random_id=rand(1,9999);
			 
						 
			 $price_input_prop='';

			//  if($variant['shop_id']>0){
				 
			// 	 $seller_id = $variant['shop_id'];
			// 	 $owner_id =	$this->session->userdata('ShopID');
 
			// 	 $Price_permission_by_shopid = $this->SellerProductModel->getPricePermissionByShopID($seller_id,$owner_id);
			// 	 $shop_perm_to_change_price=$Price_permission_by_shopid->perm_to_change_price;
			// 	 if($shop_perm_to_change_price==0){
			// 		 $price_input_prop='readonly';
			// 	 }else{
			// 		 $price_input_prop='';
			// 	 }
					 
			//  }else{
			// 	 $price_input_prop='';
			//  }
			
			$available_qty=$variant['qty']-$variant['available_qty'];
			 
			 
			  ?>
			<tr id="variant_row_<?php echo $random_id; ?>"  class="vr-row">

						<?php 
						if($ProductData->product_inv_type == 'buy') {
							  if($ProductData->id < 0) { ?>
							  	<!-- <input type="checkbox" id="ckb_Variant"> -->
							 <td>
							  	<label class="checkbox">
	                              <input type="checkbox"  name="ckb_Variant[]" value="<?php echo $variant['id']; ?>"  > 
	                              <span class="checked"></span>
	                            </label>
	                        </td>
						<?php   } else {  } // No checkbox
							}elseif($ProductData->product_inv_type == 'virtual') { ?>
							  	<td>
							  		<label class="checkbox">
		                              <input type="checkbox"  name="ckb_Variant[]" value="<?php echo $variant['id']; ?>"  > 
		                              <span class="checked"></span>
		                            </label>
		                        </td>
							 <?php }elseif($ProductData->product_inv_type == 'dropship') { ?>
							 		<td>
							 		<label class="checkbox">
		                              <input type="checkbox"  name="ckb_Variant[]"  value="<?php echo $variant['id']; ?>"  > 
		                              <span class="checked"></span>
		                            </label>
		                           </td>
						<?php }	 ?>

					
					<!-- <td> <?php //echo ucwords($variant['product_inv_type']) ; ?></td> -->
			<?php
					
					if(isset($VariantMaster) && count($VariantMaster)>0){ 
					foreach($VariantMaster as $attr){
					
					 if(isset($side_menu) && $side_menu=='product_view'){				
						$OptionSelected=$this->ShopProductModel->getSingleDataByID('products_variants',array('product_id'=>$variant['id'],'parent_id'=>$ProductData->id,'attr_id'=>$attr['attr_id']),'attr_value');
					 }else{
						 $OptionSelected=$this->SellerProductModel->getSingleDataByID('products_variants',array('product_id'=>$variant['id'],'parent_id'=>$ProductData->id,'attr_id'=>$attr['attr_id']),'attr_value');
					 }
					
					$attr_option_selected=(isset($OptionSelected) && $OptionSelected->attr_value!='')?$OptionSelected->attr_value:'';
					
					//$OptionData=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('attr_id'=>$attr['attr_id']),'id,attr_id,attr_options_name');
					$AttrData=$this->CommonModel->getSingleDataByID('eav_attributes',array('id'=>$attr['attr_id']),'id,attr_name,attr_code');
					//$OptionList=$this->CommonModel->GetDropDownOptions($OptionData->attr_id);	
					
					 if(isset($side_menu) && $side_menu=='product_view'){	
						
						$OptionList= $this->EavAttributesModel->get_attributes_options_by_seller($attr['attr_id']);					 
					 }else{
						$OptionList= $this->EavAttributesModel->get_attributes_options_by_seller($attr['attr_id']);
					 }					

					$attr_code=$AttrData->attr_code;
					$attr_code=strtolower($attr_code);
					$input_name='variant_'.$attr_code;
					?>
					
					 <td>
					 <?php // echo $attr['attr_id'].'=='.$attr_option_selected; ?>
					 <select name="<?php echo $input_name; ?>[]" class="form-control" >
					 <?php
						if(isset($OptionList) && count($OptionList)>0){
					 foreach($OptionList as $option){ ?>
						<option value="<?php echo $option['id']; ?>"  <?php echo ($attr_option_selected==$option['id'])?'selected':''; ?> ><?php echo $option['attr_options_name']; ?></option>
					<?php } } ?>
					 </select>
					 </td>
					<?php }
					}
					?>
				
				  <td>
				  <input type="number" name="variant_stock[]" class="form-control required-field"  value="<?php echo $variant['qty']; ?>" data-ordered_qty="<?php echo ($variant['qty']-$variant['available_qty']); ?>"  onkeypress="return isNumberKey(event);"  onblur="validatestockqty(this,'<?php echo $variant['id']; ?>');" placeholder="Enter Value"></td>
				 <td><input type="number" name="variant_cost_price[]" class="form-control required-field"  value="<?php echo $variant['cost_price']; ?>" <?php echo $price_input_prop; ?> onkeypress="return isNumberKey(event);" placeholder="Enter Value"  <?php if((isset($ProductData->shop_product_id) && $ProductData->shop_product_id>0) && $Price_permission_by_shopid->can_increase_price){ ?> max="<?php echo $variant['cost_price']; ?>"   <?php } ?>  <?php if((isset($ProductData->shop_product_id) && $ProductData->shop_product_id>0) && $Price_permission_by_shopid->can_decrease_price==0){ ?> min="<?php echo $variant['cost_price']; ?>"   <?php } ?></td>
				  <td><input type="number" name="variant_price[]" class="form-control required-field  selling-price" <?php echo $price_input_prop; ?> value="<?php echo $variant['price']; ?>" onkeypress="return isNumberKey(event);" placeholder="Enter Value" id="variant_price_<?php echo $random_id; ?>" onblur="calculate_webshop_price(<?php echo $Rounded_price_flag?>,<?php echo $random_id; ?>);"  <?php if((isset($ProductData->shop_product_id) && $ProductData->shop_product_id>0) && $Price_permission_by_shopid->can_increase_price==0){ ?> max="<?php echo $variant['price']; ?>"   <?php } ?>  <?php if((isset($ProductData->shop_product_id) && $ProductData->shop_product_id>0) && $Price_permission_by_shopid->can_decrease_price==0){ ?> min="<?php echo $variant['price']; ?>"   <?php } ?></td>
				  <td><input type="number" class="form-control  tax-percent" onkeypress="return isNumberKey(event);" name="variant_tax_percent[]" value="<?php echo ($variant['tax_percent']>0)?$variant['tax_percent']:''; ?>"   onblur="calculate_webshop_price(<?php echo $Rounded_price_flag?>,<?php echo $random_id; ?>);"   id="variant_tax_percent_<?php echo $random_id; ?>"></td>	
				<td><input type="number" class="form-control required-field webshop-price" onkeypress="return isNumberKey(event);" name="variant_webshop_price[]"  id="variant_webshop_price_<?php echo $random_id; ?>" value="<?php echo $variant['webshop_price']; ?>"   ></td>
				  <td><input type="hidden" name="conf_simple[]" class="form-control "  value="<?php echo $variant['id']; ?>" >
				  <input type="text" name="variant_sku[]" onblur="skudbcompare(this,<?php echo $variant['id']; ?>);"  class="form-control required-field unique-sku valid-sku" value="<?php echo $variant['sku']; ?>" placeholder="Enter Value"></td>
				  <!-- <td ><input type="text" name="variant_barcode[]" onblur="barcodedbcompare(this,<?php //echo $variant['id']; ?>);" class="form-control "  value="<?php //echo $variant['barcode']; ?>" placeholder="Enter Value"></td> -->
				  <!-- <td><input type="text" class="form-control input-sm" name="variant_weight[]"  value="<?php //echo $variant['weight']; ?>" onkeypress="return isNumberKey(event);"></td> -->
				  <!-- <td><select class="form-control required-field webshop-gifts input-sm" name="gifts[]" disabled>
    					<option value="">Select Gifts</option>
							<?php foreach($giftsTo as $gifts):?>
								<option value="<?php echo $gifts['id']?>" <?php echo ($variant['gift_id'] == $gifts['id']) ? 'selected' : '' ?>><?php echo $gifts['name']?></option>
							<?php endforeach;?>
  						</select>
					  </td>
				  <td><input type="text" class="form-control required-field webshop-subissue input-sm" name="sub_issue[]"  value="<?php echo $variant['sub_issues']; ?>" readonly></td> -->
				  <!-- <td class="<?php //echo ((isset($side_menu) && $side_menu=='product_view'))?'d-none':''; ?>"><input type="file" name="variant_image[]" class="single-file  var-custom-file-input" multiple accept="image/*"><i class="fa fa-upload" aria-hidden="true"></i>&nbsp;<span class="md-status"></span></td>   -->
				<td class="<?php echo ((isset($side_menu) && $side_menu=='product_view'))?'d-none':''; ?>"><a class="link-red" href="javascript:void(0);" onClick="removeVariantRow(<?php echo $random_id; ?>,<?php echo $variant['id']; ?>)">Delete</a></td>
			</tr>
			 
		  <?php 
		  // onblur="barcodedbcompare(this);"  unique-barcode
		 
	}
}  
?>
  
  
  
<script>
$(document).ready(function(){

	
	
});
	
</script>
