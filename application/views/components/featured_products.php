<div class="title-block">
    <h2><?= lang('trending_products') ?></h2>
</div>

<div id="sm_filterproducts_17550717591681235462" class="products-list-full home-trending-slider">
    <div class="owl-carousel owl-theme owl-loaded owl-drag">
        <div class="owl-stage-outer">
            <div class="owl-stage" style="transform: translate3d(0px, 0px, 0px); transition: all; width: 1715px;">
                <?php 
                $count = 0; 
                foreach ($featured_product as $product) :
                    if ($count >= 6) break; // show only first 6
                    $product = ProductPresenter::from($product);
                    $feat_image = $product->product_image('thumb');
                    (new ProductList())->productListData($product, $feat_image, 'TrendingListing', '', '');
                    $count++;
                endforeach; 
                ?>
            </div>
        </div>
    </div>
</div>

<div class="view-more-wrap" style="text-align:center; margin-top:20px;">
    <a href="<?= linkUrl('https://ymstore.whuso.in/trending-products') ?>" class="btn btn-primary"><?= lang('view_more') ?></a>
</div>
