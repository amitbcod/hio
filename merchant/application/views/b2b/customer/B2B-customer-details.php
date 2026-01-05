<?php $this->load->view('common/fbc-user/header'); ?>
    <main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div class="profile-details busniess-details customer-details">
		<div class="row">
		<div class="col-md-12">
		<h2>Customer  Details</h2>
		<div class="barcode-qty-box row order-details-sec-top">
			<div class="col-sm-6 order-id">
				<p><span>Owner Name :</span> <?php echo $customer_details->owner_name; ?></p>
				<p><span>Webshop Name :</span> <?php echo $customer_details->org_shop_name; ?></p>
				<p><span>Website address :</span> <?php echo $customer_details->org_website_address; ?> </p>
				<p><span>GST Number :</span> <?php echo $customer_details->gst_no; ?></p>
				<p><span>Last Purchase Date :</span> <?php echo !empty($last_purchase_date) ? date("m/d/Y | h:m A", $last_purchase_date) : "-";?></p>
			</div>
			<div class="col-sm-6 order-id">
				<p><span>Mobile Number :</span> <?php echo $customer_details->mobile_no; ?></p>
				<p><span>Email ID :</span> <?php echo $customer_details->email; ?> </p>
				<p>
					<span>Shipping Address :</span> <span class="order-address-inner"><?php echo $customer_details->ship_address_line1 ; ?>  <?php echo !empty($customer_details->ship_address_line2) ? $customer_details->ship_address_line2.',' : ''; ?>
					<?php echo !empty($customer_details->ship_city) ? $customer_details->ship_city.',' : ''; ?>
					<?php echo !empty($customer_details->ship_state) ? $customer_details->ship_state.',' : ''; ?>
					<?php echo !empty($customer_details->ship_country) ? $customer_details->ship_country.' -' : ''; ?>
					<?php echo !empty($customer_details->ship_pincode) ? $customer_details->ship_pincode : ''; ?>
					</span>
				</p>
				<p>
					<span>Billing Address :</span> <span class="order-address-inner"><?php echo $customer_details->bill_address_line1 ; ?>  <?php echo !empty($customer_details->bill_address_line2) ? $customer_details->bill_address_line2.',' : ''; ?>
					<?php echo !empty($customer_details->bill_city) ? $customer_details->bill_city.',' : ''; ?>
					<?php echo !empty($customer_details->bill_state) ? $customer_details->bill_state.',' : ''; ?>
					<?php echo !empty($customer_details->bill_country) ? $customer_details->bill_country.' -' : ''; ?>
					<?php echo !empty($customer_details->bill_pincode) ? $customer_details->bill_pincode : ''; ?>
					</span>
				</p>
				<p><span>Total purchase Amt :</span> <?php echo $currency_code; ?> <?php echo $total_purchase; ?></p>
			</div>
		<div class="col-sm-12"><h2 class="bank-head">Bank Details </h2></div>
			<div class="col-sm-4 order-id bank-customer">
				<p><span>Bank Name :</span> <?php echo $customer_details->bank_name; ?> </p>
			</div>
			<div class="col-sm-4 order-id bank-customer">
				<p><span>Branch  :</span> <?php echo $customer_details->bank_branch; ?> </p>
			</div>
			<div class="col-sm-4 order-id bank-customer">
				<p><span>IFSC :</span> <?php echo $customer_details->bank_ifsc; ?> </p>
			</div>
		</div>
		<form id="Exclusive_term_form" method="POST" action="<?php echo base_url('B2BController/postExclusiveterms') ?>">
			<input type="hidden" name="b2b_customer_id" value="<?=$customerId ; ?>">
			<input type="hidden" name="customer_id_for_b2b" value="<?=$customer_details->shop_id ; ?>">
		<div class="row">
			<div class="col-sm-12"><h2 class="bank-head exc-term">Exclusive Terms </h2></div>
			<div class="col-sm-6 profile-details-inner role-in-company mar-top-zero  pad-left-10">
				<label>Allow  Dropshipping</label>
				<div class="switch-onoff">
				<label class="checkbox">
				<?php
					$allowDropShip = (isset($customer_details->allow_dropship) && $customer_details->allow_dropship == 1) ? 'checked' : '';
