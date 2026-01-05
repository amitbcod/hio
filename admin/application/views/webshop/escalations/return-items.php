<?php $item_count=count($OrderItems); ?>

	<?php if($ReturnOrderData->status==3 || $ReturnOrderData->status==5){ ?>
	<?php } else{ ?>
	
	<div class="barcode-qty-box row order-details-sec-top" id="barcode-qty-box">
		<div class="row col-sm-12">
			<div class="col-sm-3 pad-zero barcode-order-details-entry">
				<form method="POST" id="barcode-form" action="<?php echo base_url() ?>ReturnOrderController/scanbarcodemanually">
					<input type="hidden" id="return_order_id" name="return_order_id" value="<?php echo $ReturnOrderData->return_order_id; ?>">
					<input type="hidden" id="current_tab" name="current_tab" value="<?php // echo $current_tab; ?>">
					<input type="text" class="form-control barcode_item" placeholder="Barcode" value="" id="barcode_item" name="barcode_item" maxlength="255" onmouseover="this.focus();" autofocus>
					<button type="submit" onclick="return ScanBarcodeManually();" class="btn btn-sm barcode-icon"><i class="fas fa-search"></i></button>
					<label class="error" id="barcode-error"></label>
				</form>
			</div>
		</div>
	</div>

	<?php } ?>


  <div class="table-responsive text-center">
	<table class="table table-bordered table-style"  id="DT_B2BOrderItems">
	  <thead>
		<tr>
		 <th>Action</th>
		  <th>Product Name</th>
		  <th>Variants </th>
		  <th>QTY REQUESTED </th>
		  <th  class="">QTY RECIEVED </th>
		  <th  class="">QTY APPROVED</th>
		  <th>Price/Piece </th>
		  <th>Total Price </th>
		  <th>Restock </th>
		  <th>Type </th>
		</tr>
	  </thead>
	  <tbody>
	  <?php if(isset($OrderItems) && count($OrderItems)>0){
		  foreach($OrderItems as $item){
			  $total_price=0;
			  $item_class='';
			  
			  
				
				
			  if($item->qty_return_recieved<=0){
				  $item_class='black-row';
			  }else if($item->qty_return_recieved==$item->qty_return){
				   $item_class='green-row';
			  }else if($item->qty_return_recieved<$item->qty_return){
				   $item_class='orange-row';
			  }
			 
			  if($item->qty_return_recieved<=0){
				   $total_price=0;
			  }else{
				   $total_price=$item->price * $item->qty_return_recieved; 
			  }
			  $variant_html='';
			  if($item->product_type=='conf-simple'){
				  $product_variants=$item->product_variants;
				  if(isset($product_variants) && $product_variants!=''){
					$variants=json_decode($product_variants, true);
					if(isset($variants) && count($variants)>0){
						
						
						foreach($variants as $pk=>$single_variant){
							foreach($single_variant as $key=>$val){
								
							$variant_html.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';
	
							}
						}
					}
				  }else{
					 $variants='-';  
				  }
			  }else{
				  $variants='-';  
			  }
			  
			  
			 
			  ?>
		  <tr class="<?php echo $item_class; ?>"  id="oi-single-<?php echo $item->return_order_item_id; ?>">
		   <td><?php if(($item->qty_return==$item->qty_return_recieved) || ($item->qty_return==0)){ ?>
				<?php } else{ ?><button  type="button" class="purple-btn btn-sm" onclick="OpenScanWtihQtyPopup(<?php echo $ReturnOrderData->return_order_id; ?>,<?php echo $item->return_order_item_id; ?>);">Scan</button><?php } ?></td>
		  <td><?php echo $item->product_name; ?></td>
		  <td><?php echo ($item->product_type=='conf-simple')?$variant_html:'-'; ?></td>
		  <td><?php echo  $item->qty_return; ?></td>
		   <td><?php echo  $item->qty_return_recieved; ?></td>
		    <td><input type="number" class="form-control shop-convertor" <?php echo ($ReturnOrderData->status==5 || ($item->qty_return==$item->qty_return_approved) || $item->qty_return_recieved==0)?'disabled':''; ?> onblur="UpdateQtyApproved(this.value,<?php echo $item->return_order_item_id; ?>,<?php echo $ReturnOrderData->return_order_id; ?>,'<?php echo  $item->qty_return; ?>');" value="<?php echo  $item->qty_return_approved; ?>"  min="0" max="<?php echo  $item->qty_return; ?>"></td>
			<td><?php echo $currency_code.' '.number_format($item->price,2); ?></td>
		  <td><?php echo ($item->qty_return_recieved<=0)?'0':$currency_code.' '.number_format($total_price,2); ?></td>
		   <td><div class="switch-onoff">
								<label class="checkbox">
									<input type="checkbox"  <?php echo ($ReturnOrderData->status==5)?'disabled':''; ?>  name="restock_<?php echo $item->return_order_item_id; ?>" onchange="UpdateStock(this,<?php echo $item->return_order_item_id; ?>,<?php echo $ReturnOrderData->return_order_id; ?>);" id="restock_<?php echo $item->return_order_item_id; ?>" <?php echo (isset($item->is_restock) && $item->is_restock==1)?'checked':''; ?> > 
									<span class="checked"></span>
								</label>
							</div></td>
		    <td><?php echo  $item->product_inv_type; ?></td>
		</tr>
	  <?php }  
	  } ?>
		
	  </tbody>
	</table>
  </div>
  
  <?php 
  $enable_confirm='';
			
				$QtyScanItem=$this->ReturnOrderModel->getQtyFullyScannedOrderItems($ReturnOrderData->return_order_id);
				$AllItems=$this->ReturnOrderModel->getReturnOrderItems($ReturnOrderData->return_order_id);
				if(count($QtyScanItem)==count($AllItems))
				{
					if($ReturnOrderData->order_amount_approved>0){
						$enable_confirm='1';
					}else{
						$enable_confirm='';	
					}
				}else{
					$enable_confirm='';
				}
				
  
  ?>
  
  
  <script type="text/javascript">
