<?php $this->load->view('common/header'); ?>



<div class="breadcrum-section">

    <div class="container">

        <div class="breadcrum">

            <ul>

                <li><a href="<?php echo base_url(); ?>">Home</a></li>

                <?php

					(new ProductDetails())->breadcrum($ProductData->id,'main',0);   //maincategory

					(new ProductDetails())->breadcrum($ProductData->id,'main',1);   //subcategory

				?>

                <li><span class="icon icon-keyboard_arrow_right"></span></li>

                <li class="active">
                    <?php echo ((isset($ProductData->other_lang_name) && $ProductData->other_lang_name!='') ? $ProductData->other_lang_name : $ProductData->name); ?>
                </li>

                <input type="hidden" name="product_type" id="product_type"
                    value="<?php echo $ProductData->product_type; ?>">



            </ul>

        </div>

    </div>

</div><!-- breadcrum section -->



<div class="product-details-page">

    <div class="container">

        <?php if ($this->session->userdata('addtocart_error')) { ?>

        <div class="alert alert-danger" role="alert">

            <?php echo $this->session->userdata('addtocart_error'); ?>

        </div>

        <?php $this->session->unset_userdata('addtocart_error'); } ?>

        <div class="col-md-12">

            <div class="row">

                <div class="product-image-section col-md-5 col-lg-5" id="product-image-section">

                    <?php $this->load->view('product/media_gallery'); ?>

                </div> <!-- product-image-section  -->



                <div class="product-details col-md-7 col-lg-7">

                    <form name="product-frm" id="product-frm" method="POST"
                        action="<?php echo base_url(); ?>CartController/addtocart">

                        <input type="hidden" name="media_variant_id" id="media_variant_id"
                            value="<?php echo  $ProductData->media_variant_id; ?>">

                        <input type="hidden" name="product_type" id="product_type"
                            value="<?php echo  $ProductData->product_type; ?>">

                        <div class="product-name">

                            <h1><?php echo ((isset($ProductData->other_lang_name) && $ProductData->other_lang_name!='') ? $ProductData->other_lang_name : $ProductData->name);?>
                            </h1>



                        </div><!-- product-name -->



                        <div class="product-info-ratings">

                            <?php

                            if (isset($ProductData->ratingDetails[0]) && ($ProductData->ratingDetails[0]->total_rating_count>0)) { ?>

                            <div class="ratings"> <span
                                    class="rating-percentage"><?php echo round($ProductData->ratingDetails[0]->average_rating_start); ?></span> <?php

                            echo $ProductData->ratingDetails[0]->total_rating_count; ?> <?=lang('ratings')?></div>

                            <?php } ?>

                            <!-- <div class="sku-no">Product Code <?php echo $ProductData->product_code; ?></div> -->



                            <?php if ((isset($ProductData->publication_name) && $ProductData->publication_name!='')) { ?>

                            <div class="sku-no">

                                <Label> Publisher: </Label>

                                <Label><?php echo $ProductData->publication_name; ?></Label>

                            </div>

                            <?php } ?>



                            <?php if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions)>0) {

                            		foreach ($ProductData->AttributesWithOptions as $attr) { ?>

                            <div class="sku-no">

                                <Label><?php echo $attr->attr_name; ?>: </Label>

                                <Label><?php echo $attr->attr_value; ?></Label>

                            </div>

                            <?php }

								} ?>



                            <!-- <div class="add-your-view"><a id="rv-tabs" href="#ratings-review">Add Your Review</a></div> -->

                            <div
                                class="stock-status <?php echo (isset($ProductData->stock_status) && ($ProductData->stock_status=='Instock' ))?'green':'text-danger'; ?>">
                                <?php echo (isset($ProductData->stock_status) && ($ProductData->stock_status=='Instock'))?lang('in_stock'):lang('out_of_stock'); ?>
                            </div>

                        </div><!-- product-info-ratings -->

                        <?php if($ProductData->product_type == 'simple') {?>

                        <div class="product-price-detail">

                            <!-- <?php (new ProductDetails())->productDetailsPrice($ProductData,'price');?> -->

                            <label for="" class="">- Cover Price</label>

                            <label for="variants" class=""><?php echo $ProductData->webshop_price; ?></label>

                            <?php if(isset($ProductData->special_price) && $ProductData->special_price!= ''){ ?>

                            <label for="" class="">- Offer Price</label>

                            <label for="" class=""><?php echo $ProductData->special_price; ?></label>

                            <label for="" class=""><?php echo $ProductData->off_percent_price; ?>% Off</label>

                            <?php }  ?>





                            <label for="" class="">Gift:</label>



                            <label for="" class=""><?php echo $ProductData->gift_master_name; ?></label>



                            <label for="" class=""><?php echo $ProductData->sub_issues; ?> Issues</label>



                        </div>
                        <!--product-price -->

                        <?php } elseif ($ProductData->product_type == 'bundle') {?>

                        <label for="" class=""><?php echo $ProductData->webshop_price; ?></label>

                        <?php }?>

                        <?php

                        (new ProductDetails())->productStockVariant($ProductData,$CategoryIds);

						?>







                        <div class="product-desp">

                            <p><?php echo ((isset($ProductData->other_lang_highlights) && $ProductData->other_lang_highlights !='') ? $ProductData->other_lang_highlights : $ProductData->highlights); ?>
                            </p>

                        </div><!-- product-desp -->



                        <?php

                        // (new ProductDetails())->productStockVariant($ProductData,$CategoryIds);

						?>

                        <div id="addtocart-message" class="addtocart-message"></div>

                        <div class="product-share">

                            <div class="col-sm-12">

                                <div class="share">

                                    <div class="share-cta">

                                        <span class="edit-details share-button" data-html="true" data-placement="bottom"
                                            data-toggle="popover" data-container="body" style="cursor: pointer;"><span
                                                class="icon-share2"></span>Share Link</span>

                                    </div>

                                </div>

                                <?php

										(new ProductDetails())->wishlist($ProductData->id,$customer_id);



								?>

                            </div>

                        </div><!-- product-action -->

                    </form>

                </div> <!-- product-image-section  -->

            </div><!-- row -->

        </div><!-- col-md-12 -->



        <ul class="nav nav-tabs product-details-tab-panel" id="product-nav-tab"
            <?php echo (empty($ProductData->description) && (is_array($ProductData->specification ) === false && empty($ProductData->specification)) && (is_array($ProductData->AttributesWithOptions) === false && empty($ProductData->AttributesWithOptions))) ? 'style=display:none;' : ''; ?>>

            <?php if(!empty($ProductData->description)){ ?>

            <li class="active"><a class="active show" data-toggle="tab" href="#descriptions">Descriptions</a></li>

            <?php } ?>

            <?php

				if ((is_array($ProductData->specification) && count($ProductData->specification)>0) || (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions)>0) ) {?>

            <li><a data-toggle="tab" href="#specifications">Specifications</a></li>

            <?php } ?>

        </ul>





        <div class="tab-content"
            <?php echo (empty($ProductData->description) && (is_array($ProductData->specification) === false && empty($ProductData->specification)) && (is_array($ProductData->AttributesWithOptions) === false && empty($ProductData->AttributesWithOptions))) ? 'style=display:none;' : ''; ?>>

            <?php if(!empty($ProductData->description)){ ?>

            <div id="descriptions" class="tab-pane fade in active" style="opacity:1;">

                <div class="descriptions-section">

                    <?php echo ((isset($ProductData->other_lang_description) && $ProductData->other_lang_description !='') ? $ProductData->other_lang_description : $ProductData->description); ?>

                </div>



            </div><!-- descriptions -->

            <?php } ?>

            <?php

				(new ProductDetails())->productSpecifications($ProductData);

			?>

        </div><!-- tab-content -->



        <div id="ratings-review" class="ratings-review-sec tab-content">

            <?php $this->load->view('product/customer_reviews'); ?>

        </div><!-- ratings-review -->



        <div class="page-nect-prev-action">

            <?php if (isset($prev_url) && $prev_url!='') { ?>

            <a href="<?php echo $prev_url; ?>" class="prev-page"><img
                    src="<?php echo TEMP_SKIN_IMG ?>/prev-enable.png"><span>Prev Product</span></a>

            <?php } else { ?>

            <a class="prev-page"><img src="<?php echo TEMP_SKIN_IMG ?>/prev-disabled.png"><span>Prev Product</span></a>

            <?php } ?>

            <?php if (isset($next_url) && $next_url!='') { ?>

            <a href="<?php echo $next_url; ?>" class="next-page"><img
                    src="<?php echo TEMP_SKIN_IMG ?>/next-btn.png"><span>Next Product</span></a>

            <?php } else { ?>

            <a class="next-page"><img src="<?php echo TEMP_SKIN_IMG ?>/next-btn-disabled.png"><span>Next
                    Product</span></a>

            <?php } ?>

        </div>



    </div><!-- container -->

