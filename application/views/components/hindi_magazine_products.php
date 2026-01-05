<?php
// if ($hindiLanguage !== null) {
    // echo "<pre>";
    // print_r($hindi_magazine_product);
    // die;
// }
?>
<div class="d-flex" style="justify-content: end;">
    <h2>Hindi Magazine Products</h2>
    <div class="view_all">
        <a class="" href="https://indiamags.com/category/hindi" role="" type="text">View All</a>
    </div>
</div>

<div class="your-class">
    <?php foreach ($hindi_magazine_product as $hindi_magazine_product_prod) :
        $hindi_magazine_product_prod = ProductPresenter::from($hindi_magazine_product_prod);
        $feat_image = $hindi_magazine_product_prod->product_image('thumb');
    ?>
        <!-- <div> -->
        <?php (new ProductList())->productListData($hindi_magazine_product_prod, $feat_image, 'AllProductsList', '', '', ''); ?>
        <!-- </div> -->
    <?php endforeach ?>
</div>

<!-- <div class="d-flex" style="justify-content: center;align-items: center;">
    <a class="" href="https://indiamags.com/category/language-magazines?page=1&limit=12&sort=newest&viewmode=undefined&attribute=961" role="" type="text">View All</a>
</div> -->