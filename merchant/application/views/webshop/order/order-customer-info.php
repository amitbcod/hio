<div class="barcode-qty-box row order-details-sec-top">
	<div class="col-sm-6 order-id">
		<p><span>Order Number :</span> <?php echo ($current_tab=='supplier-b2b-order')?$b2b_orders:$OrderData->increment_id; ?></p>
		<p><span>Purchased on :</span> <?php echo date('d/m/Y',$OrderData->created_at); ?> | <?php echo date('h:i A',$OrderData->created_at); ?></p>
		<p><span>Order Status :</span> <?php echo $this->CommonModel->getOrderStatusLabel($OrderData->status);?></p>
		<p class="ship-ad-para"><span>Shipping Address :</span> <span class="order-address-inner"><?php

		if(isset($ShippingAddress) && $ShippingAddress->address_id!=''){
			$shipName=$ShippingAddress->first_name.' '.$ShippingAddress->last_name;
			$shipMobile=$ShippingAddress->mobile_no;
			if($shipName){
				//echo '<br><br>';
				echo $shipName.'<br/>';
			}
			echo $this->WebshopOrdersModel->getFormattedAddress($ShippingAddress);
			if($shipMobile){
				echo '<br/>Mob:';
				echo $shipMobile;
			}
		}else{
			echo '-';
		}
		?></span>
		<?php if ($OrderData->status==0 AND $current_tab=='order')
		{
		?>
			<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
			<button type="button" class="btn btn-primary change-add-n-btn" onclick="OpenEditAddressPopup_r(<?php echo $ShippingAddress->address_id ?>,<?php echo $OrderData->order_id; ?>)"><i class="fas fa-edit"></i>
			</button>
			<?php } ?>
		<?php
		} ?>
		</p>
		
		<p><span>Order Total Quantity :</span> <?php echo $OrderData->total_qty_ordered;?></p>

	</div>
	<div class="col-sm-6 order-id">
		<?php
			$customerId=$OrderData->customer_id;
			if($customerId > 0){
				$customerNameLink="<a href='".base_url()."CustomerController/customer_details/".$customerId."' target='_blank'>".$OrderData->customer_firstname.' '.$OrderData->customer_lastname."</a>";
			}else{
				//if($OrderData->checkout_method=='guest'){
					$customerNameLink=$OrderData->customer_firstname.' '.$OrderData->customer_lastname.'<br/>('.$OrderData->customer_email.')';
				/*}else{
					$customerNameLink=$OrderData->customer_firstname.' '.$OrderData->customer_lastname;
				}*/
			}
		?>
		<p><span class="huge-name">Customer Name :</span> <?=$customerNameLink?>  </p>
		<!-- <p><span class="huge-name">Customer Name :</span> <?php echo $OrderData->customer_firstname.' '.$OrderData->customer_lastname; ?>  </p> -->
		<p><span class="huge-name">Checkout Method :</span> <?php echo ucfirst($OrderData->checkout_method); ?>  </p>
		<?php
            $use_advanced_warehouse=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value');

			if ($use_advanced_warehouse->value=="yes") {
				$warehouse_status_name=$this->CommonModel->getWarehouse_status_name($OrderData->warehouse_status);

			?>
				<p><span>Warehouse Status :</span> <?php echo $warehouse_status_name; ?></p>
			<?php
			}
        ?>
		<p class="ship-ad-para"><span>Billing Address :</span> <span class="order-address-inner"><?php

		if(isset($BillingAddress) && $BillingAddress->address_id!=''){
			$billName=$BillingAddress->first_name.' '.$BillingAddress->last_name;
			if($billName){
				echo $billName.'<br/>';
			}
			echo $this->WebshopOrdersModel->getFormattedAddress($BillingAddress);
			$billMobile=$BillingAddress->mobile_no;
			if($billMobile){
				echo '<br/>Mob:';
				echo $billMobile;
			}
			if(isset($OrderData) && $OrderData->company_name != ''){
				echo '<br/>Comp Name:';
				echo $OrderData->company_name;
			}
			if(isset($OrderData) && $OrderData->vat_no != ''){
				echo '<br/>Vat No:';
				echo $OrderData->vat_no;
			}
		}else{
			echo '-';
		}
		 ?></span>
		<?php if ($OrderData->status==0 AND $current_tab=='order')
		{
		?>
		<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
			<button type="button" class="btn btn-primary change-add-n-btn" onclick="OpenEditAddressPopup_r(<?php echo $BillingAddress->address_id ?>,<?php echo $OrderData->order_id; ?>)"><i class="fas fa-edit"></i>
			</button>
		<?php } ?>
		<?php
		} ?>

		</p>

	</div>
	<?php // $shop_data=$this->CommonModel->getShopData_by_shop_id($this->session->userdata('ShopID')); ?>
	<?php if($current_tab=='supplier-b2b-order' ) { ?>
	<div class="col-sm-4 order-id">
	   <?php if(isset($OrderData->parent_id) && $OrderData->parent_id>0){ ?>
		<?php if(isset($ParentOrder->base_subtotal) && $ParentOrder->base_subtotal>0){ ?>
		<p><span> Base Sub Total :</span> <?php echo $currency_code.' '.number_format($ParentOrder->base_subtotal,2);?></p>
		<?php } ?>

		<?php if(isset($ParentOrder->coupon_code) && $ParentOrder->coupon_code!=''){ ?>
		<p><span> Discount (<?php echo $ParentOrder->coupon_code; ?>) :</span> <?php echo $currency_code.' '.number_format($ParentOrder->discount_amount,2);?></p>
		<?php } ?>

		<?php if(isset($ParentOrder->tax_amount)){ ?>
		<p><span> Taxes (Vat) Amount :</span> <?php echo $currency_code.' '.number_format($ParentOrder->tax_amount,2);?></p>
		<?php } ?>

		<?php if(isset($ParentOrder->shipping_amount) && $ParentOrder->shipping_amount>0){ ?>
		<p><span> Shipping Charges :</span> <?php echo $currency_code.' '.number_format($ParentOrder->shipping_amount,2);?></p>
		<?php } ?>

		<?php if(isset($shop_data) && $shop_data->vat_flag==1){ ?>
		<p class="position-relative"><span> Shipping Method : </span><?php if($ParentOrder->ship_method_name==""){ echo "-"; }else{ echo $ParentOrder->ship_method_name; }  ?><?php if($ParentOrder->status==0){ ?><button type="button" class="btn btn-primary change-add-n-btn" onclick="OpenShippingPopup(<?php echo $ParentOrder->order_id; ?>);" style="right:0px;"><i class="fas fa-edit"></i></button><?php } ?></p>
		<?php } ?>

		<?php if(isset($ParentOrder->voucher_code) && ($ParentOrder->voucher_code!='' && $ParentOrder->voucher_amount>0) ){ ?>
		<p><span> Voucher (<?php echo $ParentOrder->voucher_code; ?>) :</span> <?php echo $currency_code.' '.number_format($ParentOrder->voucher_amount,2);?></p>
		<?php } ?>

		<p><span>Grand total :</span> <?php echo $currency_code.' '.number_format($ParentOrder->grand_total,2); ?> </p>
		<!-- onlyfor mutlicurrency orders -->
		<?php if ($ParentOrder->currency_code_session !='' && $ParentOrder->default_currency_flag == 0){
			$currency = $ParentOrder->currency_code_session;
			$currency_symbol = $ParentOrder->currency_symbol;
			$conversion_rate = $ParentOrder->currency_conversion_rate;
			$grand_total_in_orderd_currency = number_format($ParentOrder->grand_total*$conversion_rate, 2, '.', '');
			?>
			<div class="mt-4 or-currency-box">
				<p><span>Order Placed in :</span> <?php echo $ParentOrder->currency_name; ?> (<?php echo $currency; ?>)</p>
				<p><span>Currency Conversaion Rate:</span> <?php echo $conversion_rate; ?></p>
				<p><span>Grand Total :</span> <?php echo $currency_symbol; ?> <?php echo $grand_total_in_orderd_currency; ?></p>
			</div>
		<?php } ?>

		<?php } else{ ?>

		<?php if(isset($OrderData->base_subtotal) && $OrderData->base_subtotal>0){ ?>
		<p><span> Base Sub Total :</span> <?php echo $currency_code.' '.number_format($OrderData->base_subtotal,2);?></p>
		<?php } ?>

		<?php if(isset($OrderData->coupon_code) && $OrderData->coupon_code!=''){ ?>
		<p><span> Discount (<?php echo $OrderData->coupon_code; ?>) :</span> <?php echo $currency_code.' '.number_format($OrderData->discount_amount,2);?></p>
		<?php } ?>

		<?php if(isset($OrderData->tax_amount)){ ?>
		<p><span> Taxes (Vat) Amount :</span> <?php echo $currency_code.' '.number_format($OrderData->tax_amount,2);?></p>
		<?php } ?>

		<?php if(isset($OrderData->shipping_amount)){ ?>
		<p><span> Shipping Charges :</span> <?php echo $currency_code.' '.number_format($OrderData->shipping_amount,2);?></p>
		<?php } ?>

		<?php if(isset($shop_data) && $shop_data->vat_flag==1){ ?>
		<p class="position-relative"><span> Shipping Method : </span><?php if ($OrderData->ship_method_name==""){ echo "-"; }else{ echo $OrderData->ship_method_name; }  ?><?php if($OrderData->status==0){ ?><button type="button" class="btn btn-primary change-add-n-btn" onclick="OpenShippingPopup(<?php echo $OrderData->order_id; ?>);" style="right: 0px;"><i class="fas fa-edit"></i></button><?php } ?></p>
		<?php } ?>

		<?php if(isset($OrderData->voucher_code) && ($OrderData->voucher_code!='' && $OrderData->voucher_amount>0) ){ ?>
		<p><span> Voucher (<?php echo $OrderData->voucher_code; ?>) :</span> <?php echo $currency_code.' '.number_format($OrderData->voucher_amount,2);?></p>
		<?php } ?>

			<p><span>Grand total :</span> <?php echo $currency_code.' '.number_format($OrderData->grand_total,2); ?> </p>
			<!-- onlyfor mutlicurrency orders -->
			<?php if ($OrderData->currency_code_session !='' && $OrderData->default_currency_flag == 0){
				$currency = $OrderData->currency_code_session;
				$currency_symbol = $OrderData->currency_symbol;
				$conversion_rate = $OrderData->currency_conversion_rate;
				$grand_total_in_orderd_currency = number_format($OrderData->grand_total*$conversion_rate, 2, '.', '');
				?>
				<div class="mt-4 or-currency-box">
					<p><span>Order Placed in :</span> <?php echo $OrderData->currency_name; ?> (<?php echo $currency; ?>)</p>
					<p><span>Currency Conversaion Rate:</span> <?php echo $conversion_rate; ?></p>
					<p><span>Grand Total :</span> <?php echo $currency_symbol; ?> <?php echo $grand_total_in_orderd_currency; ?></p>
				</div>
			<?php } ?>

		<?php }
		?>
		</div>
		<div class="col-sm-4 order-id">
			<p><span>Payment Mode :</span> Offline</p>
		</div>

		<div class="col-sm-4 order-id">

			<p><span>B2B order total :</span> <?php echo $currency_code_seller.' '.number_format($B2BOrderData->subtotal,2); ?> </p>
			<p><span> Discount (<?php echo $B2BOrderData->discount_percent; ?>%) :</span> <?php echo $currency_code_seller.' '.number_format($B2BOrderData->discount_amount,2);?></p>
			<p><span> B2B Taxes amount :</span> <?php echo $currency_code_seller.' '.number_format($B2BOrderData->tax_amount,2);?></p>
			<p><span>B2B Net Payable Amount :</span> <?php echo $currency_code_seller.' '.number_format($B2BOrderData->grand_total,2); ?></p>
		</div>


	<?php } else{ ?>

		<div class="col-sm-4 order-id">
			
		<?php if(isset($OrderData->parent_id) && $OrderData->parent_id>0){ ?>

		<?php if(isset($ParentOrder->base_subtotal) && $ParentOrder->base_subtotal>0){ ?>
		<p><span> Base Sub Total :</span> <?php echo $currency_code.' '.number_format($ParentOrder->base_subtotal,2);?></p>
		<?php } ?>

		<?php if(isset($ParentOrder->coupon_code) && $ParentOrder->coupon_code!=''){ ?>
		<p><span> Discount (<?php echo $ParentOrder->coupon_code; ?>) :</span> <?php echo $currency_code.' '.number_format($ParentOrder->discount_amount,2);?></p>
		<?php } ?>

		<?php if(isset($ParentOrder->tax_amount)){ ?>
		<p><span> Taxes (Vat) Amount :</span> <?php echo $currency_code.' '.number_format($ParentOrder->tax_amount,2);?></p>
		<?php } ?>

		<?php if(isset($ParentOrder->shipping_amount) && $ParentOrder->shipping_amount>0){ ?>
		<p class="position-relative"><span> Shipping Charges :</span> <?php echo $currency_code.' '.number_format($ParentOrder->shipping_amount,2);?></p>
		<?php } ?>

		<?php if(isset($ParentOrder->ship_method_name) && $ParentOrder->ship_method_name !=''){ ?>
		<p class="position-relative"><span> Shipping Method : </span> <?php echo $ParentOrder->ship_method_name; ?><?php if($ParentOrder->status==0){ ?><button type="button" class="btn btn-primary change-add-n-btn" onclick="OpenShippingPopup(<?php echo $ParentOrder->order_id; ?>);" style="right: 0px;"><i class="fas fa-edit"></i></button><?php } ?></p>
		<?php } ?>

		<?php if(isset($ParentOrder->voucher_code) && ($ParentOrder->voucher_code!='' && $ParentOrder->voucher_amount>0) ){ ?>
		<p><span> Voucher (<?php echo $ParentOrder->voucher_code; ?>) :</span> <?php echo $currency_code.' '.number_format($ParentOrder->voucher_amount,2);?></p>
		<?php } ?>

		<p><span>Grand total :</span> <?php echo $currency_code.' '.number_format($ParentOrder->grand_total,2); ?> </p>
		<!-- onlyfor mutlicurrency orders -->
		<?php if ($ParentOrder->currency_code_session !='' && $ParentOrder->default_currency_flag == 0){
			$currency = $ParentOrder->currency_code_session;
			$currency_symbol = $ParentOrder->currency_symbol;
			$conversion_rate = $ParentOrder->currency_conversion_rate;
			$grand_total_in_orderd_currency = number_format($ParentOrder->grand_total*$conversion_rate, 2, '.', '');
			?>
			<div class="mt-4 or-currency-box">
				<p><span>Order Placed in :</span> <?php echo $ParentOrder->currency_name; ?> (<?php echo $currency; ?>)</p>
				<p><span>Currency Conversaion Rate:</span> <?php echo $conversion_rate; ?></p>
				<p><span>Grand Total :</span> <?php echo $currency_symbol; ?> <?php echo $grand_total_in_orderd_currency; ?></p>
			</div>
		<?php } ?>

		<?php } else{ ?>

		<?php if(isset($OrderData->base_subtotal) && $OrderData->base_subtotal>0){ ?>
		<p><span> Base Sub Total :</span> <?php echo $currency_code.' '.number_format($OrderData->base_subtotal,2);?></p>
		<?php } ?>

		<?php if(isset($OrderData->coupon_code) && $OrderData->coupon_code!=''){ ?>
		<p><span> Discount (<?php echo $OrderData->coupon_code; ?>) :</span> <?php echo $currency_code.' '.number_format($OrderData->discount_amount,2);?></p>
		<?php } ?>

		<?php if(isset($OrderData->tax_amount)){ ?>
		<p class="position-relative"><span> Taxes (Vat) Amount :</span> <?php echo $currency_code.' '.number_format($OrderData->tax_amount,2);?>
		<?php if(isset($shop_data) && $shop_data->vat_flag==1){ ?>
			<button type="button" style="right: 0px;" class="btn btn-primary change-add-n-btn" onclick="OpenVatAmountPopup(<?php echo $OrderData->order_id ?>);" style="right:0px;"><i class="fas fa-edit"></i>
		    </button>
			<?php } ?>
	   </p>
		<?php } ?>

		<?php if(isset($shop_data) && $shop_data->vat_flag==1){ ?>
		<p>
		  <?php $OrderItemVatPer=$this->WebshopOrdersModel->getSingleDataByID('sales_order_items',array('order_id'=>$OrderData->order_id),'tax_percent'); ?>
			<span> Taxes (Vat) % :</span> <?php echo number_format($OrderItemVatPer->tax_percent,2);?>
		</p>
	    <?php } ?>


		<?php if(isset($OrderData->shipping_amount)){ ?>
		<p class="position-relative"><span> Shipping Charges :</span> <?php echo $currency_code.' '.number_format($OrderData->shipping_amount,2);?>
		<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
		<button type="button" class="btn btn-primary change-add-n-btn" onclick="OpenShippingAmountPopup(<?php echo $OrderData->order_id; ?>,'<?php echo number_format($OrderData->shipping_amount,2);?>');" style="right:0px;"><i class="fas fa-edit"></i>
		</button>
		<?php } ?>
	</p>
		<?php } ?>

		<?php if(isset($shop_data) && $shop_data->vat_flag==1){ ?>
		<p class="position-relative"><span> Shipping Method : </span><?php if ($OrderData->ship_method_name==""){ echo "-"; }else{ echo $OrderData->ship_method_name; }  ?><?php if($OrderData->status==0){ ?>
<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
			<button type="button" class="btn btn-primary change-add-n-btn" onclick="OpenShippingPopup(<?php echo $OrderData->order_id; ?>);" style="right: 0px;"><i class="fas fa-edit"></i>
			</button>
		<?php } ?>
			<?php } ?></p>
		<?php } ?>

		<?php if(isset($OrderData->voucher_code) && ($OrderData->voucher_code!='' && $OrderData->voucher_amount>0) ){ ?>
		<p><span> Voucher (<?php echo $OrderData->voucher_code; ?>) :</span> <?php echo $currency_code.' '.number_format($OrderData->voucher_amount,2);?></p>
		<?php } ?>


		<p class ="position-relative"><span>Grand total :</span> <?php echo $currency_code.' '.number_format($OrderData->grand_total,2); ?>

			<?php
				//$shopData = $this->CommonModel->getShopData($_SESSION['ShopOwnerId'],$_SESSION['ShopID']);
				if(isset($shopData) && $shopData->multi_currency_flag == 1) {
					if($current_tab=='order' && ( (isset($OrderPaymentDetail) && ($OrderPaymentDetail->payment_method_id==4 || $OrderPaymentDetail->payment_method_id== 5 || $OrderPaymentDetail->payment_method_id== 7 || $OrderPaymentDetail->payment_method_name =="" )) || !isset($OrderPaymentDetail) ))
					{ ?>
						<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
							<button type="button" class="btn btn-primary change-add-n-btn" onclick="OpenGrandTotalPopup(<?php echo $OrderData->order_id; ?>);" style="right: 0px;"><i class="fas fa-edit"></i></button>
						<?php
						}
					}
				}
			?>

		</p>
			<!-- onlyfor mutlicurrency orders -->
			<?php if ($OrderData->currency_code_session !='' && $OrderData->default_currency_flag == 0){
				$currency = $OrderData->currency_code_session;
				$currency_symbol = $OrderData->currency_symbol;
				$conversion_rate = $OrderData->currency_conversion_rate;
				$grand_total_in_orderd_currency = number_format($OrderData->grand_total*$conversion_rate, 2, '.', '');
				?>
				<div class="mt-4 or-currency-box">
					<p><span>Order Placed in :</span> <?php echo $OrderData->currency_name; ?> (<?php echo $currency; ?>)</p>
					<p><span>Currency Conversaion Rate:</span> <?php echo $conversion_rate; ?></p>
					<p><span>Grand Total :</span> <?php echo $currency_symbol; ?> <?php echo $grand_total_in_orderd_currency; ?></p>
				</div>
			<?php } ?>

		<?php }
		?>

		<?php if(($current_tab=='shipped-order') && isset($orderReturnData) && !empty($orderReturnData)){ ?>
			<p><span><b>Return Orders : </b></span>
				<br>
				<?php foreach($orderReturnData as $value) {?>
					<br><a href="<?php echo base_url()."webshop/return-request-order/detail/".$value->return_order_id; ?>" target="_blank">#<?php echo $value->return_order_increment_id; ?></a>
					<br>
			 	<?php } ?>
			</p>
		<?php } ?>

		</div>
		<div class="col-sm-4 order-id">
			<p class="position-relative"><span>Payment Mode :</span>
				<?php if(isset($OrderPaymentDetail) && $OrderPaymentDetail->payment_method_id == 7){
				 ?>
				<button type="button" class="btn btn-primary change-add-n-btn" onclick="OpenPaymentNotesPopup(<?php echo $OrderPaymentDetail->order_id; ?>);" style="right: 30px;"><i class="fas fa-eye"></i></button>
			<?php } ?>
		 <?php
			if($OrderData->voucher_code!='' && $OrderData->grand_total<=0){
					$payment_method_name='Voucher Payment';
				}else{
					//$payment_method_name=(isset($OrderPaymentDetail) && $OrderPaymentDetail->payment_method_name!='')?$OrderPaymentDetail->payment_method_name:'-';
					if(isset($shop_gateway_credentials['display_name']) && $shop_gateway_credentials['display_name']!='') {
						$payment_method_name = $shop_gateway_credentials['display_name'];

					}else if(isset($OrderPaymentDetail) && $OrderPaymentDetail->payment_method_name !=''){
						$payment_method_name = $OrderPaymentDetail->payment_method_name;
					}else{
						$payment_method_name = '-';
					}
				}
			echo $payment_method_name; ?>

			<?php
				if(!isset($OrderPaymentDetail) && isset($use_advanced_warehouse) && $use_advanced_warehouse->value=='yes' && $OrderData->main_parent_id==0 ){
			?>
			<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
				<button type="button" class="purple-btn add-manual-pay-btn" onclick="OpenPaymentPopup(<?php echo $OrderData->order_id; ?>);">Add Manual Payment</button>
			<?php } ?>
			<?php } ?>
			</p>

			<?php if($this->session->userdata('ShopID') === '1' && !empty($OrderPaymentDetail)){ ?>
<p>
				<table style="width: 100%">
					<tr>
						<th>Payment Status</th><td><?= $OrderPaymentDetail->status ?? '' ?></td>
					</tr>
					<tr>
						<th>Payment Amount</th><td><?= $OrderPaymentDetail->payment_amount ?? '' ?> <?= $OrderPaymentDetail->payment_currency ?? '' ?></td>
					</tr>
					<?php if(($OrderPaymentDetail->payment_currency ?? '') !== ($OrderPaymentDetail->currency_code ?? '')): ?>
						<tr>
							<th>Converted Payment Amount</th><td><?= $OrderPaymentDetail->amount ?? '' ?> <?= $OrderPaymentDetail->currency_code ?? '' ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<th>Payment Transaction:</th><td>
							<?php if($OrderPaymentDetail->payment_method === 'paypal_express'): ?>
								<a href="https://www.paypal.com/activity/payment/<?=$OrderPaymentDetail->transaction_id ?>" target="_blank"><?=$OrderPaymentDetail->transaction_id ?></a>
							<?php elseif($OrderPaymentDetail->payment_method === 'stripe_payment'): ?>
								<a href="https://dashboard.stripe.com/payments/<?= $OrderPaymentDetail->payment_intent_id?>" target="_blank"><?=$OrderPaymentDetail->payment_intent_id ?></a>
							<?php else: ?>
								<?=$OrderPaymentDetail->transaction_id ?>
							<?php endif; ?>
						</td>
					</tr>
					<?php
					if(!empty($OrderRefundDetail)):
					foreach($OrderRefundDetail->result() as $orderRefund): ?>
					<tr>
						<th>Payment Refund <?= $orderRefund->id ?>:</th><td>
							<?= $orderRefund->refund_amount ?> <?= $orderRefund->refund_currency ?> - <?= $orderRefund->status ?>
						</td>
					</tr>
					<?php endforeach;
					endif; ?>
				</table>
</p>
			<?php } ?>

			<?php if($current_tab !== 'split-order'){ ?>
			<p class="position-relative"><textarea placeholder="Note" readonly class="form-control col-sm-10 "><?=$OrderItemData->internal_notes?></textarea>
				<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders/write',$this->session->userdata('userPermission'))){ ?>
				<button type="button" class="btn btn-primary change-add-n-btn" onclick="OpenNotesPopup(<?php echo $OrderData->order_id; ?>);" style="right: 30px;"><i class="fas fa-edit"></i>
				</button>
				<?php } ?>
			</p>
			<?php } ?>

			<?php
			// $use_advanced_warehouse=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value');
			// if($use_advanced_warehouse->value=="yes"){
				?>
					<?php if($current_tab=='shipped-order' || $current_tab=='order' || $current_tab=='create-shipment'){
						$order_id = $OrderData->order_id;
					?>
						<!-- <p><span>CSV downloads :</span> <br>
						<select name="download_csv" id="download_csv" onchange="javascript: return DownloadOrderCSV(<?php //echo $order_id; ?>);">
							<option value="">Please select</option>
							<option value="1">Commercial invoice detailed </option>
							<option value="2">Commercial invoice compressed</option>
							<option value="3">Order details</option>
						</select>
						</p> -->
					<?php } ?>
			<?php //} ?>
		</div>

		<?php if(isset($OrderData->parent_id) && $OrderData->parent_id>0){ ?>
		<div class="col-sm-4 order-id">
			<p><span>Split order Grand total </span> <?php echo $currency_code.' '.number_format($OrderData->grand_total,2); ?> </p>
			</div>
		<?php } ?>

		<?php if(($current_tab=='shipped-order') && (isset($b2b_orders) && count($b2b_orders)>0)){ ?>
		<div class="col-sm-4 order-id">
			<p><span>B2B order Grand total </span> <?php echo $currency_code.' '.number_format($b2b_grand_total,2); ?> </p>
			</div>
		<?php } ?>
	<?php } ?>
</div><!-- barcode-qty-box -->

<script type="text/javascript">

function OpenEditAddressPopup_r(address_id,order_id)
{
	if(address_id !='')
	{
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"WebshopOrdersController/OpenEditAddressPopup_r",
			data: {
				address_id:address_id,
				order_id:order_id
			},
			//async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				$("#FBCUserSecondaryModal").modal();
				$("#modal-content-second").html(response);
			}
		});
	}
}
</script>
