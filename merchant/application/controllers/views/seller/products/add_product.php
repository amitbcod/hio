<?php $this->load->view('common/fbc-user/header'); ?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.3/themes/ui-lightness/jquery-ui.css">

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('seller/products/breadcrums'); ?>
	<div class="tab-content">
	<div id="addnew" class="tab-pane fade active show">

	<?php //echo "<pre>";print_r($CategoryTree);  ?>
	<form name="product-frm-add" id="product-frm-add" method="POST" action="<?php echo base_url() ?>sellerproduct/add" enctype="multipart/form-data">
	<input type="hidden" value="add" id="current_page" name="current_page">
		<div class="product-details-block">
		<div class="row">
		<div class="col-md-6">
		<?php //$ParentCategory=$this->CommonModel->get_category_for_seller($this->session->userdata('ShopID')); ?>
		<h2>Product Details</h2>
			<div class="col-sm-12"><input type="text" class="form-control" name="product_name" id="product_name" placeholder="Product Name *"></div>
			<div class="col-sm-12"><input type="text" class="form-control" name="product_code" id="product_code" placeholder="Product Code *"></div>
			<div class="row">
				<h2 class="category-title">Category</h2>
				<div class="col-sm-12" id="category-tree">
					<?php require_once('category_checkbox.php'); ?>
				</div>
			</div>
			<div class="col-sm-12 gender-box">
				<label>Gender</label>
				<div class="gender-box-inner">
					<div class="col-sm-3"><label class="checkbox"><input type="checkbox" name="gender[]" class="form-control" value="Men"><span class="checked"></span>Men</label></div>
					<div class="col-sm-3"><label class="checkbox"><input type="checkbox" name="gender[]" class="form-control" value="Women"><span class="checked"></span>Women</label></div>
					<div class="col-sm-3"><label class="checkbox"><input type="checkbox" name="gender[]" class="form-control" value="Children"><span class="checked"></span>Children</label></div>
					<div class="col-sm-3"><label class="checkbox"><input type="checkbox" name="gender[]" class="form-control" value="Unisex"><span class="checked"></span>Unisex</label></div>
				</div>
			</div>

			<div class="col-sm-12">
			<h2>Description <span class="required">*</span></h2>
			<textarea class="form-control" id="description" name="description"></textarea></div>
         </div><!-- col-md-6 -->

		 <div class="col-md-6">
			<h2>Product Highlights <span class="required">*</span></h2>
			<div class="col-sm-12">
				<textarea class="form-control product-highlight-textarea" id="highlights"  name="highlights" ></textarea></div>

				<div class="col-sm-12">
			<h2>Publication<span class="required">*</span></h2>
			<select name="product_publication" class="form-control product_publication" id="product_publication">
    			<option value="">Select Publication</option>
				<?php if(isset($publication) && !empty($publication)){
					foreach($publication as $pub):?>
					<option value="<?php echo $pub->id?>"><?php echo $pub->vendor_name?></option>
				<?php endforeach; }?>
  			</select>
			</div>

				
			<div id="commission_inputs"></div>

			<div class="col-sm-12">
			<h2>Product Review Code</h2>
			<input type="text" class="form-control" name="product_reviews_code" id="product_reviews_code" placeholder="Product Review Code">
			</div>

			<div class="col-sm-12">
			<h2>Launch Date</h2>
			<input type="text" class="form-control" id="launch_date" name="launch_date" value="<?php echo date('d-m-Y'); ?>" readonly placeholder="Launch Date">
			</div>


			<div class="col-sm-12">
			<h2>Meta Title</h2>
			<input type="text" class="form-control" name="meta_title" id="meta_title" placeholder="">
			</div>
			<div class="col-sm-12">
			<h2>Meta Keyword</h2>
			<input type="text" class="form-control" name="meta_keyword" id="meta_keyword" placeholder="">
			</div>


			<div class="col-sm-12"><h2>Meta Description</h2><textarea class="form-control product-highlight-textarea " id="meta_description"  name="meta_description" ></textarea></div>
			<div class="col-sm-12">
			<h2>Search Keywords</h2>
			<input type="text" class="form-control" name="search_keywords" id="search_keywords" placeholder="">
			</div>
			<div class="col-sm-12">
			<h2>Promo Reference</h2>
			<input type="text" class="form-control" name="promo_reference" id="promo_reference" placeholder="">
			</div>
			<div class="col-sm-12">
                <h2 class="product-status-head product-drop-shipment-head">Status</h2>
             	<div class="radio">
                     <label><input type="radio" name="status" value="1">Enabled <span class="checkmark"></span></label>
                </div><!-- radio -->
				<div class="radio">
					<label><input type="radio" name="status" value="2">Disabled <span class="checkmark"></span></label>
				</div><!-- radio -->
			</div>
			<h2 class="product-drop-shipment-head">Coming Soon Flag</h2>
			<div class="col-sm-12">
             	<div class="radio">
                     <label><input type="radio" name="coming-product" checked value="0">No<span class="checkmark"></span></label>
                </div>
				<div class="radio">
					<label><input type="radio" name="coming-product" value="1">Yes <span class="checkmark"></span></label>
				</div>
			</div>

         </div><!-- col-md-6 -->

		<div class="col-md-6">
		<h2>Product Media <span class="required">*</span></h2>
			<div class="col-sm-12">
				<div class="" id="media-block">
				<input type="file" class="custom-file-input" id="gallery_image"  name="gallery_image"  multiple    onchange="preview_images();"  accept="image/*">
				<div class="uploadPreview" id="uploadPreview">
				<svg for="customFile" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-upload" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
				  <path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
				  <path fill-rule="evenodd" d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
				</svg>
				<p  >Upload media for the product</p>
				</div>
			  </div>
			</div>


         </div><!-- col-md-6 -->

		 <div class="col-md-6">
		 <h2>Product Shipment</h2>
			<div class="col-sm-12">
				<label>Product Delivery Duration</label>
				<input type="text" class="form-control" id="estimate_delivery_time" name="estimate_delivery_time" value="<?php if(isset($product_delivery_duration)) { echo $product_delivery_duration->value ; } ?>" placeholder="Estimate Delivery Time">
				<span class="days-span">Weeks</span>
			</div>
			<div class="col-sm-12 d-none">
				<label>Product Return Duration</label>
				<input type="text" class="form-control" id="product_return_time" readonly name="product_return_time" onkeypress="return isNumberKey(event);" value="<?php if(isset($product_return_duration)) { echo $product_return_duration->value ; } ?>" placeholder="Product Return Time">
				<span class="days-span">Days</span>
			</div>
		<!-- <h2 class="product-drop-shipment-head">Product Drop-Shipment</h2>
		   <div class="radio">
			  <label><input type="radio" name="product_drop_shipment" checked value="1">Allow <span class="checkmark"></span></label>
			</div>
			<div class="radio">
			  <label><input type="radio" name="product_drop_shipment" value="0">Deny <span class="checkmark"></span></label>
			</div>

		<h2 class="product-drop-shipment-head">Product Can Be Returnable</h2>
			<div class="col-sm-12">
             	<div class="radio">
                     <label><input type="radio" name="product-return" value="0">No <span class="checkmark"></span></label>
                </div>
				<div class="radio">
					<label><input type="radio" name="product-return" checked value="1">Yes <span class="checkmark"></span></label>
				</div>
			</div> -->
		 </div>

		 <div class="col-md-12 product-variant product-attributes " id="attribute_list_outer">

			 <h2>Product Attributes  <span class="product-variant-button  " id="add_attr_bottom">
			<?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
			 	<button type="button" onclick="OpenAttributeList('add_attr');"> + &nbsp;  Add Attribute</button>
			<?php } ?>
			 	 </span></h2>
				<div class="table-responsive text-center " id="attribute_list">

				 <table class="table table-bordered table-style">
					  <thead>
						<tr>
						  <th>Name</th>
						  <th>Value</th>
						  <th>ACTION</th>
						</tr>
					  </thead>
					  <tbody id="attr_tbody">

					  </tbody>
					</table>

					<input type="hidden" name="added_attr" id="added_attr" value="">

				</div>
		</div>

		<?php if(!empty($_GET) && !empty($_GET['type'])){
			$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag();
			
?>

			<div class="col-md-12 product-variant">
				<h2>Add Bundle items  <span class="required">*</span></h2>
				<div class="row">
					<div class="row col-sm-5">
						<p class="col-sm-12"> Simple / Conf-Simple</p>
						<div class="col-sm-6 pad-zero">
						<input type="text" class="form-control" id="barcode_item" name="barcode_item" placeholder="Barcode" onmouseover="this.focus();" autofocus><br>
						<input type="text" class="form-control" id="sku"  placeholder="Product Name - SKU">
						</div>
						<!-- <div class="col-sm-3 pad-zero"><input value="1" type="text" name="qty" id="qty" class="form-control pos-top-25" placeholder="Quantity"></div> -->
						<?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
							<div class="col-sm-6 pad-zero"><button class="purple-btn pos-top-25" onclick="AddBundleProduct(); return false;">    Enter</button>
							</div>
						<?php } ?>
						<label class="error" id="barcode-error"></label>
					</div>
					<div class="row col-sm-2 bundle-middle"></div>
					<div class="row col-sm-5">
						<p class="col-sm-12"> Configurable</p>
						<div class="col-sm-6 pad-zero">
							<input type="text" class="form-control" id="sku_config"  placeholder="Product Name">
							<div id="config-data" ></div>
						</div>
						<!-- <div class="col-sm-3 pad-zero"><input value="1" type="text" name="qty" id="qty" class="form-control pos-top-25" placeholder="Quantity"></div> -->
						<?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
							<!-- <div class="col-sm-6 pad-zero"><button class="purple-btn pos-top-25" onclick="ScanBarcodeManually(); return false;">    Enter</button>
							</div> -->
						<?php } ?>
						<div class="col-sm-6 pad-zero"><button class="purple-btn pos-top-25" onclick="AddBundleConfigProduct(); return false;">    Enter</button>
						</div>
						<label class="error" id="barcode-error-config"></label>
					</div>

				</div>
				<div class="row pt-5">
					<div class="col-md-12 ">
						<h2>Manage Bundle contents  <span class="required">*</span> </h2>
						<div class="table-responsive text-center">
							<table class="table table-bordered table-style" id="datatableBundleProducts">
								<thead>
									<tr>
										<th>SKU / PRODUCT CODE </th>
										<th>BARCODE </th>
										<th>PRODUCT NAME</th>
										<th>VARIANTS RESTRICT</th>
										<th>DEFAULT QTY</th>
										<th>SELLING PRICE </th>
										<th>TAX (%) </th>
										<th>WEBSHOP PRICE </th>
										<th>POSITION </th>
										<th>ACTION </th>
									</tr>
								</thead>
								<tbody id="bundleItemdata"></tbody>
							</table>
							<table class="table table-bordered table-style d-none" id="bundleTotal">
								<tr><td ><b>Bundle Selling Price</b></td><td ><input type="number" name="bundle_price" id="bundle_price" onload="calculate_bundle_webshop_selling_price(<?=$Rounded_price_flag?>);"></td></tr>
								<tr><td ><b>Bundle Webshop Price</b></td><td ><input type="number" name="bundle_webshopprice" id="bundle_webshop_price" onload="calculate_bundle_webshop_selling_price(<?=$Rounded_price_flag?>);"></td></tr>
								<?php if($_GET['type']==='bundle'){ ?>
										<input type="hidden" name="product_type" value="bundle" >
										<input type="hidden" name="product_inv_type" value="buy" >
										<input type="hidden" name="tax_amount"  id="tax_amount" value="" >
										<!-- <input type="hidden" name="tax_percent"  id="tax_percent" value="" > -->
								<?php } ?>
							</table>
						</div>
					</div>
				</div>
					<!-- <div class="table-responsive text-center" id="variant_info"></div> -->
			</div>

