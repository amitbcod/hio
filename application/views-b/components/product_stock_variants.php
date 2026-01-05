<?php 
if(isset($ProductData)){
    if($ProductData->stock_status=='Instock'){?>
<div id="mob-bottom-addtocart" class="viewpage-filter-option col-sm-12">
    <div class="column">
        <?php if ($ProductData->product_type=='configurable') {
                    if (isset($ProductData->product_variants)  && count($ProductData->product_variants)>0 && isset($ProductData->childProducts)  && count($ProductData->childProducts)>0) {?>
        <input type="hidden" id="variant_main_count" value="<?=count($ProductData->product_variants)?>">
        <?php
                        $variant_options = array();
                        foreach ($ProductData->product_variants as $variant) {
                            $variant_options=$variant->variant_options;
                            $variant_code=$variant->variant_code;
                            $variant_name=$variant->variant_name;
                            $attr_id=$variant->variant_id; ?>
        <input type="hidden" id="variant_option_count" value="<?=count($variant_options)?>">
        <div class="row">
            <?php $attr_value = '' ;?>
            <div class="col-sm-12">
                <?php if (isset($variant_options) && count($variant_options)>0) {
                                $attr_value = $variant_options[0]->attr_value;
                                    foreach ($variant_options as $vopt) {

                                        $value_attr = $vopt->attr_value;
                                        $attr_options_name = $vopt->attr_options_name;
                                        ?>
                <div class="single-input-field">
                    <input id="<?php echo $variant_code; ?>" name="variant_<?php echo $variant_code; ?>" type="radio"
                        class="form-control single_variant required-field"
                        <?php if($attr_value==$value_attr) echo "checked='checked'"; ?>
                        value="<?php echo $value_attr.",".$attr_id; ?>"
                        <?php echo $this->form_validation->set_radio('attr_value', "<?php echo $value_attr; ?>"); ?>
                    onclick="GetVariantProduct()"/>

                    <label for="variants" class=""><?php echo $attr_options_name; ?></label>
                    <label for="" class="">- Cover Price</label>
                    <label for="variants" class=""><?php echo $vopt->webshop_price; ?></label>
                    <?php if(isset($vopt->special_price) && $vopt->special_price!= ''){ ?>
                    <label for="" class="">- Offer Price</label>
                    <label for="" class=""><?php echo $vopt->special_price; ?></label>
                    <label for="" class=""><?php echo $vopt->off_percent_price; ?>% Off</label>
                    <?php } ?>
                    <label for="" class=""><?php echo $vopt->sub_issues; ?> Issues</label>
                    <label class="v-dash">|</label>
                    <label><strong>Gift:</strong></label>
                    <label for="" class=""><?php echo $vopt->gift_master_name; ?></label>
                </div>
                <?php }} ?>
            </div>
        </div>
        <?php }}} ?>
        <?php if($ProductData->product_type=='bundle') {
						$product_id_bundle_child = array();
						$bundle_child_id = array();
						foreach($ProductData->childProducts as $childProducts){
							if($childProducts->product_type =='configurable'){
								$bundle_child_id[] = $childProducts->bundle_child_id;?>
        <div class="row bundle-conf-border">
            <p class="col-sm-12 w-100 text-left"><?php echo $childProducts->name." X ".$childProducts->default_qty ?>
            </p>
            <?php $childProducts_variants = (new ProductDetails())->sortProductDataVariants($childProducts->product_variants);?>
            <input type="hidden" id="variant_main_count_<?php echo $childProducts->bundle_child_id?>"
                value="<?=count($childProducts_variants)?>">
            <input type="hidden" name="product_id_child_main[]" class="product_id_bundle"
                id="product_id_child_main_<?php echo $childProducts->bundle_child_id?>"
                value="<?php echo $childProducts->id?>">
            <input type="hidden" name="conf_simple_pid[]"
                id="conf_simple_pid_<?php echo $childProducts->bundle_child_id?>" value="">
            <input type="hidden" name="conf_simple_price[]"
                id="conf_simple_price_<?php echo $childProducts->bundle_child_id?>" value="">
            <input type="hidden" name="conf_simple_qty[]"
                id="conf_simple_qty_<?php echo $childProducts->bundle_child_id?>" value="">
            <input type="hidden" name="bundle_child_id[]" class="bundle_child_id"
                id="bundle_child_id_<?php echo $childProducts->bundle_child_id?>"
                value="<?php echo $childProducts->bundle_child_id?>">
            <?php
									$variant_options = array();
									foreach ($childProducts_variants as $variant) {
										$variant_options=$variant->variant_options;
										$variant_code=$variant->variant_code;
										$variant_name=$variant->variant_name;
										$attr_id=$variant->variant_id; 
                                        if ($variant_options == false){ ?>
            <input type="hidden" id="variant_option_count" value=0>

            <?php } else{?>
            <input type="hidden" id="variant_option_count" value="<?=count($variant_options)?>">
            <div class="col-sm-6">
                <!-- <label><?php echo $variant_name; ?></label> -->
                <?php $attr_value = '' ;?>
                <div class="col-sm-12">
                    <?php if (isset($variant_options) && count($variant_options)>0) {
                                                        $attr_value = $variant_options[0]->attr_value;
                                                        foreach ($variant_options as $vopt) { ?>
                    <input id="variant_<?php echo $variant_code; ?>_<?php echo $childProducts->bundle_child_id; ?>"
                        name="variant_<?php echo $variant_code; ?>[<?php echo $childProducts->bundle_child_id?>]"
                        type="radio"
                        class="form-control  single_variant_<?php echo $childProducts->bundle_child_id?> required-field"
                        data-bundle_child_id="<?php echo $childProducts->bundle_child_id; ?>"
                        <?php if($attr_value==$vopt->attr_value) echo "checked='checked'"; ?>
                        value="<?php echo $vopt->attr_value.",".$attr_id.",".$childProducts->default_qty; ?>"
                        <?php echo $this->form_validation->set_radio('attr_value', "<?php echo $vopt->attr_value; ?>");
                        ?>
                    onclick="GetVariantProductForBundle(<?php echo $childProducts->bundle_child_id?>)" />
                    <label for="variants" class=""><?php echo $vopt->attr_options_name; ?></label>
                    <label for="" class="">Cover Price : <?php echo $vopt->webshop_price; ?></label>
                    <label for="" class="">Special Price : <?php echo $childProducts->webshop_price; ?></label>
                    <label for="" class=""><?php echo $vopt->sub_issues; ?> Issues</label>
                    <label for="" class="">Gift:</label>
                    <label for="" class=""><?php echo $vopt->gift_master_name; ?></label>
                    <?php }
                                                 } ?>

                </div>
            </div>
            <?php } ?>
            <?php } ?>

        </div>

        <?php }else{
								$product_id_bundle_child[] = $childProducts->id;

								$variants='';
								$product_variants= ((isset($childProducts->variants) && $childProducts->variants != '') ? json_decode($childProducts->variants) : '');
								if (isset($product_variants) && $product_variants != '') {
									foreach ($product_variants as $pk=>$single_variant) {
										foreach ($single_variant as $key=>$val) {
											$variants.= $key.': '.$val.', ';
										}
									}
									$variants = rtrim($variants, ", ");

									$variants = '  ('.$variants.')';
								}?>
        <p class="col-sm-12 w-100 text-left">
            <?php echo $childProducts->name.$variants." X ".$childProducts->default_qty ?></p>
        <label for="" class=""> Cover Price</label>
        <label for="" class=""><?php echo $childProducts->webshop_price; ?></label>
        <label for="" class=""><?php echo $childProducts->sub_issues; ?> Issues</label>
        <label for="" class="">Gift:</label>
        <label for="" class=""><?php echo $childProducts->gift_master_name; ?></label>

        <?php } ?>

        <?php } ?>

        <input type='hidden' id="bundle-child-ids-merge" value="<?php echo implode(',',$bundle_child_id) ?>">

        <input type='hidden' id="bundle-child-ids" value="<?php echo implode(',',$product_id_bundle_child) ?>">

        <?php } ?>



        <div class="col-sm-6">

            <?php

                            $available_qty=$ProductData->total_qty;

                            if ($available_qty>$qty_limit) {

                                $available_qty=$qty_limit;

                            } 

                            elseif ($ProductData->product_type == 'bundle') {

								$available_qty=$qty_limit;

							}

                        ?>

            <!-- <label>Quantity</label>

                            <select class="form-control" name="quantity" id="quantity" <?php echo ($available_qty>0)?'':'disabled'; ?> onchange="ValidateQty(this.value,'<?php echo $ProductData->product_type; ?>');">

                            <?php for ($i=1;$i<=$available_qty;$i++) {?>

                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>

                            <?php } ?>

                            </select> -->

        </div>

        <?php if ($ProductData->product_type=='configurable') { ?>

        <input type="hidden" name="product_id" id="product_id" value="<?php echo $ProductData->id; ?>">
        <input type="hidden" name="conf_simple_pid" id="conf_simple_pid" value="">
        <input type="hidden" name="conf_simple_price" id="conf_simple_price" value="">
        <input type="hidden" name="conf_simple_qty" id="conf_simple_qty" value="">
        <!--                        <input type="hidden" name="total_variants" id="total_variants" value="--><?php //echo ($ProductData->product_type=='configurable')?count($ProductData->variant_master):'0'; ?>
        <!--">-->
        <input type="hidden" name="selected_variant_count" id="selected_variant_count" value="">
        <input type="hidden" name="quantity" id="quantity" value="1">
        <?php } else { ?>
        <input type="hidden" name="product_id" id="product_id" value="<?php echo $ProductData->id; ?>">
        <input type="hidden" name="quantity" id="quantity" value="1">
        <?php  } ?>





    </div>

    <div class="error " id="addtocart_error"></div>

</div><!-- viewpage-filter-option -->

<div class="product-action">

    <div class="col-sm-12">

        <?php  if (isset($ProductData->stock_status)) { ?>



        <?php 

                            if ($restricted_access == 'yes' && $customer_id ==0) {  ?>

        <a><button type="button" id="add_to_cart"
                <?php echo (isset($ProductData->stock_status) && ($ProductData->stock_status=='Instock'  && $ProductData->product_type=='simple'))?'':'disabled'; ?>
                onclick="openRestrictedAccessPopup()" class="add-to-cart-btn">Add to Cart</button></a>

        <?php } else {  

                            ?>

        <button class="add-to-cart-btn" id="add_to_cart" type="submit"
            <?php echo (isset($ProductData->stock_status) && ($ProductData->stock_status=='Instock' && ($ProductData->product_type=='simple' ||  $ProductData->product_type=='bundle') ))?'':'disabled'; ?>>Add
            to Cart</button>

        <a href="#mob-bottom-addtocart" class="mobile-botttom-cart"> Add to Cart </a>

        <?php } ?>

        <?php } else { ?>

        <button class="add-to-cart-btn" id="add_to_cart" type="submit" disabled>Out of Stock</button>

        <?php } ?>

        <?php if (isset($ProductData->estimate_delivery_time) && $ProductData->estimate_delivery_time!='') {?>

        <span class="delivery-time"><?=lang('delivery_in')?> <?php echo $ProductData->estimate_delivery_time; ?>
            Days</span>

        <?php } ?>

    </div>

</div><!-- product-action -->

<?php

        }else{

             if(isset($ProductData->coming_soon_flag) && $ProductData->coming_soon_flag==1){

?>

<div class="notified-border-box">

    <div class="">his product is not in our warehouse yet, we expect it to arrive soon!<br>Leave your email to be
        informed when it arrives!</div>

    <div class="keep-me-notify"><input class="form-control" type="text" name="email" id="notified-email"
            placeholder="Your email address" value="<?php echo $_SESSION['EmailID'] ?? ''; ?>"
            onkeyup="ValidateEmail();">

        <span id="lblError" class="notified-error"></span>

        <button type="button" class="btn keep-me-notified"
            onclick="openNotifiedPopup(<?php echo $ProductData->id; ?>)">Keep me informed</button>
    </div>

</div>

<?php

             }else{

?>

<?php if($ProductData->product_type=='bundle' && count($ProductData->bundle_childProduct_all) > 0){

			foreach($ProductData->bundle_childProduct_all as $child){ ?>

<p class="col-sm-12 w-100 text-left"><?php echo $child ?></p>

<?php } }?>

<div class="out-of-stock">Out Of Stock</div>

<?php

             }

        }

    }

