<div class="row product-list">
    <?php if(isset($product_list->is_success) && $product_list->is_success == true){ ?>
        <?php foreach($product_list->ProductList as $prod){ ?>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <?php 
                    $prod->current_category_id=$current_category_id;
                    $prod = ProductPresenter::from($prod);
                    $prod_image=$prod->product_image('thumb');

                    (new ProductList())->productListData($prod,$prod_image,'Listing');
                ?>
            </div>
        <?php } ?>
    <?php } ?>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12">
        <div class="pagination pull-right">
            <?php
                if ($this->ajax_pagination->create_links()) {
                        echo $this->ajax_pagination->create_links();
                }
            ?>
        </div>
    </div>
</div>
