<ul class="nav nav-pills">
		<li class="<?php echo (isset($current_tab) && ($current_tab=='orders' || $current_tab=='order'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/b2b-orders/">B2B Orders</a></li>
		<!-- <li class="<?php //echo (isset($current_tab) && ($current_tab=='split-orders' || $current_tab=='split-order'))?'active':''; ?>"><a  href="<?php //echo base_url() ?>webshop/split-orders/">split Orders</a></li> -->
		<!-- <li class="<?php echo (isset($current_tab) && ($current_tab=='b2b-orders' || $current_tab=='b2b-order'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/b2b-orders/">B2B Orders</a></li> -->
		<!-- <li class="<?php echo (isset($current_tab) && ($current_tab=='shipped-orders' || $current_tab=='shipped-order'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/shipped-orders/">Shipped Orders</a></li> -->
		<li class="<?php echo (isset($current_tab) && ($current_tab=='cancel-orders' || $current_tab=='cancel-order'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/cancel-orders/">Cancel Orders</a></li>
		<div class="filter_order_seaching dataTables_filter">
			<label><input id="global-order-search" type="search" class="form-control form-control-sm" placeholder="Search by order number"></label>
		</div>
</ul>