?>



<?php if(isset($ProductData->bundle_childProduct_out_of_stock) && count($ProductData->bundle_childProduct_out_of_stock) > 0 ) { ?>

<div class="bundle_out_of_stock_text tw-font-bold tw-mt-2 tw-text-xs">

    <?php echo "(Out of Stock - ". implode(', ' , $ProductData->bundle_childProduct_out_of_stock).")"; ?>

</div>

<?php } ?>



<div id="WebShopNotifiedModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel"
    aria-hidden="true" data-backdrop="static" data-keyboard="false">

    <div class="modal-dialog modal-lg">

        <div class="modal-content" id="modal-content-notify">

            <div class="modal-header">

                <h4 class="modal-title" id="fullWidthModalLabel">Modal Heading</h4>

                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

            </div>

            <div class="modal-body">

                <center>
                    <div class="spinner-border text-primary" role="status"></div>
                </center>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>

                <button type="button" class="btn btn-primary">Save changes</button>

            </div>

        </div><!-- /.modal-content -->

        <!-- <input type="hidden" name="booklist_item_id" id="booklist_item_id" value="">

        <input type="hidden" name="subject_id" id="subject_id" value=""> -->

    </div><!-- /.modal-dialog -->

</div><!-- /.modal -->



<script type="text/javascript">
function ValidateEmail() {

    var email = document.getElementById("notified-email").value;

    var lblError = document.getElementById("lblError");

    lblError.innerHTML = "";

    var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

    if (!expr.test(email)) {

        lblError.innerHTML = "Invalid email address.";

    }

}



function openNotifiedPopup(product_id) {

    var email_notified = $("#notified-email").val();

    $('#lblError').html('');

    var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;



    if (email_notified != '' && expr.test(email_notified)) {

        if (product_id != '' && email_notified != '') {

            $.ajax({

                type: "POST",

                dataType: "html",

                url: BASE_URL + "ProductsController/productNotified",

                data: {
                    email: email_notified,
                    product_id: product_id
                },

                complete: function() {

                },

                beforeSend: function() {

                    // $('#ajax-spinner').show();

                },

                success: function(response) {

                    $("#WebShopNotifiedModal").modal();

                    $("#modal-content-notify").html(response);

                }

            });

        } else {

            return false;

        }

    } else {

        $('#lblError').html('Please enter valid email.');

        return false;

    }

}
</script>