<?php }else{ ?>	


		 <div class="col-md-12 product-variant "  id="variant_info_block">
		 <h2>Product Variants  <span class="required">*</span>
		 <?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
		 	<span class="product-variant-button"><button id="temp_add_var_single" class="d-none" type="button" onclick="Addvariantsinglerow('add_variant');">+ &nbsp;  Add Field</button>  <button  type="button" onclick="OpenVariantsList('add_variant');">+ &nbsp;  Add Variant</button></span>
		 <?php } ?>
		 </h2>
			<div class="table-responsive text-center" id="variant_info"></div>
		 </div>
		 <?php } ?>

		  <div class="col-md-12 product-variant d-none"  id="single_info_block">
			<h2>Product Stock  <span class="required">*</span></h2>
			<div class="table-responsive text-center" id="single_info"></div>
		 </div>
		 <?php if(!isset($_GET['type']) || $_GET['type'] !== 'bundle') { ?>
		 <input type="hidden" class="" id="product_type" name="product_type"  value="simple">
		<?php } ?>

		 <div class="save-discard-btn">

			<button type="button" class="white-btn" onclick="gotoLocation('<?php echo base_url() ?>seller/warehouse/'); ">Discard</button>
		<?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
			<input type="submit" value="Save" name="save_product" id="save_product" class="purple-btn">
		<?php } ?>
		 </div>



	</div><!-- row -->
	</div><!-- product-details-block -->
	</form>
	</div>

	</div>
