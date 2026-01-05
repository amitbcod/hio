<?php
$fbc_user_id	=	$this->session->userdata('LoginID');
$shop_id		=	$this->session->userdata('ShopID');

$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag($shop_id);
//echo "<pre>";print_r($Combinations);
if(isset($Combinations) && count($Combinations)>0){ ?>
  <?php foreach($Combinations as $key=>$sub_group){
	 $random_id=rand(1,9999);
	  ?>
	<tr id="variant_row_<?php echo $random_id; ?>"  class="vr-row">
		<?php if(isset($flag) && $flag == 'edit') { ?>
			<?php if(isset($variant_products_count) && isset($get_own_products_count) && $variant_products_count != $get_own_products_count) { ?>
				<!-- <td><?php //echo $variant_products_count.'-*-'.$get_own_products_count; ?></td> -->
				<!-- <td><?php //echo $variant_products_count.'-**-'.$get_own_products_count; ?></td> -->
			<?php } else { ?>
				 <!-- <td><?php //echo $variant_products_count.'-*-'.$get_own_products_count; ?></td> -->

			 <?php } ?>
		<?php } else
			{ ?>

			<?php } ?>
		<?php foreach($sub_group as $k=>$val){
			$OptionData=$this->CommonModel->getSingleDataByID('eav_attributes_options',array('id'=>$val),'id,attr_id,attr_options_name');
			$AttrData=$this->CommonModel->getSingleDataByID('eav_attributes',array('id'=>$OptionData->attr_id),'id,attr_name,attr_code');
			//$OptionList=$this->CommonModel->GetDropDownOptions($OptionData->attr_id);
			$OptionList= $this->EavAttributesModel->get_attributes_options_by_seller($OptionData->attr_id);
			$attr_code=$AttrData->attr_code;
			$attr_code=strtolower($attr_code);
      $giftsTo = $this->SellerProductModel->get_gifts_data();
			$input_name='variant_'.$attr_code;
			?>
			 <td>
			 <select name="<?php echo $input_name; ?>[]" class="form-control">
			 <?php
			 if(isset($OptionList) && count($OptionList)>0){
			 foreach($OptionList as $option){ ?>
				<option value="<?php echo $option['id']; ?>"><?php echo $option['attr_options_name']; ?></option>
			 <?php } }  ?>
			 </select>
			 </td>
		<?php

		} ?>
		  <td><input type="number" name="variant_stock[]" class="form-control required-field"  value="" maxlength="10" placeholder="40000"></td>
		   <td><input type="number" name="variant_cost_price[]" class="form-control required-field"  value="" onkeypress="return isNumberKey(event);"   placeholder="Enter Value"></td>

		  <td><input type="number" name="variant_price[]" class="form-control required-field selling-price"  value="" onkeypress="return isNumberKey(event);"   placeholder="Enter Value" id="variant_price_<?php echo $random_id; ?>" onblur="calculate_webshop_price(<?php echo $Rounded_price_flag?>,<?php echo $random_id; ?>);"></td>
		  <td><input type="number" class="form-control  tax-percent" onkeypress="return isNumberKey(event);" name="variant_tax_percent[]"   onblur="calculate_webshop_price(<?php echo $Rounded_price_flag?>,<?php echo $random_id; ?>);" id="variant_tax_percent_<?php echo $random_id; ?>"></td>
		  <td><input type="number" class="form-control required-field webshop-price" onkeypress="return isNumberKey(event);" name="variant_webshop_price[]" id="variant_webshop_price_<?php echo $random_id; ?>"></td>
		  <td><?php if(isset($flag) && $flag=='edit'){?><input type="hidden" name="conf_simple[]" class="form-control"  value="" ><?php } ?><input type="text" name="variant_sku[]" class="form-control required-field unique-sku valid-sku"  onblur="skudbcompare(this);" value="<?php echo $pro_code; ?>-" placeholder="Enter Value"></td>
		  <!-- <td><input type="text" name="variant_barcode[]" class="form-control  " value="" placeholder="Enter Value" onblur="barcodedbcompare(this);"></td> -->
		  <!-- <td><input type="text" class="form-control input-sm" name="variant_weight[]"   value="" onkeypress="return isNumberKey(event);"></td> -->
      <!-- <td>
			<select name="gifts[]" id="gifts" class="form-control required-field webshop-gifts">
				<option value="">Select</option>
				<?php
			 if(isset($giftsTo) && count($giftsTo)>0){
			 foreach($giftsTo as $gift){ ?>
				<option value="<?php echo $gift['id']; ?>"><?php echo $gift['name']; ?></option>
			 <?php } }  ?>
			</select>
		  </td>
		  <td><input type="text" class="form-control required-field webshop-subissue input-sm" name="sub_issue[]"   value=""></td> -->
		  <!-- <td><input type="file" name="variant_image[]" class="image single-file  var-custom-file-input" multiple accept="image/*"><i class="fa fa-upload" aria-hidden="true"></i>&nbsp;<span class="md-status"></span></td> -->
	  <td><a class="link-red" href="javascript:void(0);" onClick="removeVariantRow(<?php echo $random_id; ?>)">Delete</a></td>
	</tr>

  <?php
  if($key==0){ break; }
  } ?>
  <?php } ?>

  <script>
 $(document).ready(function(){
  $(".single-file").change(function (e) {
	e.stopImmediatePropagation();

		  if(this.disabled) {
			return alert('File upload not supported!');
		  }

		  var F = this.files;
		  if (F && F[0]) {
			for (var i = 0; i < F.length; i++) {
			  readMediaFiles(F[i]);
			  $(this).parent().find('.md-status').html('Uploaded');
			}
		  }
	});

});

  </script>