</div><!-- product-detail-page -->



<input type="text" value="<?php echo base_url('product-detail/' .$this->uri->segment(2)); ?>" id="myClick"
    style="display:none;">

<div class="d-none" id="sharepop">

    <div class="sharepopover" style="padding-bottom: 30px;">

        <ul class="social-share-promote" style="list-style: none">

            <li class="fb-btn">

                <a href="https://www.facebook.com/sharer/sharer.php?u="><i class="icon-facebook"
                        aria-hidden="true"></i></a>

            </li>

            <li class="tw-btn">

                <a href="https://www.twitter.com/share?url="><i class="icon-twitter" aria-hidden="true"></i></a>

            </li>

            <li class="lnk-btn">

                <a href="https://www.linkedin.com/shareArticle?mini=true&url="><i class="icon-linkedin"
                        aria-hidden="true"></i></a>

            </li>

            <li class="wa-btn">

                <?php if(is_firefox()): ?>

                <a href="whatsapp://send?text="><i class="icon-whatsapp" aria-hidden="true"></i></a>

                <?php else: ?>

                <a href="https://wa.me/?text="><i class="icon-whatsapp" aria-hidden="true"></i></a>

                <?php endif; ?>



            </li>

            <li id="cp-btn">

                <a><i class="icon-copy copy-link" aria-hidden="true"></i></a>

            </li>

        </ul>

    </div>

