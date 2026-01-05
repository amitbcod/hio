<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('webshop/returns/ret_breadcrums'); ?>
	
	<div class="tab-content"  >
		<div id="new-returnss" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
				<?php  $this->load->view('webshop/returns/return-top-info'); ?>
				
				
				<input type="hidden" id="returns_id" name="returns_id" value="<?php echo $ReturnOrderData->return_order_id; ?>">
				
				<!-- form -->
				<div class="content-main form-dashboard">
					<?php  $this->load->view('webshop/returns/return-customer-info'); ?>
					
				
					<div id="order-item-outer">
						<?php  $this->load->view('webshop/returns/return-items'); ?>
					</div>
					
					<?php if($ReturnOrderData->status==3 || $ReturnOrderData->status==5){ ?>
						<div class="save-discard-btn pad-bottom-20 return-req-bottom order-id return-request">
							<p><span>Refund Approved </span>  <?php echo $currency_code; ?> <span id="return_approved"><?php echo ($ReturnOrderData->order_grandtotal_approved>0)?$ReturnOrderData->order_grandtotal_approved:'-'; ?></span> </p>
						
						 </div>
					
					<?php }else{ ?>
						<div class="save-discard-btn pad-bottom-20 return-req-bottom order-id return-request">
							<p><span>Refund Approved </span>  <?php echo $currency_code; ?> <span id="return_approved"><?php echo ($ReturnOrderData->order_grandtotal_approved>0)?$ReturnOrderData->order_grandtotal_approved:'-'; ?></span> </p>
							
							
							<?php 
							
					$disabled='';											
					$QtyScanItem=$this->ReturnOrderModel->getQtyFullyScannedOrderItems($ReturnOrderData->return_order_id);
					$AllItems=$this->ReturnOrderModel->getReturnOrderItems($ReturnOrderData->return_order_id);
					if(count($QtyScanItem)==count($AllItems))
					{
						$disabled='';
					}else{
						$disabled='disabled';
					}

							?>
							<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/refunds/write',$this->session->userdata('userPermission'))){ ?>
						   <button class="white-btn" onclick="RejectReturnRequest(<?php echo $ReturnOrderData->return_order_id; ?>);">Reject All </button>
						   <button class="purple-btn" id="confirm-returns-btn" <?php echo $disabled; ?> onclick="ConfirmReturnRequest(<?php echo $ReturnOrderData->return_order_id; ?>);">Confirm Order </button>
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
 <script type="text/javascript">
$(document).ready(function(){
	
	
	$(".sis-datepicker-ret").datepicker({ 
        autoclose: true, 
        todayHighlight: true,
		//startDate: new Date(),
		format:'dd/mm/yyyy',
	}).on('changeDate', function(selected){
	  var minDate = new Date(selected.date.valueOf());
	  
	
		
		 saveReturnReceiveDate(this.value,<?php echo $ReturnOrderData->return_order_id; ?>);
    });
  
});
  </script>
		
  
<?php $this->load->view('common/fbc-user/footer'); ?>