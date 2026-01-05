<div class="modal-header">
	<h1 class="head-name">Product List</h1>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
</div>
<?php //echo "<pre>";print_r($variantArr);//exit; ?>
<div class="modal-body">
  	<div class="table-responsive text-center make-virtual-table">
	    <table class="table table-bordered table-style">
	      	<thead>
		        <tr>
		        	<th>&nbsp;</th>
		          	<th>Product Name</th>
		          	<th>Categories</th>
		          	<th>Variant</th> 
		     		<th>Supplier</th>
					<th>Invntory</th>
					<th>Price</th>
					<th>Product Code</th>
					<th>SKU</th>
					<th>Details</th>
				</tr>
	      	</thead>
      		<tbody>
				<?php if(isset($allProducts) && !empty($allProducts)) { ?>
			      	<?php foreach($allProducts as $varnt) { ?>
			      	<tr>
			      		<td><div class="radio table-radio"><label><input type="radio" name="optradio" value="<?php echo $varnt->sku;?>" id="checkedProduct_<?php echo $varnt->id;?>" ><span class="checkmark"></span></label></div></td>
						<td><?php echo $varnt->name; ?></td>
						<td><?php echo $varnt->cat_name; ?></td>
						<td>
						<?php foreach ($varnt->variant as $value) { 
			                	echo $value['attr_name'].' : '.$value['attr_options_name'].'<br/>';
		              		} ?> 
			            </td>
			        	<td><?php echo $varnt->org_shop_name; ?></td>
						<td><?php echo $varnt->qty; ?></td>
						<td><?php echo $varnt->webshop_price; ?></td>
						<td><?php echo $varnt->product_code; ?></td>
						<td><?php echo $varnt->sku; ?></td>
						
						<td><a class="link-purple" href="<?php echo BASE_URL.'seller/product/edit/'?><?php echo ($varnt->product_type=='simple')?$varnt->id:$varnt->parent_id;?>" target="_blank">View</a></td>
					</tr>
			      <?php } ?>
		        <?php } ?>
			</tbody>
    	</table>
  	</div>
  	<div class="next-btn">
    	<?php if(isset($allProducts) && !empty($allProducts)) { ?>
    		<button type="button" class="puple-btn short-btn new-btn-height" data-dismiss="modal" onclick="selectedProductList('<?php echo $product_type; ?>')" id="product_variant_btn" name="product_variant_btn">Save</button>
    	<?php } ?>
  	</div><!-- next-btn -->
</div><!-- modal-body -->
