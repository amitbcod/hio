<div class="title-block">
    <h2><?= $this->lang->line('new_arrivals'); ?></h2>
</div>

<div id="sm_filterproducts_17550717591681235462" class="products-list-full home-trending-slider">
    <div class="owl-carousel owl-theme owl-loaded owl-drag">
        <div class="owl-stage-outer">
            <div class="owl-stage" style="transform: translate3d(0px, 0px, 0px); transition: all; width: 1715px;">
                <?php 
                $count = 0; 
                foreach ($new_arrival->NewArrivalProduct as $newarrival_prod) :
                    if ($count >= 6) break; // show only first 6
                    $newarrival_prod = ProductPresenter::from($newarrival_prod);
                    $feat_image = $newarrival_prod->product_image('thumb');
                    (new ProductList())->productListData($newarrival_prod, $feat_image, 'NewArrivalListing', '', '');
                    $count++;
                endforeach; 
                ?>
            </div>
        </div>
    </div>
</div>

<div class="view-more-wrap" style="text-align:center; margin-top:20px;">
    <a href="<?= linkUrl('https://ymstore.whuso.in/newarrival-products') ?>" class="btn btn-primary"><?= $this->lang->line('view_more'); ?></a>
</div>
