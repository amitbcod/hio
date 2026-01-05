<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('webshop/order/breadcrums'); ?>
	
	<div class="tab-content"  >
		<div id="new-orders" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
				<?php  $this->load->view('webshop/order/order-top-info'); ?>
				<input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">
				<input type="hidden" id="order_id" name="order_id" value="<?php echo $OrderData->order_id; ?>">
				
				<!-- form -->
				<div class="content-main form-dashboard">
					<?php  $this->load->view('webshop/order/order-customer-info'); ?>
					
					
					<div id="order-item-outer">
						<?php  $this->load->view('webshop/order/order-items'); ?>
					</div>
					
						<div class="save-discard-btn pad-bottom-20">
						<?php $CheckShipmentExist=$this->WebshopOrdersModel->CheckShipmentExist($OrderData->order_id); 
						if(empty($CheckShipmentExist)){
							
							if($OrderData->status==4 || $OrderData->status==5 || $OrderData->status==6 || $OrderData->status==3){ }else{
						?>
					<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?> 
						   <button class="purple-btn" type="button" disabled  id="confirm-order-btn" onclick="ConfirmOrder(<?php echo $OrderData->order_id; ?>);">Confirm Order </button>
						   <?php if($OrderData->system_generated_split_order==1){?>
							<button class="purple-btn" type="button"  id="split-order-btn" onclick="OpenSplitOrderPopup(<?php echo $OrderData->order_id; ?>);">Split Order </button>
						<?php } ?>
						<?php 
							   } 
						} 
						?>
						<?php }else{ ?>
							<button class="purple-btn" type="button"  onclick="gotoLocation('<?php echo base_url().'webshop/orders'; ?>');">Back </button>
						<?php } ?>
						 </div>
					

					
				</div>
				<!--end form-->
			</div>
	</div>
</main>
<?php 
	if(isset($cancel_order) && $cancel_order=='able_to_cancel'){ 
		if($OrderData->parent_id >0 ){ 
			$this->load->view('webshop/order/cancel-order-split-popup.php');
		}
		
	} 
?>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_order_detail.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop-order-item.js?v=<?php echo CSSJS_VERSION; ?>"></script>

		
  
<?php $this->load->view('common/fbc-user/footer'); ?>