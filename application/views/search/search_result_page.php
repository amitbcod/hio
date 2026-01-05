<?php $this->load->view('common/header'); ?>
<div class="main">
    <div class="container-fluid">
        <ul class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>">Home</a></li>
            <li class="active">Search Results</li>
        </ul>
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
            <!-- BEGIN SIDEBAR -->
            <div class="sidebar col-md-3 col-sm-5">
                <?php
                if (!empty($product_list) && (isset($product_list->statusCode) && $product_list->statusCode == '200')) {
                    (new CatalogFilters('', 'search', $search_term))->render();
                }
                ?>
                <h2>Categories</h2>
                <?php (new TopMenu('categorymenu', 1))->render(); ?>

            </div>
            <!-- END SIDEBAR -->
            <!-- BEGIN CONTENT -->
            <div class="col-md-9 col-sm-7">
                <div class="row list-view-sorting clearfix">
                    <div class="col-md-2 col-sm-2 list-view">
                        <a href="javascript:;"><i class="fa fa-th-large"></i></a>
                        <a href="javascript:;"><i class="fa fa-th-list"></i></a>
                    </div>
                    <div class="col-md-10 col-sm-10">
                        <div class="pull-right">
                            <label class="control-label">Show:</label>
                            <select class="form-control input-sm" id="show-limit">
                                <?php if (isset($show_limit) && count($show_limit) > 0) { ?>
                                    <?php foreach ($show_limit as $limit) { ?>
                                        <option value="<?php echo $limit; ?>" <?php echo ($show_limit_selected == $limit) ? 'selected' : "" ?>><?php echo $limit; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="pull-right">
                            <label class="control-label">Sort&nbsp;By:</label>
                            <select class="form-control input-sm" id="sort-by">
                                <option <?php echo ($sort_val == 'newest') ? 'selected' : '' ?> value="newest">Newest</option>
                                <option <?php echo ($sort_val == 'popular') ? 'selected' : '' ?> value="popular">Popularity</option>
                                <option <?php echo ($sort_val == 'price_des') ? 'selected' : '' ?> value="price_des">Price: High To Low</option>
                                <option <?php echo ($sort_val == 'price_asc') ? 'selected' : '' ?> value="price_asc">Price: Low To High</option>
                            </select>
                        </div>
                        <input type="hidden" id="category-id" name="category-id" value="<?php echo $cat_obj->id; ?>">
                        <input type="hidden" name="page_sort_type" id="page_sort_type" value="Listing">
                    </div>
                </div>
                <!-- BEGIN PRODUCT LIST -->
                <div class="product-list-section" id="product-list-section">
                    <div class="row product-list">
                        <input type="hidden" name="search_term" id="search_term" value="<?php echo $search_term ?>">
                        <!-- PRODUCT ITEM START -->
                        <?php if (isset($product_list->is_success) && $product_list->is_success == true) { ?>
                            <?php foreach ($product_list->ProductList as $prod) { ?>
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <?php
                                    $prod->current_category_id = $current_category_id;
                                    $prod = ProductPresenter::from($prod);
                                    $prod_image = $prod->product_image('thumb');
                                    (new ProductList())->productListData($prod, $prod_image, 'Listing');
                                    ?>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p><?= lang('no_search_found_for') ?> <?php echo $search_term; ?> </p>
                        <?php } ?>
                        <!-- PRODUCT ITEM END -->
                    </div>
                    <!-- END PRODUCT LIST -->
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="pagination pull-right">
                                <?php
                                if ($PaginationLink) {
                                    echo $PaginationLink;
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- END CONTENT -->
        </div>
        <!-- END SIDEBAR & CONTENT -->
    </div>
</div>

<?php $this->load->view('common/footer'); ?>
<script src="<?php echo SKIN_JS ?>product.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script src="<?php echo SKIN_JS ?>search_result_page.js?v=<?php echo CSSJS_VERSION; ?>"></script>