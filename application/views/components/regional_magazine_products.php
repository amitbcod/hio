<?php
// if ($hindiLanguage !== null) {
// echo "<pre>";
// print_r($regional_magazine_product);
// die;
// }
?>
<div class="d-flex" style="justify-content: end;">
    <h2>Regional Magazine Products</h2>
    <div class="view_all">
        <a class="" href="https://indiamags.com/category/language-magazines?page=1&limit=12&sort=newest&viewmode=undefined&attribute=960,962,963,964,967,968,971" role="" type="text">View All</a>
    </div>
</div>
<div class="your-class">
    <?php foreach ($regional_magazine_product as $regional_magazine_product_prod) :
        $regional_magazine_product_prod = ProductPresenter::from($regional_magazine_product_prod);
        $feat_image = $regional_magazine_product_prod->product_image('thumb');
    ?>
        <!-- <div> -->
        <?php (new ProductList())->productListData($regional_magazine_product_prod, $feat_image, 'AllProductsList', '', '', ''); ?>
        <!-- </div> -->
    <?php endforeach ?>
</div>