<?php $this->load->view('common/header'); ?>
    <div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="<?php echo base_url(); ?>"><?=lang('home')?></a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active"><?=lang('my_profile')?></li>
				</ul>
			</div>
        </div>
      </div><!-- breadcrum section -->


     <div class="my-profile-page-full">
      <div class="container">
          <div class="row">
				<?php $this->load->view('common/profile_sidebar'); ?>

				<div class="col-md-9 col-lg-9 ">
					<div class="wishlist-listing my-orders">
						<form name="return-form" id="return-form" method="POST" action="<?php echo base_url(); ?>MyOrdersController/confirmReturn">
						<input type="hidden" name="return_order_increment_id" id="return_order_increment_id" value="<?php echo $OrderData->return_order_increment_id; ?>">
						<input type="hidden" name="return_order_id" id="return_order_id" value="<?php echo $OrderData->return_order_id; ?>">
						<input type="hidden" name="order_id" id="order_id" value="<?php echo $OrderData->order_id; ?>">
						<input type="hidden" name="country_code" id="country_code" value="<?php echo $country_code; ?>">
						<div class="order-info">
							<div class="order-info-inner">
								<span class="order-id"><?=lang('order_id')?>  :  <strong><?php echo $OrderData->return_order_increment_id; ?></strong></span>
								<span class="order-total"><?=lang('return_order_total')?> :    <strong> <?php echo CURRENCY_TYPE; ?> <?php  echo $OrderData->order_grandtotal; ?></strong></span>
								<span class="return-required"><?php
                                if ($OrderData->refund_status==0) {
                                    echo lang('return_requested');
                                } elseif ($OrderData->refund_status==1) {
                                    echo lang('refund_approved');
                                } elseif ($OrderData->refund_status==2) {
                                    echo lang('refund_rejected');
                                }
                                ?></span>
							</div>
							<h6 class="refund-msg"><?=lang('are_you_sure_request_for_refund');?></h6>
							<div class="refund-textarea-section">
								<label class="refund-head"><?=lang('reason_for_the_return');?></label>
								<textarea class="refund-textarea"  id="reason_for_return" name="reason_for_return" placeholder="<?=lang('message');?>"   <?php echo ($OrderData->status==0 || $OrderData->status==1)?'':'readonly'; ?>><?php echo (isset($OrderData->reason_for_return)  && $OrderData->reason_for_return!='')?$OrderData->reason_for_return:''; ?></textarea>
							</div>
							<!-- refund-textarea -->
							<?php if($OrderData->payment_method_data->payment_method=='stripe_payment' && $online_stripe_payment_refund == 'yes'){ ?>
								<input type="hidden" name="refund_payment_mode" value="3" >
							<?php } else if($OrderData->payment_method_data->payment_method=='paypal_express' && $online_stripe_payment_refund == 'yes'){ ?>
								<input type="hidden" name="refund_payment_mode" value="4" >	
							<?php } else{ ?>
							<div class="refund-payment-mode">
								<p class="refund-head"><?=lang('refund_payment_mode');?></p>
								<div class="refund-payment-mode-checklist">
									<p> <label class="radio-label"><input type="radio"  name="refund_payment_mode" value="1"  <?php echo (isset($OrderData->refund_payment_mode) && $OrderData->refund_payment_mode==1)?'checked':''; ?>  <?php echo ($OrderData->status==0 || $OrderData->status==1)?'':'readonly'; ?>>   <span class="refund-span"><?=lang('store_credit');?></span> </label>
									</p>
									<?php
                                        if ($shop_flag) {
                                            $shop_flag=$shop_flag;
                                        }
                                        
										$payment_method_order= (($OrderData->payment_method_data) ? $OrderData->payment_method_data->payment_method : '');	

                                        if ($shop_flag==2 && $payment_method_order=='cod') {
                                        } else {
                                            ?>
									<p> <label class="radio-label"><input type="radio" name="refund_payment_mode" value="2"  <?php echo (isset($OrderData->refund_payment_mode) && $OrderData->refund_payment_mode==2)?'checked':''; ?>  <?php echo ($OrderData->status==0 || $OrderData->status==1)?'':'readonly'; ?>  ><span class="refund-span"><?=lang('bank_transfer');?></span></label>
									</p>
									<?php
                                        } ?>
									
								</div>

								<div class="bank-details-div" id="bank-details-div" style="<?php echo (isset($OrderData->refund_payment_mode)  && $OrderData->refund_payment_mode==2)?'':'display:none'; ?>">
									  <h4 class="heading-small"><?=lang('bank_details');?></h4>

									   <div class="form-row">
										<div class="col-md-6 pr-5">
											 <div class="form-group row">
												<label for="" class="col-sm-4 col-form-label"><?=lang('bank_name');?></label>
												<div class="col-sm-8">
												  <input type="text" class="form-control validate-char" id="bank_name" name="bank_name"  <?php echo ($OrderData->status==0 || $OrderData->status==1)?'':'readonly'; ?> value="<?php echo (isset($OrderData->bank_name)  && $OrderData->bank_name!='')?$OrderData->bank_name:''; ?>" placeholder="">
												</div>
											  </div>
										</div>

										<div class="col-md-6 pr-5">
											 <div class="form-group row">
												<label for="" class="col-sm-4 col-form-label"><?=lang('branch');?></label>
												<div class="col-sm-8">
												  <input type="text" class="form-control validate-char" id="bank_branch" name="bank_branch" <?php echo ($OrderData->status==0 || $OrderData->status==1)?'':'readonly'; ?> value="<?php echo (isset($OrderData->bank_branch)  && $OrderData->bank_branch!='')?$OrderData->bank_branch:''; ?>" placeholder="">
												</div>
											  </div>
										</div>
									  </div><!-- form-row-->




									  <div class="form-row">
										<div class="col-md-6 pr-5">
											 <div class="form-group row">
												<label for="" class="col-sm-4 col-form-label"><?=lang('ifsc_iban');?></label>
												<div class="col-sm-8">
												  <input type="text" class="form-control" id="ifsc_iban" name="ifsc_iban" <?php echo ($OrderData->status==0 || $OrderData->status==1)?'':'readonly'; ?> value="<?php echo (isset($OrderData->ifsc_iban)  && $OrderData->ifsc_iban!='')?$OrderData->ifsc_iban:''; ?>" placeholder="">
												</div>
											  </div>
										</div>

										 <div class="col-md-6 pr-5">
											 <div class="form-group row">
													<label for="" class="col-sm-4 col-form-label"><?=lang('bic_swift');?></label>
													<div class="col-sm-8">
													  <input type="text" class="form-control" id="bic_swift" name="bic_swift" <?php echo ($OrderData->status==0 || $OrderData->status==1)?'':'readonly'; ?> value="<?php echo (isset($OrderData->bic_swift)  && $OrderData->bic_swift!='')?$OrderData->bic_swift:''; ?>" placeholder="">
													</div>
												  </div>
											</div>
									  </div><!-- form-row-->

										<div class="form-row">


										 <div class="col-md-6 pr-5">

											 <div class="form-group row">
												<label for="" class="col-sm-4 col-form-label"><?=lang('bank_account_number');?></label>
												<div class="col-sm-8">
												  <input type="number" class="form-control" id="bank_acc_no" name="bank_acc_no" <?php echo ($OrderData->status==0 || $OrderData->status==1)?'':'readonly'; ?> value="<?php echo (isset($OrderData->bank_acc_no)  && $OrderData->bank_acc_no!='')?$OrderData->bank_acc_no:''; ?>" placeholder="">
												</div>
											  </div>
										</div>
										<div class="col-md-6 pr-5">
										 	<div class="form-group row">
												<label for="" class="col-sm-4 col-form-label"><?=lang('account_holder_name');?></label>
												<div class="col-sm-8">
											  		<input type="text" class="form-control validate-char" id="acc_holder_name" name="acc_holder_name"  <?php echo ($OrderData->status==0 || $OrderData->status==1)?'':'readonly'; ?> value="<?php echo (isset($OrderData->acc_holder_name)  && $OrderData->acc_holder_name!='')?$OrderData->acc_holder_name:''; ?>" placeholder="">
												</div>
										  	</div>
										</div>
									  </div><!-- form-row-->
									   <div class="form-row">

									  </div><!-- form-row-->

									  </div>
								<!--  -->
							</div>
							<!-- refund-payment-mode -->
							<?php } ?>
						<?php
                        $subtotal=0;
                        $discount=0;
                        $returnable_amount=0;
                        if (isset($OrderData->order_items)  && count($OrderData->order_items)>0) { ?>
						<ul class="cart-left-box-block order-return-listing">
							<?php foreach ($OrderData->order_items as $oitem) {

							$base_image= ((isset($oitem->base_image) && $oitem->base_image!='') ? PRODUCT_THUMB_IMG.$oitem->base_image : PRODUCT_DEFAULT_IMG);	
							$product_variants= (($oitem->product_variants != '') ? json_decode($oitem->product_variants) : '');	

                            $variants =array();
                            if (isset($product_variants) && $product_variants != '') {
                                foreach ($product_variants as $pk=>$single_variant) {
                                    foreach ($single_variant as $key=>$val) {
                                        $variants[]=$key.' : '.$val.' ';
                                    }
                                }
                            } else {
                                $variants[]='-';
                            }



                            $per_item_total=$oitem->price*$oitem->qty_return;
                            $subtotal=$subtotal+$per_item_total;

                            if ($oitem->discount_amount>0) {
                                $per_item_discount=$oitem->discount_amount*$oitem->qty_return;
                                $discount += $per_item_discount;
                                $per_item_return_total=$per_item_total-$per_item_discount;

                            } else {
                                $per_item_return_total=$per_item_total;
                            }



                            $returnable_amount=$returnable_amount+$per_item_return_total; ?>
							<li>

								<div class="cart-images"><img src="<?php echo $base_image; ?>"></div>
								<div class="cart-table-right">
									<h2 class="head-cart"><a href="<?php echo base_url(); ?>product-detail/<?php echo $oitem->url_key; ?>"><?php echo $oitem->product_name; ?></a></h2>
									<?php if ($oitem->product_type=='conf-simple') { ?>
									<p class="grey-light-text"><?php echo implode(', ', $variants); ?></p>
									<?php } ?>
									<p class="grey-light-text"><?=lang('qty');?>: <?php echo $oitem->qty_order; ?></p>

									<div class="price"> <div class="price-cart-table"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($oitem->price, 2); ?></div>

										<div class="qty-review">
											<p class="grey-light-text"><?=lang('return_qty');?></p>
											<select name="item_qty[<?php echo $oitem->order_item_id; ?>]"  id=""  <?php echo ($OrderData->status==0 || $OrderData->status==1)?'':'disabled'; ?>>
												<?php for ($i=1;$i<=$oitem->qty_order;$i++) {?>
													<option value="<?php echo $i; ?>"   <?php echo ($i==$oitem->qty_return)?'selected':''; ?>><?php echo $i; ?></option>
												<?php } ?>

											</select>
											<a href="<?php echo base_url(); ?>product-detail/<?php echo $oitem->url_key; ?>#ratings-review"><button class="review" type="button"><?=lang('review');?></button></a>
										</div>

									</div>

								</div><!-- cart-table-right -->
							</li>
							<?php
                        } ?>

							<div class="cart-table-amount-total">

									<p class="grey-light-text "><?=lang('price')?> (<?php echo count($OrderData->order_items);?> <?=lang('items')?>) <br> <?=lang('inclusive_of_taxes')?> <span class="amount-doller"><?php echo CURRENCY_TYPE; ?> <?php echo number_format($subtotal, 2); ?></span> </p>
									<?php if ($discount>0) {?>
									<p class="grey-light-text"><?=lang('discount_amount');?> <span class="amount-doller">- <?php echo CURRENCY_TYPE; ?> <?php echo number_format($discount, 2); ?></span> </p>
									<?php } ?>
									<p class="grey-light-text final-order-amount"><?=lang('return_order_total')?> <span class="amount-doller"><?php echo CURRENCY_TYPE; ?> <?php echo number_format($OrderData->order_grandtotal, 2); ?></span></p>
									<?php if($OrderData->refund_status==1){ ?>
						                    <p class="grey-light-text final-order-amount"><?=lang('refund_approved')?><span class="amount-doller"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($OrderData->order_grandtotal_approved, 2); ?></span></p>
						                    <p class="grey-light-text"> <?php if($OrderData->shipping_charge_flag==1){ echo '('.lang('shipping_charges').' :'.CURRENCY_TYPE .number_format($OrderData->shipping_amount, 2).')'; } ?> 
						                   	</p>
						        	<?php } ?>

							</div>


						</ul>
						<?php } ?>
						</div>
						<!-- order-info -->
						<div class="print-option-bottom">
							<span class="print-msg"><?=lang('returnable_footer_msg')?> </span>

							<?php if ($OrderData->status != 0 && $OrderData->status != 1) {?>
							<a class="blue-btn-order"  href="<?php echo base_url() ?>return-order/print/<?php echo $OrderData->return_order_id; ?>" target="_blank"><?=lang('print')?> </a>
							<?php } ?>
							
							<?php if ($OrderData->status==0 || $OrderData->status==1) {?>
							<button class="blue-btn-order" type="button" id="confirm-return"><?=lang('confirm_return')?>  </button>
							<?php }  ?>
						</div>
						</form>
						<!-- print-option-bottom -->
					</div>
					<!-- wishlist-listing -->
				</div>

          </div><!-- row -->
      </div><!-- container -->
    </div><!-- my-profile-page-full -->
	<?php $this->load->view('common/footer'); ?>
	<script src="<?php echo SKIN_JS ?>myprofile.js?v=<?php echo CSSJS_VERSION; ?>"></script>
	<script type="text/javascript" src="<?php echo SKIN_JS; ?>myprofile_return_order_detail.js?v=<?php echo CSSJS_VERSION; ?>"></script>
  </body>
</html>