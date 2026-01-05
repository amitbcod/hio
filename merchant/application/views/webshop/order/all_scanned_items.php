<div class="collapse " id="collapseExamplePD" style="overflow: auto;">
<h2 class="table-heading-small">Product Details</h2>
<div class=" text-center" >
	<table class="table table-bordered table-style" id="DT_B2BOrderItems">
	  <thead>
		<tr>
		  <th>Order Number</th>
		  <th>SKU</th>
		  <th>Product Name</th>
		  <th>Variants </th>
		  <th>Qty Ordered </th>
		   <th>Qty Scanned </th>
		  <th>Total Price </th>
		</tr>
	  </thead>
	  <tbody>
		
		<?php

		if(isset($ShippedItem) && count($ShippedItem)>0){
		  foreach($ShippedItem as $item){
			  $total_price=0;
			  $item_class='';
			  
			  
				if($OrderData->is_split==1){
					  $main_oi_qty=$this->WebshopOrdersModel->getMainOrderItemQty($OrderData->order_id,$item->product_id);
					  $qty_ordered=$main_oi_qty;
					 
				}else{
					 $qty_ordered=$item->qty_ordered;
					 
				}
				
				/*
			  if($item->qty_scanned<=0){
				  $item_class='black-row';
			  }else if($item->qty_scanned==$qty_ordered){
				   $item_class='green-row';
			  }else if($item->qty_scanned<$qty_ordered){
				   $item_class='orange-row';
			  }
			  
			  if(($current_tab=='split-order' && $OrderData->system_generated_split_order==0) || ($current_tab=='create-shipment')){
				   $item_class='black-row';
			  }
			  */
			  if($item->qty_scanned<=0){
				   $total_price=0;
			  }else{
				   $total_price=$item->price * $item->qty_scanned; 
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
		  <td><?php echo $item->increment_id; ?></td>
		  <td><?php echo $item->sku; ?></td>
		  <td><?php echo $item->product_name; ?></td>
		  <td><?php echo ($item->product_type=='conf-simple')?$variant_html:'-'; ?></td>
		  <td><?php echo  $qty_ordered; ?></td>
		  <td><?php echo $item->qty_scanned; ?></td>
		  <td><?php echo ($item->qty_scanned<=0)?'0':$currency_code.' '.number_format($total_price,2); ?></td>
		</tr>
	  <?php }  
	  } ?>
	  
	  
<?php if(isset($b2b_orders) && count($b2b_orders)>0){
			foreach($b2b_orders as $b2b_order) {

			$FbcUser = $this->CommonModel->getSingleDataByID('fbc_users_shop',array('shop_id'=>$b2b_order['shop_id']),'');
			$b2b_fbc_user_id=$FbcUser->fbc_user_id;

			$b2b_args['shop_id']	=	$b2b_order['shop_id'];
			$b2b_args['fbc_user_id']	=	$b2b_fbc_user_id;

			$this->load->model('ShopProductModel');
			$this->ShopProductModel->init($b2b_args); 
			
									
			if($b2b_order['is_split']=='1'){
				
				
				$SplitOrderIds=$this->ShopProductModel->getSplitChildOrderIds($b2b_order['order_id']);
				foreach($SplitOrderIds as $split_order){
				$so_id=$split_order->order_id;
				$ShippedItemA=$this->ShopProductModel->getOrderItemsForWebShopB2B($b2b_order['order_id'],$b2b_order['is_split']);
				

		if(isset($ShippedItemA) && count($ShippedItemA)>0){
		  foreach($ShippedItemA as $item){
			  $total_price=0;
			  $item_class='';
			  
			  
				if($OrderData->is_split==1){
					  $main_oi_qty=$this->ShopProductModel->getMainOrderItemQty($b2b_order['order_id'],$item->product_id);
					  $qty_ordered=$main_oi_qty;
					 
				}else{
					 $qty_ordered=$item->qty_ordered;
					 
				}
				
				/*
			  if($item->qty_scanned<=0){
				  $item_class='black-row';
			  }else if($item->qty_scanned==$qty_ordered){
				   $item_class='green-row';
			  }else if($item->qty_scanned<$qty_ordered){
				   $item_class='orange-row';
			  }
			  
			  if(($current_tab=='split-order' && $OrderData->system_generated_split_order==0) || ($current_tab=='create-shipment')){
				   $item_class='black-row';
			  }
			  */
			  if($item->qty_scanned<=0){
				   $total_price=0;
			  }else{
				   $total_price=$item->price * $item->qty_scanned; 
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
		  <td><?php echo $item->increment_id; ?></td>
		  <td><?php echo $item->sku; ?></td>
		  <td><?php echo $item->product_name; ?></td>
		  <td><?php echo ($item->product_type=='conf-simple')?$variant_html:'-'; ?></td>
		  <td><?php echo  $qty_ordered; ?></td>
		  <td><?php echo $item->qty_scanned; ?></td>
		  <td><?php echo ($item->qty_scanned<=0)?'0':$currency_code.' '.number_format($total_price,2); ?></td>
		</tr>
	  <?php }  
	  } 
	  
	  
	  
	}
			?>
			
			<?php } else{ 
			
			$ShippedItemB=$this->ShopProductModel->getOrderItemsForWebShopB2B($b2b_order['order_id']);
			?>
			
			
			
			<?php

		if(isset($ShippedItemB) && count($ShippedItemB)>0){
		  foreach($ShippedItemB as $item){
			  $total_price=0;
			  $item_class='';
			  
			  
				if($OrderData->is_split==1){
					  $main_oi_qty=$this->ShopProductModel->getMainOrderItemQty($b2b_order['order_id'],$item->product_id);
					  $qty_ordered=$main_oi_qty;
					 
				}else{
					 $qty_ordered=$item->qty_ordered;
					 
				}
				
				/*
			  if($item->qty_scanned<=0){
				  $item_class='black-row';
			  }else if($item->qty_scanned==$qty_ordered){
				   $item_class='green-row';
			  }else if($item->qty_scanned<$qty_ordered){
				   $item_class='orange-row';
			  }
			  
			  if(($current_tab=='split-order' && $OrderData->system_generated_split_order==0) || ($current_tab=='create-shipment')){
				   $item_class='black-row';
			  }
			  */
			  if($item->qty_scanned<=0){
				   $total_price=0;
			  }else{
				   $total_price=$item->price * $item->qty_scanned; 
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
		  <td><?php echo $item->increment_id; ?></td>
		  <td><?php echo $item->sku; ?></td>
		  <td><?php echo $item->product_name; ?></td>
		  <td><?php echo ($item->product_type=='conf-simple')?$variant_html:'-'; ?></td>
		  <td><?php echo  $qty_ordered; ?></td>
		  <td><?php echo $item->qty_scanned; ?></td>
		  <td><?php echo ($item->qty_scanned<=0)?'0':$currency_code.' '.number_format($total_price,2); ?></td>
		</tr>
	  <?php }  
	  } ?>
			
			
			<?php }
			} 
	  }
?>
		
	  </tbody>
	</table>
	</div></div>