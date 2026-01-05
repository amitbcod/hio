<?php $this->load->view('common/header'); ?>
<style>
.vshop-left-cat-filter {/*margin-left:18px;*//*padding: 5px;*/}
</style>

<main id="maincontent" class="page-main">
	<a id="contentarea" tabindex="-1"></a>
	<div class="columns col2-layout">
		<div class="container">
			<div class=" row row-content top-old">
				<div class="top-section-new"> 
					<div class="col-main">    
						<!-- <div class="page-title-wrapper">
							<h2 class="page-title"></h2>
						</div> -->
						<div class="page messages">
							<div data-placeholder="messages"></div>
							<div data-bind="scope: 'messages'">
								<!-- ko if: cookieMessages && cookieMessages.length > 0 --><!-- /ko -->

								<!-- ko if: messages().messages && messages().messages.length > 0 --><!-- /ko -->
							</div>
						</div>
						<div class="column main">
							<input name="form_key" type="hidden" value="ZlIJkWQhQeEVQLYB">
							<div id="authenticationPopup" data-bind="scope:'authenticationPopup', style: {display: 'none'}" style="display: none;">
								<script>window.authenticationPopup = {"autocomplete":"off","customerRegisterUrl":"http:\/\/localhost:8081\/mu\/customer\/account\/create\/","customerForgotPasswordUrl":"http:\/\/localhost:8081\/mu\/customer\/account\/forgotpassword\/","baseUrl":"http:\/\/localhost:8081\/mu\/"}</script>    <!-- ko template: getTemplate() -->
								<!-- /ko -->	
							</div>
							<p class="category-image">
								<img id="product-collection-image-1" alt="CsMarketplace Banner" height="100%" width="100%" 
									src="<?= !empty($shop_details->banner_img) 
											? BASE_URL . '/uploads/banner_img/' . $shop_details->banner_img 
											: '/statis_pages/shop_details_files/company_banner1751634076.jpeg' ?>">
							</p>

							<div class="vendor_list_page">
								<div class="search_vendor" style="float: left;">
									<span></span>
								</div>
								<div class="category-products">
									<div>
										<div class="toolbar toolbar-products">
											<div class="modes">
												<strong class="modes-label" id="modes-label">View as</strong>
												<strong title="Grid" class="modes-mode active mode-grid" data-value="grid">
													<span>Grid</span>
												</strong>
												<a class="modes-mode mode-list" title="List" href="http://localhost:8081/mu//marketplace/shops#" data-role="mode-switcher" data-value="list" id="mode-list" aria-labelledby="modes-label mode-list">
													<span>List</span>
												</a>
											</div>
											<div class="toolbar-sorter sorter">
												<label class="sorter-label" for="sorter">Sort By</label>
												<select id="sorter" data-role="sorter" class="sorter-options">
													<option value="name" selected="selected">Name</option>
												</select>
												<a title="Set Descending Direction" href="http://localhost:8081/mu//marketplace/shops#" class="action sorter-action sort-asc" data-role="direction-switcher" data-value="desc">
													<span>Set Descending Direction</span>
												</a>
											</div>
						
											<div class="field limiter">
												<label class="label" for="limiter">
													<span>Show</span>
												</label>
												<div class="control">
													<select id="limiter" data-role="limiter" class="limiter-options">
																		<option value="5" selected="selected">
																5                </option>
																</select>
												</div>
												<span class="limiter-text">per page</span>
											</div>
										</div>
										<div id="shop_table">
											<!-- Shops table will load here via AJAX -->
										</div>
									</div>
								</div>
								<div style="clear: both;"></div>
							</div>
						</div>
					</div>
					<div class="col-sidebar"><div class="sidebar sidebar-main"><div class="shipping">
						<div class="block block-poll">
							<div class="block-content left-shop-block">
								<div class="pic-shop-div">  
								<?php 
									$shop_image = !empty($shop_details->shop_image) && file_exists(FCPATH.'merchant/public/images/shop_images/'.$shop->shop_image) ? base_url('merchant/public/images/shop_images/'.$shop_details->shop_image) : 'http://via.placeholder.com/135x135';
								
								?>         
								<img src="<?php echo $shop_image; ?>" alt="<?php echo $shop->vendor_name; ?>" class="imagebox">

								<div class="badge-shop-left">
																			<?php 
											$avg = round($rating->avg_rating); // average rating from model

											// Only assign badge if avg rating is 3 or above
											$badge = '';
											if ($avg >= 3) {
												if ($avg == 3) {
													$badge = IMAGE_URL . '/uploads/trust_badges/ym_trusted_badges_3_stars.svg';
												} elseif ($avg == 4) {
													$badge = IMAGE_URL . '/uploads/trust_badges/ym_trusted_badges_4_stars.svg';
												} elseif ($avg == 5) {
													$badge = IMAGE_URL . '/uploads/trust_badges/ym_trusted_badges_5_stars.svg';
												}
											}

										?>

										<?php if($badge): ?>
											<img src="<?= $badge; ?>" alt="Trust Badge" width="100">
										<?php endif; ?>
										</div>
									</div>
								<ul>
									<li>
                           				<label>
                                    		<i class="fa fa-user"></i>
											Trade Name :
										</label>
										<span><?php echo $shop_details->publication_name; ?></span>
                        			</li>
                                    <li>
                                        <label>
                                    		<i class="fa fa-building"></i>
											Company Name :
										</label>
										<span><?php echo $shop_details->company_name ?? ''; ?> </span>
                                	</li>
									<li>
										<label>
                                    		<i class="fa fa-user"></i>
											Name :
										</label>
										<span><?php echo $shop_details->first_name . ' '. $shop_details->last_name; ?></span>
                                                        
                        			</li>
									<li>
                           				<label>
											<i class="fa fa-location-arrow"></i>
											Company Address :
										</label>
										<span><?php echo $shop_details->company_address ?? ''; ?></span>
                                    </li>
                                    <li>
										<label>
                                    		<i class="fa fa-calendar"></i>
											Created At :
										</label>
										<span><?php echo date("d M Y, h:i A", $shop_details->created_at); ?></span>


                                    </li>
									<li>
										<label><i class="fa fa-map-marker"></i>Location :</label>
										<a href="https://www.google.com/maps/place/21%C2%B011&#39;01.6%22N+72%C2%B049&#39;18.2%22E/@21.1837692,72.8191574,17z/data=!3m1!4b1!4m4!3m3!8m2!3d21.1837692!4d72.8217323?hl=en&amp;entry=ttu&amp;g_ep=EgoyMDI1MDcwOC4wIKXMDSoASAFQAw%3D%3D" target="_blank" style="margin-left: 30px;">Located Us</a>
									</li>
                					

            					</ul>	
							</div>
						</div>
						<div class="block block-layered-nav">
							<div class="block-title">
								<strong><span>Merchant Policy</span></strong>
							</div>
							<div class="block-content">
								<ul id="narrow-by-list2">
									<?php if (!empty($shop_details->delivery_policy)) : ?>
										<li>Delivery Policy
											<a href="<?= BASE_URL . '/uploads/delivery_policy/' . $shop_details->delivery_policy ?>" target="_blank">
												<i class="fa fa-download fa-fw" style="margin-left:46px;"></i>
											</a>
										</li>
									<?php endif; ?>

									<?php if (!empty($shop_details->return_policy)) : ?>
										<li>Return Policy
											<a href="<?= BASE_URL . '/uploads/return_policy/' . $shop_details->return_policy ?>" target="_blank">
												<i class="fa fa-download fa-fw" style="margin-left:55px;"></i>
											</a>
										</li>
									<?php endif; ?>

									<?php if (!empty($shop_details->refund_policy)) : ?>
										<li>Refund Policy
											<a href="<?= BASE_URL . '/uploads/refund_policy/' . $shop_details->refund_policy ?>" target="_blank">
												<i class="fa fa-download fa-fw" style="margin-left:53px;"></i>
											</a>
										</li>
									<?php endif; ?>
								</ul>
							</div>

						</div>
						<div class="block block-layered-nav">
							<div class="block-title">
								<strong><span>Browse By</span></strong>
							</div>
							<div class="block-content">
								<dl id="narrow-by-list2">
									<dt>Category</dt>
									<dd class="categorycontainer tree-div" id="tree-div">
										<ul class="level-0 vshop-left-cat-filter root-category root-category-wrapper"><li class="tree-node"><img class="tree-ec-icon tree-elbow-end" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"><input class="cat-fil" onchange="filterProductsByCategory(this)" type="checkbox" name="cat-fil" data-uncheckurl="http://localhost:8081/mu/merchant_shop/commerce-shop.html" value="http://localhost:8081/mu/merchant_shop/commerce-shop.html?cat-fil=313"><label>Arts, Crafts, And Sewing (1)</label></li></ul>            </dd>
								</dl>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
