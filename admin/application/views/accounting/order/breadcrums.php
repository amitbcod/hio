<ul class="nav nav-pills">
		<li class="<?php echo (isset($current_tab) && ($current_tab=='Webshop Orders' || $current_tab=='Webshop Orders'))?'active':''; ?>">
			<a  href="<?php echo base_url() ?>accounting/webshop">Webshop Orders to be Billed</a>
			<!-- <a  href="<?php echo base_url() ?>webshop/orders/">Webshop Orders to be Billed</a> -->
		</li>
		<li class="<?php echo (isset($current_tab) && ($current_tab=='B2Webshop Orders' || $current_tab=='B2Webshop Orders'))?'active':''; ?>">
			<a  href="<?php echo base_url() ?>accounting/b2Webshop">B2Webshop Orders to be Billed</a>
			<!-- <a  href="<?php echo base_url() ?>webshop/split-orders/">B2Webshop Orders to be Billed</a> -->
		</li>
		<li class="<?php echo (isset($current_tab) && ($current_tab=='Invoicing' || $current_tab=='Invoicing'))?'active':''; ?>">
			<a  href="<?php echo base_url() ?>accounting/invoicing">Invoicing</a>
			<!-- <a  href="<?php echo base_url() ?>webshop/shipped-orders/">Invoicing</a> -->
		</li>
		<?php 
			if(isset($shop_flag) && $shop_flag==2){
		?>
		<li class="<?php echo(isset($current_tab) && ($current_tab=='Sales Report' || $current_tab=='Sales Report'))?'active':''; ?>">

			<a  href="<?php echo base_url() ?>accounting/salesreport">Sales Report</a>
		</li>
		<?php } ?>
</ul>