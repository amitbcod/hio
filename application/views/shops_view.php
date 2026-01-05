<?php $this->load->view('common/header'); ?>


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
							<div style="text-align:center;" class="page-title category-title">
								<h1>Marketplace</h1>
							</div>
							<p class="category-image">
								<img id="product-collection-image-1" alt="CsMarketplace Banner" height="100%" width="100%" src="./statis_pages/shop_marketplace_files/yellow-markets-merchant-directory-5_2_.jpg">
							</p>
							<div class="category-description std" style="text-align: justify;">
								<hr>
								<span style="font-size: 10pt;">Welcome to Yellow Markets' directory of merchants to quickly navigate your local online marketplace in Mauritius. With hundreds of merchants listing their products and services, Yellow Markets makes it easy for you to find what you're looking for. Whether it is an electronic store, auto detailing shop, or a custom jewellery maker, we have a wide range of merchants to choose from. You can easily search by business name and location. With so much variety, you are sure to find a business that meets your requirements. In case you do not find a specific merchant here, you can search Mauritius Yellow Pages as well. Thanks for visiting Yellow Markets!</span>
							</div><!--<div class="category-description std">-->
							<!--</div>-->
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
							</div>
						</div>
					</div>
					<div class="col-sidebar"><div class="sidebar sidebar-main"><div class="shipping">
						<h2>Search Merchants</h2>
						<form id="vshop-filter-form">
							<input name="form_key" type="hidden" value="ZlIJkWQhQeEVQLYB">            
							<ul class="form-list" style="padding-left:0px">
								<li>
									<label for="region_id" class="">State/Province</label>
									<div class="input-box">
										<select name="state" id="state" class="form-control">
                                          	<option value="">-- Select State --</option>
											<?php foreach ($state as $s): ?>
												<option value="<?php echo $s['id']; ?>">
													<?php echo $s['state_name']; ?>
												</option>
											<?php endforeach; ?>
                                    </select> 
									</div>
								</li>	
								<li>
									<label for="city">City</label>
									<div class="input-box">
										<select name="city" id="city" placeholder="Select City" class="form-control">
											<option value="">-- Select City --</option>
											<?php foreach ($city as $c): ?>
												<option value="<?php echo $c['id']; ?>">
													<?php echo $c['city_name']; ?>
												</option>
											<?php endforeach; ?>
										</select>
										
									</div>
								</li>
								<li>
									<label for="zipcode">Zip Code</label>
									<div class="input-box">
										<input class="input-text validate-zipcode form-control" type="text" id="zipcode" name="zipcode" value="">
									</div>
								</li>
							</ul>
							<div class="buttons-set">
								<button type="submit" title="Search" class="btn btn-primary w-100"><span><span>Search</span></span></button>             
							</div>
						</form>	
					</div>
			</div>
		</div>
	</div>
</main>

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
    var base_url = "<?php echo base_url(); ?>";
</script>

<script>
	$(document).ready(function() {
		loadShops(1); // Load all shops by default
		$("#state").on("change", function () {
			var stateId = $(this).val();

			// Reset city dropdown
			$("#city").empty().append('<option value="">-- Select City --</option>');

			if (stateId != "") {
				$.ajax({
					url: base_url + "CheckoutController/getCities", // Your controller function
					type: "POST",
					data: { state_id: stateId },
					dataType: "json",
					success: function (res) {
						if (res.status == "success" && res.cities.length > 0) {
							$.each(res.cities, function (i, city) {
								$("#city").append(
									'<option value="' + city.id + '">' + city.city_name + '</option>'
								);
							});
						}
					},
				});
			}
		});
	});
	function loadShops(page) {
		var state   = $('#state').val();
		var city    = $('#city').val();
		var zipcode = $('#zipcode').val();

		$.ajax({
			url: base_url + "shops/fetch/" + page,
			method: "GET",
			data: { state: state, city: city, zipcode: zipcode },
			success: function(data) {
				$('#shop_table').html(data);
			}
		});
	}

	// Handle search form submit
	$('#vshop-filter-form').on('submit', function(e) {
		e.preventDefault();
		loadShops(1); // Reload from page 1 with filters
	});

	// Handle pagination click
	$(document).on('click', '.pagination li a', function(e){
		e.preventDefault();
		var page = $(this).attr('data-ci-pagination-page');
		loadShops(page);
	});

</script>

<?php $this->load->view('common/footer'); ?>
