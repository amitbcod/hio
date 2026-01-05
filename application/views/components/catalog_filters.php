<?php

$productListCount = $catalogFilter->productCatalogFilter->product_list_count;
// $product_list_count = count($product_list);
$productListCount = json_decode(json_encode($productListCount), true);
// echo "<pre>";
// print_r($productListCount);die;


$attributeListing = $catalogFilter->productCatalogFilter->attribute_listing->language__Language;

$min_price_rng = 0;
$max_price_rng = 0;
$variant_val_arry = array();
$attr_val_arry = array();
if (isset($_GET['variantVal'])) {
        $variant_val_arry = explode(",", $_GET['variantVal']);
}
if (isset($_GET['attribute'])) {
        $attr_val_arry = explode(",", $_GET['attribute']);
}
$catqalog_filter = $catalogFilter->productCatalogFilter;
?>

<?php if (isset($catqalog_filter->variant_listing) && !empty($catqalog_filter->variant_listing)) { ?>
        <h2 id="language_heading">Filter</h2>
        <ul class="list-group margin-bottom-25 sidebar-menu">
                <?php foreach ($catqalog_filter->variant_listing as $key => $variant) {
                        $string = str_replace(' ', '', $key);
                        $attCodeKeyData = explode('__', $key);
                        if (isset($attCodeKeyData) && !empty($attCodeKeyData)) {
                                $key = ucfirst($attCodeKeyData[0]);
                                $keyName = $attCodeKeyData[1];
                        } ?>
                        <li class="list-group-item clearfix dropdown <?php echo $key; ?>">
                                <a href="#">
                                        <i class="fa fa-angle-right"></i>
                                        <?php echo $key; ?>
                                </a>
                                <ul class="dropdown-menu">
                                        <?php foreach ($variant as $varnt) { ?>
                                                <li class="list-group-item clearfix ">
                                                        <div class="col-lg-8 checkbox-list">
                                                                <label>
                                                                        <input type="checkbox" name="variant_chk[]" value="<?php echo $varnt->attr_value; ?>" <?php echo (in_array($varnt->attr_value, $variant_val_arry) ? 'checked' : '') ?> class="chk-variant">
                                                                        <?php echo $varnt->attr_options_name; ?>

                                                                        <input type="hidden" <?php echo (in_array($varnt->attr_value, $variant_val_arry) ? 'checked' : '') ?> id="variant_attr_<?php echo $varnt->attr_value; ?>" value="<?php echo $varnt->variant_id; ?>">
                                                                </label>
                                                        </div>
                                                </li>
                                        <?php } ?>
                                </ul>
                        </li>
                <?php } ?>
                <?php foreach ($catqalog_filter->attribute_listing as $key1 => $attribute) {
                        $count = 0;
                        if ($key1 == 'language__Language') {
                                $string1 = str_replace(' ', '', $key1);
                                $attCodeKeyData = explode('__', $key1);
                                if (isset($attCodeKeyData) && !empty($attCodeKeyData)) {
                                        $key = ucfirst($attCodeKeyData[0]);
                                        $keyName = $attCodeKeyData[1];
                                } ?>
                                <li class="list-group-item clearfix dropdown" id="language_dropdown">
                                        <a href="#">
                                                <i class="fa fa-angle-right"></i>
                                                <?php echo $keyName; ?>
                                        </a>
                                        <ul class="dropdown-menu" style="display: block;">
                                                <?php foreach ($attribute as $varnt) {
                                                        $attr_val__ = $varnt->attr_value;

                                                        if (array_key_exists($attr_val__, $productListCount)) {
                                                                $count = $productListCount[$attr_val__][0]['count'];
                                                        }
                                                ?>
                                                        <li class="list-group-item clearfix ">
                                                                <div class="col-lg-8 checkbox-list">
                                                                        <label>
                                                                                <input type="checkbox" name="attribute_chk[]" value="<?php echo $varnt->attr_value; ?>" <?php echo (in_array($varnt->attr_value, $variant_val_arry) ? 'checked' : '') ?> class="chk-variant">
                                                                                <?php echo $varnt->attr_options_name; ?> (<?php echo $count; ?>)

                                                                                <input type="hidden" <?php echo (in_array($varnt->attr_value, $attr_val_arry) ? 'checked' : '') ?> id="attribute_attr_<?php echo $varnt->attr_value; ?>" value="<?php echo $varnt->variant_id; ?>">
                                                                        </label>
                                                                </div>
                                                        </li>
                                                <?php } ?>
                                        </ul>
                                </li>
                        <?php } ?>
                <?php } ?>
        </ul>
<?php } ?>

<script src="<?php echo SKIN_JS ?>product.js?v=<?php echo CSSJS_VERSION; ?>"></script>


<script>
        var attr_options_name = "<?= $attributeListing[0]->attr_options_name ?>";
</script>