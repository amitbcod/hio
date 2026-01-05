
<?php $item_count=count($OrderItems); ?>
	<?php if(($current_tab=='split-order' && $OrderData->system_generated_split_order==0) || ($current_tab=='create-shipment')){ ?>
	<?php } else{

	 if($OrderData->status==3){}else{
	?>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.3/themes/ui-lightness/jquery-ui.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<?php if($use_advanced_warehouse->value !== 'yes'): ?>
	<div class="barcode-qty-box row order-details-sec-top" id="barcode-qty-box">
		<div class="row col-sm-12">
			<div class="col-sm-3 pad-zero barcode-order-details-entry">
				<form method="POST" id="barcode-form" action="<?php echo base_url() ?>WebshopOrdersController/scanbarcodemanually">
					<input type="hidden" id="order_id" name="order_id" value="<?php echo $OrderData->order_id; ?>">
					<input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">
				<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
					<input type="text" class="form-control barcode_item" placeholder="Barcode" value="" id="barcode_item" name="barcode_item" maxlength="255" onmouseover="this.focus();" autofocus>
					<button type="submit" onclick="return ScanBarcodeManually();" class="btn btn-sm barcode-icon"><i class="fas fa-search"></i></button>
					<label class="error" id="barcode-error"></label>
				<?php } ?>
				</form>
			</div>
		</div>
	</div>

	<?php endif; ?>
	<?php }  } ?>

	<div class="col-12"><?php if($current_tab=='order' && $OrderData->status==0 && empty($OrderData->coupon_code) && empty($OrderData->voucher_code) && (empty($OrderPaymentDetail) || $OrderPaymentDetail->payment_method_id==4 || $OrderPaymentDetail->payment_method_id==5)){ ?>
		<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
		<!-- <button type="button" class="purple-btn float-right" onclick="OpenAddProductPopup(<?php echo $OrderData->order_id; ?>);" >Add Product</button> -->
		<?php } ?>
		<?php } ?></div>
  <div class="table-responsive text-center">
	<table class="table table-bordered table-style"  id="DT_B2BOrderItems">
	  <thead>
		<tr>
		<?php if($current_tab!=='create-shipment'){?>
		  <!-- <th>Action</th> -->
		<?php } ?>
		  <th>SKU</th>
		  <th>Product Name</th>
		  <th>Variants </th>
			<?php if($use_advanced_warehouse->value === 'yes'): ?>
		  		<!-- <th>Qty Original</th> -->
			<?php endif ?>
		  <th>Qty Ordered </th>
		  <th>Gift</th>

		  <th  class="<?php echo  ($current_tab=='split-order' && $OrderData->system_generated_split_order==1)?'':'d-none'; ?>">Qty Delivered </th>
		  <!-- <th  class="<?php echo  ($current_tab=='order')?'':'d-none'; ?>">Inventory</th> -->
		  <th  class="<?php echo  ($current_tab=='order' || $current_tab=='split-order')?'':'d-none'; ?>">Inventory</th>
		  <th  class="<?php echo  ($current_tab=='split-order' && $OrderData->system_generated_split_order==1)?'':'d-none'; ?>">Qty Pending</th>
		  <!-- <th>Qty Scanned </th> -->
		<?php if($use_advanced_warehouse->value !== 'yes'): ?>
		  <th>Location </th>
		<?php endif ?>
		  <?php if($current_tab!='create-shipment' && $use_advanced_warehouse->value !== 'yes'){?>
			  <th>Estimated Delivery Time</th>
		<?php } ?>
		  <th>Price/Piece </th>
		  <th>Total Price </th>
		</tr>
	  </thead>
	  <tbody>
	  <?php if(isset($OrderItems) && count($OrderItems)>0){
	  	$product_delivery_duration = $this->CommonModel->getSingleShopDataByID('custom_variables as cv',array('identifier'=>'product_delivery_duration'),'cv.*');
		  foreach($OrderItems as $item){
			  $split_EstimateTime ="";
			//print_r($OrderItems);
			if($current_tab=='split-order'){
				// now fixed
				if(isset($OrderData) && $OrderData->main_parent_id){
					$split_EstimateTime = $this->WebshopOrdersModel->getSplitProductsEstimateTime($OrderData->main_parent_id,$item->product_id);
				}
				//$split_EstimateTime = $this->WebshopOrdersModel->getSplitProductsEstimateTime($OrderData->parent_id,$item->product_id);
			 }
			  $total_price=0;
			  $item_class='';
				if($current_tab=='split-order'){
						$main_oi_qty=$this->WebshopOrdersModel->getMainOrderItemQty($OrderData->main_parent_id,$item->product_id);
						$qty_ordered=$main_oi_qty;
						$pending_qty=$this->WebshopOrdersModel->getPendingQtyToBeScanned($OrderData->main_parent_id,$item->product_id,1);
						$TotalRowShipped=$this->WebshopOrdersModel->getShippedSingleOrderItems($OrderData->main_parent_id,$item->product_id,1);
						$delivered_qty=$TotalRowShipped->qty_scanned;

				}else{
						$qty_ordered=$item->qty_ordered;
						$pending_qty=$this->WebshopOrdersModel->getPendingQtyToBeScanned($OrderData->order_id,$item->product_id,'');
						$TotalRowShipped=$this->WebshopOrdersModel->getShippedSingleOrderItems($OrderData->order_id,$item->product_id);
						$delivered_qty=$TotalRowShipped->qty_scanned;
				}


			//   if($item->qty_ordered>=0){
			// 	  $item_class='black-row';
			//   }else if(($current_tab=='order') && ($item->qty_ordered==$qty_ordered)){
			// 	   $item_class='green-row';
			//   }else if(($current_tab=='split-order') && ($item->qty_ordered==$qty_ordered)){
			// 	   $item_class='green-row';

			//   }else if($item->qty_ordered<$qty_ordered){
			// 	   $item_class='orange-row';
			//   }

			//   if(($current_tab=='split-order' && $OrderData->system_generated_split_order==0) || ($current_tab=='create-shipment')){
			// 	   $item_class='black-row';
			//   }

			  if($item->qty_ordered >= 0){
				   $total_price=$item->price * $item->qty_ordered;
			  }else{
				   $total_price= 0 ;
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
		  <tr class="<?php echo $item_class; ?>"  id="oi-single-<?php echo $item->item_id; ?>">
		  <?php
		  	  if($OrderData->status==3){ // echo '<td></td>';}else{
		  		if($current_tab!='create-shipment'){ ?>
			  	<!-- <td>
						<?php //if(($item->qty_ordered==$item->qty_scanned) || ($item->qty==0)){ ?>
						<?php
						//} else{ ?>
							<?php //if($use_advanced_warehouse->value !== 'yes'): ?>
							<?php //if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
							<button  type="button" class="purple-btn btn-sm" onclick="OpenScanWtihQtyPopup(<?php //echo $OrderData->order_id; ?>,<?php //echo $item->item_id; ?>);">Scan</button>
							<?php //} ?>
							<?php //endif;?>

						<?php //} ?><?php //if($current_tab=='order' && $OrderData->status==0 && empty($OrderData->coupon_code) && empty($OrderData->voucher_code) && (empty($OrderPaymentDetail) || $OrderPaymentDetail->payment_method_id==4 || $OrderPaymentDetail->payment_method_id==5)){ ?>
						<?php //if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
							<button  type="button" class="btn btn-primary change-add-n-btn" style="position: static;right: 0;margin-left: 5px;" onclick="OpenDeletePopup(<?php //echo $OrderData->order_id; ?>,<?php //echo $item->item_id; ?>);"><i class="fas fa-trash"></i>
							</button>
						<?php //} ?>
							<?php 
						//} ?>
					</td> -->
			<?php
				}
		      }
			?>
		  <td class="check_sku"><?php echo $item->sku; ?></td>
		  <td><?php echo $item->product_name; ?></td>
		  <td><?php echo ($item->product_type=='conf-simple')?$variant_html:'-'; ?></td>

		  <?php if($use_advanced_warehouse->value === 'yes'): ?>
		  <!-- <td> <?php  //$item->qty_original ?> </td> -->
		  <?php endif; ?>

		  <td><?php echo  $qty_ordered; ?><?php if($current_tab=='order' && $OrderData->status==0 && empty($OrderData->coupon_code) && empty($OrderData->voucher_code) && (empty($OrderPaymentDetail) || $OrderPaymentDetail->payment_method_id==4 || $OrderPaymentDetail->payment_method_id==5)){ ?>
		  	<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
		  	<button  type="button" class="btn btn-primary change-add-n-btn" style="position: static;right: 0;margin-left: 5px;" onclick="OpenQtyPopup(<?php echo $OrderData->order_id; ?>,<?php echo $item->item_id; ?>);"><i class="fas fa-edit"></i>
		  	</button>
		  	 <?php } ?>

		  	<?php } ?></td>
			  <td><?php echo ($item->gift_name) ?></td>

		   <td class="<?php echo  ($current_tab=='split-order' && $OrderData->system_generated_split_order==1)?'':'d-none'; ?>"><?php echo  ($delivered_qty>0)?$delivered_qty:0; ?></td>
		  <td class="<?php echo  ($current_tab=='order' || $current_tab=='split-order')?'':'d-none'; ?>"><?php echo $item->qty; ?></td>
		  <td class="<?php echo  ($current_tab=='split-order' && $OrderData->system_generated_split_order==1)?'':'d-none'; ?>"><?php echo ($pending_qty>0)?$pending_qty:0; ?></td>
		  <!-- <td><?php //echo $item->qty_scanned; ?></td> -->
		  <?php if($use_advanced_warehouse->value !== 'yes'): ?>
		  	<td><?= $item->prod_location ?? '-' ?></td>
		  <?php endif; ?>
		  <?php if($current_tab!='create-shipment' && $use_advanced_warehouse->value !== 'yes'){?>
		  <td><span <?php if($item->estimate_delivery_time > $product_delivery_duration->value) { echo "class='estimate_delivery_time'" ; } ?> > <?php
			if($current_tab=='split-order'){
				// now fixed
				if (isset($split_EstimateTime) && $split_EstimateTime->estimate_delivery_time){
					echo $split_EstimateTime->estimate_delivery_time;
				}else{
					echo '-';
				}

			}else{
				echo $item->estimate_delivery_time;
			}?></span></td>
		  <?php } ?>
		  <td><?php echo $currency_code.' '.number_format($item->price,2); ?><?php if($current_tab=='order' && $OrderData->status==0 && empty($OrderData->coupon_code) && empty($OrderData->voucher_code) && (empty($OrderPaymentDetail) || $OrderPaymentDetail->payment_method_id==4 || $OrderPaymentDetail->payment_method_id==5)){ ?>
		 <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
		  	<button  type="button" class="btn btn-primary change-add-n-btn" style="position: static;right: 0;margin-left: 5px;" onclick="OpenPricePopup(<?php echo $OrderData->order_id; ?>,<?php echo $item->item_id; ?>);"><i class="fas fa-edit"></i>
		  	</button>
		  <?php } ?>
		  	<?php } ?></td>
		  <td><?php echo ($item->qty_ordered<=0)?'0':$currency_code.' '.number_format($total_price,2); ?></td>
		</tr>
	  <?php }
	  } ?>

	  </tbody>
	</table>
  </div>


  <script type="text/javascript">
