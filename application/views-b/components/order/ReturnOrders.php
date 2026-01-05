<?php

if($login_type == 'general'){
    $ReturnOrders_common = $order->ReturnOrders;
}else{
    $ReturnOrders_common = $OrderData->ReturnOrders;
}

if (isset($ReturnOrders_common) && $ReturnOrders_common!='[]' && count($ReturnOrders_common)>0) {

    foreach ($ReturnOrders_common as $return_order) {
    $order_items = count($return_order->order_items); ?>

        <div class="order-info">
            <div class="order-info-inner">
                <span class="order-id"><?=lang('order_id')?>  :  <strong><?php echo $return_order->return_order_increment_id; ?></strong></span>
                <span class="order-date"><?=lang('order_on')?> :   <strong> <?php echo date('d F Y', $return_order->created_at); ?></strong></span>
                <span class="order-total"><?=lang('order_total')?> :    <strong> <?php echo CURRENCY_TYPE; ?> <?php echo number_format($return_order->order_grandtotal, 2); ?></strong></span>

            <?php if($login_type == 'general'){ ?>
                <a href="<?php echo base_url(); ?>customer/my-orders/return-detail/<?php  echo $return_order->return_order_id; ?>">
            <?php } else{ ?>
                <a href="<?php echo base_url(); ?>customer/my-guest-orders/return-detail/<?php  echo $return_order->return_order_id; ?>">    
            <?php  } ?>
                <span class="return-required">
                    <?php
                        if ($return_order->refund_status==0) {
                            echo lang('return_requested');
                        } elseif ($return_order->refund_status==1) {
                            echo lang('refund_approved');
                        } elseif ($return_order->refund_status==2) {
                            echo lang('refund_rejected');
                        } ?>
                </span>
                </a>
            </div>

            <?php if (isset($return_order->order_items)  && count($return_order->order_items)>0) { ?>
            <ul class="cart-left-box-block">
                <?php
                        $order_total_price = 0;
                        $order_total_tax = 0;
                        $order_total_discount = 0;

                        foreach ($return_order->order_items as $roitem) {
                            $order_total_price += $roitem->price *  $roitem->qty_return;
                            $order_total_discount += $roitem->discount_amount *  $roitem->qty_return;
                            $base_image = ((isset($roitem->base_image) && $roitem->base_image!='') ? PRODUCT_THUMB_IMG.$roitem->base_image : PRODUCT_DEFAULT_IMG);
                            $product_variants= ((isset($roitem->product_variants) && $roitem->product_variants != '') ? json_decode($roitem->product_variants) : '');

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
                        <div class="cart-images"><img src="<?php echo $base_image; ?>"></div>
                        <div class="cart-table-right">

                            <h2 class="head-cart"><a href="<?php echo base_url(); ?>product-detail/<?php echo $roitem->url_key; ?>"><?php echo $roitem->product_name; ?></a></h2>
                            <?php if ($roitem->product_type=='conf-simple') { ?>
                                <p class="grey-light-text"><?php echo implode(', ', $variants); ?></p>
                            <?php } ?>

                            <p class="grey-light-text"><?=lang('qty')?>: <?php echo $roitem->qty_return; ?></p>

                            <div class="price">
                                <div class="price-cart-table"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($roitem->price, 2); ?></div>
                                <div class="qty-review">
                                    <a href="<?php echo base_url(); ?>product-detail/<?php echo $roitem->url_key; ?>#ratings-review" target="_blank"><button class="review" type="button"><?=lang('review')?></button></a>
                                </div>
                            </div>
                        </div><!-- cart-table-right -->
                    </li>
                <?php } ?>

                <!-- cart-table-amount-total start -->
                        <?php
                            $order_sub_total = $order_total_price  /*- $order_total_discount + $order_total_shipping */;
                            $order_total_amount = $order_sub_total /*-  $order_total_voucher_amount*/;
                        ?>

                        <div class="cart-table-amount-total">
                            <p class="grey-light-text"><?=lang('price')?> (<?php echo $order_items?> <?=lang('items')?>) <br> <?=lang('inclusive_of_taxes')?> <span class="amount-doller"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order_total_price, 2); ?></span> </p>


                            <?php if($order_total_discount > 0 ){ ?>
                                    <p class="grey-light-text"><?=lang('discount_amount')?><span class="amount-doller">- <?php echo CURRENCY_TYPE; ?>  <?php echo number_format($order_total_discount, 2); ?></span> 
                                    </p>
                                <?php } ?> 
                                <p class="grey-light-text sub-total-amount"><?=lang('return_order_total')?> <span class="amount-doller"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($return_order->order_grandtotal, 2); ?></span> </p>


                                <?php if($return_order->refund_status==1){ ?>
                                        <p class="grey-light-text final-order-amount"><?=lang('refund_approved')?><span class="amount-doller"><?php echo CURRENCY_TYPE; ?>  <?php echo number_format($return_order->order_grandtotal_approved, 2); ?></span> 
                                        </p>
                                        <p class="grey-light-text"> <?php if($return_order->shipping_charge_flag==1){ echo '('.lang('shipping_charges').' :'.CURRENCY_TYPE .number_format($return_order->shipping_amount, 2).')'; } ?> 
                                        </p>
                                <?php } ?>
                            
                        </div><!-- cart-table-amount-total end -->
            </ul>
            <?php } ?>
        </div><!-- order-info -->

        <?php }
    } ?>