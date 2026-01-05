<div class="collapse show" id="collapseExamplePD">
<h2 class="table-heading-small">B2B Order List</h2>
<div class=" text-center" >
	<table class="table table-bordered table-style" id="DT_B2BOrderItems">
	  <thead>
		<tr>
		  <th>Order Number</th>
		  <th>SKU</th>
		  <th>Product Name</th>
		  <th>Supplier </th>
		  <th>Variants </th>
		  <th>Qty Ordered </th>
		  <!-- -->
		  <th>Total Price </th>
		</tr>
	  </thead>
	  <tbody>
		
		<?php

		if(isset($ShippedItem) && count($ShippedItem)>0){
		  foreach($ShippedItem as $item){
			  $total_price=0;
			  $item_class='';
			  
			  
				if($B2BOrderData->is_split==1){
					  $main_oi_qty=$this->ShopProductModel->getMainOrderItemQty($B2BOrderData->order_id,$item->product_id);
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
			  
			  
			  */
			  $total_price=$item->price * $item->qty_ordered; 
			  
			  
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
		  <td><?php echo $this->CommonModel->getWebShopNameByShopId($b2b_shop_id); ?></td>
		  <td><?php echo ($item->product_type=='conf-simple')?$variant_html:'-'; ?></td>
		  <td><?php echo  $qty_ordered; ?></td>
		  <td><?php echo $currency_code_seller.' '.number_format($total_price,2); ?></td>
		</tr>
	  <?php }  
	  } ?>
		
	  </tbody>
	</table>
	</div></div>