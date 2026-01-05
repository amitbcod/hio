<ul class="nav nav-pills">
	<?php
		if(isset($shipmentStatusList) && !empty($shipmentStatusList)){
			foreach($shipmentStatusList as $key => $svalue){
	?>
			<li class="shpment-status <?php echo (isset($current_tab) && ($current_tab==$key || $current_tab==$key))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/shipment-status/<?php echo $key; ?>"><?php echo $svalue; ?></a></li>
	<?php
			}
		}
	?>
</ul>
