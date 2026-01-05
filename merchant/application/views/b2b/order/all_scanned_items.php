<div class="collapse " id="collapseExamplePD">
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
		   <th>Location </th>
		  <th>Total Price </th>
		</tr>
	  </thead>
	  <tbody>
		<?php

		if (isset($ShippedItem) && count($ShippedItem)>0) {
			foreach ($ShippedItem as $item) {
				$location_prod = $this->B2BOrdersModel->getProdLocation($item->product_id);
				$total_price=0;
				$item_class='';
				if ($OrderData->is_split==1) {
					$main_oi_qty=$this->B2BOrdersModel->getMainOrderItemQty($OrderData->order_id, $item->product_id);
					$qty_ordered=$main_oi_qty;
				} else {
					$qty_ordered=$item->qty_ordered;
				}
				if ($item->qty_scanned<=0) {
					$total_price=0;
				} else {
					$total_price=$item->price * $item->qty_scanned;
				}
				$variant_html='';
				if ($item->product_type=='conf-simple') {
					$product_variants=$item->product_variants;
					if (isset($product_variants) && $product_variants!='') {
						$variants=json_decode($product_variants, true);
						if (isset($variants) && count($variants)>0) {
							foreach ($variants as $pk=>$single_variant) {
								foreach ($single_variant as $key=>$val) {
									$variant_html.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';
								}
							}
						}
					} else {
						$variants='-';
					}
				} else {
					$variants='-';
				}
				?>
		  <tr class="<?php echo $item_class; ?>"  id="oi-single-<?php echo $item->item_id; ?>">
		  <td><?php echo $item->increment_id; ?></td>
		  <td><?php echo $item->sku; ?></td>
		  <td><?php echo $item->product_name; ?></td>
		  <td><?php echo ($item->product_type=='conf-simple') ? $variant_html : '-'; ?></td>
		  <td><?php echo  $qty_ordered; ?></td>
		  <td><?php echo $item->qty_scanned; ?></td>
		  <td><?php echo $location_prod; ?></td>
		  <td><?php echo ($item->qty_scanned<=0) ? '0' : $currency_code.' '.number_format($total_price, 2); ?></td>
		</tr>
	  <?php }
			} ?>

	  </tbody>
	</table>
	</div></div>