</div>



<!-- product view zoomer and slider js and css start -->

<script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js"></script>

<script src="<?php echo SKIN_JS; ?>jquery.exzoom.js"></script>

<link href="<?php echo SKIN_CSS; ?>jquery.exzoom.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
$(document).ready(function($) {

    var product_type = $('#product_type').val();

    if (product_type == 'bundle') {

        $(".bundle_child_id").each(function(i) {

            var bundle_child_id = $(this).val();

            GetVariantProductForBundle(bundle_child_id);

        });

    } else {

        if (product_type == 'configurable') {

            GetVariantProduct();

            return false;

        }

        // var countMainVariant=$('#variant_main_count').val();

        // var countVariant=$('#variant_option_count').val();

        // if(countMainVariant==1 && countVariant==1){

        // 	GetVariantProduct();

        // 	return false;

        // }else if(countVariant==1){

        // 	GetVariantProduct();

        // 	return false;

        // }

    }



});
</script>



<!-- product view zoomer and slider js and css end -->

<?php $this->load->view('common/footer'); ?>

<script src="<?php echo SKIN_JS ?>product.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script src="<?php echo SKIN_JS ?>social_share.js"></script>

<script>
//$('html, body').animate({ scrollTop: $(".custom-checkbox").offset().top }, 'slow');



