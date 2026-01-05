<?php $this->load->view('common/header'); ?>

<?php $customer_id = isset($_SESSION['LoginID']) ? $_SESSION['LoginID']:0; ?>

<?php
    $currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
    $currency_symbol = $this->session->userdata('currency_symbol');
    $default_currency_flag = $this->session->userdata('default_currency_flag');
?>


<div class="product-listing-page">
	<div class="container">
    	<div class="col-md-12">
          	<div class="row">

				<div class="col-md-12 col-lg-12 ">

					<div class="banner-section">
					  	<div class="container">
							<div class="regular slider">

							</div>
						</div>
					</div><!-- banner section -->


					<div class="categories-top">

						<div class="right-sorting-option">
							<div class="sort-by-cat no-sort"><?=lang('show')?>
								<select id="show-limit">
									<?php if (isset($show_limit) && $show_limit > 0) { ?>
									<?php foreach ($show_limit as $limt) { ?>
									<option <?php echo ($show_limit_selected==$limt)?'selected':'' ?> value="<?php echo $limt; ?>"><?php echo $limt; ?></option>
									<?php } } ?>
								</select>
							</div><!-- sort-by-cat -->
							<div class="sort-by-cat popularity-option"><?=lang('sort_by')?>
								<select id="sort-by">
									<option <?php echo ($sort_val=='newest')?'selected':'' ?> value="newest"><?=lang('newest')?></option>
									<option <?php echo ($sort_val=='popular')?'selected':'' ?> value="popular"><?=lang('newest')?></option>
									<option <?php echo ($sort_val=='price_des')?'selected':'' ?> value="price_des"><?=lang('price_high_to_low')?></option>
									<option <?php echo ($sort_val=='price_asc')?'selected':'' ?> value="price_asc"><?=lang('price_low_to_high')?></option>
								</select>
							</div><!-- sort-by-cat -->
							<div class="sort-by-cat view-option"> <?=lang('view')?> <span class="grid-view icon-grid_on <?php echo ($current_viewmode=='grid-view')?'active':'' ?>"></span> <span class="list-view icon-menu <?php echo ($current_viewmode=='list-view')?'active':'' ?>"></span> </div><!-- sort-by-cat -->
						</div><!-- right-sorting-option -->

						<input type="hidden" name="current_viewmode" id="current_viewmode" value="<?php echo $current_viewmode ?>">
						<input type="hidden" name="page_sort_type" id="page_sort_type" value="PrelaunchListing">
					</div><!-- categories-top -->
					<div class="product-list-section" id="product-list-section">
						<div class="product-grid-listing-view pre-lanuch" style="<?php echo ($current_viewmode=='grid-view')?'display:block;':'display:none;'?>">
							<?php if (isset($product_list->is_success) && $product_list->is_success == 'true') {
    $productListing = $product_list->ProductList; ?>
							<ul>
								<?php foreach ($productListing as $key=>$prod) {
                                    $prod = ProductPresenter::from($prod);
                                    $prod_image=$prod->product_image('thumb');

									(new ProductList())->productListData($prod,$prod_image,'PrelaunchListing');
                                ?>

								<?php } ?>
							</ul>
							<?php
} else { ?>
                                <h2><?=lang('coming_soon')?></h2>
							<?php } ?>
						</div><!-- product-grid-listing-view -->

						<!-- product-listing-view -->
						<div class="product-listing-view" style="<?php echo ($current_viewmode=='list-view')?'display:block;':'display:none;'?>">
							<?php if (isset($product_list->is_success) && $product_list->is_success == 'true') {
        $productListing = $product_list->ProductList; ?>
							<ul>
                                <?php foreach ($productListing as $prod) {
                                    $prod = ProductPresenter::from($prod);
                                    $prod_image=$prod->product_image('thumb');

									(new ProductList())->productListData($prod,$prod_image,'PrelaunchListing');
                                ?>

								<?php } ?>
							</ul>
							<?php
    } ?>
						</div><!-- product-list-view -->
						<?php
                            /* if($this->ajax_pagination->create_links()) {
                                echo $this->ajax_pagination->create_links();
                            } */
                            if ($PaginationLink) {
                                echo $PaginationLink;
                            }
                        ?>
					</div>
				</div><!-- col-sm-9 -->

          </div><!-- row -->
        </div><!-- col-md-12 -->
      </div><!-- container -->
    </div><!-- product-listing-page -->

    <?php $this->load->view('common/footer'); ?>
	<script src="<?=base_url('public/js/recliner.js')?>"></script>
    <script type="text/javascript" src="<?php echo SKIN_JS; ?>prelauch_product_list.js?v=<?php echo CSSJS_VERSION; ?>"></script>
  </body>
</html>