$displayDropShip = (isset($customer_details->allow_dropship) && $customer_details->allow_dropship == 1) ? '' : 'display:none';
?>
				<input type="checkbox" name="dropShipChk" id="dropShipChk" autocomplete="off"  <?php if ($customer_details->allow_dropship) {
					echo "checked" ;
				} ?>  >
					<span class="checked"  ></span>
				</label>
				</div><!-- checkbox switch-onoff -->

				<div class="profile-inside-box toggle-dropshp" style="<?php echo $displayDropShip;?>">
					<label>Dropshipping  Discount</label>
					<input class="form-control" type="text"  name="dropshipDiscount" id="dropshipDiscount" value="<?php  echo "$customer_details->dropship_discount" ; ?>"  placeholder="Discount in %">
				</div><!-- profile-inside-box -->

				<div class="profile-inside-box toggle-dropshp" style="<?php echo $displayDropShip;?>">
					<label>Dropshipping  Delivery Time</label>
					<input class="form-control" type="text"  name="dropshipTime" id="dropshipTime" value="<?php  echo "$customer_details->dropship_del_time" ; ?>"   placeholder="Days">
				</div><!-- profile-inside-box -->
			</div><!-- col-sm-6 -->

			<div class="col-sm-6 profile-details-inner role-in-company mar-top-zero  pad-left-10">
				<label>Allow  BuyIn</label>
				<div class="switch-onoff">
				<label class="checkbox">
				<?php
					$allowBuyIn = (isset($customer_details->allow_buyin) && $customer_details->allow_buyin == 1) ? 'checked' : '';
$displayBuyIn = (isset($customer_details->allow_buyin) && $customer_details->allow_buyin == 1) ? '' : 'display:none';
?>
				<input type="checkbox" name="buyInChk" id="buyInChk"  autocomplete="off" <?php if ($customer_details->allow_buyin) {
					echo "checked" ;
				} ?>>
					<span class="checked"></span>
				</label>
				</div><!-- checkbox switch-onoff -->

				<div class="profile-inside-box toggle-buy-in" style="<?php echo $displayBuyIn;?>">
					<label>BuyIn  Discount</label>
					<input class="form-control" type="text" name="buyinDiscount" id="buyinDiscount" value="<?php  echo "$customer_details->buyin_discount" ; ?>" placeholder="" value="30%">
				</div><!-- profile-inside-box -->

				<div class="profile-inside-box toggle-buy-in" style="<?php echo $displayBuyIn;?>">
					<label>BuyIn  Delivery Time</label>
					<input class="form-control" type="text" name="buyinTime" id="buyinTime"  value="<?php  echo "$customer_details->buyin_del_time" ; ?>" placeholder="" value="3 days">
				</div><!-- profile-inside-box -->

			</div><!-- col-sm-6 -->

			<div class="col-sm-6 profile-details-inner mar-top-zero pad-left-10">
				<label>Permission to change the price</label>
				<div class="switch-onoff">
				<label class="checkbox">
				<?php
					$canChangePrice = (isset($customer_details->perm_to_change_price) && $customer_details->perm_to_change_price == 1) ? 'checked' : '';
$displayChangePrice = (isset($customer_details->perm_to_change_price) && $customer_details->perm_to_change_price == 1) ? '' : 'display:none';
?>
				<input type="checkbox" name="priceChk" id="priceChk"  autocomplete="off"  <?php if ($customer_details->perm_to_change_price) {
					echo "checked" ;
				} ?>>
					<span class="checked"></span>
				</label>
				</div><!-- switch off -->

				<div class="col-sm-12 profile-details-inner mar-top-zero toggle-price" style="<?php echo $displayChangePrice;?>">
					<label class="pad-left-20">Can Increase the Price</label>
					<div class="switch-onoff">
					<label class="checkbox">
					<?php
						$canIncPrice = (isset($customer_details->can_increase_price) && $customer_details->can_increase_price == 1) ? 'checked' : '';
