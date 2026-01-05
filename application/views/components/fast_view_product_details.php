    <div class="container-fluid">
        <div class="row">
            <!-- BEGIN CONTENT -->
            <div class="col-md-12">
                <div class="product-page">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="product-name">
                                <?php echo ((isset($ProductData->other_lang_name) && $ProductData->other_lang_name != '') ? $ProductData->other_lang_name : $ProductData->name); ?>
                            </h1>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="product-image-section" id="product-image-section">
                                <?php $this->load->view('product/media_gallery') ?>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-8">
                            <ul class="list-group">
                                <?php if (isset($ProductData->publication_name) && !empty($ProductData->publication_name) && $ProductData->publication_name !== 'Anu Test Developer Account') { ?>
                                    <li class="list-group-item"><b>Merchant:
                                        </b><?php echo $ProductData->publication_name; ?>
                                    </li>
                                <?php } ?>
                                <?php if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0) {
                                    foreach ($ProductData->AttributesWithOptions as $attr) { ?>
                                        <?php if ($attr->attr_value) { ?>
                                            <li class="list-group-item"><b><?php echo $attr->attr_name ?>:
                                                </b><?php echo $attr->attr_value ?>
                                            </li>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            </ul>
                            <form name="product-frm" id="quick-product-frm">
                                <input type="hidden" name="media_variant_id" id="media_variant_id" value="<?php echo  $ProductData->media_variant_id; ?>">

                                <input type="hidden" name="product_type" id="product_type" value="<?php echo  $ProductData->product_type; ?>">

                                <?php if ($ProductData->product_type == 'simple') { ?>
                                    <div class="table-responsive product-price">
                                        <table class="table" width="100%" cellpadding="3" cellspacing="1" border="0">
                                            <thead>
                                                <tr class="active">
                                                    <th>Price (MUR)</th>
                                                    <?php if (isset($ProductData->special_price) && $ProductData->special_price != '') { ?>
                                                        <th>Offer Price (MUR)</th>
                                                    <?php } ?>
                                                    <!-- <th>Issues</th>
                                                    <th>Gift</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo $ProductData->webshop_price; ?></td>
                                                    <?php if (isset($ProductData->special_price) && $ProductData->special_price != '') { ?>
                                                        <td><?php echo $ProductData->special_price; ?>
                                                            <?php echo $ProductData->off_percent_price; ?>% Off
                                                        </td>
                                                    <?php } ?>
                                                    <!-- <td><?php //echo $ProductData->sub_issues; ?> issues</td>
                                                    <td><?php //echo $ProductData->gift_master_name; ?> </td> -->
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>

                                <?php (new ProductDetails())->productStockVariant($ProductData, $CategoryIds); ?>

                                <?php
                                if (isset($ProductData->coming_soon_flag) && $ProductData->coming_soon_flag == 1) { ?>
                                    <!-- To use when items OUT of stock (no variants - STARTS) -->
                                    <div class="notifywrap">
                                        <div class="notifyhd">
                                            <i class="fa fa-exclamation-circle fa-2x"></i> <span>OUT OF STOCK!</span>
                                        </div>
                                        <div class="notifyfrm">
                                            <p>This product is not on our stands. We expect it to arrive soon!<br>Leave your
                                                email to be notified when it arrives!</p>
                                            <div class="form-inline" id="keepnotified">
                                                <div class="form-group">
                                                    <label class="sr-only" for="notified-email">Email address</label>
                                                    <input type="email" class="form-control" id="notified-email" placeholder="Email" value="<?php echo $_SESSION["EmailID"] ?? ""; ?>" onkeyup="ValidateEmail();">

                                                </div>

                                                <button type="button" id="notified-keep-inform" class="btn btn-primary" onclick=openNotifiedPopup(<?php echo $ProductData->id; ?>)>Keep Me Informed!</button>
                                            </div>
                                            <span id="lblError" class="notified-error"></span>
                                        </div>
                                    </div>
                                    <!-- To use when items OUT of stock (no variants - ENDS) -->

                                <?php } ?>

                                <div class="subscribe-deli">
                                    <?php if (isset($ProductData->stock_status) && ($ProductData->stock_status == 'Instock')) { ?>
                                        <?php if ($restricted_access == "yes" && $customer_id == 0) { ?>
                                            <button type="button" class="btn btn-primary subscr-now pull-left as" <?php echo isset($ProductData->stock_status) &&
                                                                                                                        ($ProductData->stock_status == "Instock" &&
                                                                                                                            $ProductData->product_type == "simple")
                                                                                                                        ? ""
                                                                                                                        : "disabled"; ?> onclick="openRestrictedAccessPopup()" class="add-to-cart-btn">
                                                Add to cart
                                            </button>
                                            <a href="<?php echo $productLink; ?>" class="btn btn-default subscr-moredet pull-left" target="Parent">More details</a>
                                            <div class="clearfix"></div>
                                            <?php if (isset($ProductData->estimate_delivery_time) && !empty($ProductData->estimate_delivery_time)) { ?>
                                                <p><b>Expected Delivery Duration: <?php echo $ProductData->estimate_delivery_time; ?> Days.</b></p>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <button type="button" id="add_to_cart" onclick="Quickaddtocart()" class="btn btn-primary subscr-now pull-left as">Add to cart </button>
                                            <a href="<?php echo $productLink; ?>" class="btn btn-default subscr-moredet pull-left" target="Parent">More details</a>
                                            <div class="clearfix"></div>
                                            <?php if (isset($ProductData->estimate_delivery_time) && !empty($ProductData->estimate_delivery_time)) { ?>
                                                <p><b>Expected Delivery Duration: <?php echo $ProductData->estimate_delivery_time; ?> Days.</b></p>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <button class="btn btn-primary subscr-now pull-left" id="add_to_cart" type="submit" disabled>Out of Stock</button>
                                    <?php } ?>
                                    <div id="addtocart-message" class="addtocart-message"></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END CONTENT -->
        </div>
    </div>