<script>
	function filterProductsByCategory(el) {
		let url = el.value; // URL from checkbox value
		let uncheckedUrl = el.getAttribute("data-uncheckurl");

		// if checked → load filter URL
		// if unchecked → load uncheck/reset URL
		let targetUrl = el.checked ? url : uncheckedUrl;

		// Send AJAX request to load filtered products
		fetch(targetUrl, {
			method: 'GET',
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		})
		.then(response => response.text())
		.then(html => {
			// replace product list inside shop_table
			document.getElementById("shop_table").innerHTML = html;
		})
		.catch(err => {
			console.error("Filter error:", err);
		});
	}
</script>

<script>
    require(
        [
            'jquery',
            'Magento_Ui/js/modal/modal'
        ],
        function(
            $,
            modal
        ) {
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Create New Ticket',
                buttons: []
            };

            var popup = modal(options, $('#popup-modal'));
            $("#click-me").on('click',function(){ 
                $("#popup-modal").modal("openModal");
            });

        }
    );
</script>
<script>
require(['jquery'],function($){
    $(document).ready(function(){
        $('.product-image-photo').hover(
           function () {
              var mainImg = $(this).attr("data-src");
              var sliderImg = $(this).attr("data-slider-src");
              $(this).attr('src', sliderImg);
           }, 
			
           function () {
              var mainImg = $(this).attr("data-src");
              var sliderImg = $(this).attr("data-slider-src");
              $(this).attr('src', mainImg);
           }
        );
    });
});