$(document).ready(function(){
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

	<?php if((($current_tab=='split-order') && $OrderData->system_generated_split_order==0) || (isset($pending_scanned_qty) && $pending_scanned_qty<=0)){ ?>
	// $('#confirm-order-btn').removeAttr('disabled');
	$('#split-order-btn').attr('disabled',true);
	//$('#split-order-btn').hide();
	$('#barcode-qty-box').hide();

	<?php } else{ ?>
	// $('#confirm-order-btn').attr('disabled',true);
	$('#split-order-btn').removeAttr('disabled');
	//$('#split-order-btn').show();
	$('#barcode-qty-box').show();

	<?php } ?>

});

function ScanBarcodeManually(){
	$('#barcode-error').html('');
	$('#barcode_item').removeClass('error');
	var order_id=$('#order_id').val();
	var current_tab=$('#current_tab').val();
	var barcode_item=$('#barcode_item').val();
	if(barcode_item==''){
		$('#barcode_item').addClass('error');
		$('#barcode-error').html('Please enter barcode');
		return false;
	}
	else{
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/scanbarcodemanually",
				type: "POST",
				data: {
				  order_id:order_id,
				  barcode_item:barcode_item,
				  current_tab:current_tab
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
						RefreshOrderItems(order_id,current_tab);
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

//JavaScript function to play the beep sound
function play(flag) {
	if(flag == 'beep-success') {
		var beepsound = new Audio(BASE_URL+'public/beepsounds/beep-success.wav');
	} else {
		var beepsound = new Audio(BASE_URL+'public/beepsounds/beep-error.wav');
	}
	beepsound.play();
}

function OpenScanWtihQtyPopup(order_id,item_id){
	if(order_id!='' && item_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openScanQtyPopup",
				type: "POST",
				data: {
				  order_id:order_id,
				  item_id:item_id
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

function ConfirmQtyScan(order_id,item_id){
	var qty_scan=$('#qty_scan').val();
	var current_tab=$('#current_tab').val();
	if(qty_scan!=''){
		$('#conf-qty-scan-btn').prop('disabled',true);

		$.ajax({
				url: BASE_URL+"WebshopOrdersController/scanitemwithqty",
				type: "POST",
				data: {
				  order_id:order_id,
				  item_id:item_id,
				  qty:qty_scan,
				  current_tab:current_tab
				},
				success: function(response) {
					$("#barcode_item").focus();

					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						play('beep-success');

						RefreshOrderItems(order_id,current_tab);
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


function OpenQtyPopup(order_id,item_id){
	if(order_id!='' && item_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openQtyPopup",
				type: "POST",
				data: {
				  order_id:order_id,
				  item_id:item_id
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

function ConfirmQty(order_id,item_id){
	var qty_ordered=$('#qty_ordered').val();
	var current_tab=$('#current_tab').val();
	if(qty_ordered!=''){
		$('#conf-qty-btn').prop('disabled',true);

		$.ajax({
				url: BASE_URL+"WebshopOrdersController/itemwithqty",
				type: "POST",
				data: {
				  order_id:order_id,
				  item_id:item_id,
				  qty:qty_ordered,
				  current_tab:current_tab
				},
				success: function(response) {
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						// play('beep-success');
						RefreshOrderItems(order_id,current_tab);
						swal('Success',obj.message,'success');
						location.reload();
					} else {
						$('#conf-qty-btn').prop('disabled',false);
						// play('beep-error');
						swal('Error',obj.message,'error');
						return false;

					}

				}
			});

	}else{
		return false;
	}

}

function OpenPricePopup(order_id,item_id){
	if(order_id!='' && item_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openPricePopup",
				type: "POST",
				data: {
				  order_id:order_id,
				  item_id:item_id
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

function ConfirmPrice(order_id,item_id){
	var price_ordered=$('#price_ordered').val();
	var current_tab=$('#current_tab').val();
	if(price_ordered!=''){
		$('#conf-price-btn').prop('disabled',true);

		$.ajax({
				url: BASE_URL+"WebshopOrdersController/itemwithprice",
				type: "POST",
				data: {
				  order_id:order_id,
				  item_id:item_id,
				  price:price_ordered,
				  current_tab:current_tab
				},
				success: function(response) {
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						// play('beep-success');
						RefreshOrderItems(order_id,current_tab);
						swal('Success',obj.message,'success');
						location.reload();
					} else {
						$('#conf-price-btn').prop('disabled',false);
						// play('beep-error');
						swal('Error',obj.message,'error');
						return false;

					}

				}
			});

	}else{
		return false;
	}

}

function OpenDeletePopup(order_id,item_id){
	if(order_id!='' && item_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openDeletePopup",
				type: "POST",
				data: {
				  order_id:order_id,
				  item_id:item_id
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

function ConfirmDelete(order_id,item_id){
	var current_tab=$('#current_tab').val();
	if(item_id!=''){
		$('#conf-price-btn').prop('disabled',true);

		$.ajax({
				url: BASE_URL+"WebshopOrdersController/itemwithdelete",
				type: "POST",
				data: {
				  order_id:order_id,
				  item_id:item_id,
				  current_tab:current_tab
				},
				success: function(response) {
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						// play('beep-success');
						RefreshOrderItems(order_id,current_tab);
						swal('Success',obj.message,'success');
						location.reload();
					} else {
						$('#conf-price-btn').prop('disabled',false);
						// play('beep-error');
						swal('Error',obj.message,'error');
						return false;

					}

				}
			});

	}else{
		return false;
	}

}

function OpenAddProductPopup(order_id){
	if(order_id!='' && order_id!=''){
		$.ajax({
				url: BASE_URL+"WebshopOrdersController/openAddProductPopup",
				type: "POST",
				data: {
				  order_id:order_id
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

function ConfirmAddProduct(order_id){
	var current_tab=$('#current_tab').val();
	var sku=$('#sku').val();
	var qty=$('#qty').val();

	if(order_id!=''){
		$('#conf-price-btn').prop('disabled',true);

		$.ajax({
				url: BASE_URL+"WebshopOrdersController/itemwithAddProduct",
				type: "POST",
				data: {
				  order_id:order_id,
				  current_tab:current_tab,
				  sku:sku,
				  qty:qty

				},
				success: function(response) {
					var obj = JSON.parse(response);
					if(obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						// play('beep-success');
						RefreshOrderItems(order_id,current_tab);
						swal('Success',obj.message,'success');
						location.reload();
					} else {
						$('#conf-price-btn').prop('disabled',false);
						// play('beep-error');
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
