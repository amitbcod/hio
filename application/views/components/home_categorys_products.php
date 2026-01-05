<h2><?php echo $section_title; ?></h2>
<div class="owl-carousel <?php if($section_title == 'BUY & TRY'){echo "owl-carousel-gifts margin-bottom-40";}else{ echo "owl-carousel2x owl-responsive-1200";} ?> owl-theme owl-loaded">

    <?php foreach($magazinngift_product->ProductList as $magazingift_prod):
    $magazingift_prod = ProductPresenter::from($magazingift_prod);
    $feat_image = $magazingift_prod->product_image('thumb');
    ?>
    <?php (new ProductList())->productListData($magazingift_prod,$feat_image,'AllProductsList','','','owlView'); ?>
    <?php endforeach ?>
</div>