</script>
<script type="text/javascript">
	function relaod(){
		var param = "";
		var url = "http://localhost:8081/mu/csmarketplace/vshops/index/";
		if (param !== '') {
			url = url+"?product_list_mode="+param;
		}
		window.location.href = url;
	}

</script>
<script>
	require([
		'mage/url',
		'jquery'
	], function() {
		jQuery('#country').val('');
		jQuery('#country').removeAttr('data-validate');

	});


	require([
	'jquery',
	'jquery/ui',
	'regionUpdater'
	], function($){

	});
</script>
<script>
	require(['jquery'], function($){
		$(document).ready( function() {
			$('.page_limiter .page, .page_limiter .action').click(function(){
			var url = $(this).attr('href');
			//alert(url);
			window.location.href(url);
			});
			
			// $('.csmarketplace-vendors-list').hide();
			// $('#mode-list').click(function() {
			//     $('.csmarketplace-vendors-list').show();
			//     $('.csmarketplace-vendors-grid').hide();
				
			// });
			// $('.mode-grid').click(function() {
			//     $('.csmarketplace-vendors-list').hide();
			//     $('.csmarketplace-vendors-grid').show()
			// });
		});
	});
</script>
<script>
	require(['jquery'],function($){
		$(document).ready(function(){
			$('.product-image-photo').hover(
			function () {
				var mainImg = $(this).attr("data-src");
				var sliderImg = $(this).attr("data-slider-src");
				$(this).attr('src', sliderImg);
			}, 
				
			function () {
				var mainImg = $(this).attr("data-src");
				var sliderImg = $(this).attr("data-slider-src");
				$(this).attr('src', mainImg);
			}
			);
		});
	});

</script>

<script>
	require(['jquery'], function($) {
		$(document).ready(function() {
			$('.modes-mode, .sorter-action, .limiter-options,.sorter-options').click(function() {
			var mode = $(this).attr("id");
			var perpage = this.value;
			var href = new URL($(location).attr('href'));
			var params = new URLSearchParams(href.search.slice(1));
			if ($(this).hasClass('modes-mode')) {
				if (mode == "mode-list") {
				params.set('product_list_mode', 'list');
				} else {
				params.delete('product_list_mode');
				}
			} else if ($(this).hasClass('sorter-action')) {
				if (mode == "desc") {
				params.set('product_list_dir', 'desc');
				} else {
				params.delete('product_list_dir');
				}
			}
			else if ($(this).hasClass('limiter-options')) {
				if (mode == "limiter") {
				if(perpage == "5"){
					params.set('product_list_limit', '5');
				}
				else if(perpage == "10"){
					params.set('product_list_limit', '10');
				}
				else if(perpage == "15"){
					params.set('product_list_limit', '15');
				}else if(perpage == "20"){
					params.set('product_list_limit', '20');
				}else if(perpage == "25"){
					params.set('product_list_limit', '25');
				}else if(perpage == "30"){
					params.set('product_list_limit', '30');
				}
					
				}
			else {
				params.delete('product_list_limit');
				}
			}else if($(this).hasClass('sorter-options'))
			{
				if (mode == "sorter") {
				if(perpage == "name"){
					params.set('product_list_order', 'name');
				}else {
				params.delete('product_list_order');
				}
				}
			}

			href.search = params.toString();
			location.href = href;
			});
		});
	});
</script>

<script>
	require([ 'jquery', 'jquery/ui'], function($){
		$(document).on('change', '#state', function(){
			//alert( this.value );
			var param =  this.value;
			$.ajax({
				type:"POST",
				url: 'http://localhost:8081/mu/marketplace/city/city',
				data:{"state_id":param},
				cache: false,
				success:function(response){
					// console.log(response.output);
					var arr = [];
					arr = JSON.parse(response.output);
					$('select[name="city"]').empty();
					$.each(arr,function(key,value){
						$('select[name="city"]').append('<option value="'+value.city_name+'">'+value.city_name+'</option>');
						// alert(value.city_name);
					});
				}
			});
		});
	});
</script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
	$(document).ready(function(){
    	// Get shop_id from URL
		var urlParts = window.location.pathname.split("/");
		var shop_id = urlParts[urlParts.length - 1]; // last part of URL (e.g. 1)

		// Load initial data
		loadShops(shop_id, 1);

		// Function to fetch shop products
		function loadShops(shop_id, page) {
			$.ajax({
				url: "<?php echo site_url('ShopController/fetch_shops_products'); ?>/" + shop_id + "/" + page,
				method: "GET",
				success: function(data) {
					$('#shop_table').html(data);
				}
			});
		}

		// Handle pagination click
		$(document).on('click', '.pagination li a', function(e){
			e.preventDefault();
			var page = $(this).attr('data-ci-pagination-page'); // use CI's attr
			loadShops(shop_id, page);
		});
	});

</script>

<?php $this->load->view('common/footer'); ?>
