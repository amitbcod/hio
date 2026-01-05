<?php $this->load->view('common/header'); ?>
<?php $customer_id = isset($_SESSION['LoginID']) ? $_SESSION['LoginID']:0; ?>
<?php
	$min_price_rng = 0;
	$max_price_rng = 0;

	$currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
	$currency_symbol = $this->session->userdata('currency_symbol');
	$default_currency_flag = $this->session->userdata('default_currency_flag');

   $gender_selected_arry  = array();
	if (isset($_GET['gender'])) {
		$gender_selected_arry = explode(",", $_GET['gender']);
	}
?>
<div class="breadcrum-section">
   <div class="container">
      <div class="breadcrum">
         <ul>
            <li><a href="<?php echo BASE_URL;?>"><?=lang('home')?></a></li>
            <li><span class="icon icon-keyboard_arrow_right"></span></li>
            <li><?=lang('featured_products')?></li>
         </ul>
      </div>
   </div>
</div>
<!-- breadcrum section -->
<div class="product-listing-page" id="listing-prd-main">
   <div class="container">
      <div class="col-md-12">
         <div class="row">
            <div class="col-md-3 col-lg-3 ">
               <div class="filter-section">
                  <h2><?=lang('filter')?> <span class="mob-filter-add"><span class="icon-arrow_drop_down"></span>
                     <span style="display:none;" class="icon-arrow_drop_up"></span></span>
                  </h2>
                  <div class="left-filter">
                     <?php (new TopMenu('categorymenu',1))->render(); ?>
                  </div>
                  <?php
                     if (isset($customVariable->is_success) && $customVariable->is_success=='true') {
                        if ($customVariable->custom_variable->value == 'yes') {
				      ?>
                     <div class="filter-inner-section shop-for-filter">
                        <h3 data-toggle="collapse" data-target="#filterByShopFor" class="collapsed" aria-expanded="false"><?=lang('shop_for')?><span class="icon-arrow_drop_down"></span></h3>
                        <ul class="collapse" id="filterByShopFor">
                           <li><label class="container-checkbox">
                           <input <?php echo(in_array('Women', $gender_selected_arry)?'checked':'')?> type="checkbox" name="gender[]" value="Women" class="chk-gender"><?=lang('women')?><span class="checkmark"></span>
                           </label></li>
                           <li><label class="container-checkbox"><input <?php echo(in_array('Men', $gender_selected_arry)?'checked':'')?> type="checkbox" name="gender[]" value="Men" class="chk-gender"><?=lang('men')?><span class="checkmark"></span></label></li>
                           <li><label class="container-checkbox"><input <?php echo(in_array('Children', $gender_selected_arry)?'checked':'')?> type="checkbox" name="gender[]" value="Children" class="chk-gender"><?=lang('children')?><span class="checkmark"></span></label></li>
                           <li><label class="container-checkbox"><input <?php echo(in_array('Unisex', $gender_selected_arry)?'checked':'')?> type="checkbox" name="gender[]" value="Unisex" class="chk-gender"><?=lang('unisex')?><span class="checkmark"></span></label></li>
                        </ul>
						   </div>
                  <?php
                     }
                     }
                  ?>
                  <?php
                     if (!empty($product_list) && (isset($product_list->statusCode) && $product_list->statusCode == '200')) {
                        (new CatalogFilters('','newarrival',''))->render();
                     }
				      ?>
                  <!-- left-filter-->
               </div>
               <!-- filter-section -->
            </div>
            <!-- col-sm-3 -->
            <div class="col-md-9 col-lg-9 ">
               <div class="categories-top">
                  <h2>
                     <?= lang('featured_products') ?>
                  </h2>
                  <div class="right-sorting-option">
                     <div class="sort-by-cat no-sort">
                        <?=lang('show')?>
                        <select id="show-limit_custom">
                           <?php if (isset($show_limit) && $show_limit > 0) { ?>
                           <?php foreach ($show_limit as $limt) { ?>
                           <option <?php echo ($show_limit_selected==$limt)?'selected':'' ?> value="<?php echo $limt; ?>"><?php echo $limt; ?></option>
                           <?php } } ?>
                        </select>
                     </div>
                     <!-- sort-by-cat -->
                     <div class="sort-by-cat popularity-option">
                        <?=lang('sort_by')?>
                        <select id="sort-by_custom">
                           <option <?php echo ($sort_val=='newest')?'selected':'' ?> value="newest"><?=lang('newest')?></option>
                           <option <?php echo ($sort_val=='popular')?'selected':'' ?> value="popular"><?=lang('polularity')?></option>
                           <option <?php echo ($sort_val=='price_des')?'selected':'' ?> value="price_des"><?=lang('price_high_to_low')?></option>
                           <option <?php echo ($sort_val=='price_asc')?'selected':'' ?> value="price_asc"><?=lang('price_low_to_high')?></option>
                        </select>
                     </div>
                     <!-- sort-by-cat -->
                     <div class="sort-by-cat view-option"> <?=lang('view')?> <span class="grid-view icon-grid_on <?php echo ($current_viewmode=='grid-view')?'active':'' ?>"></span> <span class="list-view icon-menu <?php echo ($current_viewmode=='list-view')?'active':'' ?>"></span> </div>
                     <!-- sort-by-cat -->
                  </div>
                  <!-- right-sorting-option -->
                  <input type="hidden" id="category-id" name="category-id" value="">
                  <input type="hidden" name="current_viewmode" id="current_viewmode" value="<?php echo $current_viewmode ?>">
               </div>
               <!-- categories-top -->
               <div class="product-list-section" id="product-list-section">
                  <div class="product-grid-listing-view" style="<?php echo ($current_viewmode=='grid-view')?'display:block;':'display:none;'?>">
                     <?php if (isset($product_list->is_success) && $product_list->is_success == 'true') { ?>
                     <ul>
                        <?php foreach ($product_list->ProductList as $prod) {
                           $prod = ProductPresenter::from($prod);
                           $prod_image=$prod->product_image('thumb');

						    (new ProductList())->productListData($prod,$prod_image,'FeaturedListing','grid'); ?>

                        <?php } ?>
                     </ul>
                     <?php
                        } else { ?>
                     <h2><?=lang('coming_soon')?></h2>
                     <?php } ?>
                  </div>
                  <!-- product-grid-listing-view -->
                  <!-- product-listing-view -->
                  <div class="product-listing-view" style="<?php echo ($current_viewmode=='list-view')?'display:block;':'display:none;'?>">
                     <?php if (isset($product_list->is_success) && $product_list->is_success == 'true') { ?>
                     <ul>
                        <?php foreach ($product_list->ProductList as $prod) {
                           $prod = ProductPresenter::from($prod);
                           $prod_image=$prod->product_image('thumb');

						   (new ProductList())->productListData($prod,$prod_image,'FeaturedListing','list');
                           ?>

                        <?php } ?>
                     </ul>
                     <?php
                        } ?>
                  </div>
                  <!-- product-list-view -->
                  	<?php
					if ($PaginationLink) {
					echo $PaginationLink;
					}
                    ?>
               </div>
            </div>
            <!-- col-sm-9 -->
         </div>
         <!-- row -->
      </div>
      <!-- col-md-12 -->
   </div>
   <!-- container -->
</div>
<!-- product-listing-page -->
<?php $this->load->view('common/footer'); ?>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>featured_product_list.js?v=<?php echo CSSJS_VERSION; ?>"></script>
</body>
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
			console.log('lazyload', $e);
		});

		// handle lazyshow events
		$(document).on('lazyshow', '.lazy', function() {
			var $e = $(this);
			// do something with the loaded element...
			console.log('lazyshow', $e);
		});
	});
</script>
</body>
</html>
