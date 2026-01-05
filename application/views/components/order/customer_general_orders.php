<?php
// echo "<pre>";
// print_r($OrderList);
// die;
?>

<?php
function getSubOrderStatusText($status) {
    $text = 'Unknown';
    $class = 'green-text completed'; // default green

    if ($status == 0) {
        $text = 'To Be Processed';
        $class = 'green-text completed';
    } elseif (in_array($status, [1,10,11,12,4,5,6,7])) {
        $text = 'Processing';
        $class = 'green-text completed';
    } elseif (in_array($status, [2,8,9])) {
        $text = 'Completed';
        $class = 'green-text completed';
    } elseif ($status == 3) {
        $text = 'Cancelled';
        $class = 'red-text cancelled';
    } elseif ($status == 13) {
        $text = 'Collect From Warehouse';
        $class = 'green-text completed';
    }
    return ['text' => $text, 'class' => $class];
}
?>
<div class="col-md-9 col-sm-9 ">
    <div class="content-page">
        <div class="row">
            <div class="col-md-12">
                <h1>Your Orders</h1>
            </div>
        </div>
        <div class="wishlist-listing my-orders ">
            <?php
            if (isset($OrderList) &&  count($OrderList) > 0) {
                $order_total_shipping = 0;
                $order_total_voucher_amount = 0;

                foreach ($OrderList as $order) {
                    $order_items = count($order->order_items);
                    $order_total_shipping = $order->shipping_amount;
                    $order_total_voucher_amount = $order->voucher_amount; ?>

                   <?php
// Determine parent order status based on sub-orders
$parent_status = 'Unknown';
$parent_status_class = 'green-text completed'; // default green

if (isset($order->b2b_orders) && count($order->b2b_orders) > 0) {
    $sub_status_codes = array_map(fn($b2b) => $b2b->status, $order->b2b_orders);

    $all_tobe = count(array_filter($sub_status_codes, fn($s) => $s == 0)) == count($sub_status_codes);
    $all_complete = count(array_filter($sub_status_codes, fn($s) => in_array($s, [2,8,9]))) == count($sub_status_codes);
    $any_processing = count(array_filter($sub_status_codes, fn($s) => in_array($s, [1,4,5,6,7,10,11,12,13]))) > 0;
    $any_cancelled = count(array_filter($sub_status_codes, fn($s) => $s == 3)) > 0;

    if ($all_tobe) {
        $parent_status = 'To Be Processed';
        $parent_status_class = 'green-text completed';
    } elseif ($any_cancelled) {
        $parent_status = 'Cancelled';
        $parent_status_class = 'red-text cancelled';
    } elseif ($all_complete) {
        $parent_status = 'Completed';
        $parent_status_class = 'green-text completed';
    } elseif ($any_processing) {
        $parent_status = 'Processing';
        $parent_status_class = 'green-text completed';
    }
}
?>


                    <div class="order-info panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-7">
                                    <p class="stats"><b>Order ID: <?php echo $order->increment_id; ?></b></p>
                                    <p class="stats"><b>Order Date: <?php echo date('d F Y', $order->created_at); ?></b></p>
                                </div>
                                <div class="col-md-5">
                                   <p class="stats on-right"><b>Order Status: 
    <span class="return-required <?php echo $parent_status_class; ?>">
        <?php echo $parent_status; ?>
    </span>
</b></p>
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
                        <?php if (isset($order->order_items)  && count($order->order_items) > 0) { ?>


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
            $total_active_item = 0;

            if (isset($order->b2b_orders) && count($order->b2b_orders) > 0) {
                foreach ($order->b2b_orders as $b2b) {

                    // Determine sub-order status text & CSS class
                    $status_map = [
                        0 => ['text' => 'To Be Processed', 'class' => 'green-text completed'],
                        1 => ['text' => 'Processing', 'class' => 'green-text completed'],
                        10 => ['text' => 'Processing', 'class' => 'green-text completed'],
                        11 => ['text' => 'Processing', 'class' => 'green-text completed'],
                        12 => ['text' => 'Processing', 'class' => 'green-text completed'],
                        2 => ['text' => 'Completed', 'class' => 'green-text completed'],
                        8 => ['text' => 'Completed', 'class' => 'green-text completed'],
                        9 => ['text' => 'Completed', 'class' => 'green-text completed'],
                        3 => ['text' => 'Cancelled', 'class' => 'red-text cancelled'],
                        4 => ['text' => 'Shipped', 'class' => 'green-text completed'],
                        5 => ['text' => 'Shipped', 'class' => 'green-text completed'],
                        6 => ['text' => 'Shipped', 'class' => 'green-text completed'],
                        13 => ['text' => 'Collect From Warehouse', 'class' => 'green-text completed'],
                        7 => ['text' => 'Warehouse Pickup Required', 'class' => 'green-text completed']
                    ];

                    $b2b_status_info = isset($status_map[$b2b->status]) ? $status_map[$b2b->status] : ['text'=>'Unknown','class'=>'green-text completed'];

                    foreach ($b2b->sub_order_items as $sub_item) {
                        // Get parent item info for image & price
                        $parent_item = null;
                        foreach ($order->order_items as $oitem) {
                            if ($oitem->product_name == $sub_item->product_name) {
                                $parent_item = $oitem;
                                break;
                            }
                        }

                        $base_image = isset($parent_item->base_image) && $parent_item->base_image != '' ? PRODUCT_THUMB_IMG . $parent_item->base_image : PRODUCT_DEFAULT_IMG;
                        $price = isset($parent_item->price) ? $parent_item->price : 0;
                        ?>
                        <div class="row margbot20 sub-order-block">
                            <div class="col-sm-4 col-md-4">
                                <div class="shpcart-img-wrap vv">
                                    <img src="<?php echo $base_image; ?>" alt="<?php echo $sub_item->product_name; ?>" class="img-responsive">
                                </div>
                            </div>
                            <div class="col-sm-8 col-md-8">
                                <div class="shpcart">
                                    <h4>Sub Order ID: <?php echo $b2b->order_id; ?> | 
                                        <span class="return-required <?php echo $b2b_status_info['class']; ?>">
                                            <?php echo $b2b_status_info['text']; ?>
                                        </span>
                                    </h4>
                                    <p><?php echo $sub_item->product_name; ?></p>
                                    <p>Quantity: <?php echo isset($sub_item->qty_ordered) ? $sub_item->qty_ordered : 1; ?></p>
                                    <p>Price: <?php echo CURRENCY_TYPE . ' ' . number_format($price, 2); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php
                        $total_active_item++;
                    }
                }
            }
            ?>
        </div>

        <!-- Right-side order totals remain unchanged -->
        <div class="col-md-4">
            <div class="shpcart-ship-details">
                <div class="shpcart-ship-details-wrap">
                    <p>Price (<?php echo count($order->order_items) ?> Items)<br>(Inclusive of taxes)</p>
                    <p><?php echo CURRENCY_TYPE . ' ' . number_format($order->base_subtotal, 2); ?></p>
                </div>
                <div class="shpcart-ship-details-wrap">
                    <p>Taxes</p>
                    <p><?php echo CURRENCY_TYPE . ' ' . number_format($order->tax_amount, 2); ?></p>
                </div>
                   <?php if ($order->voucher_amount > 0) { ?>
                    <div class="shpcart-ship-details-wrap">
                        <p>Gift Card Amount</p>
                        <p>- <?php echo CURRENCY_TYPE . ' ' . number_format($order->voucher_amount, 2); ?></p>
                    </div>
                <?php } ?>
                <div class="shpcart-ship-details-wrap subtotal">
                    <p>Sub Total</p>
                    <p><?php echo CURRENCY_TYPE . ' ' . number_format($order->subtotal, 2); ?></p>
                </div>
                <?php if ($order->discount_amount > 0) { ?>
                    <div class="shpcart-ship-details-wrap">
                        <p>Discount Amount</p>
                        <p>- <?php echo CURRENCY_TYPE . ' ' . number_format($order->discount_amount, 2); ?></p>
                    </div>
                <?php } ?>
             
                <div class="shpcart-ship-details-wrap">
                    <p>Shipping Charges</p>
                    <p>+ <?php echo CURRENCY_TYPE . ' ' . number_format($order->shipping_amount, 2); ?></p>
                </div>
                <div class="shpcart-ship-details-wrap ordtotal">
                    <p><b>Order Total</b></p>
                    <p><?php echo CURRENCY_TYPE . ' ' . number_format($order->grand_total, 2); ?></p>
                </div>
            </div>
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
                                if ($order->status == 5 || $order->status == 6) {
                                ?>
                                    <!-- <button type="button" class="blue-btn-order tracking_details_btn btn btn-primary" orderid='<?php echo $order->order_id; ?>'>Track Order</button> -->
                                    <?php if (THEMENAME != 'theme_zumbawear') { ?>
                                        <div class="d-none" id="tracking_details_div_<?php echo $order->order_id; ?>"></div>
                                    <?php }
                                }
                                if (isset($order->invoice_file) && $order->invoice_file != '' && $order->invoice_self == 1) {
                                    ?>
                                    <a class="blue-btn-order download-invoice btn " href="<?php echo INVOICE_FILE . $order->invoice_file; ?>" target="_blank"><button type="submit" class="btn btn-primary">Download Invoice</button></a>
                                <?php
                                }
                                if ($order->status != 3) {
                                    $encoded_id = base64_encode($order->order_id);
                                    $encoded_id = urlencode($encoded_id);
                                ?>
                                    <a class="blue-btn-order download-invoice print-receipt-btn " href="<?php echo BASE_URL('receipt-order/print' . '/' . $encoded_id) ?>" target="_blank"> <button type="submit" class="btn btn-primary">Download Receipt</button></a>
                                    <?php
                                }

                                if ($order->status == 5 || $order->status == 6) {
                                    if (THEMENAME == 'theme_zumbawear') { ?>
                                        <div class="d-none" id="tracking_details_div_<?php echo $order->order_id; ?>"></div>
                                <?php }
                                }
                                ?>

                            </div>

                        <?php } ?>
                        <?php include('ReturnOrders.php') ?>
                    </div><!-- order-info -->
            <?php }
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