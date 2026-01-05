<div class="col-md-12 col-lg-12 ">
	<div class="wishlist-listing my-orders">
	<?php  if ($OrderData) {?>

        <div class="order-info">
            <div class="order-info-inner">
                <span class="order-id"><?=lang('order_id')?>  :  <strong><?php echo $OrderData->increment_id; ?></strong></span>
                <span class="order-date"><?=lang('ordered_on')?> :   <strong> <?php echo date('d F Y', $OrderData->created_at); ?></strong></span>
                <span class="order-total"><?=lang('order_total')?> :    <strong> <?php echo CURRENCY_TYPE; ?> <?php echo number_format($OrderData->grand_total, 2); ?></strong></span>
                <?php $able_to_return_flag = 0; ?>
                <?php if (isset($OrderData->status) && $OrderData->status==3) {?>
                    <span class="return-required red-text"><?=lang('order_status_cancelled')?></span>
                <?php } elseif (isset($OrderData->flag) && $OrderData->flag=='able_to_cancel') {?>
                    <button  name="cancel_order_btn" class="blue-btn-order modalLink" data-toggle="modal" id="cancel_order_btn" data-id="<?php echo $OrderData->order_id; ?>" value="<?php echo $OrderData->order_id; ?>" data-target="#cancel-order-modal"><?=lang('cancel_order')?></button>
                <?php } elseif (isset($OrderData->flag) && $OrderData->flag=='able_to_return') {
                if(SHOP_ID == 3 && $OrderData->order_id >= 514 && $order->order_id <= 609)	{
                }else{
                $able_to_return_flag = 1;	?>
                    <button type="button" class="blue-btn-order" id="ret-btn-<?php echo $OrderData->order_id; ?>" onclick="ReturnRequest('<?php echo $OrderData->order_id; ?>','<?php echo $OrderData->increment_id; ?>')" ><?=lang('return_order')?></button>
                <?php } } elseif (isset($OrderData->status) && $OrderData->status==6 && $able_to_return_flag==0 ) {?>
                    <span class="return-required green-text"><?=lang('order_status_completed')?></span>
                <?php } ?>
            </div>

        <?php if (isset($OrderData->order_items)  && count($OrderData->order_items)>0) {
            $total_active_item = 0;
            $order_items = count($OrderData->order_items);
            $order_total_shipping = $OrderData->shipping_amount;
            $order_total_voucher_amount =$OrderData->voucher_amount; ?>

            <ul class="cart-left-box-block">
            <?php
                $order_total_price = 0;
                $order_total_tax = 0;
                $order_total_discount = 0;

                foreach ($OrderData->order_items as $oitem) {
                    $order_total_price += $oitem->price *  $oitem->qty_ordered;
                    $order_total_tax += $oitem->tax_amount;
                    $order_total_discount += $oitem->total_discount_amount;

                    if ($oitem->item_display_status==2) {
                        continue;
                    }


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
                    $variants[]=' ';
                } ?>

                <li>
                    <?php if (isset($OrderData->flag) && $OrderData->flag=='able_to_return') { ?>
                    <div class="order-checkbox">
                        <label class="checkbox-label">
                            <?php if($oitem->can_be_returned==0){ ?>
                                <input type="checkbox" disabled><span class="checked"></span>
                            <?php }else{ ?>
                                <input type="checkbox" class="ro-check-item-<?php echo $OrderData->order_id; ?>"  name="return_order_items[]" value="<?php echo $oitem->item_id; ?>" data-qty_ordered="<?php echo $oitem->qty_ordered; ?>"><span class="checked"></span>
                            <?php } ?>
                        </label>
                    </div>
                    <?php } ?>
                    <div class="cart-images"><img src="<?php echo $base_image; ?>"></div>
                    <div class="cart-table-right">
                        <h2 class="head-cart"><a href="<?php echo base_url(); ?>product-detail/<?php echo $oitem->url_key; ?>"><?php echo $oitem->product_name; ?></a></h2>
                        <?php if ($oitem->product_type=='conf-simple') { ?>
                            <p class="grey-light-text"><?php echo implode(', ', $variants); ?></p>
                        <?php } ?>
                        <p class="grey-light-text"><?=lang('qty')?>: <?php echo $oitem->qty_ordered; ?></p>
                        <div class="price">
                            <div class="price-cart-table"><?php echo CURRENCY_TYPE; ?>
                                <?php echo number_format($oitem->price, 2); ?><?php if (isset($OrderData->flag) && $OrderData->flag=='able_to_return' && $oitem->can_be_returned==0) { ?><span class="return-required red-text prd-cant"><?=lang('my_order_page_cant_return')?></span><?php } ?>
                            </div>
                        <?php if (isset($OrderData->flag) && $OrderData->flag=='able_to_return') { ?>
                            <div class="qty-review">
                                <select name="item_qty_<?php echo $oitem->item_id; ?>"  id="item_qty_<?php echo $oitem->item_id; ?>">
                                    <?php for ($i=1;$i<=$oitem->qty_ordered;$i++) {?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>
                        </div>
                    </div><!-- cart-table-right -->
                </li>
            <?php
            $total_active_item++;
            } ?>
            <?php if ($total_active_item<=0) { ?>
                <input type="hidden" name="active_items" id="active_items" value="<?php echo  $total_active_item; ?>">
                <li><p class="mb-5 pb-5 item-re-text"><?=lang('items_are_returned')?></p></li>
            <?php } ?>

            <!-- cart-table-amount-total start -->
            <?php
                $order_sub_total = $order_total_price  - $order_total_discount + $order_total_shipping;
                $order_total_amount = $order_sub_total -  $order_total_voucher_amount; ?>

                <div class="cart-table-amount-total">
                    <p class="grey-light-text"><?=lang('price')?> (<?php echo $order_items?> <?=lang('items')?>) <br> <?=lang('inclusive_of_taxes')?> <span class="amount-doller"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order_total_price, 2); ?></span> </p>
                    <p class="grey-light-text"><?=lang('taxes')?> <span class="amount-doller">+ <?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order_total_tax, 2); ?></span> </p>
                <?php if ($order_total_discount > 0) { ?>
                        <p class="grey-light-text"><?=lang('discount_amount')?><span class="amount-doller">- <?php echo CURRENCY_TYPE; ?>  <?php echo number_format($OrderData->discount_amount, 2); ?></span> </p>
                <?php  } ?>
                    <p class="grey-light-text"><?=lang('shipping_charges')?> <span class="amount-doller">+ <?php echo CURRENCY_TYPE; ?>  <?php echo number_format($OrderData->shipping_amount, 2); ?></span> </p>
                    <p class="grey-light-text sub-total-amount"><?=lang('sub_total')?> <span class="amount-doller"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($OrderData->subtotal, 2); ?></span> </p>
                <?php if ($order_total_voucher_amount > 0) { ?>
                    <p class="grey-light-text"><?=lang('voucher_amount')?> <span class="amount-doller">- <?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order_total_voucher_amount, 2); ?></span> </p>
                <?php } ?>
                    <p class="grey-light-text final-order-amount"><?=lang('order_total')?> <span class="amount-doller"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($OrderData->grand_total, 2); ?></span></p>
                </div><!-- cart-table-amount-total end -->
                <?php

                    if ($OrderData->status == 5 || $OrderData->status == 6) {
                        ?>
                        <button type="button" class="blue-btn-order tracking_details_btn" orderid='<?php echo $OrderData->order_id; ?>'><?=lang('tracking_details') ?></button>
						<?php if(THEMENAME != 'theme_zumbawear') { ?>
                        <div class="d-none" id="tracking_details_div_<?php echo $OrderData->order_id; ?>"></div>
						<?php }
                    }

                    if (isset($OrderData->invoice_file) && $OrderData->invoice_file != '' && $OrderData->invoice_self == 1) {
                        ?>
                        <a class="blue-btn-order download-invoice" href="<?php echo INVOICE_FILE.$OrderData->invoice_file; ?>" target="_blank" ><?=lang('download_invoice')?></a>
                        <?php
                    }
                     if ($OrderData->status!=3) {
                         $encoded_id = base64_encode($OrderData->order_id);
                         $encoded_id = urlencode($encoded_id);
                        ?>
                        <a class="blue-btn-order download-invoice print-receipt-btn" href="<?php echo BASE_URL('receipt-order/print'.'/'.$encoded_id) ?>" target="_blank"><?=lang('download_receipt');?></a>
                        <?php
                    }

					if ($OrderData->status == 5 || $OrderData->status == 6) {
                        if(THEMENAME == 'theme_zumbawear') { ?>
                        <div class="d-none" id="tracking_details_div_<?php echo $OrderData->order_id; ?>"></div>
						<?php }
                    }

                ?>

            </ul>
        <?php } ?>
        <?php include('ReturnOrders.php') ?>
        </div><!-- order-info -->
    <?php } ?>

    </div><!-- wishlist-listing -->
</div><!-- col-md-9 -->
