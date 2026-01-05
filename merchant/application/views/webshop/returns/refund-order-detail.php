<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('webshop/returns/ref_breadcrums'); ?>
	
	<div class="tab-content"  >
		<div id="refund-request-tab" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
				<?php  $this->load->view('webshop/returns/return-top-info'); ?>
				
				
				<input type="hidden" id="returns_id" name="returns_id" value="<?php echo $ReturnOrderData->return_order_id; ?>">
				
				<!-- form -->
				<div class="content-main form-dashboard">
					<?php  $this->load->view('webshop/returns/return-customer-info'); ?>

				
					<div id="order-item-outer">
						<?php  $this->load->view('webshop/returns/refund-items'); ?>
					</div>
				<?php 
					$shipping_amount=0;
					$final_order_grandtotal_approved=0;
					if($ReturnOrderData->shipping_charge_flag==1){
						$refundShippingCost='checked';
						$shipping_amount=$ReturnOrderData->shipping_amount;
						$final_order_grandtotal_approved=$ReturnOrderData->order_grandtotal_approved;
						$shipping_msg="(shipping cost :".$currency_code.$shipping_amount.")";
					}else{
						$refundShippingCost='';
						$final_order_grandtotal_approved=$ReturnOrderData->order_grandtotal_approved;
						$shipping_msg='';
					}
					if($ReturnOrderData->refund_status==0){

				?>
					<div class="row col-sm-12">
	                    <div class="col-sm-10 pad-zero checkbox-label">
	                        <label class="checkbox">
	                        	<?php if(!empty($this->session->userdata('userPermission')) && !in_array('webshop/refunds/write',$this->session->userdata('userPermission'))){ ?>
	                            <input type="checkbox" id="add_shipping_cost" disabled name="access_prelaunch" <?= $refundShippingCost ?>> Refund Shipping cost <span class="checked" style="width:12px;"></span>
	                            <?php } else{ ?>
	                            	<input type="checkbox" id="add_shipping_cost" name="access_prelaunch" <?= $refundShippingCost ?>> Refund Shipping cost <span class="checked" style="width:12px;"></span>
	                            <?php } ?>

	                        </label>
	                        <input type="hidden" value="<?=$OrderData->shipping_amount?>" id="shipping_amount">
	                    </div>
	                </div>
				<?php } ?> 
					<div class="save-discard-btn pad-bottom-20 return-req-bottom order-id return-request">
							
							<p><span>Refund Approved </span>  <?php echo $currency_code; ?> <span id="return_approved"><?php echo ($final_order_grandtotal_approved>0)?$final_order_grandtotal_approved:'-'; ?> <?=$shipping_msg?></span> </p>
							<input type="hidden" id="order_grandtotal_approved" value="<?=$ReturnOrderData->order_grandtotal_approved?>">
							
					<?php 		if($ReturnOrderData->refund_status==0){ ?>
					<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/refunds/write',$this->session->userdata('userPermission'))){ ?> 
						 <button class="white-btn" onclick="RejectRefundRequest(<?php echo $ReturnOrderData->return_order_id; ?>);">Reject All </button>
						  <button class="purple-btn" id="confirm-returns-btn"  onclick="OpenConfirmRefundPopup(<?php echo $ReturnOrderData->return_order_id; ?>);">Confirm Refund </button>
					<?php } ?>
					<?php } ?>
						 
						 </div>

					
				</div>
				<!--end form-->
			</div>
	</div>
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_returns_detail.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop-returns-item.js?v=<?php echo CSSJS_VERSION; ?>"></script>
 
<?php $this->load->view('common/fbc-user/footer'); ?>