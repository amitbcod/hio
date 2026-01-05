<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('webshop/returns/ref_breadcrums'); ?>
	
	<div class="tab-content"  >
		<div id="refund-request-tab" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
				<?php  $this->load->view('webshop/escalations/escalations-top-info'); ?>
				
				
				<input type="hidden" id="returns_id" name="returns_id" value="<?php echo $EscalationsOrderData->id; ?>">
				
				<!-- form -->
				<div class="content-main form-dashboard">
				<?php  
					if($OrderData->parent_id > 0 || $OrderData->main_parent_id > 0){
						$this->load->view('webshop/escalations/escalations-split-request-top-info.php'); 
					}else{
						$this->load->view('webshop/escalations/escalations-request-top-info.php'); 
					}
					//$this->load->view('webshop/escalations/escalations-request-top-info.php'); 
				?>

				
					<div id="order-item-outer">
						<?php  $this->load->view('webshop/escalations/refund-items'); ?>
					</div>
					<?php if($EscalationsOrderData->cancel_refund_type!=''){ 
						//echo $EscalationsOrderData->cancel_refund_type;
						?>
						<div class="save-discard-btn pad-bottom-20 return-req-bottom order-id return-request">
								<p><span>Refund Approved </span>  <?php echo $currency_code; ?> <span id="return_approved"><?php 
								//print_r($OrderData);
								//echo $OrderData->base_subtotal - $OrderData->discount_amount;
								echo number_format($EscalationsOrderData->order_grandtotal_approved,2)
								//echo ($EscalationsOrderData->order_grandtotal_approved>0)?$EscalationsOrderData->order_grandtotal_approved:'-'; ?></span> </p>
								
						<?php 		if($EscalationsOrderData->refund_status==0){ ?> 
							<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/refunds/write',$this->session->userdata('userPermission'))){ ?>
							 <button class="white-btn" onclick="RejectEscalationsRequest(<?php echo $EscalationsOrderData->id; ?>);">Reject All </button>
							   <button class="purple-btn" id="confirm-returns-btn"  onclick="OpenConfirmEscalationsPopup(<?php echo $EscalationsOrderData->id; ?>);">Confirm Refund </button>
							<?php } ?>
						<?php } ?>
							 
						</div>
					<?php } ?>
					
				</div>
				<!--end form-->
			</div>
	</div>
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_returns_detail.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop-returns-item.js?v=<?php echo CSSJS_VERSION; ?>"></script>
 
<?php $this->load->view('common/fbc-user/footer'); ?>