</main>

<script type="text/javascript">
	$(function () {
      	CKEDITOR.replace('description', {
	     extraPlugins :'justify',
	     extraAllowedContent : "span(*)",
     		allowedContent: true,
	    });
		CKEDITOR.replace('highlights',{
	       extraPlugins :'justify',
			extraAllowedContent : "span(*)",
     		allowedContent: true,
      	});
      	CKEDITOR.dtd.$removeEmpty.span = 0;
      	CKEDITOR.dtd.$removeEmpty.i = 0;
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="<?php echo SKIN_JS; ?>seller_product_common.js"></script>
<script src="<?php echo SKIN_JS; ?>seller_product_add.js"></script>
<script src="<?php echo SKIN_JS; ?>bundle.js"></script>

<script type="text/javascript">
	function AddBundleProduct(){
        var barcode_item=$('#barcode_item').val();
        var sku=$('#sku').val();
        if(barcode_item=='' && sku ==''){
            $('#barcode_item').addClass('error');
            $('#barcode-error').html('Please enter barcode/sku.');
            return false;
        }else{
            $.ajax({
                url: BASE_URL+"BundleProductsController/checkBundleProduct",
                type: "POST",
                data: { barcode_code:barcode_item,sku:sku },
                success: function(response) {
                    var obj = JSON.parse(response);
                    $('#bundleItemdata').append(obj.data);
                    $('#bundleTotal').removeClass('d-none');
                    calculate_bundle_webshop_selling_price(<?=$Rounded_price_flag?>);
                    calculate_bundle_selling_price(<?=$Rounded_price_flag?>);
                    calculate_bundle_tax_amount(<?=$Rounded_price_flag?>);
                    return false;
                }
            });
        }
    }

    //Config Product

    $('#sku_config').autocomplete({
    minLength: 3,
    source: function(request, response) {
        $.getJSON(BASE_URL+"BundleProductsController/getProductChildSkuConfig", {
            term: request.term
        }, function(data) {
            var array = data.error ? [] : $.map(data, function(m) {
                return {
                    label: m.name+" - "+m.product_code,
                    value: m.name+" - "+m.product_code,
					id: m.id,
					parent_id: m.parent_id,
					product_code: m.product_code,
                };
            });
            response(array);
         });
    },
    select: function (event, ui) {
		$('#sku_config').val(ui.item.value); // save selected id to hidden input
		$("#sku_config").attr("product-id",ui.item.id);
		$("#sku_config").attr("product-code",ui.item.product_code);

		if(ui.item.id){
			var VariantListData=getVariantList(ui.item.id);
		}
        return false;
    },
    focus: function( event, ui ) {
		$('#sku_config').val(ui.item.label);
		$("#sku_config").attr("product-id",ui.item.id);
		$("#sku_config").attr("product-code",ui.item.product_code);
		$('#config-data').html('');
        return false;
    },
    change: function( event, ui ) {
		$( "#sku_config" ).val( ui.item? ui.item.value : "" );
    },




});

    function getVariantList(productId){
	$.ajax({
		url: BASE_URL+"BundleProductsController/getBundleProductVariant",
		type: "POST",
		data: {product_id:productId},
		success: function(response) {
			var obj = JSON.parse(response);
			$('#config-data').html(obj);
			//return false;
		}
	});
}


function myVarientItemLists(productId,VarientId){
	$.ajax({
		url: BASE_URL+"BundleProductsController/getBundleProductVariantItemList",
		type: "POST",
		data: {product_id:productId,varient_id:VarientId},
		success: function(response) {
			var obj = JSON.parse(response);
			$('#config-data-inner_'+VarientId).html(obj);
		}
	});

}



//Add Config Product

function AddBundleConfigProduct(){
		var varientMainIds = [];
		var finalVarientData='';
		var totalMainItem = $("input#varientListMainItem:checked").length;
		$('input[id="varientListMainItem"]:checked').each(function() {
			var mainVarient=this.value;
			var totalSeen = $("input#varientListItem_"+mainVarient+":checked").length;
			console.log(totalSeen)
			if(totalSeen > 0){
				$('#barcode-error-config').html('');
				var varientItemIds = [];
				$('input[id="varientListItem_'+mainVarient+'"]:checked').each(function() {
					varientItemIds.push(this.value);

				});

				var varientMainId="'"+mainVarient+"':'"+varientItemIds+"'";
				varientMainIds.push(varientMainId);

			}else{
				$('#barcode-error-config').html('Please checked varient item.');
				return false;
			}
		});

		if(totalMainItem >0){
			finalVarientData = "{"+varientMainIds+"}";
		}
		var productId= $("#sku_config").attr("product-id");
		var productCode= $("#sku_config").attr("product-code");
		var sku=$('#sku_config').val();
		if(sku =='' && productId==''){
			$('#barcode-error-config').html('Please enter valid product name.');
			return false;
		}else{
			$('#barcode-error-config').html('');
			$.ajax({
				url: BASE_URL+"BundleProductsController/checkBundleConfigProduct",
				type: "POST",
				data: {sku:sku,productId:productId,finalVarientData:finalVarientData,productCode:productCode},
				success: function(response) {
					$('#barcode-error-config').html('');
					var obj = JSON.parse(response);
					$('#bundleItemdata').append(obj.data);
					$('#bundleTotal').removeClass('d-none');
					calculate_bundle_webshop_selling_price(<?=$Rounded_price_flag?>);
					calculate_bundle_selling_price(<?=$Rounded_price_flag?>);
					calculate_bundle_tax_amount(<?=$Rounded_price_flag?>);
					return false;
				}
			});
			return false;
		}

		return false;
	}





</script>





<script>
$(document).ready(function() {
//swal("Good job!", "You clicked the button!", "success");
});
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
