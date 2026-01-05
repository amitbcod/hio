<style type="text/css">
.fe-new-item {
    width: 32.33%;
    display: inline-block;
    margin-bottom: 50px;
}
</style>
<div class="site-section block-3 site-blocks-2 pro-slider">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 site-section-heading">
                <h2>Featured Products</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?php (new TopMenu('categorymenu',1,'feature_prod'))->render(); ?>
            </div>
            <div class="col-md-9 row">
                <div class="fe-new">
                    <?php
                    foreach ($featured_product as $feature_prod):
                        $feature_prod = ProductPresenter::from($feature_prod);
                        $feat_image = $feature_prod->product_image('thumb');
                        ?>

                    <div class="fe-new-item">
                        <?php (new ProductList())->productListData($feature_prod,$feat_image,'FeaturedListing','',''); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div><!-- Featured products -->