$(document).ready(function(){
	
	
	<?php if($enable_confirm==1){ ?>
	$('#confirm-returns-btn').attr('disabled',false);
	<?php } ?>
	
	$("#barcode_item").focus();
	
	
	$("#barcode_item").keypress(function(){
	  $(this).removeClass('error');
	  $('#barcode-error').html('');
	});
	
	
	$("#barcode-form").submit(function(event){
		event.preventDefault();
		//ScanBarcodeManually();
		return false;
	});
	
	<?php if($ReturnOrderData->status==3){ ?>
	$('#confirm-order-btn').removeAttr('disabled');
	$('#split-order-btn').attr('disabled',true);
	//$('#split-order-btn').hide();
	$('#barcode-qty-box').hide();
	
	<?php } else{ ?>
	$('#confirm-order-btn').attr('disabled',true);
	$('#split-order-btn').removeAttr('disabled');
	//$('#split-order-btn').show();
	$('#barcode-qty-box').show();
	
	<?php } ?>
	
});

function ScanBarcodeManually(){
	$('#barcode-error').html('');
	$('#barcode_item').removeClass('error');
	var return_order_id=$('#return_order_id').val();
	var current_tab=$('#current_tab').val();
	var barcode_item=$('#barcode_item').val();
	if(barcode_item==''){
		$('#barcode_item').addClass('error');
		$('#barcode-error').html('Please enter barcode');
		return false;
	}	
	else{
		$.ajax({ 
				url: BASE_URL+"ReturnOrderController/scanbarcodemanually",
				type: "POST",
				data: {
				  return_order_id:return_order_id,
				  barcode_item:barcode_item
				},
				beforeSend: function(){
					//$('#ajax-spinner').show();
				},	
				success: function(response) {
					//$('#ajax-spinner').hide();
					$('#barcode_item').val('');
					$("#barcode_item").focus();
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						
						play('beep-success');
						if(obj.item_id!=''){
							
						}
						RefreshOrderItems(return_order_id);
						$("#barcode_item").focus();
						//swal('Success',obj.message,'success');
						return true;
						
					} else {
						play('beep-error');
						$('#barcode_item').addClass('error');
						$('#barcode-error').html(obj.message);
						return false;
						
					}
					
				}
			});
	}
	
}

