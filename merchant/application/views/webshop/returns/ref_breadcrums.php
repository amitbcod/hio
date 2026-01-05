<ul class="nav nav-pills">
		<li class="<?php echo (isset($current_tab) && ($current_tab=='refund-request-order' || $current_tab=='refund_request'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/orders/refund-request">Refund Request</a></li>
		<li class="<?php echo (isset($current_tab) && ($current_tab=='refund_complete'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/orders/refund-completed">Refund Completed</a></li>
		<li class="<?php echo (isset($current_tab) && ($current_tab=='' || $current_tab=='escalations_request' || $current_tab=='escalations-request-order'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/orders/escalations-request">Escalations Request</a></li>		
		<li class="<?php echo (isset($current_tab) && ($current_tab=='' || $current_tab=='escalations_completed'))?'active':''; ?>"><a  href="<?php echo base_url() ?>webshop/orders/escalations-completed">Escalations Completed</a></li>		
		
</ul>