<?php $item_count=count($OrderItems); ?>



  <div class="table-responsive text-center">
	<table class="table table-bordered table-style"  id="DT_RefundOrderItems">
	  <thead>
		<tr>
		
		  <th>SKU</th>
		  <th>Product Name</th>
		  <th>Variants </th>
		  <th>QTY ORDERED </th>
		  <!-- <th  class="">QTY RECIEVED </th>
		  <th  class="">QTY APPROVED</th> -->
		  <th>Price/Piece </th>
		  <th>Total Price </th>
		 
		</tr>
	  </thead>
	  <tbody>
	  <?php if(isset($OrderItems) && count($OrderItems)>0){
	  	// print_r($OrderItems);exit();
		  foreach($OrderItems as $item){
			  $total_price=0;
			  $item_class='';
			  
			  
				
				$item_class='black-row';
			  /*if($item->qty_return_recieved<=0){
				  $item_class='black-row';
			  }else if($item->qty_return_recieved==$item->qty_return){
				   $item_class='green-row';
			  }else if($item->qty_return_recieved<$item->qty_return){
				   $item_class='orange-row';
			  }*/
			 
			  /*if(isset($item->qty_return_recieved) && $item->qty_return_recieved<=0){
				   $total_price=0;
			  }else{*/
				   $total_price=$item->price * $item->qty_ordered; 
			  //}
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
		   
		  <td><?php echo $item->sku; ?></td>
		  <td><?php echo $item->product_name; ?></td>
		  <td><?php echo ($item->product_type=='conf-simple')?$variant_html:'-'; ?></td>
		  <td><?php echo  $item->qty_ordered; ?></td>
		   <!-- <td><?php echo  $item->qty_return_recieved; ?></td>
		    <td><?php echo  $item->qty_return_approved; ?></td> -->
			<td><?php echo $currency_code.' '.number_format($item->price,2); ?></td>
		  <td><?php echo ($item->qty_ordered<0)?'0':$currency_code.' '.number_format($total_price,2); ?></td>
		 
		</tr>
	  <?php }  
	  } ?>
		
	  </tbody>
	</table>
  </div>
  
  
 