function RefreshOrderItems(return_order_id,current_tab='')
{
	if(return_order_id!=''){
		$.ajax({ 
				url: BASE_URL+"ReturnOrderController/refreshOrderItems",
				type: "POST",
				data: {
				  return_order_id:return_order_id
				  
				},
				success: function(response) {
					if(response!='error'){
						$('#order-item-outer').html(response);
					} else {				
						$('#barcode-error').html('Something went wrong.');
						return false;						
					}
					
				}
			});
	}else{
		return false;
	}
	
}

function UpdateStock(elem,return_order_item_id,return_order_id){
	var flag='';
	
	if($(elem).is(':checked')){
		flag=1;
	}else{
		flag=0;
	}
	$.ajax({ 
				url: BASE_URL+"ReturnOrderController/updatestock",
				type: "POST",
				data: {
				  return_order_item_id:return_order_item_id,
				  return_order_id:return_order_id,
				  flag:flag
				},
				beforeSend: function(){
					//$('#ajax-spinner').show();
				},	
				success: function(response) {
					//$('#ajax-spinner').hide();
					
				
					if(response=='success') {
						
						return true;
						
					} else {
						swal('Error','Something went wrong. ','error');
						
						return false;
						
					}
					
				}
			});
}


function UpdateQtyApproved(qty_approved,return_order_item_id,return_order_id,qty_return){

	if($('#return_recieved_date').val() == ''){

		swal('Error','Please enter recieved date.','error');
		return false;

	}

	if(qty_approved!='' && qty_approved<0){
		swal('Error','Please enter valid quantity.','error');
		return false;
		
	}else if(qty_approved>qty_return){
		swal('Error','Quantity approved must be less than or equal '+qty_return+'','error');
		return false;
	}else{
		$.ajax({ 
				url: BASE_URL+"ReturnOrderController/updateQtyApproved",
				type: "POST",
				data: {
				  return_order_item_id:return_order_item_id,
				  return_order_id:return_order_id,
				  qty_approved:qty_approved
				},
				beforeSend: function(){
					//$('#ajax-spinner').show();
				},	
				success: function(response) {
					//$('#ajax-spinner').hide();
					var obj = JSON.parse(response);
					if(obj.status==200) {
						RefreshOrderItems(return_order_id);
						var refund_approved=obj.refund_approved;
						$('#return_approved').html(refund_approved);
						return true;
						
					} else {
						swal('Error','Something went wrong. ','error');
						return false;
						
					}
					
				}
			});
	}
}



//JavaScript function to play the beep sound
function play(flag) {
	if(flag == 'beep-success') { 
		var beepsound = new Audio(BASE_URL+'public/beepsounds/beep-success.wav'); 
	} else {
		var beepsound = new Audio(BASE_URL+'public/beepsounds/beep-error.wav');
	}
	beepsound.play(); 
}


function OpenScanWtihQtyPopup(return_order_id,return_order_item_id){
	if(return_order_id!='' && return_order_item_id!=''){

		if($('#return_recieved_date').val() == ''){

			swal('Error','Please enter recieved date.','error');
			return false;

		}

		$.ajax({ 
				url: BASE_URL+"ReturnOrderController/openScanQtyPopup",
				type: "POST",
				data: {
				  return_order_id:return_order_id,
				  return_order_item_id:return_order_item_id
				},
				success: function(response) {
					if(response!='error'){
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					}else{
						return false;
					}
					
				}
			});
	}else{
		return false;
	}
	
}

function ConfirmQtyScan(return_order_id,return_order_item_id){
	var qty_scan=$('#qty_scan').val();
	var current_tab='';
	if(qty_scan!=''){
		$('#conf-qty-scan-btn').prop('disabled',true);
		
		$.ajax({ 
				url: BASE_URL+"ReturnOrderController/scanitemwithqty",
				type: "POST",
				data: {
				  return_order_id:return_order_id,
				  return_order_item_id:return_order_item_id,
				  qty:qty_scan,
				  current_tab:''
				},
				success: function(response) {
					$("#barcode_item").focus();
					
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						play('beep-success');
						
						RefreshOrderItems(return_order_id,'');
						swal('Success',obj.message,'success');
						
						return true;
						
					} else {
						$('#conf-qty-scan-btn').prop('disabled',false);
						play('beep-error');
						swal('Error',obj.message,'error');
						return false;
						
					}
					
				}
			});
		
	}else{
		return false;
	}
	
}
  </script>
  