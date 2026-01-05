<?php if(!empty($shops)): ?>

<div class="category-product products wrapper grid products-grid">

    <ol class="products list items product-items row">

        <?php foreach($shops as $product): ?>

            <li class="item product product-item col-md-3">

                <div class="product-item-info" data-container="product-grid">

                    <div class="item-inner">

                        <div class="box-image">

                            <!-- product image -->

                            <a href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>" 

                               class="product photo product-item-photo">

                                <span class="product-image-container" style="width: 240px;">

                                    <span class="product-image-wrapper" style="padding-bottom: 100%;">

                                        <img class="product-image-photo <?php echo BASE_URL .'uploads/products/thumb/' . $product->base_image ; ?>" src="<?php echo BASE_URL .'uploads/products/thumb/' . $product->base_image ; ?>" alt="<?php echo $product->name; ?>">

                                    </span>

                                </span>

                            </a>

                        </div>



                        <!-- product details -->

                        <div class="product details product-item-details box-info">

                            <h2 class="product name product-item-name product-name">

                                <a class="product-item-link" 

                                   href="<?php echo BASE_URL . 'product-detail/' . $product->url_key; ?>">

                                    <?php echo $product->name; ?>

                                </a>

                            </h2>



                            <div class="price-box price-final_price">

                                <?php if(!empty($product->special_price)): ?>

                                    <span class="special-price">

                                        <span class="price">MUR <?php echo $product->special_price; ?></span>

                                    </span>

                                    <span class="old-price">

                                        <span class="price">MUR <?php echo $product->webshop_price; ?></span>

                                    </span>

                                <?php else: ?>

                                    <span class="regular-price">

                                        <span class="price">MUR <?php echo $product->webshop_price; ?></span>

                                    </span>

                                <?php endif; ?>

                            </div>



                            <!-- Add to cart button -->

                            <!-- <div class="bottom-action">

                                <button type="submit" id="add_to_cart" class="btn btn-primary subscr-now pull-left">Add To Cart</button>

                            </div> -->

                        </div>

                    </div>

                </div>

            </li>

        <?php endforeach; ?>

    </ol>

</div>



<div class="pagination-wrap">

    <?php echo $pagination; ?>

</div>

<?php else: ?>

    <p>No products found for this shop.</p>

<?php endif; ?>