function GetVariantProductForBundle(bundle_child_id) {

    $('#addtocart_error').html('');

    $('#add_to_cart').attr('disabled', true);

    var selected_variant = [];

    var count_variant = $('.single_variant_' + bundle_child_id).length;

    var variant_count = [];

    var product_id = $('#product_id_child_main_' + bundle_child_id).val();

    var className = '.single_variant_' + bundle_child_id;

    var value_variants = $('.single_variant_' + bundle_child_id + ':checked').val();



    var array_attr = value_variants.split(',');

    if (array_attr.length > 0) {



        var attr_value = array_attr[0];

        var attr_id = array_attr[1];

        var default_qty = array_attr[2];



        selected_variant.push({
            attr_id,
            attr_value
        });

        variant_count.push({
            attr_id,
            attr_value
        });



        var total_selected_variant = variant_count.length;

        if (total_selected_variant != '')

        {

            $.ajax({

                url: BASE_URL + "ProductsController/getVariantProduct",

                type: "POST",

                dataType: "json",

                cache: false,

                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },

                data: {

                    product_id: product_id,

                    total_variant: total_selected_variant,

                    selected_variant: selected_variant,

                    media_variant_id: '',

                },

                success: function(response) {

                    if (response.status == 200) {

                        const simple_product_id = response.ConfigSimpleDetails.conf_simple_pro_id;

                        const simple_price = parseFloat(response.ConfigSimpleDetails.conf_simple_pro_pice)
                            .toFixed(2);

                        var simple_qty = response.ConfigSimpleDetails.conf_simple_pro_inventory.qty;

                        const simple_qty_status = response.ConfigSimpleDetails.conf_simple_pro_inventory
                            .status;



                        const qty_limit = response.qty_limit;



                        if (response.mediaGallery != '') {

                            $('#product-image-section').html(response.mediaGallery);

                        }

                        if (simple_qty_status === 'instock' && simple_qty >= default_qty) {



                            $('#conf_simple_pid_' + bundle_child_id).val(simple_product_id);

                            $('#conf_simple_price_' + bundle_child_id).val(simple_price);

                            $('#conf_simple_qty_' + bundle_child_id).val(simple_qty);



                            var string = $('#bundle-child-ids-merge').val();

                            var bundle_child_ids = string.split(',');

                            var flag = 0;



                            $.each(bundle_child_ids, function(key, value) {

                                var id = $('#conf_simple_pid_' + value).val();

                                if ($('#conf_simple_pid_' + value).val() == '') {

                                    flag = 1

                                }

                            });



                            if (flag == 0) {

                                $('#add_to_cart').attr('disabled', false);

                                $('#addtocart_error').html('');

                            } else {

                                $('#add_to_cart').attr('disabled', true);

                                $('#addtocart_error').html('Not all values selected');

                            }



                        } else {

                            $('#quantity').val(1);

                            $('#quantity').attr('disabled', true);

                            $('#add_to_cart').attr('disabled', true);

                            $('#addtocart_error').html('Product is out of stock.');

                            return false;

                        }

                    } else {

                        $('#conf_simple_pid_' + bundle_child_id).val('');

                        $('#conf_simple_price_' + bundle_child_id).val('');

                        $('#conf_simple_qty_' + bundle_child_id).val('');

                        $('#add_to_cart').attr('disabled', true);

                        $('#addtocart_error').html(response.message);

                        return false;

                    }



                }

            });



        } else {

            return false;

        }



    }









}





