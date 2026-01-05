<?php $customer_id = isset($_SESSION['LoginID']) ? $_SESSION['LoginID']:0; ?>
<?php
    $currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
    $currency_symbol = $this->session->userdata('currency_symbol');
    $default_currency_flag = $this->session->userdata('default_currency_flag');
?>

<div class="product-grid-listing-view" style="<?php echo ($current_viewmode=='grid-view')?'display:block;':'display:none;'?>">
	<?php if (isset($product_list->is_success) && $product_list->is_success == 'true') { ?>
	<ul>
		<?php foreach ($product_list->ProductList as $prod) {
            $prod = ProductPresenter::from($prod);
            $prod_image=$prod->product_image('thumb');

			(new ProductList())->productListData($prod,$prod_image,'NewArrivalListing','grid');
        ?>

		<?php } ?>
	</ul>
	<?php } else { ?>
        <h2><?=lang('coming_soon')?></h2>
	<?php } ?>
</div><!-- product-grid-listing-view -->

<!-- product-listing-view -->
<div class="product-listing-view" style="<?php echo ($current_viewmode=='list-view')?'display:block;':'display:none;' ?>">
	<?php if (isset($product_list->is_success) && $product_list->is_success == 'true') { ?>
	<ul>
        <?php foreach ($product_list->ProductList as $prod) {
            $prod = ProductPresenter::from($prod);
            $prod_image=$prod->product_image('thumb');

			(new ProductList())->productListData($prod,$prod_image,'NewArrivalListing','list');
        ?>

		<?php } ?>
	</ul>
	<?php } ?>
</div><!-- product-list-view -->


