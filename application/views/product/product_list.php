<?php
// echo "<pre>";
// print_r($current_category_id);
// die;

$attributeListing = $catalogFilter->productCatalogFilter->attribute_listing->language__Language;
// echo "<pre>";
// print_r($attributeListing);
// die;
?>

<?php $this->load->view('common/header'); ?>
<div class="main">
    <div class="container-fluid">
        <?php $this->load->view('product/breadcrum'); ?>
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
            <!-- BEGIN SIDEBAR -->
            <span class="filter-op"><i class="fa fa-filter" aria-hidden="true"></i> Filter</span>
            <div class="sidebar col-md-3 col-sm-5">
                <?php
                if (!empty($product_list) && (isset($product_list->statusCode) && $product_list->statusCode == '200')) {
                    (new CatalogFilters($current_category_id))->render();
                }
                ?>
                <h2>Categories</h2>
                <?php (new TopMenu('categorymenu'))->render(); ?>

            </div>
            <!-- END SIDEBAR -->
            <!-- BEGIN CONTENT -->
            <div class="col-md-9 col-sm-7">
                <div class="row list-view-sorting clearfix">
                    <div class="col-md-12 col-sm-12">
                        <?php $this->load->view('product/category_heading'); ?>
                        <div class="check-flex">
                            <div class="pull-left" id="language_dropdown_new">
                                <label class="control-label">Language:</label>
                                <select class="form-control input-sm " id="language">
                                    <option value="">All</option>
                                    <?php foreach ($attributeListing as $language) : ?>
                                        <?php $selected = ($language->attr_value == '') ? 'selected' : ""; ?>
                                        <option value="<?php echo $language->attr_value; ?>" <?php echo $selected; ?>><?php echo $language->attr_options_name; ?></option>
                                    <?php endforeach; ?>
                                </select>

                            </div>
                            <div class="pull-left" style="margin-left: 15px;">
                                <label class="control-label">Sort&nbsp;By:</label>
                                <select class="form-control input-sm" id="sort-by">
                                    <option <?php echo ($sort_val == 'newest') ? 'selected' : '' ?> value="newest">Newest</option>
                                    <option <?php echo ($sort_val == 'popular') ? 'selected' : '' ?> value="popular">Popularity</option>
                                    <option <?php echo ($sort_val == 'price_des') ? 'selected' : '' ?> value="price_des">Price: High To Low</option>
                                    <option <?php echo ($sort_val == 'price_asc') ? 'selected' : '' ?> value="price_asc">Price: Low To High</option>
                                </select>
                            </div>
                            <div class="pull-left" style="margin-left: 15px;">
                                <label class="control-label">Show:</label>
                                <select class="form-control input-sm" id="show-limit">
                                    <?php if (isset($show_limit) && count($show_limit) > 0) { ?>
                                        <?php foreach ($show_limit as $limit) { ?>
                                            <option value="<?php echo $limit; ?>" <?php echo ($show_limit_selected == $limit) ? 'selected' : "" ?>><?php echo $limit; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" id="category-id" name="category-id" value="<?php echo $cat_obj->id; ?>">
                        <input type="hidden" name="page_sort_type" id="page_sort_type" value="Listing">
                    </div>
                </div>
                <!-- BEGIN PRODUCT LIST -->
                <div class="product-list-section" id="product-list-section">
                    <div class="row product-list">
                        <!-- PRODUCT ITEM START -->
                        <?php if (isset($product_list->is_success) && $product_list->is_success == true) { ?>
                            <?php foreach ($product_list->ProductList as $prod) { ?>
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <?php
                                    $prod->current_category_id = $current_category_id;
                                    $prod = ProductPresenter::from($prod);
                                    $prod_image = $prod->product_image('thumb');
                                    (new ProductList())->productListData($prod, $prod_image, 'Listing');
                                    ?>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <h2><?= lang('coming_soon') ?></h2>
                        <?php } ?>
                        <!-- PRODUCT ITEM END -->
                    </div>
                    <!-- END PRODUCT LIST -->
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="pagination pull-right">
                                <?php
                                if ($PaginationLink) {
                                    echo $PaginationLink;
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- END CONTENT -->
        </div>
        <!-- END SIDEBAR & CONTENT -->
    </div>
</div>


<?php $this->load->view('common/footer'); ?>
<script src="<?php echo SKIN_JS ?>product.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script>
    var language_attr_options_name = "<?= $attributeListing[0]->attr_options_name ?>";
</script>
<script>
    function sort_by(page, ajaxType = "", lastpagenum = "") {
        window.prevUrl = window.location.href;

        $("html, body").animate({
            scrollTop: 0
        }, "slow");

        var page = page ? page : 0;
        var p1 = $("#href_" + page).text();
        if (p1 == '' && lastpagenum != '') {
            p1 = lastpagenum;
        }
        var variantAttrIdArr = new Array();
        var variantAttrValArr = new Array();

        $('input[name="variant_chk[]"]:checked').each(function() {
            var attr_v = $(this).val();
            var varnt_id = $("#variant_attr_" + attr_v).val();
            variantAttrIdArr.push(varnt_id);
            variantAttrValArr.push(attr_v);
        });

        var attributeIdArr = new Array();
        $('input[name="attribute_chk[]"]:checked').each(function() {
            var attr_id = $(this).val();
            attributeIdArr.push(attr_id);
        });

        var sortValue = $('#sort-by option:selected').val();
        var showlimit = $('#show-limit option:selected').val();
        var catId = $('#category-id').val();

        if (p1 != "") {
            setGetParameter('page', p1);
        } else {
            removeParam('page');
        }

        if (showlimit != "") {
            setGetParameter('limit', showlimit);
        } else {
            removeParam('limit');
        }

        if (sortValue != "") {

            setGetParameter('sort', sortValue);
        } else {
            removeParam('sort');
        }

        if (variantAttrIdArr != "" && variantAttrIdArr.length > 0) {
            setGetParameter('variantId', variantAttrIdArr.toString());
        } else {
            removeParam('variantId');
        }

        if (variantAttrValArr != "" && variantAttrValArr.length > 0) {
            setGetParameter('variantVal', variantAttrValArr.toString());
        } else {
            removeParam('variantVal');
        }

        if (attributeIdArr != "" && attributeIdArr.length > 0) {
            setGetParameter('attribute', attributeIdArr.toString());
        } else {
            removeParam('attribute');
        }

        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL + 'ProductsController/sort_by/' + page,
            data: {
                sort_val: sortValue,
                cat_Id: catId,
                variantId: variantAttrIdArr,
                variantVal: variantAttrValArr,
                attributeArr: attributeIdArr,
                show_limit: showlimit,
                page: p1
            },
            beforeSend: function() {
                $('#ajax-spinner').show();
                $('#product-list-section').hide();
            },
            success: function(response) {
                $('#ajax-spinner').hide();
                $('#product-list-section').show();
                $('#product-list-section').html(response);

            }
        });
    }
</script>