function GetVariantProduct() {





    $('#addtocart_error').html('');

    $('#add_to_cart').attr('disabled', true);

    var selected_variant = [];

    var variant_count = [];

    var product_id = $('#product_id').val();



    var media_variant_id = $('#media_variant_id').val();



    var value_variants = $('.single_variant:checked').val();



    var array_attr = value_variants.split(',');



    if (array_attr.length > 0) {



        var attr_value = array_attr[0];

        var attr_id = array_attr[1];

        selected_variant.push({
            attr_id,
            attr_value
        });



        variant_count.push({
            attr_id,
            attr_value
        });



        var total_selected_variant = variant_count.length;

        if (total_selected_variant != '')

        {

            $.ajax({

                url: BASE_URL + "ProductsController/getVariantProduct",

                type: "POST",

                dataType: "json",

                cache: false,

                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },

                data: {

                    product_id: product_id,

                    total_variant: total_selected_variant,

                    selected_variant: selected_variant,

                    media_variant_id: media_variant_id,

                    //   isPrelaunch:isPrelaunch

                },

                success: function(response) {

                    if (response.status == 200) {





                        const simple_product_id = response.ConfigSimpleDetails.conf_simple_pro_id;

                        const simple_price = parseFloat(response.ConfigSimpleDetails.conf_simple_pro_pice)
                            .toFixed(2);

                        const simple_qty = response.ConfigSimpleDetails.conf_simple_pro_inventory.qty;

                        const simple_qty_status = response.ConfigSimpleDetails.conf_simple_pro_inventory
                            .status;

                        const special_price = response.ConfigSimpleDetails.special_price;

                        const display_original = response.ConfigSimpleDetails.display_original;

                        const qty_limit = response.qty_limit;



                        if (response.mediaGallery != '') {

                            $('#product-image-section').html(response.mediaGallery);

                        }

                        if (simple_qty_status === 'instock') {



                            $('#add_to_cart').attr('disabled', false);

                            $('#conf_simple_pid').val(simple_product_id);

                            $('#conf_simple_price').val(simple_price);

                            $('#conf_simple_qty').val(simple_qty);



                            if (special_price > 0) {

                                if (display_original > 0 && display_original == 1) {

                                    if (parseInt(special_price) < parseInt(simple_price)) {

                                        var percent = (((special_price - simple_price) / simple_price) *
                                            100);

                                        var percent_ceil = Math.round(percent);

                                        var final_percentage = percent_ceil.toFixed(0);



                                        var addition_percent_html = '';

                                        if (final_percentage != 0.00) {

                                            var addition_percent_html =
                                                '<span class="price save-discount">(' + final_percentage +
                                                '%)</span>';

                                        }



                                        // if (currency_code_session !== '' && default_currency_flag  !== '1'){

                                        // 	simple_price_convert = converted_price(simple_price,currency_conversion_rate);

                                        // 	special_price_convert = converted_price(special_price,currency_conversion_rate);



                                        // 	$('.product-price-detail').html('<span class="discounted-price special-price" id="product_price">'+currency_symbol +'&nbsp;'+simple_price_convert+'</span> <span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>'+addition_percent_html+'');



                                        // }else{

                                        $('.product-price-detail').html(
                                            '<span class="discounted-price special-price" id="product_price">' +
                                            CURRENCY_TYPE + '&nbsp;' + numberWithCommas(simple_price) +
                                            '</span> <span class="special-price" id="discounted_price">' +
                                            CURRENCY_TYPE + '&nbsp;' + numberWithCommas(special_price) +
                                            '</span>' + addition_percent_html + '');

                                        // }

                                    } else {

                                        // if (currency_code_session !== '' && default_currency_flag  !== '1'){

                                        // 	special_price_convert = converted_price(special_price,currency_conversion_rate);



                                        // 	$('.product-price-detail').html('<span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>');

                                        // }else{

                                        $('.product-price-detail').html(
                                            '<span class="special-price" id="discounted_price">' +
                                            CURRENCY_TYPE + '&nbsp;' + numberWithCommas(special_price) +
                                            '</span>');

                                        // }

                                    }

                                } else {

                                    // if (currency_code_session !== '' && default_currency_flag  !== '1'){

                                    // 	special_price_convert = converted_price(special_price,currency_conversion_rate);



                                    // 	$('.product-price-detail').html('<span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>');

                                    // }else{

                                    $('.product-price-detail').html(
                                        '<span class="special-price" id="discounted_price">' +
                                        CURRENCY_TYPE + '&nbsp;' + numberWithCommas(special_price) +
                                        '</span>');

                                    // }

                                }

                            } else {

                                // if (currency_code_session !== '' && default_currency_flag  !== '1'){

                                // 	simple_price_convert = converted_price(simple_price,currency_conversion_rate);



                                // 	$('.product-price-detail').html('<span class="special-price here" id="product_price">'+currency_symbol +'&nbsp;'+simple_price_convert+'</span>');



                                // }else{

                                $('.product-price-detail').html(
                                    '<span class="special-price" id="product_price">' + CURRENCY_TYPE +
                                    '&nbsp;' + numberWithCommas(simple_price) + '</span>');

                                // }

                            }



                            var j = 0;

                            var qty_html_refresh = '';

                            if (simple_qty > 0) {

                                if (simple_qty > qty_limit) {

                                    simple_qty = qty_limit;

                                }

                                for (j = 1; j <= simple_qty; j++) {

                                    qty_html_refresh += '<option value="' + j + '">' + j + '</option>';

                                }

                                $('#quantity').attr('disabled', false);



                            }





                            $('#quantity').html(qty_html_refresh);



                        } else {

                            $('#quantity').html('');

                            $('#quantity').attr('disabled', true);

                            $('#add_to_cart').attr('disabled', true);

                            $('#addtocart_error').html('Product is out of stock.');

                            //swal('Error','Product is out of stock.','error');

                            return false;

                        }

                    } else {

                        $('#add_to_cart').attr('disabled', true);

                        $('#addtocart_error').html(response.message);

                        //swal('Error',response.message,'error');

                        return false;

                    }



                }

            });





        } else {

            return false;

        }

    } else {

        return false;



    }



}



function converted_price(price, currency_conversion_rate) {



    let new_price = price * currency_conversion_rate;

    new_price = new_price.toFixed(2);

    new_price = numberWithCommas(new_price);



    return new_price;

}





function numberWithCommas(number) {

    var parts = number.toString().split(".");

    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");

    return parts.join(".");

}



