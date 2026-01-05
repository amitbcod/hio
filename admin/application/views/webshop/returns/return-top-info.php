<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
  <h1 class="head-name">Return Order Details </h1> 
  <div class="float-right">
	<a class="purple-btn" type="button" target="_blank" href="<?php echo base_url(); ?>webshop/return-order/print/<?php echo $ReturnOrderData->return_order_id; ?>">Print</a>
	<span class="barcode"><img src="<?php echo getBarcodeUrl($ReturnOrderData->return_order_barcode); ?>" width="132"></span>
  </div>
</div>
