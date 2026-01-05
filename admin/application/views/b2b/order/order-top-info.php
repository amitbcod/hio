<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
  <h1 class="head-name">Order Details </h1>
  <div class="float-right">
  <?php if ($OrderData->parent_id>0) {?>
		<button class="white-btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="View Split Orders">View Split Orders </button>
		<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
		<?php if (isset($SplitOrderIds) && count($SplitOrderIds)>0) {
			foreach ($SplitOrderIds as $spo) {?>
			<a id="" class="m-2 dropdown-item" href="<?php echo base_url() ?>b2b/split-order/detail/<?php echo $spo->order_id; ?>"><?php echo $spo->increment_id; ?></a>
			<div class="dropdown-divider"></div>
		<?php }
			} ?>
		</div>

  <?php } ?>

	<?php if ($current_tab=='order' || $current_tab=='split-order') { ?>
	<!-- <a class="purple-btn" type="button" target="_blank" href="<?php echo base_url(); ?>b2b/order/print/<?php echo $OrderData->order_id; ?>">Print</a> -->
	<?php } elseif ($current_tab=='shipped-order') { ?>
	<!-- <a class="purple-btn" type="button" target="_blank" href="<?php echo base_url(); ?>b2b/shipped-order/print/<?php echo $OrderData->order_id; ?>">Print</a> -->
	<?php } ?>

	</div>
</div>
