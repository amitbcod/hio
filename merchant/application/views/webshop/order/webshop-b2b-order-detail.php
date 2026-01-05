<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('webshop/order/breadcrums'); ?>
	
	<div class="tab-content"  >
		<div id="new-orders" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
				<?php  $this->load->view('webshop/order/order-top-info'); ?>
				
				
				<!-- form -->
				
				<div class="content-main form-dashboard">
				
					<?php  $this->load->view('webshop/order/order-customer-info'); ?>
					
					<input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">
					<input type="hidden" id="order_id" name="order_id" value="<?php echo $OrderData->order_id; ?>">
						
					<?php  $this->load->view('webshop/order/all_webshop_b2b_items'); ?>
							
							
				</div>
				
				<!--end form-->
			</div>
	</div>
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop-order-item.js?v=<?php echo CSSJS_VERSION; ?>"></script>

		
  
<?php $this->load->view('common/fbc-user/footer'); ?>