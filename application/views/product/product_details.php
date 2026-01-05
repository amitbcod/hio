<?php $this->load->view('common/header'); ?>

<style>
    .badge-section {
        display: flex;
        flex-wrap: nowrap;
        /* ❌ prevent wrapping */
        gap: 10px;
        margin-top: 10px;
        align-items: center;
        /* vertical align */
    }

    .product-badge {
        width: 80px;
        /* adjust as needed */
        height: auto;
    }
</style>

<?php
//echo "<pre>";
$lang = $this->session->userdata('site_lang');
//print_r($ProductData); 

?>
<div class="main">
    <div class="container">
        <ul class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><?php echo $this->lang->line('home'); ?></a></li>

            <?php
            (new ProductDetails())->breadcrum($ProductData->id, 'main', 0);
            (new ProductDetails())->breadcrum($ProductData->id, 'main', 1);
            ?>
            <li class="active">
                <?php echo ((isset($ProductData->other_lang_name) && $ProductData->other_lang_name != '') ? $ProductData->other_lang_name : $ProductData->name); ?>
            </li>
        </ul>

        <div class="row margin-bottom-40">
            <div class="col-md-12">
                <div class="product-page pad20">
                    <div class="row">

                        <?php if ($this->session->userdata('addtocart_error')) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $this->session->userdata('addtocart_error'); ?>
                            </div>
                            <?php $this->session->unset_userdata('addtocart_error');
                        } ?>

                        <div class="col-md-12">
                            <h1 class="product-name">
                                <?php
                                if ($lang == 'french' && !empty($ProductData->lang_title)) {
                                    echo $ProductData->lang_title;
                                } else {
                                    echo $ProductData->name;
                                }
                                ?>
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
                                    <li class="list-group-item"><b><?php echo $this->lang->line('merchant'); ?>:</b>
                                        <?php echo $ProductData->publication_name; ?></li>
                                <?php } ?>

                                <?php if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0) {
                                    foreach ($ProductData->AttributesWithOptions as $attr) { ?>
                                        <?php if ($attr->attr_value) { ?>
                                            <li class="list-group-item"><b><?php echo $attr->attr_name ?>:</b>
                                                <?php echo $attr->attr_value ?></li>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>

                                <?php
                                $categoryIds = $CatApiResponse->CategoryIds->cat_ids;
                                $categoryArray = explode(',', $categoryIds);

                                if (in_array('8', $categoryArray) || (isset($ProductData) && ($ProductData->id == 3914 || $ProductData->id == 3388))) {
                                    ?>
                                    <li class="list-group-item" style="position: relative;">
                                        <span style="position: absolute;top: 4px; left: -5px;color: #ef0d08;">* </span>
                                        <b><?php echo $this->lang->line('shipping_charges'); ?>:</b>
                                        <span><?php echo $this->lang->line('shipping_info'); ?></span>
                                    </li>
                                <?php } ?>
                            </ul>

                            <?php if (
                                $ProductData->made_in_maurituus_approval_status == "approve" ||
                                $ProductData->social_empowerment_approval_status == "approve" ||
                                $ProductData->environment_friendly_approval_status == "approve" ||
                                $ProductData->health_friendly_approval_status == "approve"
                            ) { ?>
                                <div class="badge-section pull-right">
                                    <?php if ($ProductData->made_in_maurituus_approval_status == "approve") { ?>
                                        <img src="<?php echo base_url('public/images/badge_made_in_mauritius.png'); ?>"
                                            alt="Made in Mauritius" class="product-badge" />
                                    <?php } ?>

                                    <?php if ($ProductData->social_empowerment_approval_status == "approve") { ?>
                                        <img src="<?php echo base_url('public/images/badge_social_empowerment.png'); ?>"
                                            alt="Social Empowerment" class="product-badge" />
                                    <?php } ?>

                                    <?php if ($ProductData->environment_friendly_approval_status == "approve") { ?>
                                        <img src="<?php echo base_url('public/images/badge_environment_friendly.png'); ?>"
                                            alt="Environment Friendly" class="product-badge" />
                                    <?php } ?>

                                    <?php if ($ProductData->health_friendly_approval_status == "approve") { ?>
                                        <img src="<?php echo base_url('public/images/badge_health_friendly.png'); ?>"
                                            alt="Health Friendly" class="product-badge" />
                                    <?php } ?>
                                </div>
                            <?php } ?>

                            <form name="product-frm" id="product-frm" method="POST"
                                action="<?php echo base_url(); ?>CartController/addtocart">
                                <input type="hidden" name="media_variant_id" id="media_variant_id"
                                    value="<?php echo $ProductData->media_variant_id; ?>">
                                <input type="hidden" name="product_type" id="product_type"
                                    value="<?php echo $ProductData->product_type; ?>">

                                <?php if ($ProductData->product_type == 'simple') { ?>
                                    <div class="table-responsive product-price">
                                        <table class="table" width="100%" cellpadding="3" cellspacing="1" border="0">
                                            <thead>
                                                <tr class="active">
                                                    <th><?php echo $this->lang->line('eshop_price'); ?></th>
                                                    <?php if (!empty($ProductData->special_price)) { ?>
                                                        <th><?php echo $this->lang->line('special_price'); ?></th>
                                                    <?php } ?>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <tr>
                                                    <?php if (!empty($ProductData->special_price)) { ?>
                                                        <!-- Eshop price with strikethrough -->
                                                        <td class="cover-price" style="text-decoration: line-through;">
                                                            <?php echo $ProductData->webshop_price; ?>
                                                        </td>

                                                        <!-- Special price with badge on right -->
                                                        <td class="offer-price">
                                                            <div
                                                                style="display: flex; justify-content: space-between; align-items: center;">
                                                                <!-- Left: price + discount -->
                                                                <span>
                                                                    <?php
                                                                    $offPercentPrice = '';
                                                                    $excludedIds = [];
                                                                    if (!in_array($ProductData->id, $excludedIds)) {
                                                                        $cal1 = ($ProductData->webshop_price - $ProductData->special_price) / $ProductData->webshop_price;
                                                                        $off_percent_price = round($cal1 * 100);
                                                                        if ($off_percent_price > 0) {
                                                                            $offPercentPrice = ' <span>(' . $off_percent_price . '% ' . $this->lang->line('off') . ')</span>';
                                                                        }
                                                                    }
                                                                    echo round($ProductData->special_price) . $offPercentPrice;
                                                                    ?>
                                                                </span>

                                                                <!-- Right: badge -->
                                                                <div class="right-badge">
                                                                    <?php
                                                                    $avg = round($rating->avg_rating); // average rating
                                                                    $badge = '';
                                                                    if ($avg >= 3) {
                                                                        if ($avg == 3) {
                                                                            $badge = IMAGE_URL . '/uploads/trust_badges/ym_trusted_badges_3_stars.svg';
                                                                        } elseif ($avg == 4) {
                                                                            $badge = IMAGE_URL . '/uploads/trust_badges/ym_trusted_badges_4_stars.svg';
                                                                        } elseif ($avg == 5) {
                                                                            $badge = IMAGE_URL . '/uploads/trust_badges/ym_trusted_badges_5_stars.svg';
                                                                        }
                                                                    }
                                                                    if ($badge): ?>
                                                                        <img src="<?= $badge; ?>"
                                                                            alt="<?php echo $this->lang->line('trust_badge'); ?>"
                                                                            width="100" class="badge-product-details">
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    <?php } else { ?>
                                                        <!-- Only normal price -->
                                                        <td class="cover-price">
                                                            <div
                                                                style="display: flex; justify-content: space-between; align-items: center;">
                                                                <?php echo $ProductData->webshop_price; ?>
                                                                <!-- Right: badge -->
                                                                <div class="right-badge">
                                                                    <?php
                                                                    $avg = round($rating->avg_rating); // average rating
                                                                    $badge = '';
                                                                    if ($avg >= 3) {
                                                                        if ($avg == 3) {
                                                                            $badge = IMAGE_URL . '/uploads/trust_badges/ym_trusted_badges_3_stars.svg';
                                                                        } elseif ($avg == 4) {
                                                                            $badge = IMAGE_URL . '/uploads/trust_badges/ym_trusted_badges_4_stars.svg';
                                                                        } elseif ($avg == 5) {
                                                                            $badge = IMAGE_URL . '/uploads/trust_badges/ym_trusted_badges_5_stars.svg';
                                                                        }
                                                                    }
                                                                    if ($badge): ?>
                                                                        <img src="<?= $badge; ?>"
                                                                            alt="<?php echo $this->lang->line('trust_badge'); ?>"
                                                                            width="100" class="badge-product-details">
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    <?php } ?>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>



                                <?php } ?>



                                <?php (new ProductDetails())->productStockVariant($ProductData, $CategoryIds); ?>



                                <?php if (isset($ProductData->coming_soon_flag) && $ProductData->coming_soon_flag == 1) { ?>
                                    <div class="notifywrap">
                                        <div class="notifyhd">
                                            <i class="fa fa-exclamation-circle fa-2x"></i>
                                            <span><?= $this->lang->line('out_of_stock_label'); ?></span>
                                        </div>
                                        <div class="notifyfrm">
                                            <p>
                                                <?= $this->lang->line('out_of_stock_message'); ?>
                                            </p>
                                            <div class="form-inline" id="keepnotified">
                                                <div class="form-group">
                                                    <label class="sr-only"
                                                        for="notified-email"><?= $this->lang->line('email_address_label'); ?></label>
                                                    <input type="email" class="form-control" id="notified-email"
                                                        placeholder="<?= $this->lang->line('email_placeholder'); ?>"
                                                        value="<?php echo $_SESSION["EmailID"] ?? ""; ?>"
                                                        onkeyup="ValidateEmail();">
                                                </div>
                                                <button type="button" id="notified-keep-inform" class="btn btn-primary"
                                                    onclick="openNotifiedPopup(<?php echo $ProductData->id; ?>)">
                                                    <?= $this->lang->line('keep_me_informed_btn'); ?>
                                                </button>
                                            </div>
                                            <span id="lblError" class="notified-error"></span>
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="subscribe-deli">
                                    <?php if (isset($ProductData->stock_status) && ($ProductData->stock_status == 'Instock')) { ?>
                                        <?php if ($restricted_access == "yes" && $customer_id == 0) { ?>
                                            <button type="button" class="btn btn-primary subscr-now pull-left" <?php echo isset($ProductData->stock_status) &&
                                                ($ProductData->stock_status == "Instock" && $ProductData->product_type == "simple")
                                                ? ""
                                                : "disabled"; ?>
                                                onclick="openRestrictedAccessPopup()">
                                                <?= $this->lang->line('add_to_cart_btn'); ?>
                                            </button>
                                            <?php if (isset($ProductData->estimate_delivery_time) && !empty($ProductData->estimate_delivery_time)) { ?>
                                                <p class="pull-right"><b>
                                                        <?= $this->lang->line('delivery_duration_label'); ?>
                                                        <?php echo $ProductData->estimate_delivery_time; ?>
                                                        <?= $this->lang->line('days_label'); ?>
                                                    </b></p>
                                                <div class="clearfix"></div>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <button type="submit" id="add_to_cart" class="btn btn-primary subscr-now pull-left">
                                                <?= $this->lang->line('add_to_cart_btn'); ?>
                                            </button>
                                            <button type="button" id="askQuestionBtn" class="btn btn-outline-primary">Ask a
                                                Question</button>

                                            <?php if (isset($ProductData->estimate_delivery_time) && !empty($ProductData->estimate_delivery_time)) { ?>
                                                <p class="pull-right"><b>
                                                        <?= $this->lang->line('expected_delivery_label'); ?>
                                                        <?php echo $ProductData->estimate_delivery_time; ?>
                                                        <?= $this->lang->line('days_label'); ?>
                                                    </b></p>
                                                <div class="clearfix"></div>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <button class="btn btn-primary subscr-now pull-left" id="add_to_cart" type="submit"
                                            disabled>
                                            <?= $this->lang->line('out_of_stock_label'); ?>
                                        </button>
                                    <?php } ?>
                                    <div id="addtocart-message" class="addtocart-message"></div>
                                </div>

                                <ul id="myTab" class="nav nav-tabs">
                                    <?php if (!empty($ProductData->description)) { ?>
                                        <li class="active"><a href="#Description"
                                                data-toggle="tab"><?= $this->lang->line('description_tab'); ?></a></li>
                                    <?php } ?>
                                    <?php if ((is_array($ProductData->specification) && count($ProductData->specification) > 0) || (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0)) { ?>
                                        <li><a href="#Specifications"
                                                data-toggle="tab"><?= $this->lang->line('specifications_tab'); ?></a></li>
                                    <?php } ?>
                                </ul>

                                <div id="myTabContent" class="tab-content">
                                    <div class="tab-pane fade in active" id="Description">
                                        <?php
                                        if ($lang == 'french' && !empty($ProductData->lang_description)) {
                                            echo $ProductData->lang_description;
                                        } else {
                                            echo $ProductData->description;
                                        }
                                        ?>
                                    </div>
                                    <div class="tab-pane fade" id="Specifications">
                                        <table class="datasheet">
                                            <tr>
                                                <th colspan="2"><?= $this->lang->line('additional_features_label'); ?>
                                                </th>
                                            </tr>
                                            <?php if (isset($ProductData->publication_name) && !empty($ProductData->publication_name)) { ?>
                                                <tr>
                                                    <td class="datasheet-features-type">
                                                        <?= $this->lang->line('merchant_label'); ?></td>
                                                    <td><?php echo $ProductData->publication_name; ?></td>
                                                </tr>
                                            <?php } ?>
                                            <?php if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions) > 0) {
                                                foreach ($ProductData->AttributesWithOptions as $attr) {
                                                    if (isset($attr->attr_value) && !empty($attr->attr_value)) { ?>
                                                        <tr>
                                                            <td class="datasheet-features-type"><?php echo $attr->attr_name; ?></td>
                                                            <td><?php echo $attr->attr_value; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                        </table>
                                    </div>
                                </div>

                            </form>
                            <!-- Ask a Question Modal -->
                            <div class="modal fade" id="askQuestionModal" tabindex="-1"
                                aria-labelledby="askQuestionLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title mb-0" id="askQuestionLabel">Ask a Question</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">
                                            <form id="askQuestionForm">
                                                <input type="hidden" name="product_id" value="<?= $ProductData->id ?>">
                                                <input type="hidden" name="merchant_id"
                                                    value="<?= $ProductData->publisher_id ?>">
                                                <input type="hidden" name="customer_id" value="<?= $customer_id ?>">

                                                <div class="mb-3">
                                                    <label for="questionName" class="form-label">Name</label>
                                                    <?php if (!empty($customer_id)): ?>
                                                        <input type="text" class="form-control" id="questionName"
                                                            name="name"
                                                            value="<?= htmlspecialchars($customer_name, ENT_QUOTES, 'UTF-8'); ?>"
                                                            readonly>
                                                    <?php else: ?>
                                                        <input type="text" class="form-control" id="questionName"
                                                            name="name" placeholder="Enter your name" required>
                                                    <?php endif; ?>
                                                </div>


                                                <?php if (!isset($customer_id) || empty($customer_id) || $customer_id == 0): ?>
                                                    <div class="mb-3">
                                                        <label for="questionEmail" class="form-label">Email</label>
                                                        <input type="email" class="form-control" id="questionEmail"
                                                            name="email" placeholder="Enter your email" required>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="mb-3">
                                                    <label for="questionCategory" class="form-label">Category</label>
                                                    <select class="form-select" id="questionCategory" name="category"
                                                        required>
                                                        <option value="">Select Category</option>
                                                        <option value="Merchant">Merchant Delivery</option>
                                                        <option value="General Enquiry">Enquiry - Product(s)</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="questionMessage" class="form-label">Message</label>
                                                    <textarea class="form-control" id="questionMessage" name="message"
                                                        rows="4" required></textarea>
                                                </div>

                                                <button type="submit" class="btn btn-primary w-100">Save</button>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

</div>
</div>


        <?php $this->load->view('common/footer'); ?>



        <script>

            $(document).ready(function ($) {
                $('#askQuestionBtn').on('click', function () {
                    $('#askQuestionModal').modal('show');
                });

                $('#askQuestionForm').on('submit', function (e) {
                    e.preventDefault();

                    $.ajax({
                        url: BASE_URL + 'ProductsController/saveQuestion',
                        type: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function (response) {
                            if (response.flag == 1) {
                                swal({
                                    title: "",
                                    icon: "success",
                                    text: response.msg,
                                    buttons: false,
                                    timer: 1000
                                }).then(() => {
                                    $('#askQuestionForm')[0].reset();
                                    location.reload();
                                });
                            } else {
                                swal({
                                    title: "Error",
                                    icon: "error",
                                    text: response.msg
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error(error);
                            alert('Something went wrong!');
                        }
                    });
                });

                document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const modalEl = btn.closest('.modal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();
                    });
                });



                var product_type = $("#product_type").val();



                if (product_type == "bundle") {

                    $(".bundle_child_id").each(function (i) {

                        var bundle_child_id = $(this).val();



                        GetVariantProductForBundle(bundle_child_id);

                    });

                } else {

                    if (product_type == "configurable") {

                        GetVariantProduct();



                        return false;

                    }

                }

            });
        </script>



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



                            complete: function () {



                            },



                            beforeSend: function () {

                                $('#notified-keep-inform').prop("disabled", true);

                                // $('#ajax-spinner').show();



                            },



                            success: function (response) {

                                Swal.fire({

                                    title: 'KEEP ME NOTIFIED',

                                    html: '<label>You have been successfully subscribed to updates of this product.</label>',

                                    icon: 'success'

                                });

                                $('#notified-keep-inform').prop("disabled", false);

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



            function GetVariantProduct() {

                $("#addtocart_error").html("");

                $("#add_to_cart").prop("disabled", true);

                var selected_variant = [];

                var variant_count = [];

                var product_id = $("#product_id").val();



                var media_variant_id = $("#media_variant_id").val();



                // var value_variants = $(".single_variant:checked").val();

                var value_variants = $('input[name="variant_options_data"]:checked').val();

                var conf_simple_id = value_variants;





                // console.log("VariantsOptionData: ",$('input[name="variant_options_data"]:checked').val());

                // return;



                var array_attr = value_variants.split(",");

                var conf_simple_id = array_attr[0];

                var product_id = array_attr[1];





                if (array_attr.length > 0) {

                    var attr_value = array_attr[0];

                    var attr_id = array_attr[1];

                    selected_variant.push({

                        attr_id,

                        attr_value,

                    });

                    variant_count.push({

                        attr_id,

                        attr_value,

                    });



                    var total_selected_variant = variant_count.length;



                    if (conf_simple_id != "") {

                        $.ajax({

                            url: BASE_URL + "ProductsController/getVariantProductNew",

                            type: "POST",

                            dataType: "json",

                            cache: false,

                            headers: {

                                "X-Requested-With": "XMLHttpRequest",

                            },

                            data: {

                                parent_id: product_id,

                                product_id: conf_simple_id,

                                total_variant: total_selected_variant,

                                selected_variant: selected_variant,

                                media_variant_id: media_variant_id,

                                //   isPrelaunch:isPrelaunch

                            },



                            success: function (response) {



                                if (response.status == 200) {

                                    // alert(response.qty_limit);

                                    const simple_product_id =

                                        response.ConfigSimpleDetails.conf_simple_pro_id;



                                    const simple_price = parseFloat(

                                        response.ConfigSimpleDetails.conf_simple_pro_pice

                                    ).toFixed(2);



                                    const simple_qty =

                                        response.ConfigSimpleDetails.conf_simple_pro_inventory.qty;



                                    const simple_qty_status =

                                        response.ConfigSimpleDetails.conf_simple_pro_inventory.status;



                                    const special_price = response.ConfigSimpleDetails.special_price;



                                    const display_original =

                                        response.ConfigSimpleDetails.display_original;





                                    const qty_limit = response.qty_limit;







                                    if (response.mediaGallery != "") {

                                        $("#product-image-section").html(response.mediaGallery);

                                    }



                                    if (simple_qty_status === "instock") {

                                        $("#add_to_cart").prop("disabled", false);



                                        $("#conf_simple_pid").val(simple_product_id);



                                        $("#conf_simple_price").val(simple_price);



                                        $("#conf_simple_qty").val(simple_qty);



                                        if (special_price > 0) {

                                            if (display_original > 0 && display_original == 1) {

                                                if (parseInt(special_price) < parseInt(simple_price)) {

                                                    var percent =

                                                        ((special_price - simple_price) / simple_price) * 100;



                                                    var percent_ceil = Math.round(percent);



                                                    var final_percentage = percent_ceil.toFixed(0);



                                                    var addition_percent_html = "";



                                                    if (final_percentage != 0.0) {

                                                        var addition_percent_html =

                                                            '<span class="price save-discount">(' +

                                                            final_percentage +

                                                            "%)</span>";

                                                    }



                                                    // if (currency_code_session !== '' && default_currency_flag  !== '1'){



                                                    // 	simple_price_convert = converted_price(simple_price,currency_conversion_rate);



                                                    // 	special_price_convert = converted_price(special_price,currency_conversion_rate);



                                                    // 	$('.product-price-detail').html('<span class="discounted-price special-price" id="product_price">'+currency_symbol +'&nbsp;'+simple_price_convert+'</span> <span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>'+addition_percent_html+'');



                                                    // }else{



                                                    $(".product-price-detail").html(

                                                        '<span class="discounted-price special-price" id="product_price">' +

                                                        CURRENCY_TYPE +

                                                        "&nbsp;" +

                                                        numberWithCommas(simple_price) +

                                                        '</span> <span class="special-price" id="discounted_price">' +

                                                        CURRENCY_TYPE +

                                                        "&nbsp;" +

                                                        numberWithCommas(special_price) +

                                                        "</span>" +

                                                        addition_percent_html +

                                                        ""

                                                    );



                                                    // }

                                                } else {

                                                    // if (currency_code_session !== '' && default_currency_flag  !== '1'){



                                                    // 	special_price_convert = converted_price(special_price,currency_conversion_rate);



                                                    // 	$('.product-price-detail').html('<span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>');



                                                    // }else{



                                                    $(".product-price-detail").html(

                                                        '<span class="special-price" id="discounted_price">' +

                                                        CURRENCY_TYPE +

                                                        "&nbsp;" +

                                                        numberWithCommas(special_price) +

                                                        "</span>"

                                                    );



                                                    // }

                                                }

                                            } else {

                                                // if (currency_code_session !== '' && default_currency_flag  !== '1'){



                                                // 	special_price_convert = converted_price(special_price,currency_conversion_rate);



                                                // 	$('.product-price-detail').html('<span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>');



                                                // }else{



                                                $(".product-price-detail").html(

                                                    '<span class="special-price" id="discounted_price">' +

                                                    CURRENCY_TYPE +

                                                    "&nbsp;" +

                                                    numberWithCommas(special_price) +

                                                    "</span>"

                                                );



                                                // }

                                            }

                                        } else {

                                            // if (currency_code_session !== '' && default_currency_flag  !== '1'){



                                            // 	simple_price_convert = converted_price(simple_price,currency_conversion_rate);



                                            // 	$('.product-price-detail').html('<span class="special-price here" id="product_price">'+currency_symbol +'&nbsp;'+simple_price_convert+'</span>');



                                            // }else{



                                            $(".product-price-detail").html(

                                                '<span class="special-price" id="product_price">' +

                                                CURRENCY_TYPE +

                                                "&nbsp;" +

                                                numberWithCommas(simple_price) +

                                                "</span>"

                                            );



                                            // }

                                        }



                                        var j = 0;



                                        var qty_html_refresh = "";



                                        if (simple_qty > 0) {

                                            if (simple_qty > qty_limit) {

                                                simple_qty = qty_limit;

                                            }



                                            for (j = 1; j <= simple_qty; j++) {

                                                qty_html_refresh +=

                                                    '<option value="' + j + '">' + j + "</option>";

                                            }



                                            $("#quantity").attr("disabled", false);

                                        }



                                        $("#quantity").html(qty_html_refresh);

                                    } else {

                                        $("#quantity").html("");



                                        $("#quantity").attr("disabled", true);



                                        $("#add_to_cart").prop("disabled", true);



                                        $("#addtocart_error").html("Product is out of stock.");



                                        //swal('Error','Product is out of stock.','error');



                                        return false;

                                    }

                                } else {

                                    $("#add_to_cart").prop("disabled", true);



                                    $("#addtocart_error").html(response.message);



                                    //swal('Error',response.message,'error');



                                    return false;

                                }

                            },

                        });

                    } else {

                        return false;

                    }

                } else {

                    return false;

                }

            }



            function GetVariantProductForBundle(bundle_child_id) {



                $("#addtocart_error").html("");



                $("#add_to_cart").attr("disabled", true);



                var selected_variant = [];



                var count_variant = $(".single_variant_" + bundle_child_id).length;



                var variant_count = [];



                var product_id = $("#product_id_child_main_" + bundle_child_id).val();



                var className = ".single_variant_" + bundle_child_id;



                var value_variants = $(

                    ".single_variant_" + bundle_child_id + ":checked"

                ).val();



                var array_attr = value_variants.split(",");





                if (array_attr.length > 0) {

                    var attr_value = array_attr[0];



                    var attr_id = array_attr[1];



                    var default_qty = array_attr[2];



                    selected_variant.push({

                        attr_id,

                        attr_value,

                    });



                    variant_count.push({

                        attr_id,

                        attr_value,

                    });



                    var total_selected_variant = variant_count.length;



                    if (total_selected_variant != "") {

                        $.ajax({

                            url: BASE_URL + "ProductsController/getVariantProduct",



                            type: "POST",



                            dataType: "json",



                            cache: false,



                            headers: {

                                "X-Requested-With": "XMLHttpRequest",

                            },



                            data: {

                                product_id: product_id,



                                total_variant: total_selected_variant,



                                selected_variant: selected_variant,



                                media_variant_id: "",

                            },



                            success: function (response) {



                                if (response.status == 200) {

                                    const simple_product_id =

                                        response.ConfigSimpleDetails.conf_simple_pro_id;



                                    const simple_price = parseFloat(

                                        response.ConfigSimpleDetails.conf_simple_pro_pice

                                    ).toFixed(2);



                                    var simple_qty =

                                        response.ConfigSimpleDetails.conf_simple_pro_inventory.qty;



                                    const simple_qty_status =

                                        response.ConfigSimpleDetails.conf_simple_pro_inventory.status;



                                    const qty_limit = response.qty_limit;



                                    if (response.mediaGallery != "") {

                                        $("#product-image-section").html(response.mediaGallery);

                                    }



                                    if (simple_qty_status === "instock" && simple_qty >= default_qty) {

                                        $("#conf_simple_pid_" + bundle_child_id).val(simple_product_id);



                                        $("#conf_simple_price_" + bundle_child_id).val(simple_price);



                                        $("#conf_simple_qty_" + bundle_child_id).val(simple_qty);



                                        var string = $("#bundle-child-ids-merge").val();



                                        var bundle_child_ids = string.split(",");





                                        var flag = 0;



                                        $.each(bundle_child_ids, function (key, value) {

                                            var id = $("#conf_simple_pid_" + value).val();



                                            if ($("#conf_simple_pid_" + value).val() == "") {

                                                flag = 1;

                                            }

                                        });



                                        if (flag == 0) {

                                            $("#add_to_cart").attr("disabled", false);



                                            $("#addtocart_error").html("");

                                        } else {

                                            $("#add_to_cart").attr("disabled", true);



                                            $("#addtocart_error").html("Not all values selected");

                                        }

                                    } else {

                                        $("#quantity").val(1);



                                        $("#quantity").attr("disabled", true);



                                        $("#add_to_cart").attr("disabled", true);



                                        $("#addtocart_error").html("Product is out of stock.");



                                        return false;

                                    }

                                } else {

                                    $("#conf_simple_pid_" + bundle_child_id).val("");



                                    $("#conf_simple_price_" + bundle_child_id).val("");



                                    $("#conf_simple_qty_" + bundle_child_id).val("");



                                    $("#add_to_cart").attr("disabled", true);



                                    $("#addtocart_error").html(response.message);



                                    return false;

                                }

                            },

                        });

                    } else {

                        return false;

                    }

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



            function validateBundleQty(value) {

                var bundle_chid = $("#bundle-child-ids").val();



                var product_id = $("#product_id").val();



                var conf_simple_array = [];



                var string = $("#bundle-child-ids-merge").val();



                if (string != "") {

                    var bundle_child_ids = string.split(",");



                    $.each(bundle_child_ids, function (key, value) {

                        var product_id_child_main = $("#product_id_child_main_" + value).val();



                        var conf_simple_pid = $("#conf_simple_pid_" + value).val();



                        conf_simple_array.push({

                            bundle_child_id: value,

                            product_id_child_main: product_id_child_main,

                            conf_simple_pid: conf_simple_pid,

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



                        conf_simple_array: conf_simple_array,

                    },



                    success: function (response) {

                        if (response.status == 200) {

                            $("#add_to_cart").attr("disabled", false);



                            $("#addtocart_error").html("");

                        } else {

                            $("#add_to_cart").attr("disabled", true);



                            $("#addtocart_error").html(

                                "This Product qty " + value + " is not available."

                            );



                            return false;

                        }

                    },

                });

            }







            $(document).ready(function () {





                $("#product-frm").validate({



                    ignore: [],



                    debug: false,



                    rules: {

                        product_name: {

                            required: true,

                        },

                    },



                    errorPlacement: function (error, element) {

                        error.insertAfter(element);

                    },



                    messages: {

                        quantity: {

                            required: "Product quantity is required.",

                        },

                    },



                    submitHandler: function (form) {



                        $("#addtocart_error").html("");



                        $("#add_to_cart").attr("disabled", true);



                        var form_obj = $("#product-frm")[0];



                        var formData = new FormData(form_obj);



                        $.ajax({

                            url: form.action,



                            type: "ajax",



                            method: form.method,



                            dataType: "json",



                            //enctype: 'multipart/form-data',



                            processData: false, // Important!



                            contentType: false,



                            cache: false,



                            data: formData,



                            beforeSend: function () {



                                $("#ajax-spinner").show();

                            },



                            success: function (response) {



                                $("#add_to_cart").attr("disabled", false);



                                $("#ajax-spinner").hide();



                                if (response.status == 200) {

                                    $("#addtocart-message")

                                        .html('<span class="success-msg">' + response.message + "</span>")



                                        .show();



                                    setTimeout(function () {

                                        $("#addtocart-message").hide();

                                    }, 3000);



                                    $.ajax({

                                        type: "POST",



                                        url: BASE_URL + "CartController/updateminicart",



                                        dataType: "html",



                                        data: {},



                                        success: function (response) {

                                            $("#mini-cart-main-container").html(response);

                                            $(".top-cart-content").css('display', 'block');



                                        },

                                    });



                                    return false;

                                } else {

                                    $("#addtocart-message")

                                        .html('<span class="error-msg">' + response.message + "</span>")



                                        .show();



                                    setTimeout(function () {

                                        $("#addtocart-message").hide();

                                    }, 3000);



                                    return false;

                                }

                            },

                        });

                    },

                });



                $(".required-field").each(function () {

                    $(this).rules(

                        "add",



                        {

                            required: true,



                            messages: {

                                required: "Field is required",

                            },

                        }

                    );

                });



            });
        </script>



        <script>
            $(document).ready(function () {

                var $container = $('.badge-section');

                var imageCount = $container.find('img').length;



                if (imageCount > 3) {

                    $container.removeClass('three-per-row').addClass('two-rows');

                } else {

                    $container.removeClass('two-rows').addClass('three-per-row');

                }

            });
        </script>