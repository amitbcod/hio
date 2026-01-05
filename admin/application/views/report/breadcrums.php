<ul class="nav nav-pills">
	<li class="<?php echo (isset($current_tab) && ($current_tab=='reports' || $current_tab=='reports'))?'active':''; ?>">
		<a  href="<?php echo base_url() ?>reports">Voucher Overview</a>
	</li>
	<li class="<?php echo (isset($current_tab) && ($current_tab=='discount-overview' || $current_tab=='discount-overview'))?'active':''; ?>">
		<a  href="<?php echo base_url() ?>discount-overview">Discount Overview</a>
	</li>
	<li class="<?php echo (isset($current_tab) && ($current_tab=='sales-overview' || $current_tab=='sales-overview'))?'active':''; ?>">
		<a  href="<?php echo base_url() ?>sales-overview">Sales Report</a>
	</li>
	<!-- <li class="<?php echo (isset($current_tab) && ($current_tab=='return-overview' || $current_tab=='return-overview'))?'active':''; ?>">
		<a  href="<?php echo base_url() ?>return-overview">Return / Escalations & Refund REPORTS</a>
	</li> -->
	<li class="<?php echo (isset($current_tab) && ($current_tab=='customer-overview' || $current_tab=='customer-overview'))?'active':''; ?>">
		<a  href="<?php echo base_url() ?>customer-overview">CUSTOMERS REPORT</a>
	</li>
	<li class="<?php echo (isset($current_tab) && ($current_tab=='publisher-overview' || $current_tab=='publisher-overview'))?'active':''; ?>">
		<a  href="<?php echo base_url() ?>publisher-overview">MERCHANT REPORT</a>
	</li>

	<li class="<?php echo (isset($current_tab) && ($current_tab=='product-overview' || $current_tab=='product-overview'))?'active':''; ?>">
		<a  href="<?php echo base_url() ?>product-overview">PRODUCT REPORT</a>
	</li>
</ul>