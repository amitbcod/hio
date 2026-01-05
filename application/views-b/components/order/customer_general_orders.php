<div class="col-md-9 col-lg-9 ">
    <div class="wishlist-listing my-orders">
        <?php 
            if (isset($OrderList) &&  count($OrderList)>0) {
                $order_total_shipping = 0;
                $order_total_voucher_amount = 0;

                foreach ($OrderList as $order) {
                    $order_items = count($order->order_items);
                    $order_total_shipping = $order->shipping_amount;
                    $order_total_voucher_amount = $order->voucher_amount; ?>

                <div class="order-info">
                    <div class="order-info-inner">
                        <span class="order-id">Order ID  :  <strong><?php echo $order->increment_id; ?></strong></span>
                        <span class="order-date">Ordered On :   <strong> <?php echo date('d F Y', $order->created_at); ?></strong></span>
                        <span class="order-total">Order Total :    <strong> <?php echo CURRENCY_TYPE; ?> <?php echo number_format($order->grand_total, 2); ?></strong></span>

                        <?php $able_to_return_flag = 0;?>
                        <?php if (isset($order->status) && $order->status==3) {?>
                            <span class="return-required red-text">Cancelled</span>
                        <?php } elseif (isset($order->flag) && $order->flag=='able_to_cancel') {?>
                        <button  name="cancel_order_btn" class="blue-btn-order modalLink" data-toggle="modal" id="cancel_order_btn" data-id="<?php echo $order->order_id; ?>" value="<?php echo $order->order_id; ?>" data-target="#cancel-order-modal">Cancel Order</button>
                        <?php  } elseif (isset($order->flag) && $order->flag=='able_to_return') {
                            if(SHOP_ID == 3 && $order->order_id >= 514 && $order->order_id <= 609)	{
                            }else{
                            $able_to_return_flag = 1;?>

                            <button type="button" class="blue-btn-order" id="ret-btn-<?php echo $order->order_id; ?>" onclick="ReturnRequest('<?php echo $order->order_id; ?>','<?php echo $order->increment_id; ?>')" >Return Order</button>
                        <?php } } elseif (isset($order->status) && $order->status==6 && $able_to_return_flag==0 ) {?>
                            <span class="return-required green-text">Completed</span>
                        <?php }	?>
                    </div>

                    <?php if (isset($order->order_items)  && count($order->order_items)>0) { ?>

                    <ul class="cart-left-box-block order-return-listing">
                    <?php
                        $total_active_item=0;
                        $order_total_price = 0;
                        $order_total_tax = 0;
                        $order_total_discount = 0;

                    foreach ($order->order_items as $oitem) {
                        $order_total_price += $oitem->price *  $oitem->qty_ordered;
                        $order_total_tax += $oitem->tax_amount;
                        $order_total_discount += $oitem->total_discount_amount;

                        if ($oitem->item_display_status==2) {
                            continue;
                        }

                        $base_image = ((isset($oitem->base_image) && $oitem->base_image!='') ? PRODUCT_THUMB_IMG.$oitem->base_image : PRODUCT_DEFAULT_IMG);
                        $product_variants= ((isset($oitem->product_variants) && $oitem->product_variants != '') ? json_decode($oitem->product_variants) : '');

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
                        <?php if (isset($order->flag) && $order->flag=='able_to_return') { ?>
                            <div class="order-checkbox">
                                <label class="checkbox-label">
                                    <?php if($oitem->can_be_returned==0){ ?>
                                        <input type="checkbox" disabled><span class="checked"></span>
                                    <?php }else{ ?>
                                        <input type="checkbox" class="ro-check-item-<?php echo $order->order_id; ?>"  name="return_order_items[]" value="<?php echo $oitem->item_id; ?>" data-qty_ordered="<?php echo $oitem->qty_ordered; ?>"><span class="checked"></span>
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

                            <p class="grey-light-text">Qty: <?php echo $oitem->qty_ordered; ?></p>
                            <div class="price"> <div class="price-cart-table"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($oitem->price, 2); ?><?php if (isset($order->flag) && $order->flag=='able_to_return' && $oitem->can_be_returned==0) { ?><span class="return-required red-text prd-cant">Item is not returnable</span><?php } ?></div>
                            <?php if (isset($order->flag) && $order->flag=='able_to_return') { ?>
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
                        <li><p class="mb-5 pb-5 item-re-text">Items are returned</p></li>
                <?php } ?>

                        <!-- cart-table-amount-total start -->
                        <?php
                            $order_sub_total = $order_total_price  - $order_total_discount + $order_total_shipping;
                            $order_total_amount = $order_sub_total -  $order_total_voucher_amount;
                        ?>

                    <div class="cart-table-amount-total">
                        <p class="grey-light-text">Price (<?php echo $order_items?> Items) <br> (Inclusive of taxes) <span class="amount-doller"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order->base_subtotal, 2); ?></span> </p>
                        <p class="grey-light-text">Taxes <span class="amount-doller"> <?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order->tax_amount, 2); ?></span> </p>
                        <?php
                        if ($order_total_discount > 0) { ?>
                            <p class="grey-light-text">Discount Amount<span class="amount-doller">- <?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order->discount_amount, 2); ?></span> </p>
                        <?php } ?>
                        <p class="grey-light-text">Shipping Charges <span class="amount-doller">+ <?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order->shipping_amount, 2); ?></span> </p>
                        <p class="grey-light-text sub-total-amount">Sub Total <span class="amount-doller"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order->subtotal, 2); ?></span> </p>
                        <?php if ($order_total_voucher_amount > 0) { ?>
                        <p class="grey-light-text">Voucher Amount <span class="amount-doller">- <?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order_total_voucher_amount, 2); ?></span> </p>
                        <?php } ?>
                        <p class="grey-light-text final-order-amount">Order Total <span class="amount-doller"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order->grand_total, 2); ?></span></p>
                    </div><!-- cart-table-amount-total end -->
                    <?php
                         if ($order->status == 5 || $order->status == 6) {
                            ?>
                            <button type="button" class="blue-btn-order tracking_details_btn" orderid='<?php echo $order->order_id; ?>'>Tracking Details</button>
							<?php if(THEMENAME != 'theme_zumbawear') { ?>
                            <div class="d-none" id="tracking_details_div_<?php echo $order->order_id; ?>"></div>
							<?php }
                        }
						if (isset($order->invoice_file) && $order->invoice_file != '' && $order->invoice_self == 1) {
                            ?>
                            <a class="blue-btn-order download-invoice" href="<?php echo INVOICE_FILE.$order->invoice_file; ?>" target="_blank" >Download Invoice</a>
                            <?php
                        }
                        if ($order->status!=3) {
                             $encoded_id = base64_encode($order->order_id);
                             $encoded_id = urlencode($encoded_id);
                            ?>
                           <a class="blue-btn-order download-invoice print-receipt-btn" href="<?php echo BASE_URL('receipt-order/print'.'/'.$encoded_id) ?>" target="_blank">Download Receipt</a>
                            <?php
                        }

						if ($order->status == 5 || $order->status == 6) {
                           if(THEMENAME == 'theme_zumbawear') { ?>
                            <div class="d-none" id="tracking_details_div_<?php echo $order->order_id; ?>"></div>
							<?php }
                        }
                    ?>

                </ul>

            <?php } ?>
            <?php include('ReturnOrders.php') ?>
            </div><!-- order-info -->
        <?php }
            }else{
                echo "<div class='empty-record'>"."No orders found"."</div>";
            } ?>

    </div><!-- order-listing -->
</div>

<div class="col-md-12 col-lg-12">
    <div class="paging-main">
        <ul class="pagination myorder-pagination">
            <?php
             if (isset($links)) {
                echo $links;
            } ?></ul>
    </div>
</div>
