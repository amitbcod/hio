<ul class="nav nav-pills">
	<li class="<?php echo (isset($current_tab) && ($current_tab=='return_request'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/orders/return-request">Return Requests</a></li>
	<li class="<?php echo (isset($current_tab) && ($current_tab=='' || $current_tab=='expected_return'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/orders/expected-returns">Expected Returns</a></li>		
</ul>