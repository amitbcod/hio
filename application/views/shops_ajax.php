<ul class="products-grid products-grid--max-5-col csmarketplace-vendors-grid">
<?php if(!empty($shops)) {
    foreach($shops as $shop){ 
        // Set image path
        $shop_image = !empty($shop->shop_image) && file_exists(FCPATH.'merchant/public/images/shop_images/'.$shop->shop_image) 
                      ? base_url('merchant/public/images/shop_images/'.$shop->shop_image) 
                      : 'http://via.placeholder.com/135x135';
    ?>
        <li class="item">
            <div class="shop_grid_list_wrap 123">
                <a href="<?php echo BASE_URL . 'shops/shop_details/' . $shop->id; ?>" class="product-image vendor-logo-image">
                    <img src="<?php echo $shop_image; ?>" alt="<?php echo $shop->vendor_name; ?>">
                </a>
                <h2 class="product-name csmarketplace-vendor-name">
                    <a href="<?php echo BASE_URL . 'shops/shop_details/' . $shop->id; ?>" title="Go to Shop"><?php echo $shop->vendor_name; ?></a>
                </h2>
                <p><?php echo $shop->publication_name; ?></p>
            </div>
        </li>
<?php } } else { ?>
    <li>No shops found.</li>
<?php } ?>
</ul>

<div class="page_limiter">
    <?php echo $pagination; ?>
</div>
