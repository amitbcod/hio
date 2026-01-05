<?php $this->load->view('common/header'); ?>
<?php $customer_id = isset($_SESSION['LoginID']) ? $_SESSION['LoginID']:0; ?>
<?php $this->load->view('product/breadcrum'); ?>
<?php
    $gender_selected_arry  = array();
    if (isset($_GET['gender'])) {
        $gender_selected_arry = explode(",", $_GET['gender']);
    }
?>
<?php
	$min_price_rng = 0;
	$max_price_rng = 0;
    $currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
    $currency_symbol = $this->session->userdata('currency_symbol');
    $default_currency_flag = $this->session->userdata('default_currency_flag');
?>

<div class="product-listing-page" id="listing-prd-main">
	<div class="container">
    	<div class="col-md-12">
          	<div class="row">
				<div class="col-md-3 col-lg-3 ">
					<div class="filter-section">
						<h2>Filter <span class="mob-filter-add"><span class="icon-arrow_drop_down"></span>
							<span style="display:none;" class="icon-arrow_drop_up"></span></span>
						</h2>
						<div class="left-filter">

							<?php (new TopMenu('categorymenu'))->render(); ?>

							<?php if (isset($customVariable->is_success) && $customVariable->is_success=='true') {
    if ($customVariable->custom_variable->value == 'yes') {
        ?>
							<!-- <div class="filter-inner-section shop-for-filter">
								<h3 data-toggle="collapse" data-target="#filterByShopFor" class="collapsed" aria-expanded="false">Subscriptions<span class="icon-arrow_drop_down"></span></h3>
								<ul class="collapse" id="filterByShopFor">

								<?php if (isset($subsVariantsData->is_success) && $subsVariantsData->is_success == 'true') { 
									foreach ($subsVariantsData->SubsVariantsDetails as $key => $subsValue) {	
									?>
									<li><label class="container-checkbox">
									<input type="checkbox" name="subscription[]" value="<?php echo $subsValue->id; ?>" class="chk-gender">
									<?php echo $subsValue->attr_options_name; ?><span class="checkmark"></span>
									</label></li>

									 <li><label class="container-checkbox"><input <?php echo(in_array('Men', $gender_selected_arry)?'checked':'')?> type="checkbox" name="gender[]" value="Men" class="chk-gender">Men<span class="checkmark"></span></label></li>
									<li><label class="container-checkbox"><input <?php echo(in_array('Children', $gender_selected_arry)?'checked':'')?> type="checkbox" name="gender[]" value="Children" class="chk-gender">Children<span class="checkmark"></span></label></li>
									<li><label class="container-checkbox"><input <?php echo(in_array('Unisex', $gender_selected_arry)?'checked':'')?> type="checkbox" name="gender[]" value="Unisex" class="chk-gender">Unisex<span class="checkmark"></span></label></li> 
								<?php } } ?>
								</ul>
							</div> --><!-- filter-inner-section -->
							<?php
    }
} ?>

						<?php
						if (!empty($product_list) && (isset($product_list->statusCode) && $product_list->statusCode == '200')) {
							(new CatalogFilters($current_category_id))->render();
						}?>

					</div><!-- left-filter-->
					</div><!-- filter-section -->
				</div><!-- col-sm-3 -->

				<div class="col-md-9 col-lg-9 ">

					<?php //(new HomeCategoryBanners('categorybanner',$current_category_id))->render(); ?>

					<div class="categories-top">
						<h2><?php
						echo ((!empty($category->CategoryDetails->lang_cat_name) && $category->CategoryDetails->lang_cat_name !='') ? $category->CategoryDetails->lang_cat_name : $category->CategoryDetails->cat_name);
						 ?></h2>
						<div class="right-sorting-option">
							<div class="sort-by-cat no-sort">Show
								<select id="show-limit">
									<?php if (isset($show_limit) && $show_limit > 0) { ?>
									<?php foreach ($show_limit as $limt) { ?>
									<option <?php echo ($show_limit_selected==$limt)?'selected':'' ?> value="<?php echo $limt; ?>"><?php echo $limt; ?></option>
									<?php } } ?>
								</select>
							</div><!-- sort-by-cat -->
							<div class="sort-by-cat popularity-option">Sort By
								<select id="sort-by">
									<option <?php echo ($sort_val=='newest')?'selected':'' ?> value="newest">Newest</option>
									<option <?php echo ($sort_val=='popular')?'selected':'' ?> value="popular">Polularity</option>
									<option <?php echo ($sort_val=='price_des')?'selected':'' ?> value="price_des">Price: High To Low</option>
									<option <?php echo ($sort_val=='price_asc')?'selected':'' ?> value="price_asc">Price: Low To High</option>
								</select>
							</div><!-- sort-by-cat -->
							<div class="sort-by-cat view-option"> <?=lang('view')?> <span class="grid-view icon-grid_on <?php echo ($current_viewmode=='grid-view')?'active':'' ?>"></span> <span class="list-view icon-menu <?php echo ($current_viewmode=='list-view')?'active':'' ?>"></span> </div><!-- sort-by-cat -->
						</div><!-- right-sorting-option -->
						<input type="hidden" id="category-id" name="category-id" value="<?php echo $cat_obj->id; ?>">
						<input type="hidden" name="current_viewmode" id="current_viewmode" value="<?php echo $current_viewmode ?>">
						<input type="hidden" name="page_sort_type" id="page_sort_type" value="Listing">
					</div><!-- categories-top -->

					<div class="product-list-section" id="product-list-section">
						<div class="product-grid-listing-view" style="<?php echo ($current_viewmode=='grid-view')?'display:block;':'display:none;'?>">
							<?php if (isset($product_list->is_success) && $product_list->is_success == 'true') { ?>
							<ul>
								<?php foreach ($product_list->ProductList as $prod) {
									$prod->current_category_id=$current_category_id;
                                    $prod = ProductPresenter::from($prod);
                                    $prod_image=$prod->product_image('thumb');

									(new ProductList())->productListData($prod,$prod_image,'Listing');
                                ?>

								<?php } ?>
							</ul>
							<?php
                      } else { ?>
								<h2><?=lang('coming_soon')?></h2>
							<?php } ?>
						</div><!-- product-grid-listing-view -->

						<!-- product-listing-view -->
						<div class="product-listing-view" style="<?php echo ($current_viewmode=='list-view')?'display:block;':'display:none;'?>">
							<?php if (isset($product_list->is_success) && $product_list->is_success == 'true') { ?>
							<ul>
								<?php foreach ($product_list->ProductList as $prod) {
									$prod->current_category_id=$current_category_id;
                                    $prod = ProductPresenter::from($prod);
                                    $prod_image=$prod->product_image('thumb');

									(new ProductList())->productListData($prod,$prod_image,'Listing');
                              ?>

								<?php } ?>
							</ul>
							<?php
                      } ?>
						</div><!-- product-list-view -->
						<?php

                            if ($PaginationLink) {
                                echo $PaginationLink;
                            }
                        ?>
					</div>
				</div><!-- col-sm-9 -->

          </div><!-- row -->
        </div><!-- col-md-12 -->
      </div><!-- container -->
    </div><!-- product-listing-page -->

    <?php $this->load->view('common/footer'); ?>
