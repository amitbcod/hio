<?php
    $currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
    $currency_symbol = $this->session->userdata('currency_symbol');
    $default_currency_flag = $this->session->userdata('default_currency_flag');

    if (isset($CartData) && isset($CartData->cartItems) && count($CartData->cartItems)>0) {
?>
    <div class="col-md-8 col-lg-8 ">
        <div class="cart-left-box">
        <form id="update-cart-form" method="post" enctype="multipart/form-data">
            <ul class="cart-left-box-block">
                <?php if (isset($CartData) && isset($CartData->cartItems) && count($CartData->cartItems)>0) { ?>
                <?php
                    $cartItems = $CartData->cartItems;
                    $cartDetails = $CartData->cartDetails;
                    $cart_prelauch_flag = 0;
                ?>
                <?php foreach ($cartItems as $value) {
                    $base_image= ((isset($value->base_image) && $value->base_image!='') ? PRODUCT_THUMB_IMG.$value->base_image : PRODUCT_DEFAULT_IMG);
                    $product_variants= (($value->product_variants != '') ? json_decode($value->product_variants) : '');

					$bundle_child_ids = '';
					if(isset($value->bundle_child_details) && $value->bundle_child_details != ''){
						$bundle_child_details = json_decode($value->bundle_child_details);
						if(isset($bundle_child_details)){
                            $ids = array_column($bundle_child_details, 'bundle_child_product_id');
                            $bundle_child_ids =  implode(',', $ids);
                        }
					}

                    $variants = '';
                    if (isset($product_variants) && $product_variants != '') {
                        foreach ($product_variants as $pk=>$single_variant) {
                            foreach ($single_variant as $key=>$val) {
                                $variants.='<span class="variant-item">'.$key.' - '.$val.'</span><br>';
                            }
                        }
                    }
                ?>
                <li>
                    <div class="cart-images"><img src="<?php echo $base_image; ?>"></div>
                    <div class="cart-table-right">
                        <input type="hidden" name="item_id[]" value="<?= $value->item_id ?>">
						<input type="hidden"  name="bundle_child_ids[]" id="bundle_child_ids_<?= $value->item_id ?>" value="<?php echo $bundle_child_ids ?>">
                        <?php if ($value->prelaunch == 1) {
                            $cart_prelauch_flag = 1; ?>
                            <a href="<?php echo BASE_URL.'product-detail/'.$value->url_key.'?type=prelaunch'; ?>">
                        <?php } else { ?>
                            <a href="<?php echo BASE_URL.'product-detail/'.$value->url_key; ?>">
                        <?php } ?>

                        <h2 class="head-cart">
                        <!-- <?php echo ((isset($value->other_lang_name) && $value->other_lang_name!='') ? $value->other_lang_name : $value->product_name);?></h2></a> -->

                        <?php
                                if (isset($value->product_type) && $value->product_type=='bundle') {
                                    ?>
                                    <p><strong><?php echo((isset($value->other_lang_name) && $value->other_lang_name!='') ? $value->other_lang_name : $value->product_name);?></strong>
                                        <span class="tw-text-gray-500 tw-text-xs"><br/>
                                        <?php echo((isset($value->bundleData) && $value->bundleData!='') ? $value->bundleData : '');?>
                                    </span></p>
                            <?php }  ?>
                    </h2></a>
                            
                        <?php if (isset($product_variants) && $product_variants != '') { ?>
                        <p class="grey-light-text"><?= rtrim($variants, ", "); ?></p>
                        <?php }?>
                        <div class="price"> <div class="price-cart-table">
                        <?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($value->price, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($value->price, 2));?></div> <div class="qty-box-cart"> <span>Quantity</span>
                        <?php
						 $available_qty=$value->available_qty;

						 if ($available_qty>$qty_limit) {
							 $available_qty=$qty_limit;
						 } elseif ($value->prelaunch == 1) {
							 $available_qty=$qty_limit;
						 }elseif ($value->product_type == 'bundle') {
                        	$available_qty=$qty_limit;
                    	 }
						 ?>
						  <?php if($value->product_type == 'bundle'){
							foreach($bundle_child_details as $data){ ?>
							<input type="hidden" value="<?php echo $data->product_id?>" name="conf_simple_pid[]" id="conf_simple_pid_<?php echo $value->item_id;?>_<?php echo $data->bundle_child_product_id;?>">
							<?php } ?>
						<?php } ?>
						
                         <button onclick="decreaseQtyValue(<?php echo $value->item_id;?>,'<?php echo $value->product_type;?>',<?php echo $value->product_id;?>,<?php echo $value->parent_product_id;?>)" type="button" class="qty-btn-new tw-w-6 tw-h-6 tw-bg-gray-100 tw-rounded-full tw-p-1 hover:tw-bg-zumbared tw-transition hover:tw-text-white tw-flex tw-items-center tw-justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-4 tw-w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6"></path>
                                </svg>
                            </button>

                            <input onfocusout="updateItemQty(<?php echo $value->item_id;?>,'<?php echo $value->product_type;?>',<?php echo $value->product_id;?>,<?php echo $value->parent_product_id;?>)" data-item-id="<?php echo $value->item_id;?>" data-price="<?php echo number_format($value->price, 2);?>" type="text" name="quantity[]" id="quantity_<?php echo $value->item_id;?>" class="qty-input tw-w-10 tw-h-8 tw-text-center tw-border tw-border-gray-400" placeholder="0" min="1" max="<?php echo $available_qty ?>" value="<?php echo $value->qty_ordered ?>">
                            <input type="hidden" value="<?php echo $value->qty_ordered ?>" name="previous_qty[]" id="previous_qty_<?php echo $value->item_id;?>">
                            <input type="hidden" value="<?php echo $available_qty ?>" name="max_qty[]" id="max_qty_<?php echo $value->item_id;?>">

                            <button onclick="increaseQtyValue(<?php echo $value->item_id;?>,'<?php echo $value->product_type;?>',<?php echo $value->product_id;?>,<?php echo $value->parent_product_id;?>)" type="button" class="qty-btn-new tw-w-6 tw-h-6 tw-bg-gray-100 tw-rounded-full hover:tw-bg-zumbared tw-transition hover:tw-text-white tw-p-1 tw-flex tw-items-center tw-justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-4 tw-w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
						<input type="hidden" value="<?php echo $value->qty_ordered ?>" name="previous_qty[]" id="previous_qty_<?php echo $value->item_id;?>">
						<input type="hidden" value="<?php echo $available_qty ?>" name="max_qty[]" id="max_qty_<?php echo $value->item_id;?>">
                        <p id="qtyError_<?php echo $value->item_id;?>" class="qty-error"></p></div> </div>
                        <?php
                            if ($value->qty_ordered > $available_qty) {
                                if ($available_qty <= 0) {
                                    echo '<p class="not-available">'.lang('the_product_is_not_available').'</p>';
                                } else {
                                    echo '<p class="not-available">'.lang('the_requested_quantity_exceeds_the_maximum_quantity_allowed_in_shopping_cart').'</p>';
                                }
                            }
                        ?>
                        <p class="delivery-time"><?= ($value->estimate_delivery_time != '')?' Delivery in '.$value->estimate_delivery_time.' Days':'';?> <a class="remove-cart" href="javascript:void(0)" onclick="RemoveCartItem('<?php echo $value->item_id; ?>')"><i class="icon-delete"></i>Remove</a></p>
                    </div><!-- cart-table-right -->
                </li>
                <?php }?>
                <?php } ?>
                <li class="text-right">
                    <a onclick="updateCartItems()"><button class="checkout">Update Cart</button></a>
                    <a href="<?php echo base_url(); ?>" class="continue-shopping">Continue Shopping</a>
                    <?php if ($cart_prelauch_flag == 1) { ?>
                        <a href="<?php echo base_url(); ?>pre-launch" class="continue-shopping prelaunch-btn <?php echo (THEMENAME=='theme_zumbawear') ? 'tw-bg-black hover:tw-bg-zumbahotlime tw-transition hover:tw-text-black tw-text-white tw-transition tw-px-4 tw-py-2' : ''; ?>"><?=lang('Rreturn_to_pre_launch_page')?></a>
                    <?php } ?>
                    <a href="<?php echo base_url(); ?>checkout"><button class="checkout" type="button">Checkout</button></a>
                </li>

            </ul>
        </form>
        </div><!-- cart-left-box -->
    </div><!-- col-md-8 -->

    <div class="col-md-4 col-lg-4 "  id="cart-page-sidebar">
        <?php (new CartList())->cartPriceDetails($CartData,'cartPage'); ?>
    </div><!-- col-md-4 -->
<?php } else { ?>
<div class="card-body ">
        <div class="col-sm-12 empty-cart-cls text-center"><span class="icon icon-shopping_cart"></span>
            <h3><strong><?=lang('your_cart_is_empty')?></strong></h3>
            <h4><?=lang('add_something_to_make_me_happy')?></h4> <a href="<?php echo base_url(); ?>" class="btn btn-blue" data-abc="true"><?=lang('continue_shopping')?></a>
        </div>
    </div>

<?php } ?>
