<div class="site-section block-3 site-blocks-2 pro-slider">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-md-12 site-section-heading">
                <h2><?=lang('new_arrival')?></h2>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php $class =  ($display_type == 'grid') ? 'error-page-listing-main product-grid-listing-view' : 'nonloop-block-3 owl-carousel';?>
                <div class="<?php echo $class;?>">
                    <?php if (isset($new_arrival) && $new_arrival !='') {
                        echo ($display_type == 'grid')?'<ul>':'';
                            foreach ($new_arrival as $arrival_prod) {
                                $arrival_prod = ProductPresenter::from($arrival_prod);
                                $narr_image=$arrival_prod->product_image('thumb');
                                echo ($display_type == 'grid')?'<li class="error-page-listing">':'<div class="item">'; ?>
                                    <?php (new ProductList())->productListData($arrival_prod,$narr_image,'NewArrivalListing','','homepage'); ?>
                                <?php echo ($display_type == 'grid')?'</li>':'</div>';
                            }
                        echo ($display_type == 'grid')?'</ul>':'';
                    } ?>
                </div>
            </div>
        </div>

    </div>
</div><!-- New Arrival -->