function ValidateQty(value, ptype) {

    $('#addtocart_error').html('');

    if (ptype == 'configurable') {

        var conf_simple_price = $('#conf_simple_price').val();

        if (conf_simple_price != '' || conf_simple_price > 0) {

            var conf_simple_qty = $('#conf_simple_qty').val();



            if (parseInt(conf_simple_qty) < parseInt(value))

            {

                $('#add_to_cart').attr('disabled', true);

                $('#addtocart_error').html('This Product qty ' + value + ' is not available.');

                //swal('Error','This Product qty '+value+' is not available.','error');

                return false;

            } else {

                $('#add_to_cart').attr('disabled', false);

            }

        }

    }



    if (ptype == 'bundle') {

        validateBundleQty(value);

    }

}



function validateBundleQty(value) {



    var bundle_chid = $('#bundle-child-ids').val();

    var product_id = $('#product_id').val();

    var conf_simple_array = [];

    var string = $('#bundle-child-ids-merge').val();



    if (string != '') {

        var bundle_child_ids = string.split(',');

        $.each(bundle_child_ids, function(key, value) {

            var product_id_child_main = $('#product_id_child_main_' + value).val();

            var conf_simple_pid = $('#conf_simple_pid_' + value).val();

            conf_simple_array.push({
                "bundle_child_id": value,
                "product_id_child_main": product_id_child_main,
                "conf_simple_pid": conf_simple_pid
            });

        });

    }



    $.ajax({

        url: BASE_URL + "ProductsController/getBundleChildValidateQty",

        type: "POST",

        dataType: "json",

        data: {

            product_id: product_id,

            bundle_chid_ids: bundle_chid,

            qty: value,

            conf_simple_array: conf_simple_array

        },

        success: function(response) {

            if (response.status == 200) {

                $('#add_to_cart').attr('disabled', false);

                $('#addtocart_error').html('');

            } else {

                $('#add_to_cart').attr('disabled', true);

                $('#addtocart_error').html('This Product qty ' + value + ' is not available.');

                return false;

            }

        }

    });

}





$(document).ready(function() {

    //Open

    $("#product-nav-tab a").each(function(i) { // Loop through all the links

        if (document.location.hash == $(this).attr(
                "href")) { // Compare the value from the url with the id

            $(this).tab('show'); // If equal add class active

        }

    });



    // $('#rv-tabs').click(function(){

    // var hrf = $(this).attr("href");

    // $('#product-nav-tab a[href="' + hrf + '"]').tab('show');

    // });



    $("#product-frm").validate({



        ignore: [],

        debug: false,

        rules: {

            product_name: {

                required: true,

            },

        },

        errorPlacement: function(error, element) {

            error.insertAfter(element);

        },

        messages: {

            quantity: {

                required: "Product quantity is required."

            }

        },

        submitHandler: function(form) {

            $('#addtocart_error').html('');

            $('#add_to_cart').attr('disabled', true);



            var form_obj = $('#product-frm')[0];



            var formData = new FormData(form_obj);





            $.ajax({

                url: form.action,

                type: 'ajax',

                method: form.method,

                dataType: 'json',

                //enctype: 'multipart/form-data',

                processData: false, // Important!

                contentType: false,

                cache: false,

                data: formData,

                beforeSend: function() {

                    $('#ajax-spinner').show();

                },

                success: function(response) {

                    $('#add_to_cart').attr('disabled', false);

                    $('#ajax-spinner').hide();



                    if (response.status == 200) {

                        $("#addtocart-message")

                            .html('<span class="success-msg">' + response.message +
                                '</span>')

                            .show();

                        setTimeout(function() {

                            $("#addtocart-message").hide();

                        }, 3000);



                        $.ajax({

                            type: 'POST',

                            url: BASE_URL + 'CartController/updateminicart',

                            dataType: 'html',

                            data: {},

                            success: function(response) {

                                $("#mini-cart-main-container").html(
                                    response);

                            }

                        });



                        return false;



                    } else {

                        $("#addtocart-message")

                            .html('<span class="error-msg">' + response.message +
                                '</span>')

                            .show();



                        setTimeout(function() {

                            $("#addtocart-message").hide();

                        }, 3000);

                        return false;

                    }



                }

            });

        }

    });



    $('.required-field').each(function() {

        $(this).rules("add",

            {

                required: true,

                messages: {

                    required: "Field is required",

                }

            });

    });





});
</script>



</body>

</html>