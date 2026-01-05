<?php //echo"<pre>";print_r($CartData);echo"</pre>"; ?>
<div class="col-md-12 col-sm-12 <?php echo (isset($CartData->cartItems) && count($CartData->cartItems) > 0)?'':'text-center'?>">
    <h1><?php echo $this->lang->line('shopping_cart'); ?></h1>

    <?php if(isset($CartData->cartItems) && count($CartData->cartItems) > 0){ ?>
        <div class="goods-page">
            <div class="row">
                <div class="col-md-9 col-sm-8">
                    <div class="goods-data">
                        <div class="table-wrapper-responsive">
                            <table summary="<?php echo $this->lang->line('shopping_cart'); ?>">
                                <tr>
                                    <th class="goods-page-image"><?php echo $this->lang->line('image'); ?></th>
                                    <th class="goods-page-description"><?php echo $this->lang->line('description'); ?></th>
                                    <th class="goods-page-quantity"><?php echo $this->lang->line('quantity'); ?></th>
                                    <th class="goods-page-price"><?php echo $this->lang->line('unit_price'); ?></th>
                                    <th class="goods-page-total" colspan="2"><?php echo $this->lang->line('total'); ?></th>
                                </tr>

                                <?php foreach($CartData->cartItems as $value){ 
                                    $base_image= ((isset($value->base_image) && $value->base_image!='') ? PRODUCT_THUMB_IMG.$value->base_image : PRODUCT_DEFAULT_IMG);
                                    $product_variants= (($value->product_variants != '') ? json_decode($value->product_variants) : '');
                                    
                                    $bundle_child_ids='';
                                    if(isset($value->bundle_child_details) && $value->bundle_child_details != ''){
                                        $bundle_child_details = json_decode($value->bundle_child_details);
                                        if(isset($bundle_child_details)){
                                            $ids = array_column($bundle_child_details,'bundle_child_product_id');
                                            $bundle_child_ids.= implode(',',$ids);
                                        }
                                    }

                                    $variants = '';
                                    if (isset($product_variants) && $product_variants != '') {
                                        foreach ($product_variants as $pk=>$single_variant) {
                                            foreach ($single_variant as $key=>$val) {
                                                $variants.='<p>'.$key.' : '.$val.'</p>';
                                            }
                                        }
                                    }
                                ?>
                                    <tr>
                                        <input type="hidden" name="item_id[]" value="<?= $value->item_id ?>">
                                        <input type="hidden" name="bundle_child_ids[]" id="bundle_child_ids_<?= $value->item_id ?>" value="<?php echo $bundle_child_ids; ?>">

                                        <td class="goods-page-image">
                                            <a href="<?php echo base_url(); ?>product-detail/<?php echo $value->url_key; ?>" target="_blank">
                                                <img src="<?php echo $base_image; ?>" alt="<?php echo (!empty($value->other_lang_name) ? $value->other_lang_name : $value->product_name);?>">
                                            </a>
                                        </td>

                                        <td class="goods-page-description">
                                            <h3>
                                                <a href="<?php echo base_url(); ?>product-detail/<?php echo $value->url_key; ?>" target="_blank">
                                                    <?php echo (!empty($value->other_lang_name) ? $value->other_lang_name : $value->product_name);?>
                                                </a>
                                            </h3>
                                            <?php echo $variants; ?>
                                            <?php if(isset($value->product_type) && $value->product_type == 'bundle') { ?>
                                                <em><?php echo (!empty($value->bundleData) ? $value->bundleData : '');?></em>
                                            <?php } ?>
                                            <p id="qtyError_<?php echo $value->item_id;?>" class="qty-error"></p>
                                            <p class="delivery-time"><?= ($value->estimate_delivery_time != '') ? $this->lang->line('delivery_in_days').' '.$value->estimate_delivery_time.' '.$this->lang->line('days') : '';?></p>
                                        </td>

                                        <td class="goods-page-quantity">
                                            <div class="product-quantity">
                                                <?php 
                                                    $available_qty = $value->available_qty;
                                                    if ($available_qty>$qty_limit || $value->prelaunch == 1 || $value->product_type == 'bundle') {
                                                        $available_qty=$qty_limit;
                                                    }
                                                ?>
                                                <div class="input-group bootstrap-touchspin input-group-sm">
                                                    <span class="input-group-btn">
                                                        <button class="btn quantity-down bootstrap-touchspin-down" 
                                                            onclick="decreaseQtyValue(<?php echo $value->item_id; ?>,'<?php echo $value->product_type; ?>',<?php echo $value->product_id; ?>,<?php echo $value->parent_product_id; ?>)" 
                                                            type="button">
                                                            <i class="fa fa-angle-down"></i>
                                                        </button>
                                                    </span>

                                                    <input id="quantity_<?php echo $value->item_id; ?>" 
                                                        data-item-id="<?php echo $value->item_id;?>" 
                                                        data-price="<?php echo number_format($value->price, 2);?>"
                                                        type="text" 
                                                        min="1"
                                                        max="<?php echo $available_qty; ?>"
                                                        value="<?php echo $value->qty_ordered; ?>" 
                                                        readonly 
                                                        class="form-control input-sm" 
                                                        style="display: block;"
                                                    >
                                                    <input type="hidden" value="<?php echo $value->qty_ordered ?>" name="previous_qty[]" id="previous_qty_<?php echo $value->item_id;?>">
                                                    <input type="hidden" value="<?php echo $available_qty ?>" name="max_qty[]" id="max_qty_<?php echo $value->item_id;?>">

                                                    <span class="input-group-btn">
                                                        <button class="btn quantity-up bootstrap-touchspin-up" 
                                                            onclick="increaseQtyValue(<?php echo $value->item_id; ?>,'<?php echo $value->product_type; ?>',<?php echo $value->product_id; ?>,<?php echo $value->parent_product_id; ?>)" 
                                                            type="button">
                                                            <i class="fa fa-angle-up"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="goods-page-total">
                                            <strong><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($value->price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($value->price, 2));?></strong>
                                        </td>

                                        <td class="goods-page-total">
                                            <strong id="item_total_price_<?php echo $value->item_id; ?>"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($value->total_price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($value->total_price, 2));?></strong>
                                        </td>

                                        <td class="del-goods-col text-center">
                                            <a href="javascript:;" onclick="RemoveCartItem('<?php echo $value->item_id; ?>')">
                                                <i class="fa fa-trash" aria-hidden="true" style="font-size: 20px;"></i>
                                                <?php echo $this->lang->line('remove_label'); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-4">
                    <div class="clearfix">
                        <div class="shopping-total">
                            <div id="cart-page-sidebar">
                                <?php (new CartList())->cartPriceDetails($CartData,'cartPage'); ?>
                            </div>
                            <div class="divcent text-center">
                                <a href="<?php echo base_url(); ?>checkout" class="btn btn-primary chkout" type="submit">
                                    <?php echo $this->lang->line('checkout_label'); ?> <i class="fa fa-check"></i>
                                </a>
                                <a href="<?php echo base_url(); ?>" class="btn btn-default">
                                    <?php echo $this->lang->line('continue_shopping'); ?> <i class="fa fa-shopping-cart"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } else { ?> 
        <div class="shopping-cart-page">
            <div class="shopping-cart-data clearfix">
                <p><?php echo $this->lang->line('cart_empty'); ?></p>
            </div>
        </div>
    <?php } ?>
</div>
