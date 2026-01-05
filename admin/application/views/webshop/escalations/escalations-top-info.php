<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
  <h1 class="head-name"><?php if(isset($PageTitle) && !empty($PageTitle)){echo $PageTitle;} ?></h1> 
  <div class="float-right">
	<!-- <a class="purple-btn" type="button" target="_blank" href="<?php echo base_url(); ?>webshop/return-order/print/<?php echo $EscalationsOrderData->id; ?>">Print</a> -->
	<span class="barcode"><img src="<?php echo getBarcodeUrl($EscalationsOrderData->esc_order_id); ?>" width="132"></span>
  </div>
</div>
