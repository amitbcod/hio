<h2>Magazines With Gifts</h2>
<div class="your-class">
    <?php 
    if(isset($magazinngift_product->ProductList)){
        foreach($magazinngift_product->ProductList as $magazingift_prod):
        $magazingift_prod = ProductPresenter::from($magazingift_prod);
        $feat_image = $magazingift_prod->product_image('thumb');
        ?>
        <!-- <div> -->
        <?php (new ProductList())->productListData($magazingift_prod,$feat_image,'AllProductsList','','',''); ?>
        <!-- </div> -->
        <?php endforeach ?>
    <?php } ?>
</div>