<script src="<?php echo SKIN_JS ?>product.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript">
  jQuery(document).ready(function($) {

	window.prevUrl = window.location.href;

	$("#min_price,#max_price").on('change', function () {

	  var min_price_range = parseInt($("#min_price").val());

	  var max_price_range = parseInt($("#max_price").val());

	  if (min_price_range > max_price_range) {
		$('#max_price').val(min_price_range);
	  }

	  $("#slider-range").slider({
		values: [min_price_range, max_price_range]
	  });

	});

	$(function () {
	  $("#slider-range").slider({
		range: true,
		orientation: "horizontal",
		min: <?php echo $min_price_rng; ?>,
		max: <?php echo $max_price_rng; ?>,
		values: [<?php echo $min_price_rng; ?>, <?php echo $max_price_rng; ?>],

		stop: function (event, ui) {
		  if (ui.values[0] == ui.values[1]) {
			return false;
		  }

		  $("#min_price").val(ui.values[0]);
		  $("#max_price").val(ui.values[1]);
		  load_product(ui.values[0], ui.values[1]);
		}
	  });

		$("#min_price").val($("#slider-range").slider("values", 0));
		$("#max_price").val($("#slider-range").slider("values", 1));

	});

	$(".mob-filter-add ").click(function(){
	  $(".left-filter").toggle(100);
	  $(".mob-filter-add").toggleClass('active');
	});

});

function load_product(minimum_range, maximum_range)
{
	$("html, body").animate({ scrollTop: 0 }, "slow");

	var variantAttrIdArr = new Array();
	var variantAttrValArr = new Array();

	$('input[name="variant_chk[]"]:checked').each(function(){
		var attr_v = $(this).val();
		var varnt_id = $("#variant_attr_"+attr_v).val();
		variantAttrIdArr.push(varnt_id);
		variantAttrValArr.push(attr_v);
	});

	var attributeIdArr = new Array();
	$('input[name="attribute_chk[]"]:checked').each(function(){
		var attr_id = $(this).val();
		attributeIdArr.push(attr_id);
	});

	var chkArray = new Array();
	  $('input[name="subscription[]"]:checked').each(function(){
		 chkArray.push($(this).val());
	  });


	var priceArray = new Array();
	priceArray.push(minimum_range);
	priceArray.push(maximum_range);

	var sortValue = $('#sort-by option:selected').val();
	var showlimit = $('#show-limit option:selected').val();
	var catId = $('#category-id').val();
	var current_viewmode = $('#current_viewmode').val();

	$.ajax({
		type: 'POST',
		url: BASE_URL+'ProductsController/sort_by',
		dataType: 'html',
		data: {sort_val:sortValue, cat_Id:catId, subscription:chkArray, price_range:priceArray, variantId:variantAttrIdArr, variantVal:variantAttrValArr, attributeArr:attributeIdArr,current_viewmode:current_viewmode,show_limit:showlimit},
		beforeSend: function () {
			$('#ajax-spinner').show();
			$('#product-list-section').hide();
		},
		success: function(response){

			$('#ajax-spinner').hide();
			$('#product-list-section').show();
			$('#product-list-section').html(response);
		}
	});
}

