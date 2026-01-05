<div class="modal-header">
  <h1 class="head-name">Variant List</h1>
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
            <th><label class="checkbox"><input type="checkbox" class="form-control"><span class="checked"></span></label></th>
            <th>Product Name</th>
            <th>Categories</th>
            <th>Variant</th>
            <th>SKU</th>
            <th>Inventory</th>
            <th>Price</th>
        </tr>
      </thead>
      <tbody>

        <?php if(isset($variantArr) && !empty($variantArr)) { ?>
          <?php foreach($variantArr as $varnt) { ?>
          <tr>
            <td><label class="checkbox"><input type="checkbox" <?php echo (isset($productIdArr) && in_array($varnt['id'], $productIdArr)) ? 'checked' : ''; ?> class="form-control variant-checkbox" data-product_id="<?php echo $varnt['id'];?>" value="<?php echo $varnt['id'];?>" id="checkedVariant_<?php echo $varnt['id'];?>" name="checkedVariant[]">
              <span class="checked"></span>
              </label>
            </td>
					  <td><?php echo $varnt['name']; ?></td>
            <td class="vrnt_cat_name"></td>
            <td>
              <?php foreach ($varnt['variant'] as $value) { 
                echo $value['attr_name'].' : '.$value['attr_options_name'].'<br/>';
              } ?>
            </td>
					  <td><?php echo $varnt['sku']; ?></td>
					  <td><?php echo $varnt['qty']; ?></td>
					  <td><?php echo $varnt['webshop_price']; ?></td>
          </tr>
          <?php } ?>
        <?php } ?>
			</tbody>
    </table>
  </div>
  <div class="next-btn">
    <?php if(isset($variantArr) && !empty($variantArr)) { ?>
    <button type="button" class="puple-btn short-btn new-btn-height" data-dismiss="modal" onclick="selectedVariantList(<?php echo $varnt['parent_id']; ?>)" id="product_variant_btn" name="product_variant_btn">Save</button>
    <?php } ?>
  </div><!-- next-btn -->
</div><!-- modal-body -->
