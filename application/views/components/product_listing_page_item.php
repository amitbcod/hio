<?php 
// echo "<pre>";
// print_r($prod); 
// die;
$lang = $this->session->userdata('site_lang');
if ($lang == 'french' && !empty($prod->lang_title)) {
    $display_name = $prod->lang_title;
} else {
    $display_name = $prod->name;
}
 ?>

<?php if ($check == 'owlView') {

    echo "<div>";

} ?>

<div class="product-item" title="<?php echo ((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $prod->name); ?>">

    <div class="pi-img-wrapper">

        <a href="<?= $product_url ?>">

            <img src="<?php echo $prod_image; ?>" class="img-responsive" title="<?php echo ((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $display_name); ?>" alt="<?php echo ((isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $display_name); ?>">

        </a>

        <div>

            <a href="javascript:QuickViewProdDetails('<?php echo $prod->url_key; ?>','<?= $product_url ?>')" class="btn btn-default"><?= lang('view_label') ?></a>

        </div>

    </div>

    <div class="pi-content-wrapper">

        <?php

        $productname = (isset($prod->other_lang_name) && $prod->other_lang_name != '') ? $prod->other_lang_name : $prod->name;

        ?>

        <h3 id="product-name-<?php echo $prod->id; ?>" title="<?php echo $display_name; ?>">
            <a href="<?= $product_url ?>">
                <?php echo (strlen($display_name) > 30) ? substr($display_name, 0, 28) . '...' : $display_name; ?>
            </a>
        </h3>



        <div class="pi-price"><?php $prod->display_list_price(); ?></div>



        <?php if ($type === 'PrelaunchListing') : ?>

            <?php if ($prod->product_type === 'simple') { ?>

                <a href="<?= $product_url ?>" data-prelaunch="yes" <?php echo $prod->product_type; ?> data-product-id="<?php echo $prod->id; ?>" data-qty="1" class="btn btn-default add2cart"><?= lang('view_details') ?></a>

            <?php } else { ?>

                <a href="javascript:;" onclick="gotoLocation('<?php echo BASE_URL . 'product-detail/' . $prod->url_key . '?type=prelaunch'; ?>');" class="btn btn-default add2cart"><?= lang('view_details') ?></a>

            <?php } ?>

        <?php else : ?>

            <?php if (isset($prod->stock_status)) { ?>

                <?php if ($prod->stock_status == 'Instock' && ($prod->product_type == 'simple' || $prod->product_type == 'conf-simple')) { ?>

                    <?php if (isset($restricted_access) && $restricted_access == 'yes' && $customer_id == 0) { ?>

                        <a href="javascript:;" id="add_to_cart" onclick="openRestrictedAccessPopup()" class="btn btn-default add2cart"><?= lang('add_to_cart') ?></a>

                    <?php } else { ?>

                        <a href="<?= $product_url ?>" <?php echo $prod->product_type; ?> data-product-id="<?php echo $prod->id; ?>" data-qty="1" class="btn btn-default add2cart"><?= lang('view_details') ?></a>

                    <?php } ?>

                <?php } elseif ($prod->stock_status == 'Instock' && ($prod->product_type == 'configurable' || $prod->product_type == 'bundle')) { ?>

                    <a href="<?= $product_url ?>" class="btn btn-default add2cart"><?= lang('view_details') ?></a>

                <?php } ?>

            <?php } else { ?>

                <a href="<?= $product_url ?>" class="btn btn-default add2cart"><?= lang('view_details') ?></a>

            <?php } ?>

        <?php endif; // prelaunchlisting ?>



        <div id="addtocart-message-<?php echo $prod->id; ?>" class="addtocart-message addtocart-message-<?php echo $prod->id; ?>"></div>

    </div>

</div>

<?php if ($check == 'owlView') {

    echo "</div>";

} ?>