?>
					<input type="checkbox" name="incPriceChk" id="incPriceChk"  autocomplete="off"  <?php if ($customer_details->can_increase_price) {
						echo "checked" ;
					} ?>>
						<span class="checked"></span>
					</label>
					</div>
				</div><!-- col-sm-12 -->

				<div class="col-sm-12 profile-details-inner mar-top-zero toggle-price" style="<?php echo $displayChangePrice;?>">
					<label class="pad-left-20">Can Decrease the Price</label>
					<div class="switch-onoff">
					<label class="checkbox">
					<?php
						$canDecPrice = (isset($customer_details->can_decrease_price) && $customer_details->can_decrease_price == 1) ? 'checked' : '';
?>
					<input type="checkbox" name="decPriceChk" id="decPriceChk" autocomplete="off"  <?php if ($customer_details->can_decrease_price) {
						echo "checked" ;
					} ?>>
						<span class="checked"></span>
					</label>
					</div>
				</div><!-- col-sm-12 -->

			</div><!-- col-sm-6 -->

			<div class="col-sm-6 profile-details-inner">
				<label>Display Catalogues Overseas</label>
				<div class="switch-onoff">
				<label class="checkbox">
				<?php
					$displayCatlog = (isset($customer_details->display_catalog_overseas) && $customer_details->display_catalog_overseas == 1) ? 'checked' : '';
?>
				<input type="checkbox" name="catalogChk" id="catalogChk"  autocomplete="off"  <?php if ($customer_details->display_catalog_overseas) {
					echo "checked" ;
				} ?>>
					<span class="checked"></span>
				</label>
				</div>
			</div><!-- col-sm-6 -->

			<div class="col-sm-6 profile-details-inner toggle-b2b-details" >
				<label>Payment Term</label>
				<div class="switch-onoff">
				<label class="checkbox">
				<?php
					$payment_term = (isset($customer_details->enable_payment_term) && $customer_details->enable_payment_term == 1) ? 'checked' : '';
?>
				<input type="checkbox" name="payment_term" id="payment_term" autocomplete="off" <?php echo $payment_term; ?> >
					<span class="checked"></span>
				</label>
				</div>
			</div><!-- col-sm-6 -->

		</div>
		<div class="row margin-bot-10 b2webshop-import">
			<div class="col-sm-12"><h2 class="bank-head exc-term">B2Webshop Import </h2></div>
				<div class="col-sm-6 profile-details-inner toggle-b2b-details" >
					<label>Tax Exampted</label>
					<div class="switch-onoff">
					<label class="checkbox">
					<?php
		$tax_exampted = (isset($b2bCustomerInfo->tax_exampted) && $b2bCustomerInfo->tax_exampted == 1) ? 'checked' : '';
?>
					<input type="checkbox" name="tax_exampted" id="tax_exampted" autocomplete="off" <?php echo $tax_exampted; ?> >
						<span class="checked"></span>
					</label>
					</div>
				</div><!-- col-sm-6 -->
				<div class="col-sm-12 profile-details-inner toggle-b2b-details" >
					<label>Can Import Through A Quick Page Without Any Approval</label>
					<div class="switch-onoff">
						<label class="checkbox">
						<?php
		$import_through_quickpage = (isset($b2bCustomerInfo->import_through_quickpage) && $b2bCustomerInfo->import_through_quickpage == 1) ? 'checked' : '';
