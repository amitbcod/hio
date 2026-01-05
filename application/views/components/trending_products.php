<?php $this->load->view('common/header'); ?>
<div class="main">
    <div class="container-fluid">
        <?php $this->load->view('product/breadcrum'); ?>
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
            <!-- BEGIN SIDEBAR -->
            <span class="filter-op"><i class="fa fa-filter" aria-hidden="true"></i> <?= lang('filter') ?></span>
            <div class="sidebar col-md-3 col-sm-5">
                <?php
                if (!empty($product_list) && (isset($product_list->statusCode) && $product_list->statusCode == '200')) {
                    (new CatalogFilters($current_category_id))->render();
                }
                ?>
                <h2><?= lang('categories') ?></h2>
                <?php (new TopMenu('categorymenu'))->render(); ?>
            </div>
            <!-- END SIDEBAR -->
            <!-- BEGIN CONTENT -->
            <div class="col-md-9 col-sm-7">
                <div class="row list-view-sorting clearfix">
                    <div class="col-md-12 col-sm-12">
                        <h2><?= lang('trending_products') ?></h2>
                    </div>
                </div>
                <!-- BEGIN PRODUCT LIST -->
                <div class="products-list-full grid-view">
                    <div class="row">
                        <?php foreach ($featured_product as $prod): 
                            $prod = ProductPresenter::from($prod);
                            $feat_image = $prod->product_image('thumb');
                        ?>
                            <div class="col-md-4 mb-4">
                                <?php (new ProductList())->productListData($prod, $feat_image, 'TrendingListing', '', ''); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper text-center mt-4">
                    <?php echo $pagination_links; ?>
                </div>
            </div>
            <!-- END CONTENT -->
        </div>
        <!-- END SIDEBAR & CONTENT -->
    </div>
</div>

<?php $this->load->view('common/footer'); ?>
<script src="<?php echo SKIN_JS ?>product.js?v=<?php echo CSSJS_VERSION; ?>"></script>
