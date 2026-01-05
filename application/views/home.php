<?php $this->load->view('common/header'); ?>

<div class="main">
    <div class="container-fluid">

        <!-- BEGIN SALE PRODUCT & NEW ARRIVALS -->
        <div class="row margin-bottom-40">

            <!-- BEGIN SALE PRODUCT -->
            <div class="col-md-12 sale-product homepage-top">

                <div class="row">
                    <div class="col-md-12">
                        <div class="margin-bottom-25 home-top-banner">
                            <?php //(new HomeCategoryBanners('topbanner'))->render(); ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-12 home-top-banner-main">
                <div class="container">
                    <div class="slidershow home-toped">
                        <div class="owl-carousel owl-theme">

                            <div class="item"><a title="<?= $this->lang->line('jewellery_accessories') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600x535_slider-banner-computer_1_.jpg"
                                        alt="<?= $this->lang->line('jewellery_accessories') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('flash_sales') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600_535-ym-slider-banner-flash-sale-a_1_.jpg"
                                        alt="<?= $this->lang->line('flash_sales') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('new_arrivals') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600_535-ym-slider-banner-new-arrivals_2_.jpg"
                                        alt="<?= $this->lang->line('new_arrivals') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('daily_deals') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600_535-ym-slider-banner-daily-deals-launch_1_.jpg"
                                        alt="<?= $this->lang->line('daily_deals') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('home_appliances') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600x535_slider-banner-home-appliances_1_.jpg"
                                        alt="<?= $this->lang->line('home_appliances') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('food_groceries') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600x535_slider-banner-groceries-1_1_.jpg"
                                        alt="<?= $this->lang->line('food_groceries') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('art_craft') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600x535_slider-banner-craft_1_.jpg"
                                        alt="<?= $this->lang->line('art_craft') ?>"></a></div>

                            <div class="item"><a title="<?= $this->lang->line('electronics') ?>" href="#"><img
                                        src="./statis_pages/home_page_files/1600x535_slider-banner-computer_1_.jpg"
                                        alt="<?= $this->lang->line('electronics') ?>"></a></div>

                        </div>
                    </div>

                    <!-- second-section -->
                    <div class="banner-1">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="banner-image">
                                    <a title="<?= $this->lang->line('probieau_solution') ?>" href="#" target="_blank" rel="noopener">
                                        <img class="mark-lazy"
                                             src="./statis_pages/home_page_files/item-1.jpg"
                                             alt="<?= $this->lang->line('probieau_solution') ?>">
                                    </a>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="banner-image">
                                    <a title="<?= $this->lang->line('nama_boutique') ?>" href="#" target="_blank" rel="noopener">
                                        <img class="mark-lazy"
                                             src="./statis_pages/home_page_files/nama_boutique_banner_sales.jpg"
                                             alt="<?= $this->lang->line('nama_boutique') ?>" width="" height="150">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- second-section -->

                    <!-- Daily Deals Section -->
                    <?php if (!empty($daily_deals_products)) : ?>
                        <div class="product-slider product-slider-1 home-products-section">
                            <div class="title-block">
                                <h2><?= $this->lang->line('daily_deals') ?></h2>
                            </div>

                            <div id="sm_filterproducts_daily_deals" class="products-list-full home-trending-slider">
                                <div class="owl-carousel owl-theme owl-loaded owl-drag">
                                    <div class="owl-stage-outer">
                                        <div class="owl-stage" style="transform: translate3d(0px, 0px, 0px); transition: all; width: 1715px;">
                                            <?php 
                                            $count = 0; 
                                            foreach ($daily_deals_products as $deal_prod) :
                                                if ($count >= 3) break;
                                                $deal_prod = ProductPresenter::from($deal_prod);
                                                $feat_image = $deal_prod->product_image('thumb');
                                                (new ProductList())->productListData($deal_prod, $feat_image, 'DailyDealsListing', '', '');
                                                $count++;
                                            endforeach; 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="view-more-wrap" style="text-align:center; margin-top:20px;">
                                <a href="<?= linkUrl('https://ymstore.whuso.in/daily-deals') ?>" class="btn btn-primary">
                                    <?= $this->lang->line('view_more') ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Flash Sales Section -->
                    <?php if (!empty($flash_sale_products)) : ?>
                        <div class="product-slider product-slider-1 home-products-section">
                            <div class="title-block">
                                <h2><?= $this->lang->line('flash_sales') ?></h2>
                            </div>

                            <div id="sm_filterproducts_flash_sales" class="products-list-full home-trending-slider">
                                <div class="owl-carousel owl-theme owl-loaded owl-drag">
                                    <div class="owl-stage-outer">
                                        <div class="owl-stage">
                                            <?php 
                                            $count = 0; 
                                            foreach ($flash_sale_products as $flash_sale) :
                                                if ($count >= 3) break;
                                                $flash_sale = ProductPresenter::from($flash_sale);
                                                $feat_image = $flash_sale->product_image('thumb');
                                                (new ProductList())->productListData($flash_sale, $feat_image, 'FlashSaleListing', '', '');
                                                $count++;
                                            endforeach; 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="view-more-wrap" style="text-align:center; margin-top:20px;">
                                <a href="<?= linkUrl('https://ymstore.whuso.in/flash-sale/category/34') ?>" class="btn btn-primary">
                                    <?= $this->lang->line('view_more') ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- New Arrivals Section -->
                    <div class="product-slider product-slider-1 home-products-section">
                        <?php (new NewArrivalProducts(20, ""))->new_arrivals_products(); ?>
                    </div>

                    <!-- Banner 2 -->
                    <div class="banner-2">
                        <div class="row">
                            <div class="col-lg-4 col-md-4">
                                <div class="banner-image">
                                    <a title="<?= $this->lang->line('support_os') ?>" href="https://mu.yellowmarkets.com/merchant_shop/support-operating-system-ltd.html" target="_blank" rel="noopener">
                                        <img class="mark-lazy" src="./statis_pages/home_page_files/3-banner-ad-520x580.png"
                                             alt="<?= $this->lang->line('support_os') ?>">
                                    </a>
                                </div>
                            </div>

                            <div class="col-lg-5 col-md-5">
                                <div class="banner-image">
                                    <a title="<?= $this->lang->line('joulsy_joys') ?>" href="https://mu.yellowmarkets.com/merchant_shop/joulsy-joys.html" target="_blank" rel="noopener">
                                        <img class="mark-lazy" src="./statis_pages/home_page_files/4-ad-banner-655x280_1_.jpg"
                                             alt="<?= $this->lang->line('joulsy_joys') ?>">
                                    </a>
                                </div>
                                <div class="banner-image">
                                    <a title="<?= $this->lang->line('ad_banner') ?>" href="https://www.yellow.mu/en/register.html" target="_blank" rel="noopener">
                                        <img class="mark-lazy" src="./statis_pages/home_page_files/5-ad-banner-655x280.jpg"
                                             alt="<?= $this->lang->line('ad_banner') ?>">
                                    </a>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3">
                                <div class="banner-image">
                                    <a title="<?= $this->lang->line('cleanera') ?>" href="https://mu.yellowmarkets.com/merchant_shop/cleanera-ltd.html" target="_blank" rel="noopener">
                                        <img class="mark-lazy" src="./statis_pages/home_page_files/6-banner-ad-385x580.jpg"
                                             alt="<?= $this->lang->line('cleanera') ?>">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Featured Products Section -->
                    <div class="product-slider product-slider-2 trending-products-section">
                        <?php (new FeaturedProducts(70))->render(); ?>
                    </div>
                </div>
            </div>
            <!-- END SALE PRODUCT & NEW ARRIVALS -->

        </div>
    </div>
</div>

<?php $this->load->view('common/footer'); ?>
