<ul class="nav nav-pills">
		<li class="<?php echo (isset($current_tabs) && ($current_tabs=='orders' || $current_tabs=='order')) ? 'active' : ''; ?>"><a  href="<?php echo base_url() ?>b2b/orders/">New Orders</a></li>
		<li class="<?php echo (isset($current_tabs) && ($current_tabs=='split-orders' || $current_tabs=='split-order')) ? 'active' : ''; ?>"><a  href="<?php echo base_url() ?>b2b/split-orders/">Split Orders</a></li>
		<li class="<?php echo (isset($current_tabs) && ($current_tabs=='shipped-orders' || $current_tabs=='shipped-order')) ? 'active' : ''; ?>"><a  href="<?php echo base_url() ?>b2b/shipped-orders/">Shipped Orders</a></li>
		<li class="<?php echo (isset($current_tabs) && ($current_tabs=='cancel-orders' || $current_tabs=='cancel-order')) ? 'active' : ''; ?>"><a  href="<?php echo base_url() ?>b2b/cancel-orders/">Cancel Orders</a></li>
		<div class="filter_order_seaching dataTables_filter">
			<label><input id="global-b2b-order-search" type="search" class="form-control form-control-sm" placeholder="Search by order number"></label>
		</div>
</ul>
