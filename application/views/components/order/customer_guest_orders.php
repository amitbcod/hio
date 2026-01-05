<div class="col-md-9 col-sm-9 ">
    <div class="content-page">
        <div class="row">
            <div class="col-md-12">
                <h1>Your Order</h1>
            </div>
        </div>
        <div class="wishlist-listing my-orders ">
            <?php
            if ($OrderData) {
                $order_total_shipping = 0;
                $order_total_voucher_amount = 0;

                $order_items = count($OrderData->order_items);
                $order_total_shipping = $OrderData->shipping_amount;
                $order_total_voucher_amount = $OrderData->voucher_amount; ?>

                <div class="order-info panel panel-default">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-md-7">
                                <p class="stats"><b>Order ID: <?php echo $OrderData->increment_id; ?></b></p>
                                <p class="stats"><b>Order Date: <?php echo date('d F Y', $OrderData->created_at); ?></b></p>
                            </div>
                            <div class="col-md-5">
                                <!-- <p class="stats on-right"><b>Order Status:
                                        <?php $able_to_return_flag = 0; ?>
                                        <?php if (isset($OrderData->status) && $OrderData->status == 3) { ?>
                                            <?php } elseif (isset($OrderData->flag) && $OrderData->flag == 'able_to_return') {
                                            if ($OrderData->order_id >= 514 && $OrderData->order_id <= 609) {
                                            } else {
                                                $able_to_return_flag = 1; ?>

                                        <?php }
                                        } elseif (isset($OrderData->status) && $OrderData->status == 0) { ?>
                                            <span class="return-required green-text completed ">To Be Processed</span>
                                        <?php } elseif (isset($OrderData->status) && $OrderData->status == 1) { ?>
                                            <span class="return-required green-text completed ">Processing</span>
                                        <?php } elseif (isset($OrderData->status) && $OrderData->status == 7) { ?>
                                            <span class="return-required green-text completed ">Pending</span>
                                        <?php } elseif (isset($OrderData->status) && $OrderData->status == 6 && $able_to_return_flag == 0) { ?>
                                            <span class="return-required green-text completed ">Completed</span>
                                        <?php }    ?></b> </p> -->
                            </div>
                        </div>
                    </div>
                    <!------------------ old design -------------------------->
                    <!-- <div class="order-info-inner">
                                <span class="order-id">Order ID  :  <strong><?php echo $order->increment_id; ?></strong></span>
                                <span class="order-date">Ordered On :   <strong> <?php echo date('d F Y', $order->created_at); ?></strong></span>
                                <span class="order-total">Order Total :    <strong> <?php echo CURRENCY_TYPE; ?> <?php echo number_format($order->grand_total, 2); ?></strong></span>

                                <?php $able_to_return_flag = 0; ?>
                                <?php if (isset($order->status) && $order->status == 3) { ?>
                                    <span class="return-required red-text">Cancelled</span>
                                <?php } elseif (isset($order->flag) && $order->flag == 'able_to_cancel') { ?>
                                <button  name="cancel_order_btn" class="blue-btn-order modalLink" data-toggle="modal" id="cancel_order_btn" data-id="<?php echo $order->order_id; ?>" value="<?php echo $order->order_id; ?>" data-target="#cancel-order-modal">Cancel Order</button>
                                <?php  } elseif (isset($order->flag) && $order->flag == 'able_to_return') {
                                    if (SHOP_ID == 3 && $order->order_id >= 514 && $order->order_id <= 609) {
                                    } else {
                                        $able_to_return_flag = 1; ?>

                                    <button type="button" class="blue-btn-order" id="ret-btn-<?php echo $order->order_id; ?>" onclick="ReturnRequest('<?php echo $order->order_id; ?>','<?php echo $order->increment_id; ?>')" >Return Order</button>
                                <?php }
                                } elseif (isset($order->status) && $order->status == 6 && $able_to_return_flag == 0) { ?>
                                    <span class="return-required green-text">Completed</span>
                                <?php }    ?>
                            </div>-->

                    <!------------------    old design -------------------------->
                    <?php if (isset($OrderData->order_items)  && count($OrderData->order_items) > 0) { ?>


                        <?php
                        $total_active_item = 0;
                        $order_total_price = 0;
                        $order_total_tax = 0;
                        $order_total_discount = 0;
                        ?>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <?php
                                    foreach ($OrderData->order_items as $oitem) {
                                        $order_total_price += $oitem->price *  $oitem->qty_ordered;
                                        $order_total_tax += $oitem->tax_amount;
                                        $order_total_discount += $oitem->total_discount_amount;

                                        if ($oitem->item_display_status == 2) {
                                            continue;
                                        }

                                        $base_image = ((isset($oitem->base_image) && $oitem->base_image != '') ? PRODUCT_THUMB_IMG . $oitem->base_image : PRODUCT_DEFAULT_IMG);
                                        $product_variants = ((isset($oitem->product_variants) && $oitem->product_variants != '') ? json_decode($oitem->product_variants) : '');

                                        $variants = array();

                                        if (isset($product_variants) && $product_variants != '') {
                                            foreach ($product_variants as $pk => $single_variant) {
                                                foreach ($single_variant as $key => $val) {
                                                    $variants[] = $key . ' : ' . $val . ' ';
                                                }
                                            }
                                        } else {
                                            $variants[] = ' ';
                                        } ?>


                                        <div class="row margbot20">

                                            <?php if (isset($order->flag) && $order->flag == 'able_to_return') { ?>
                                                <div class="order-checkbox">
                                                    <label class="checkbox-label">
                                                        <?php if ($oitem->can_be_returned == 0) { ?>
                                                            <input type="checkbox" disabled><span class="checked"></span>
                                                        <?php } else { ?>
                                                            <input type="checkbox" class="ro-check-item-<?php echo $order->order_id; ?>" name="return_order_items[]" value="<?php echo $oitem->item_id; ?>" data-qty_ordered="<?php echo $oitem->qty_ordered; ?>"><span class="checked"></span>
                                                        <?php } ?>
                                                    </label>
                                                </div>
                                            <?php } ?>
                                            <div class="col-sm-4 col-md-4">
                                                <div class="shpcart-img-wrap vv">
                                                    <img src="<?php echo $base_image; ?>" alt="<?php echo $oitem->product_name; ?>" class="img-responsive">
                                                </div>
                                            </div>
                                            <div class="col-sm-8 col-md-8">
                                                <div class="shpcart">
                                                    <h3>
                                                        <a href="<?php echo base_url(); ?>product-detail/<?php echo $oitem->url_key; ?>" title="<?php echo $oitem->product_name; ?>" target="_blank">
                                                            <?php echo $oitem->product_name; ?>
                                                        </a>
                                                    </h3>
                                                    <div class="shpcart-details">
                                                        <?php if ($oitem->product_type == 'conf-simple') { ?>
                                                            <p><?php echo implode(', ', $variants); ?></p>
                                                        <?php } ?>
                                                        <p>Quantity: <?php echo $oitem->qty_ordered; ?></p>
                                                        <div class="price">
                                                            <div class="price-cart-table">
                                                                <p>Price: <b><?php echo CURRENCY_TYPE; ?> <?php echo number_format($oitem->price, 2); ?></b></p><?php if (isset($order->flag) && $order->flag == 'able_to_return' && $oitem->can_be_returned == 0) { ?><span class="return-required red-text prd-cant">Item is not returnable</span><?php } ?>
                                                            </div>
                                                            <?php if (isset($order->flag) && $order->flag == 'able_to_return') { ?>
                                                                <div class="qty-review">
                                                                    <select name="item_qty_<?php echo $oitem->item_id; ?>" id="item_qty_<?php echo $oitem->item_id; ?>">
                                                                        <?php for ($i = 1; $i <= $oitem->qty_ordered; $i++) { ?>
                                                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!------------------ old design -------------------------->
                                        <!-- <div class="cart-images"><img src="<?php echo $base_image; ?>"></div>
                                <div class="cart-table-right">
                                    <h2 class="head-cart"><a href="<?php echo base_url(); ?>product-detail/<?php echo $oitem->url_key; ?>"><?php echo $oitem->product_name; ?></a></h2>
                                    <?php if ($oitem->product_type == 'conf-simple') { ?>
                                        <p class="grey-light-text"><?php echo implode(', ', $variants); ?></p>
                                    <?php } ?>

                                    <p class="grey-light-text">Qty: <?php echo $oitem->qty_ordered; ?></p>
                                    <div class="price"> <div class="price-cart-table"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($oitem->price, 2); ?><?php if (isset($order->flag) && $order->flag == 'able_to_return' && $oitem->can_be_returned == 0) { ?><span class="return-required red-text prd-cant">Item is not returnable</span><?php } ?></div>
                                    <?php if (isset($order->flag) && $order->flag == 'able_to_return') { ?>
                                        <div class="qty-review">
                                            <select name="item_qty_<?php echo $oitem->item_id; ?>"  id="item_qty_<?php echo $oitem->item_id; ?>">
                                                <?php for ($i = 1; $i <= $oitem->qty_ordered; $i++) { ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    <?php } ?>
                                    </div>
                                </div><!-- cart-table-right -->
                                        <!------------------ old design -------------------------->
                                    <?php
                                        $total_active_item++;
                                    } ?>

                                </div>
                                <div class="col-md-4">
                                    <!-- PRICES, TAXES, SHIPPING CHARGES DETAILS OF THE ORDER -->
                                    <div class="shpcart-ship-details">
                                        <div class="shpcart-ship-details-wrap">
                                            <p>Price (<?php echo $order_items ?> Items) <br> (Inclusive of taxes)</p>
                                            <p><?php echo CURRENCY_TYPE; ?> <?php echo number_format($OrderData->base_subtotal, 2); ?></p>
                                        </div>
                                        <div class="shpcart-ship-details-wrap">
                                            <p>Taxes</p>
                                            <p><?php echo CURRENCY_TYPE; ?> <?php echo number_format($OrderData->tax_amount, 2); ?></p>
                                        </div>
                                        <div class="shpcart-ship-details-wrap subtotal">
                                            <p>Sub Total</p>
                                            <p><?php echo CURRENCY_TYPE; ?> <?php echo number_format($OrderData->subtotal, 2); ?></p>
                                        </div>
                                        <div class="shpcart-ship-details-wrap">
                                            <?php
                                            if ($order_total_discount > 0) { ?>
                                                <p>Discount Amount</p>
                                                <p>- <?php echo CURRENCY_TYPE; ?> <?php echo number_format($OrderData->discount_amount, 2); ?></p>
                                            <?php } ?>
                                        </div>
                                        <?php
                                        if ($order_total_voucher_amount > 0) { ?>
                                            <div class="shpcart-ship-details-wrap">
                                                <p>Voucher Amount</p>
                                                <p>- <?php echo CURRENCY_TYPE; ?> <?php echo number_format($order_total_voucher_amount, 2); ?></p>
                                            </div>
                                        <?php } ?>
                                        <div class="shpcart-ship-details-wrap">
                                            <p>Shipping Charges</p>
                                            <p>+ <?php echo CURRENCY_TYPE; ?> <?php echo number_format($OrderData->shipping_amount, 2); ?></p>
                                        </div>

                                        <div class="shpcart-ship-details-wrap ordtotal">
                                            <p><b>Order Total</b></p>
                                            <p><?php echo CURRENCY_TYPE; ?> <?php echo number_format($OrderData->grand_total, 2); ?></p>
                                        </div>
                                    </div><!-- .shpcart-ship-details end -->
                                    <!-- PRICES, TAXES, SHIPPING CHARGES DETAILS OF THE ORDER ENDS-->
                                </div>
                            </div>
                        </div>
                        <?php if ($total_active_item <= 0) { ?>
                            <input type="hidden" name="active_items" id="active_items" value="<?php echo  $total_active_item; ?>">
                            <li>
                                <p class="mb-5 pb-5 item-re-text">Items are returned</p>
                            </li>
                        <?php } ?>

                        <!-- cart-table-amount-total start -->
                        <?php
                        $order_sub_total = $order_total_price  - $order_total_discount + $order_total_shipping;
                        $order_total_amount = $order_sub_total -  $order_total_voucher_amount;
                        ?>

                        <!--<div class="cart-table-amount-total">
                                <p class="grey-light-text">Price (<?php echo $order_items ?> Items) <br> (Inclusive of taxes) <span class="amount-doller"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order->base_subtotal, 2); ?></span> </p>
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
                        <div class="panel-footer">
                            <!-- ACTION BUTTONS OF THE ORDER -->
                            <?php
                            if (isset($order->flag) && $order->flag == 'able_to_cancel') { ?>
                                <!-- <button  name="cancel_order_btn" class="btn btn-primary" data-toggle="modal" id="cancel_order_btn" data-id="<?php echo $order->order_id; ?>" value="<?php echo $order->order_id; ?>" data-target="#cancel-order-modal">Cancel Order</button> -->

                            <?php   } ?>


                            <?php
                            if ($OrderData->status == 5 || $OrderData->status == 6) {
                            ?>
                                <!-- <button type="button" class="blue-btn-order tracking_details_btn btn btn-primary" orderid='<?php echo $OrderData->order_id; ?>'>Track Order</button> -->
                                <?php if (THEMENAME != 'theme_zumbawear') { ?>
                                    <div class="d-none" id="tracking_details_div_<?php echo $OrderData->order_id; ?>"></div>
                                <?php }
                            }
                            if (isset($OrderData->invoice_file) && $OrderData->invoice_file != '' && $OrderData->invoice_self == 1) {
                                ?>
                                <a class="blue-btn-order download-invoice btn " href="<?php echo INVOICE_FILE . $OrderData->invoice_file; ?>" target="_blank"><button type="submit" class="btn btn-primary">Download Invoice</button></a>
                            <?php
                            }
                            if ($OrderData->status != 3) {
                                $encoded_id = base64_encode($OrderData->order_id);
                                $encoded_id = urlencode($encoded_id);
                            ?>
                                <a class="blue-btn-order download-invoice print-receipt-btn " href="<?php echo BASE_URL('receipt-order/print' . '/' . $encoded_id) ?>" target="_blank"> <button type="submit" class="btn btn-primary">Download Receipt</button></a>
                                <?php
                            }

                            if ($OrderData->status == 5 || $OrderData->status == 6) {
                                if (THEMENAME == 'theme_zumbawear') { ?>
                                    <div class="d-none" id="tracking_details_div_<?php echo $OrderData->order_id; ?>"></div>
                            <?php }
                            }
                            ?>

                        </div>

                    <?php } ?>
                    <?php include('ReturnOrders.php') ?>
                </div><!-- order-info -->
            <?php
            } else {
                echo "<div class='empty-record'>" . "No orders found" . "</div>";
            } ?>

        </div><!-- order-listing -->
        <!--</div><!-- ROW -->
    </div><!-- content-page -->
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