function sort_by(page, ajaxType="", lastpagenum="") {

	window.prevUrl = window.location.href;

	$("html, body").animate({ scrollTop: 0 }, "slow");

	var page = page?page:0;
	var p1 = $("#href_"+page).text();
	if(p1=='' && lastpagenum!=''){
		p1=lastpagenum;
	}
	var variantAttrIdArr = new Array();
	var variantAttrValArr = new Array();

	$('input[name="variant_chk_base[]"]:checked').each(function(){
		var attr_v = $(this).val();
		var attr_v1 = attr_v.replace(/'/g, "");
		var reg = attr_v.split(/[ ,]+/);
		//console.log(reg.length);
		var varnt_id =$("#base_color_variant_id_value").val();
		// variantAttrIdArr.push(varnt_id);
		reg.forEach((element) => {
		    variantAttrValArr.push(element);
		    variantAttrIdArr.push(varnt_id);
		});
	});

	$('input[name="variant_chk[]"]:checked').each(function(){
		var attr_v = $(this).val();
		var varnt_id = $("#variant_attr_"+attr_v).val();
		variantAttrIdArr.push(varnt_id);
		variantAttrValArr.push(attr_v);
	});

	var attributeIdArr = new Array();
	$('input[name="attribute_chk[]"]:checked').each(function(){
		var attr_id = $(this).val();
		attributeIdArr.push(attr_id);
	});

	var chkArray = new Array();
	$('input[name="subscription[]"]:checked').each(function(){
		chkArray.push($(this).val());
	});

	var priceArray = new Array();
	var minimum_range = $('#min_price').val();
	var maximum_range = $('#max_price').val();
	priceArray.push(minimum_range);
	priceArray.push(maximum_range);

	var sortValue = $('#sort-by option:selected').val();
	var showlimit = $('#show-limit option:selected').val();
	var catId = $('#category-id').val();
	var current_viewmode = $('#current_viewmode').val();

	if(p1 !=""){
		setGetParameter('page',p1);
	}else{
		removeParam('page');
	}

	if(showlimit != ""){
		setGetParameter('limit',showlimit);
	}else{
		removeParam('limit');
	}

	if(sortValue != ""){

		setGetParameter('sort',sortValue);
	}else{
		removeParam('sort');
	}


	if(current_viewmode !=""){
		setGetParameter('viewmode',current_viewmode);
	}else{
		removeParam('viewmode');
	}

	if(chkArray !="" && chkArray.length > 0){
		setGetParameter('subscription',chkArray.toString());
	}else{
		removeParam('subscription');
	}

	if(variantAttrIdArr !="" && variantAttrIdArr.length > 0){
		setGetParameter('variantId',variantAttrIdArr.toString());
	}else{
		removeParam('variantId');
	}

	if(variantAttrValArr !="" && variantAttrValArr.length > 0){
		setGetParameter('variantVal',variantAttrValArr.toString());
	}else{
		removeParam('variantVal');
	}

	if(attributeIdArr !="" && attributeIdArr.length > 0){
		setGetParameter('attribute',attributeIdArr.toString());
	}else{
		removeParam('attribute');
	}

	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+'ProductsController/sort_by/'+page,
		data: {sort_val:sortValue, cat_Id:catId, subscription:chkArray, price_range:priceArray, variantId:variantAttrIdArr, variantVal:variantAttrValArr, attributeArr:attributeIdArr,current_viewmode:current_viewmode,show_limit:showlimit,page:p1},
		beforeSend: function () {
			$('#ajax-spinner').show();
			$('#product-list-section').hide();
		},
		success: function (response) {
			$('#ajax-spinner').hide();
			$('#product-list-section').show();
			$('#product-list-section').html(response);

		}
	});
}

	$(window).bind("popstate", function() {
		location.replace(window.prevUrl);
	});

</script>

<script src="<?=base_url('public/js/recliner.js')?>"></script>

  <script type="text/javascript">
   $(function() {

            // instantiate recliner
            $('.lazy').recliner({
                attrib: "data-src", // selector for attribute containing the media src
                throttle: 300,      // millisecond interval at which to process events
                threshold: 100,     // scroll distance from element before its loaded
                live: true          // auto bind lazy loading to ajax loaded elements
            });

            // handle lazyload events
            $(document).on('lazyload', '.lazy', function() {
                var $e = $(this);
                // do something with the element to be loaded...
                console.log('lazyload', $e);
            });

            // handle lazyshow events
            $(document).on('lazyshow', '.lazy', function() {
                var $e = $(this);
                // do something with the loaded element...
                console.log('lazyshow', $e);
            });
        });
  </script>


  </body>
</html>
