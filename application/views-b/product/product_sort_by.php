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
			$prod->current_category_id=$current_category_id;
			$prod->search_term=$search_term ?? '';
			$page_sort_type=$page_sort_type ?? 'Listing';
            $prod = ProductPresenter::from($prod);
            $prod_image=$prod->product_image('thumb');

			(new ProductList())->productListData($prod,$prod_image,$page_sort_type);
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
			$prod->current_category_id=$current_category_id;
			$prod->search_term=$search_term ?? '';
			$page_sort_type=$page_sort_type ?? 'Listing';
            $prod = ProductPresenter::from($prod);
            $prod_image=$prod->product_image('thumb');

			(new ProductList())->productListData($prod,$prod_image,$page_sort_type);
        ?>

		<?php } ?>
	</ul>
	<?php } ?>
</div><!-- product-list-view -->
<?php
if ($this->ajax_pagination->create_links()) {
        echo $this->ajax_pagination->create_links();
    }
?>

<script src="<?=base_url('public/js/recliner.js')?>"></script>

  <script type="text/javascript">
   $(function() {

            // instantiate recliner
            $('.lazy').recliner({
                attrib: "data-src", // selector for attribute containing the media src
                throttle: 300,      // millisecond interval at which to process events
                threshold: 100,     // scroll distance from element before its loaded
                live: true          // auto bind lazy loading to ajax loaded elements
            });

            // handle lazyload events
            $(document).on('lazyload', '.lazy', function() {
                var $e = $(this);
                // do something with the element to be loaded...
               // console.log('lazyload', $e);
            });

            // handle lazyshow events
            $(document).on('lazyshow', '.lazy', function() {
                var $e = $(this);
                // do something with the loaded element...
              //  console.log('lazyshow', $e);
            });
        });
  </script>
