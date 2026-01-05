<div class="modal-header">
  <h1 class="head-name">Variant List</h1>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>

<div class="modal-body">
  <div class="table-responsive text-center make-virtual-table">
    <table class="table table-bordered table-style">
      <thead>
        <tr>
            <th>Product Name</th>
            <th>Categories</th>
            <th>Variant</th>
            <th>SKU</th>
            <th>Inventory</th>
            <th>Price</th>
            <th>Details</th>
        </tr>
      </thead>
      <tbody>
        <?php if(isset($variantArr) && !empty($variantArr)) { ?>
          <?php foreach($variantArr as $varnt) { ?>
          <tr>
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
            <td><a class="link-purple" href="<?php echo BASE_URL.'seller/product/edit/'.$varnt['parent_id'];?>">View</a></td>
          </tr>
          <?php } ?>
        <?php } ?>
			</tbody>
    </table>
  </div>
</div><!-- modal-body -->