?>
						<input type="checkbox" name="import_through_quickpage" id="import_through_quickpage" autocomplete="off" <?php echo $import_through_quickpage; ?> >
							<span class="checked"></span>
						</label>
					</div>
				</div><!-- col-sm-6 -->

		</div>
	<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/customers/write', $this->session->userdata('userPermission'))) { ?>
		<div class="col-sm-12 save-discard-btn">
			<input type="submit" id="save_exclusive_terms" value="Save" class="purple-btn">
		</div>
	<?php } ?>
		</form>

	<div class="row sub-tab-style">
   <ul class="nav nav-pills">
	<li class="active orderClass"><a data-toggle="pill" class="order" id="order" href="#b2b-order-and-details">Orders </a></li>
	<?php if (isset($this->CommonModel->page_access()->acc_inv_flag) && $this->CommonModel->page_access()->acc_inv_flag==1) { ?>
		<li class="invoiceClass"><a  class="invoice" id="invoice" href="javascript:void(0);">Invoices</a></li>
	<?php } ?>
	<li class=""><a  class="" id="" href=<?php echo base_url().'B2BController/b2b_special_pricing/'.$shop_id; ?>>Special Pricing</a></li>
  </ul>

  <div class="tab-content sub-tab-contant-style">

	    <div id="b2b-order-and-details" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">

				<div class="table-responsive text-center">
	                <table class="table table-bordered table-style">
	                  <thead>
	                    <tr>
	                      <th>Order Number </th>
	                      <th>Purchased On</th>
						  <th>Status </th>
						  <th>Shipment </th>
						  <th>Details </th>
	                    </tr>
	                  </thead>
	                  <tbody>
					  <?php if (isset($OrderList) && count($OrderList)>0) {
					  	foreach ($OrderList as $order) {
					  		$order_status=$this->CommonModel->getOrderStatusLabel($order->status);
					  		$shipment_type_label=$this->CommonModel->getOrderShipmentLabel($order->shipment_type);
					  		if (($order->parent_id==0  && $order->main_parent_id==0) && !in_array($order->status, array('4','5','6'))) {
					  			$order_url=base_url().'b2b/order/detail/'.$order->order_id;
					  		} elseif (($order->parent_id==0  && $order->main_parent_id==0) && in_array($order->status, array('4','5','6'))) {
					  			$order_url=base_url().'b2b/shipped-order/detail/'.$order->order_id;
					  		} elseif ($order->parent_id>0) {
					  			$order_url=base_url().'b2b/split-order/detail/'.$order->order_id;
					  		} else {
					  			$order_url=base_url().'b2b/customers';
					  		}
					  		?>
	                    <tr>
	                      <td><?php echo $order->increment_id; ?></td>
	                      <td><?php echo date(SIS_DATE_FM, $order->created_at); ?></td>
						  <td><?php echo $order_status; ?></td>
						  <td><a class="link-purple"><?php echo $shipment_type_label; ?></a></td>
	                      <td><a class="link-purple" href="<?php echo $order_url; ?>" target="_blank">View</a></td>
	                    </tr>
					  <?php }
					  	} ?>
	                  </tbody>
	                </table>
	              </div>
			 <div class="save-discard-btn">
				<input type="button" value="Back" class="purple-btn" onclick="gotoLocation('<?php echo base_url(); ?>b2b/customers/');" >
			 </div>
		</div>
		<?php
			if (isset($this->CommonModel->page_access()->acc_inv_flag) && $this->CommonModel->page_access()->acc_inv_flag==1) {
				if (isset($InvoiceList)) {
					$invoice_type=$InvoiceList->invoice_type;
					$inv_daily_max_inv_amt=$InvoiceList->inv_daily_max_inv_amt;
					$inv_weekly_max_inv_amt=$InvoiceList->inv_weekly_max_inv_amt;
					$inv_monthly_max_inv_amt=$InvoiceList->inv_monthly_max_inv_amt;
					$payment_term=$InvoiceList->payment_term;
				} else {
					$invoice_type='';
					$inv_daily_max_inv_amt='';
					$inv_weekly_max_inv_amt='';
					$inv_monthly_max_inv_amt='';
					$payment_term=0;
				}
				?>
		<!-- invoices data -->
		<div id="b2b-order-and-invoices" class="tab-pane fade common-tab-section hideDiv" style="opacity:1; display:block;">
			<form id="B2BCustomerInvoiceForm" method="POST" action="<?php echo base_url('B2BController/postB2BCustomerInvoice') ?>">
					<div class="row b2b-invoicing-sec">
					  	<div class="order-listing-head"><h2 class="bank-head no-underline">B2B  Invoicing Options</h2></div>

							<div class="col-sm-6">
								<div class="radio">
								  <label><input type="radio" name="invoice" value="1" <?php echo ($invoice_type=='1') ? 'checked="checked"' : '';?>>Invoice per Order <span class="checkmark" ></span></label>
								</div>
							</div>

							<div class="col-sm-6 b2b-invoicing-sec-sub">
								<div class="radio">
								  <label><input type="radio" name="invoice" value="2" <?php echo ($invoice_type=='2') ? 'checked="checked"' : '';?>>Invoice Daily <span class="checkmark" ></span></label>
								</div>
								<div class="profile-inside-box">
										<label>Maximum Invoicing Amount :</label>
										<input class="form-control" type="text" onkeypress="return isNumberKey(event);" step="any" name="invDailyAmt" value="<?php echo ($inv_daily_max_inv_amt >'0') ? $inv_daily_max_inv_amt : '';?>" placeholder="">
								</div>
							</div>

							<div class="col-sm-6 b2b-invoicing-sec-sub">
								<div class="radio">
								  <label><input type="radio" name="invoice" value="3" <?php echo ($invoice_type=='3') ? 'checked="checked"' : '';?>>Invoice Weekly <span class="checkmark" ></span></label>
								</div>
								<div class="profile-inside-box">
										<label>Maximum Invoicing Amount :</label>
										<input class="form-control" type="text" onkeypress="return isNumberKey(event);" name="invWeeklyAmt" value="<?php echo ($inv_weekly_max_inv_amt >'0') ? $inv_weekly_max_inv_amt : '';?>" placeholder="">
								</div>
							</div>

							<div class="col-sm-6 b2b-invoicing-sec-sub">
								<div class="radio">
								  <label><input type="radio" name="invoice" value="4" <?php echo ($invoice_type=='4') ? 'checked="checked"' : '';?>>Invoice Monthly <span class="checkmark" ></span></label>
								</div>
								<div class="profile-inside-box">
										<label>Maximum Invoicing Amount :</label>
										<input class="form-control" type="text" onkeypress="return isNumberKey(event);" name="invMonthlyAmt" value="<?php echo ($inv_monthly_max_inv_amt >'0') ? $inv_monthly_max_inv_amt : '';?>" placeholder="">
								</div>
							</div>
							<div class="col-sm-12">
	                            <div class="form-group row">
	                              <label for="PaymentTerm" class="col-sm-2 col-form-label">Payment Term :</label>
	                              <div class="col-sm-1">
	                                <input class="form-control" type="text" name="payment_term" onkeypress="return isNumberKey(event);" value="<?php echo ($payment_term >'0') ? $payment_term : $payment_term;?>" placeholder="">
	                              </div> Days
	                            </div>

	                        </div>
							<div class="col-sm-12 save-discard-btn">
								<input type="submit" value="Save" class="purple-btn">
							</div>
					</div>
					<input type="hidden" name="customerId" value="<?=$customerId?>">
					<input type="hidden" name="shopId" value="<?=$shop_id?>">
				</form>
				<div class="order-listing-head"><h2 class="bank-head no-underline">Invoice Listing</h2></div>
				<div class="table-responsive text-center">
	                <?php include('b2b_invoice_list.php');?>
	            </div>
		</div>
		<?php } ?>
	</div>
	</div>
	</div><!-- row -->
	</div><!-- profile-details-block -->
    </main>
<script src="<?php echo SKIN_JS; ?>b2b_order_details_invoice.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
