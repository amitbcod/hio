<?php $this->load->view('common/fbc-user/header'); ?>
    <main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div class="tab-content all-notification-list">	
		<div class="tab-pane fade in active common-tab-section" style="opacity:1;">
		<?php if (count($notificationData) > 0){ ?>
		<div class="">	
		<ul>
			<?php	
				$count=1;
				foreach($notificationData as $value){
				if($value->notification_type == 1){
			?>
			<li class="order-request <?php echo ($value->read_flag == 0)?'unread':''; ?>" data-id="<?php echo $value->id; ?>" onclick="updateNotificationUnreadlFlag(<?php echo $value->id; ?>)" id="notification<?php echo $value->id; ?>">
			<div class="order-head">
			<h4>Order Status</h4>
			<a class="dropdown-item" href="<?php echo BASE_URL.'seller/requested-applied-orders/view/'.$value->shop_id.'/'.$value->area_id;?>" onclick="updateNotificationUnreadlFlag(<?php echo $value->id; ?>)"><p><?php echo $value->notification_text;?></p></a>
			</div>
			<!--p><?php //$this->NotificationModel->getTime($value->created_at);?></p-->
			<div class="notification-btn" id="notification-btn<?php echo $value->id; ?>"> 
			<?php if($value->status == 0){?>

			<!--<button class="purple-btn" id="accept-btn" onclick="location.href = '<?php echo BASE_URL.'seller/requested-applied-orders/view/'.$value->shop_id.'/'.$value->area_id;?>';">Accept </button>	
			<button class="purple-btn" id="accept-btn" onclick="updateOrderStatus('<?php echo $value->id; ?>','1')">Accept </button>
			<button class="white-btn" id="decline-btn" onclick="updateOrderStatus('<?php echo $value->id; ?>','2')" >Decline </button>-->


			<?php }else{ 
				echo ($value->status == 1)?'<span class="order-confirmed">Accepted</sapn>':'<span class="order-rejected">Declined</sapn>';
			} ?>
			</div>
			</li>
			<?php }else{ ?>
			<?php //if($value->notification_type == 2){?>
			<li class="<?php echo ($value->notification_type == 2)?'order-confirmed':'order-rejected'?> <?php echo ($value->read_flag == 0)?'unread':''; ?>" data-id="<?php echo $value->id; ?>" onclick="updateNotificationUnreadlFlag(<?php echo $value->id; ?>)" id="notification<?php echo $value->id; ?>">
			<h4>Order Status</h4>
			<a class="dropdown-item" href="<?php echo BASE_URL.'seller/applied-orders/view/'.$value->shop_id.'/'.$value->area_id;?>" ><p><?php echo $value->notification_text;?></p></a>
			<!--p><?php //$this->NotificationModel->getTime($value->created_at);?></p-->
			</li>
			<?php } $count++;} ?>
		</ul>
		</div>
		<?php } ?>
		</div>
	</div>
    </main>
 <?php $this->load->view('common/fbc-user/footer'); ?>