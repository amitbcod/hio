<?php $this->load->view('common/fbc-user/header'); ?>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.3/themes/ui-lightness/jquery-ui.css">

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<?php $this->load->view('seller/products/breadcrums'); ?>

	<div class="tab-content">

		<div id="addnew" class="tab-pane fade active show">

			<form name="product-frm-edit" id="product-frm-edit" method="POST" action="<?php echo base_url() ?>seller/product/edit/<?php echo $ProductData->id; ?>" enctype="multipart/form-data">

				<input type="hidden" value="edit" id="current_page" name="current_page">

				<input type="hidden" value="<?php echo $ProductData->id; ?>" id="pid" name="pid">

				<div class="product-details-block">

					<div class="row">

						<div class="col-md-6">

							<?php



							$Rounded_price_flag = $this->CommonModel->getRoundedPriceFlag();

							$ParentCategory = $this->CommonModel->get_category_for_seller();



							$gender = $ProductData->gender;



							if (isset($gender) && $gender != '') {

								$gender_arr = explode(',', $gender);

							} else {

								$gender_arr = array();

							}

							?>

							<h2>Product Details</h2>
							<h2>Product Name (English)</h2>

							<div class="col-sm-12"><input type="text" class="form-control" name="product_name" value="<?php echo $ProductData->name; ?>" id="product_name" placeholder="Product Name *" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" , maxlength='500'></div>

							<div class="col-sm-12"><input type="text" class="form-control" name="product_code" id="product_code" value="<?php echo $ProductData->product_code; ?>" placeholder="Product Code *"></div>

							<div class="col-sm-12">
								<h2>Product Name (French)</h2>
								<input type="text" class="form-control" name="langTitle" id="langTitle" value="<?php echo $ProductData->lang_title; ?>" placeholder="" maxlength='500'>
							</div>
							<div class="col-sm-12">

								<div class="row">

									<h2 class="category-title">Category</h2>

									<div class="col-sm-12" id="category-tree">



										<?php require_once('category_checkbox.php'); ?>

									</div>



								</div>

							</div>


							<!-- <div class="col-sm-12 gender-box">

				<label>Gender</label>

				<div class="gender-box-inner">

					<div class="col-sm-3"><label class="checkbox"><input type="checkbox" name="gender[]" class="form-control" value="Men"  <?php echo (count($gender_arr) > 0 && in_array('Men', $gender_arr)) ? 'checked' : ''; ?>><span class="checked"></span>Men</label></div>

					<div class="col-sm-3"><label class="checkbox"><input type="checkbox" name="gender[]" class="form-control" value="Women" <?php echo (count($gender_arr) > 0 && in_array('Women', $gender_arr)) ? 'checked' : ''; ?>><span class="checked"></span>Women</label></div>

					<div class="col-sm-3"><label class="checkbox"><input type="checkbox" name="gender[]" class="form-control" value="Children" <?php echo (count($gender_arr) > 0 && in_array('Children', $gender_arr)) ? 'checked' : ''; ?>><span class="checked"></span>Children</label></div>

					<div class="col-sm-3"><label class="checkbox"><input type="checkbox" name="gender[]" class="form-control" value="Unisex"  <?php echo (count($gender_arr) > 0 && in_array('Unisex', $gender_arr)) ? 'checked' : ''; ?>><span class="checked"></span>Unisex</label></div>

				</div>

			</div> -->


							<div class="col-sm-12">

								<h2>Description (English)<span class="required">*</span></h2>

								<textarea class="form-control" id="description" name="description"><?php echo (isset($ProductData->description) && $ProductData->description != '') ? $ProductData->description : ''; ?></textarea>

							</div>

							<div class="col-sm-12">
								<h2>Description (french)</h2>

								<textarea class="form-control product-highlight-textarea" id="lang_description" name="lang_description"><?php echo (isset($ProductData->lang_description) && $ProductData->lang_description != '') ? $ProductData->lang_description : ''; ?></textarea>

							</div>

						</div><!-- col-md-6 -->



						<div class="col-md-6">

							<h2>Product Highlights (English)<span class="required">*</span></h2>
							
							<div class="col-sm-12"><textarea class="form-control product-highlight-textarea" id="highlights" name="highlights"><?php echo (isset($ProductData->highlights) && $ProductData->highlights != '') ? $ProductData->highlights : ''; ?></textarea></div>

							<h2>Product Highlights (french)</h2>

							<div class="col-sm-12"><textarea class="form-control product-highlight-textarea" id="lang_highlights" name="lang_highlights"><?php echo (isset($ProductData->lang_highlights) && $ProductData->lang_highlights != '') ? $ProductData->lang_highlights : ''; ?></textarea></div>

							


							<div class="col-sm-12">

								<h2>Merchant</h2>

								<select name="product_publication" class="form-control" id="product_publication">

									<option value="">Select Merchant</option>

									<?php foreach ($publication as $pub) : ?>

										<option value="<?php echo $pub->id ?>" <?php echo ($ProductData->publisher_id == $pub->id) ? 'selected' : '' ?>><?php echo $pub->vendor_name ?></option>

									<?php endforeach; ?>

								</select>

							</div>



							<div class="container col-sm-12">

								<h2>Type of Commission</h2>

							</div>

							<div class="container col-sm-12">

								<form>

									<label class="radio-inline">

										<input type="radio" name="type-of-commission" <?php echo ($ProductData->pub_com_per_type == 0) ? 'checked' : '' ?> value="0">Merchant Commission

										<span class="checkmark"></span></label>

									<label class="radio-inline">

										<input type="radio" name="type-of-commission" <?php echo ($ProductData->pub_com_per_type == 1) ? 'checked' : '' ?> value="1">Product Commission

										<span class="checkmark"></span></label>

								</form>

							</div>



							<?php if ($ProductData->pub_com_per_type == 0) {

								$readonly = "readonly";

							} else {

								$readonly = "";

							}

							?>



							<div class="col-sm-12">

								<h2>Merchant Commission Percentage</h2>

								<input type="text" class="form-control" name="pub_com_percentage" id="pub_com_percentage" placeholder="Merchant Commission Percentage" value="<?php echo (isset($ProductData->pub_com_percent) && $ProductData->pub_com_percent != '') ? $ProductData->pub_com_percent : ''; ?>">

							</div>



							<div class="col-sm-12">

								<h2>Product Review Code</h2>

								<input type="text" class="form-control" name="product_reviews_code" id="product_reviews_code" placeholder="Product Review Code" value="<?php echo (isset($ProductData->product_reviews_code) && $ProductData->product_reviews_code != '') ? $ProductData->product_reviews_code : ''; ?>">

							</div>





							<div class="col-sm-12">

								<h2>Launch Date</h2>

								<input type="text" class="form-control" id="launch_date" name="launch_date" readonly placeholder="Launch Date" value="<?php echo (isset($ProductData->launch_date) && $ProductData->launch_date != '0') ? date('d-m-Y', $ProductData->launch_date) : ''; ?>">

							</div>



							<div class="col-sm-12">

								<h2>Meta Title</h2>

								<input type="text" class="form-control" name="meta_title" id="meta_title" placeholder="" value="<?php echo (isset($ProductData->meta_title) && $ProductData->meta_title != '') ? $ProductData->meta_title : ''; ?>">

							</div>

							<div class="col-sm-12">

								<h2>Meta Keyword</h2>

								<input type="text" class="form-control" name="meta_keyword" id="meta_keyword" placeholder="" value="<?php echo (isset($ProductData->meta_keyword) && $ProductData->meta_keyword != '') ? $ProductData->meta_keyword : ''; ?>">

							</div>





							<div class="col-sm-12">

								<h2>Meta Description</h2><textarea class="form-control product-highlight-textarea " id="meta_description" name="meta_description"><?php echo (isset($ProductData->meta_description) && $ProductData->meta_description != '') ? $ProductData->meta_description : ''; ?></textarea>

							</div>



							<div class="col-sm-12">

								<h2>Search Keywords</h2>

								<input type="text" class="form-control" name="search_keywords" id="search_keywords" value="<?php echo (isset($ProductData->search_keywords) && $ProductData->search_keywords != '') ? $ProductData->search_keywords : ''; ?>" placeholder="">

							</div>

							<div class="col-sm-12">

								<h2>Promo Reference</h2>

								<input type="text" class="form-control" name="promo_reference" id="promo_reference" value="<?php echo (isset($ProductData->promo_reference) && $ProductData->promo_reference != '') ? $ProductData->promo_reference : ''; ?>" placeholder="">

							</div>



							<div class="col-sm-12">

								<h2 class="product-status-head product-drop-shipment-head">Status</h2>

								<div class="radio">

									<label><input type="radio" name="status" <?php echo (isset($ProductData->status) && $ProductData->status == '1') ? 'checked' : ''; ?> value="1">Enabled <span class="checkmark"></span></label>

								</div><!-- radio -->

								<div class="radio">

									<label><input type="radio" name="status" <?php echo (isset($ProductData->status) && $ProductData->status == '2') ? 'checked' : ''; ?> value="2">Disabled <span class="checkmark"></span></label>

								</div><!-- radio -->

							</div>



							<div class="col-sm-12">

							<h2 class="product-approval-head product-drop-shipment-head">Approval Status</h2>



							<div class="radio">

								<label>

									<input type="radio" name="approval_status" 

										<?php echo (isset($ProductData->approval_status) && $ProductData->approval_status == '0') ? 'checked' : ''; ?> 

										value="0">

									Pending <span class="checkmark"></span>

								</label>

							</div>

							<div class="radio">

								<label>

									<input type="radio" name="approval_status" 

										<?php echo (isset($ProductData->approval_status) && $ProductData->approval_status == '1') ? 'checked' : ''; ?> 

										value="1">

									Approved <span class="checkmark"></span>

								</label>

							</div>

							<div class="radio">

								<label>

									<input type="radio" name="approval_status" 

										<?php echo (isset($ProductData->approval_status) && $ProductData->approval_status == '2') ? 'checked' : ''; ?> 

										value="2">

									Rejected <span class="checkmark"></span>

								</label>

							</div>

							</div>





							<h2 class="product-drop-shipment-head">Coming Soon Flag</h2>

							<div class="col-sm-12">

								<div class="radio">

									<label><input type="radio" name="coming-product" <?php echo (isset($ProductData->coming_soon_flag) && $ProductData->coming_soon_flag == '0') ? 'checked' : ''; ?> value="0">No<span class="checkmark"></span></label>

								</div><!-- radio -->

								<div class="radio">

									<label><input type="radio" name="coming-product" <?php echo (isset($ProductData->coming_soon_flag) && $ProductData->coming_soon_flag == '1') ? 'checked' : ''; ?> value="1">Yes <span class="checkmark"></span></label>

								</div><!-- radio -->

							</div>

							<h2 class="product-drop-shipment-head">Is Fragile</h2>

							<div class="col-sm-12">

								<div class="radio">

									<label><input type="radio" name="is_fragile" <?php echo (isset($ProductData->is_fragile_flag) && $ProductData->is_fragile_flag == '0') ? 'checked' : ''; ?> value="0">No<span class="checkmark"></span></label>

								</div><!-- radio -->

								<div class="radio">

									<label><input type="radio" name="is_fragile" <?php echo (isset($ProductData->is_fragile_flag) && $ProductData->is_fragile_flag == '1') ? 'checked' : ''; ?> value="1">Yes <span class="checkmark"></span></label>

								</div><!-- radio -->

							</div>



						</div><!-- col-md-6 -->



						<div class="col-md-6">

							<h2>Product Media <span class="required">*</span></h2>



							<div class="col-sm-12">

								<div class="" id="media-block">

									<input type="file" class="custom-file-input" id="gallery_image" name="gallery_image" multiple onchange="preview_images();" accept="image/*">

									<div class="uploadPreview" id="uploadPreview">

										<svg for="customFile" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-upload" fill="currentColor" xmlns="http://www.w3.org/2000/svg">

											<path fill-rule="evenodd" d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />

											<path fill-rule="evenodd" d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z" />

										</svg>

										<p>Upload media for the product</p>

										<p>Upload image only in jpg,jpeg,png format.Maximum 5 images allowed. </p>

										<?php

										$shop_id		=	$this->session->userdata('ShopID');

										$shop_upload_path = 'shop' . $shop_id;

										$MediaPath = IMAGE_URL_SHOW . '/products/thumb/';



										if (isset($ProductMedia) && count($ProductMedia) > 0) {

											foreach ($ProductMedia as $media) {



										?>

												<input type="hidden" value="<?php echo $media->id; ?>" name="media_ids[]" class='m-img'>

												<span class="single-img radio" id="media-file-<?php echo $media->id; ?>">

													<a href="javascript:void(0);" onclick="removeMediaFile(<?php echo $media->id; ?>)" class="rm-media">X</a>

													<img src="<?php echo $MediaPath . $media->image; ?>" class="thumb">

													<label>

														<input type="radio" name="default_image" <?php echo (isset($media->is_base_image) && $media->is_base_image == 1) ? 'checked' : ''; ?> value="<?php echo $media->image; ?>">&nbsp;

														<span class="checkmark"></span>

													</label>

												</span>



										<?php

											}

										}

										?>



									</div>

									<input type="hidden" name="deleted_md" id="deleted_md" value="">



								</div>

							</div>

							<div>

								<p>Upload PDF</p>

								<!-- File input for PDF upload -->

								<input type="file" id="digit_pdf" name="digit_pdf" accept="application/pdf" onchange="updatePDFLink(this)" />



								<?php

								// Media path for uploaded PDFs

								$MediaPath = IMAGE_URL_SHOW2 . '/digit_pdf/' . $ProductData->id . '/';





								$digit_pdf = $ProductData->digit_pdf ?? null;

								$fileExtension = $digit_pdf ? strtolower(pathinfo($digit_pdf, PATHINFO_EXTENSION)) : null;

								$pdf_img = IMAGE_URL_SHOW2 . '/pdf_img.png'; // Path to your PDF icon

								// print_r($digit_pdf);die;

								// Initialize preview and link for PDFs only

								$previewSrc = null;

								$previewLink = null;



								if ($fileExtension === 'pdf') {

									$previewLink = $MediaPath . $digit_pdf;

									$previewSrc = $pdf_img;

								}

								?>



								<!-- Preview Link and Image -->

								<?php if ($previewLink && $previewSrc): ?>

									<a id="pdf_preview_link" href="<?= htmlspecialchars($previewLink, ENT_QUOTES, 'UTF-8') ?>" target="_blank">

										<img id="pdf_image_preview" src="<?= htmlspecialchars($previewSrc, ENT_QUOTES, 'UTF-8') ?>" alt="PDF Preview" style="width:100px;height:100px;">

									</a>

								<?php else: ?>

									<a id="pdf_preview_link" href="#" style="display: none;width:100px;height:100px;">

										<img id="pdf_image_preview" src="" alt="" style="display: none;width:100px;height:100px;">

									</a>

								<?php endif; ?>

							</div>





						</div><!-- col-md-6 -->



						<div class="col-md-6">

							<h2>Product Shipment</h2>

							<div class="col-sm-12">

								<label>Product Delivery Duration</label>

								<input type="text" class="form-control" id="estimate_delivery_time" name="estimate_delivery_time" placeholder="Estimate Delivery Time" value="<?php echo (isset($ProductData->estimate_delivery_time) && $ProductData->estimate_delivery_time != '') ? $ProductData->estimate_delivery_time : ''; ?>"><span class="days-span">Days</span>

							</div>



							<div class="col-sm-12 d-none">

								<label>Product Return Duration</label>

								<input type="text" class="form-control" id="product_return_time" readonly name="product_return_time" onkeypress="return isNumberKey(event);" placeholder="Product Return Time" value="<?php if (isset($product_return_duration)) {

																																																							echo $product_return_duration->value;

																																																						} ?>"><span class="days-span">Days</span>

							</div>

							<!-- <div class="container col-sm-12">

								<h2>Product Shipping Charge</h2>

							</div> -->

							<!-- <div class="container col-sm-12">

								<label class="radio-inline">

									<input type="checkbox" id="shipping_charges" name="type-of-shipping" value="1">Has Shipping charges

									<span class="checkmark"></span></label>



							</div> -->



							<!-- <div class="col-sm-12">

								<input type="number" class="form-control" name="shipping_amount" id="shipping_amount" onkeypress="return isNumberKey(event);" value="<?php echo $ProductData->shipping_amount ?>" placeholder="Product Shipping Charge">

							</div> -->



							<!-- <h2 class="product-drop-shipment-head">Product Drop-Shipment</h2>

                <div class="radio">

                    <label><input type="radio" name="product_drop_shipment" <?php //echo (isset($ProductData->product_drop_shipment) && $ProductData->product_drop_shipment=='1')?'checked':''; 

																			?> value="1">Allow <span class="checkmark"></span></label>

                </div>

                <div class="radio">

                    <label><input type="radio" name="product_drop_shipment"  <?php //echo (isset($ProductData->product_drop_shipment) && $ProductData->product_drop_shipment=='0')?'checked':''; 

																				?> value="0">Deny <span class="checkmark"></span></label>

                </div>



			<h2 class="product-drop-shipment-head">Product Can Be Returnable</h2>

				<div class="col-sm-12">

					<div class="radio">

						<label><input type="radio" name="product-return" <?php //echo (isset($ProductData->can_be_returned) && $ProductData->can_be_returned=='0')?'checked':''; 

																			?> value="0">No <span class="checkmark"></span></label>

					</div>

					<div class="radio">

						<label><input type="radio" name="product-return" <?php //echo (isset($ProductData->can_be_returned) && $ProductData->can_be_returned=='1')?'checked':''; 

																			?> value="1">Yes <span class="checkmark"></span></label>

					</div>

				</div> -->

						</div>



						<div class="col-md-12 product-variant product-attributes " id="attribute_list_outer">



							<h2>Product Attributes <span class="product-variant-button " id="add_attr_bottom">

									<?php if (empty($this->session->userdata('userPermission')) || in_array('seller/database/write', $this->session->userdata('userPermission'))) { ?>

										<button type="button" onclick="OpenAttributeList('edit_attr');"> + &nbsp; Add Attribute</button>

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

										<?php include('load_ep_attributes.php'); ?>

									</tbody>

								</table>

								<?php

								$selected_attributes = (isset($selected_attributes) && count($selected_attributes) > 0) ? implode(',', $selected_attributes) : ''; ?>

								<input type="hidden" name="added_attr" id="added_attr" value="<?php echo $selected_attributes; ?>">



							</div>

						</div>



						<?php

						if (isset($ProductData) && $ProductData->product_type == 'bundle') {

						?>

							<div class="col-md-12 product-variant">

								<h2>Add Bundle items <span class="required">*</span></h2>

								<div class="row">

									<div class="row col-sm-5">

										<p class="col-sm-12"> Simple / Conf-Simple</p>

										<div class="col-sm-6 pad-zero">

											<input type="text" class="form-control" id="barcode_item" name="barcode_item" placeholder="Barcode" onmouseover="this.focus();" autofocus><br>

											<input type="text" class="form-control" id="sku" placeholder="Product Name - SKU">

										</div>

										<!-- <div class="col-sm-3 pad-zero"><input value="1" type="text" name="qty" id="qty" class="form-control pos-top-25" placeholder="Quantity"></div> -->

										<?php if (empty($this->session->userdata('userPermission')) || in_array('seller/database/write', $this->session->userdata('userPermission'))) { ?>

											<div class="col-sm-6 pad-zero"><button class="purple-btn pos-top-25" onclick="AddBundleProduct(); return false;"> Enter</button>

											</div>

										<?php } ?>

										<label class="error" id="barcode-error"></label>

									</div>

									<div class="row col-sm-2 bundle-middle"></div>

									<div class="row col-sm-5">

										<p class="col-sm-12"> Configurable</p>

										<div class="col-sm-6 pad-zero">

											<input type="text" class="form-control" id="sku_config" placeholder="Product Name">

											<div id="config-data"></div>

										</div>

										<!-- <div class="col-sm-3 pad-zero"><input value="1" type="text" name="qty" id="qty" class="form-control pos-top-25" placeholder="Quantity"></div> -->

										<?php if (empty($this->session->userdata('userPermission')) || in_array('seller/database/write', $this->session->userdata('userPermission'))) { ?>

											<!-- <div class="col-sm-6 pad-zero"><button class="purple-btn pos-top-25" onclick="ScanBarcodeManually(); return false;">    Enter</button>

						</div> -->

										<?php } ?>

										<div class="col-sm-6 pad-zero"><button class="purple-btn pos-top-25" onclick="AddBundleConfigProduct(); return false;"> Enter</button>

										</div>

										<label class="error" id="barcode-error-config"></label>

									</div>



								</div>

								<div class="row pt-5">

									<div class="col-md-12 ">

										<h2>Manage Bundle contents <span class="required">*</span> </h2>

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

												<tbody id="bundleItemdata">

													<?php

													$bundleCalculationDisplay = 'No';

													if (isset($BundleProduct) && count($BundleProduct) > 0) {

														$bundleCalculationDisplay = 'Yes';

														foreach ($BundleProduct as $bundleArr) {



															$bundle_product_id = $bundleArr['product_id'];

															$bundle_id = $bundleArr['id'];



															if (!empty($_POST['barcode_code']) && $_POST['barcode_code'] != '') {

																// $whereArray=array('barcode'=>$_POST['barcode_code'],'remove_flag'=>0,'product_type !='=>'configurable');

																$whereArray = array('product_id' => $bundle_product_id);

															} elseif (!empty($_POST['sku']) && $_POST['sku'] != '') {

																// $whereArray=array('sku'=>$_POST['sku'],'remove_flag'=>0,'product_type !='=>'configurable');

																$whereArray = array('product_id' => $bundle_product_id);

															}



															$whereArray = array('id' => $bundle_product_id);

															$result_data = $this->SellerProductModel->getSingleDataByID('products', $whereArray, '*');



															if (!empty($BundleProduct) && $BundleProduct != '') {



																// $id=$result_data->id;

																$id = $bundle_id;

																$barcode = $result_data->barcode;

																$product_type = $bundleArr['product_type'];

																$productName = $result_data->name;

																if (isset($product_type) && $product_type == 'configurable') {

																	$sku = '';

																	$sku_product_code = $result_data->product_code;

																	/*if(!empty($bundleArr['variant_options'])){

										$vdata=$this->SellerProductModel->bundleProductVarientName($bundleArr['variant_options']);

										print_r($vdata);

										//$productName=$result_data->name.'('.$vdata.')';

									}*/

																} else {

																	$sku = $result_data->sku;

																	$sku_product_code = $sku;

																}

																if (isset($product_type) && !empty($product_type) && $product_type == 'conf-simple') {

																	$parent_id = $bundleArr['product_parent_id'];

																} else {

																	$parent_id = 0;

																}

																$tax_amount = $bundleArr['tax_amount'];

																$row_tax_amount = $tax_amount * $bundleArr['default_qty'];

																//$row_tax_amount= $tax_amount * $bundleArr->default_qty;

																//$productName='<input type="text" name="productName[]" id="productName_'.$id.'" value="'.$result_data->name.'" class="form-control" onkeypress="return isNumberKey(event);">';





																$defaultQty = '<input type="text" name="bundle_default_qty[]" id="stock_qty_' . $id . '" value="' . $bundleArr['default_qty'] . '" class="form-control" onkeypress="return isNumberKey(event);" onblur="calculate_bundle_selling_price_qty(' . $id . ',' . $Rounded_price_flag . ');">';

																$sellingPrice = '<input type="text" name="price[]" id="price_' . $id . '" value="' . $bundleArr['price'] . '" class="form-control" onkeypress="return isNumberKey(event);" onblur="calculate_bundle_webshop_price(' . $Rounded_price_flag . ',' . $id . ');" >';

																$tax_percent = '<input type="text" name="bundle_tax_percent[]" id="tax_percent_' . $id . '" value="' . $bundleArr['tax_percent'] . '" class="form-control" onkeypress="return isNumberKey(event);" onblur="calculate_bundle_webshop_price(' . $Rounded_price_flag . ',' . $id . ');" >';

																$webshopPrice = '<input type="text" name="bundle_webshop_price[]" id="webshop_price_' . $id . '" value="' . $bundleArr['webshop_price'] . '" class="form-control " onkeypress="return isNumberKey(event);" readonly>'; //onkeypress="return isNumberKey(event);" readonly=""

																$position = '<input type="number" onkeypress="return isNumberKey(event);" id="bundle_position_' . $id . '" name="bundle_position[]" class="form-control" value="' . $bundleArr['position'] . '">';

																$removedData = "$(this).closest('tr')";

																if (isset($bundleArr['variant_options']) && !empty($bundleArr['variant_options'])) {

																	$bundle_variant_options_change = str_replace("'", '"', $bundleArr['variant_options']);



																	$variant_optionsData = $this->CommonModel->getAttributesOptions($bundle_variant_options_change);

																} else {

																	$variant_optionsData = '';

																}

																$VariantsRestrict = $variant_optionsData;

																$removed = '<input type="button" onClick="removeEdit(' . $removedData . ',' . $Rounded_price_flag . ',' . $bundle_id . ');" value="Remove" >';

																$hiddenValues = '<input type="hidden"  name="bundle_row_type[]"  value="edit">

												<input type="hidden"  name="bundle_id_row[]"  value="' . $bundle_id . '">

												<input type="hidden" name="bundle_product_type[]"  value="' . $product_type . '">

												<input type="hidden" id="bundle_sku_' . $id . '" name="bundle_sku[]"  value="' . $sku . '">

												<input type="hidden" id="bundle_barcode_' . $id . '" name="bundle_barcode[]"  value="' . $barcode . '">

												<input type="hidden" id="bundle_product_id_' . $id . '" name="bundle_product_id[]"  value="' . $id . '">

												<input type="hidden" id="bundle_product_parent_id_' . $id . '" name="bundle_product_parent_id[]"  value="' . $parent_id . '">

												<input type="hidden" id="bundle_variant_options_' . $id . '" name="bundle_variant_options[]"  value="">

												<input type="hidden" id="bundle_tax_amount_' . $id . '" name="bundle_tax_amount[]"  value="' . $tax_amount . '">

												<input type="hidden" class="row_tax_amount" id="tax_amount_' . $id . '" name="tax_amount[]"  value="' . $row_tax_amount . '">';

																$hiddenQtyTotalValues = '<input type="hidden" id="qty_total_' . $id . '"  class="bundleSellingPrice" value="' . $bundleArr['price'] * $bundleArr['default_qty'] . '"> <input type="hidden" id="webshop_qty_total_' . $id . '"  class="bundleWebshopPrice" value="' . $bundleArr['webshop_price'] * $bundleArr['default_qty'] . '">';

																$trData = '<tr><td>' . $sku_product_code . '</td><td>' . $barcode . '</td><td>' . $productName . '</td><td>' . $VariantsRestrict . '</td><td>' . $defaultQty . '</td><td>' . $sellingPrice . '</td><td>' . $tax_percent . '</td><td>' . $webshopPrice . '</td><td>' . $position . '</td><td>' . $removed . '</td>' . $hiddenValues . $hiddenQtyTotalValues . '</tr>';

													?>



																<?php echo $result = $trData; ?>





													<?php





															}

														}

													}



													?>

												</tbody>

											</table>

											<table class="table table-bordered table-style <?php if ($bundleCalculationDisplay != 'Yes') {

																								echo 'd-none';

																							} ?>" id="bundleTotal">

												<tr>

													<td><b>Bundle Selling Price</b></td>

													<td><input type="number" name="bundle_price" id="bundle_price" onload="calculate_bundle_webshop_selling_price(<?= $Rounded_price_flag ?>);" value="<?php if (isset($ProductData->price)) {

																																																			echo $ProductData->price;

																																																		} ?>"></td>

												</tr>

												<tr>

													<td><b>Bundle Webshop Price</b></td>

													<td><input type="number" name="bundle_webshopprice" id="bundle_webshop_price" onload="calculate_bundle_webshop_selling_price(<?= $Rounded_price_flag ?>);" value="<?php if (isset($ProductData->webshop_price)) {

																																																							echo $ProductData->webshop_price;

																																																						} ?>"></td>

												</tr>

												<?php if (isset($ProductData) && $ProductData->product_type == 'bundle') { ?>

													<input type="hidden" name="product_inv_type" value="buy">

													<input type="hidden" name="tax_amount" id="tax_amount" value="<?php if (isset($ProductData->tax_amount)) {

																														echo $ProductData->tax_amount;

																													} ?>">

												<?php } ?>

											</table>

										</div>

									</div>

								</div>

								<!-- <div class="table-responsive text-center" id="variant_info"></div> -->

							</div>





						<?php

						} else {

						?>



							<div class="col-md-12 product-variant " id="<?php echo ($ProductData->product_type == 'configurable') ? 'variant_info_block' : 'single_info_block'; ?>">

								<h2>Product <?php echo ($ProductData->product_type == 'simple') ? 'Stock' : 'Variants'; ?> <span class="required">*</span>

									<?php if ($ProductData->product_type == 'configurable') { ?>



										<span class="product-variant-button"><button type="button" onclick="Addvariantsinglerow('add_variant');">+ &nbsp; Add Field</button> <button type="button" onclick="AddAdditionalVariant('edit_variant');"><i class="fas fa-edit"></i> &nbsp; Edit Variant</button></span>



									<?php } ?>

								</h2>

								<input type="hidden" id="variant_products_count" value="<?php if (isset($variant_products_count)) {

																							echo $variant_products_count;

																						} ?>">

								<input type="hidden" id="get_own_products_count" value="<?php if (isset($get_own_products_count)) {

																							echo $get_own_products_count;

																						} ?>">



								<?php if ($ProductData->product_type == 'configurable' || $ProductData->product_type == 'simple') { ?>

									<?php if ($ProductData->product_type == 'simple' && ($ProductData->product_inv_type == 'buy')) { 	?>

										<!-- No Apply btn -->

									<?php } elseif ($ProductData->product_type == 'configurable' && $variant_products_count == $get_own_products_count) { ?>

										<!-- No Apply btn -->

									<?php } else {  ?>

										<?php if (empty($this->session->userdata('userPermission')) || in_array('seller/database/write', $this->session->userdata('userPermission'))) { ?>

											<div class="make-virtual">

												<div class="make-virtual-select">

													<select class="" name='inv_type_dp' id='inv_type_dp'>

														<option value="">Select Type</option>

														<option value="buy">Make Buy</option>

														<option value="virtual">Make Virtual</option>

														<option value="dropship">Make Dropship</option>

													</select>

													<button class="virtual-apply" name='type_apply' id='type_apply'>Apply</button>

												</div>

											</div>

										<?php } ?>

									<?php 	} ?>

								<?php } ?>



								<div class="table-responsive text-center" id="<?php echo ($ProductData->product_type == 'configurable') ? 'variant_info' : 'single_info'; ?>">

									<?php if ($ProductData->product_type == 'configurable') {

										//var_dump($VariantProducts);



									?>





										<table class="table table-bordered table-style">

											<thead>

												<tr>

													<?php if ($variant_products_count != $get_own_products_count) { ?>

														<th><input type="checkbox" name='ckbCheckAllVariants' id="ckbCheckAllVariants"></th>

													<?php } ?>



													<!-- <th>TYPE</th> -->

													<?php

													$sv_arr = array();

													if (isset($VariantMaster) && count($VariantMaster) > 0) {

														foreach ($VariantMaster as $attr) {

															$sv_arr[] = $attr['attr_id'];



													?>

															<th><?php echo $attr['attr_name']; ?> </th>

													<?php }

													} ?>

													<th>INVENTORY</th>

													<th>SHIPPING</th>

													<th>COST PRICE </th>

													<th>SELLING PRICE </th>

													<th>VAT (%) </th>

													<th>ESHOP PRICE </th>

													<th>SKU </th>

													<!-- <th>BARCODE </th> -->

													<!-- <th>WEIGHT (KG)  </th> -->

													<!-- <th>GIFTS</th>

													<th>SUB ISSUE </th> -->

													<!-- <th>MEDIA STATUS</th> -->

													<th>ACTION</th>



												</tr>

											</thead>

											<tbody id="variant_tbody">

												<?php include('load_ep_variants.php'); ?>

											</tbody>

										</table>





										<input type="hidden" name="added_variant" id="added_variant" value="<?php echo implode(',', $sv_arr); ?>">

										<input type="hidden" name="deleted_vs" id="deleted_vs" value="">







									<?php } else {



										$price_input_prop = '';



										//var_dump($ProductStock);



										$qty = (isset($ProductStock) && $ProductStock->qty > 0) ? ($ProductStock->qty - $ProductStock->available_qty) : 0;

									?>





										<table class="table table-bordered table-style">

											<thead>

												<tr>

													<?php if (($ProductData->product_inv_type == 'buy') || $ProductData->product_inv_type == 'virtual' || $ProductData->product_inv_type == 'dropship') { ?>

														<!-- <th><input type="checkbox" name='ckbCheckAllVariants' id="ckbCheckAllVariants"></th> -->

													<?php } ?>

													<!-- <th>TYPE</th> -->

													<th>SKU </th>

													<!-- <th>BARCODE </th> -->

													<!-- <th>WEIGHT (KG)  </th> -->

													<th>INVENTORY</th>

													<th>COST PRICE </th>

													<th>SELLING PRICE </th>

													<th>VAT (%) </th>

													<th>ESHOP PRICE </th>

													<!-- <th>GIFTS</th>

													<th>SUB ISSUE </th> -->

												</tr>

											</thead>

											<tbody>

												<tr>



													<?php

													if ($ProductData->product_inv_type == 'buy') {

														if ($ProductData->id > 0) { ?>

															<!-- <input type="checkbox" id="ckb_Variant"> -->

															<!-- <td>

								<label class="checkbox">

							  	<input type="checkbox"  name="ckb_Variant[]" value="<?php echo $ProductData->id; ?>"  >

	                              <span class="checked"></span>

	                            </label>

	                        </td> -->

														<?php   } else {

														} // No checkbox

													} elseif ($ProductData->product_inv_type == 'virtual') { ?>

														<td>

															<label class="checkbox">

																<input type="checkbox" name="ckb_Variant[]" value="<?php echo $ProductData->id; ?>">

																<span class="checked"></span>

															</label>

														</td>

													<?php } elseif ($ProductData->product_inv_type == 'dropship') { ?>

														<td>

															<label class="checkbox">

																<input type="checkbox" name="ckb_Variant[]" value="<?php echo $ProductData->id; ?>">

																<span class="checked"></span>

															</label>

														</td>

													<?php }	 ?>





													<!-- <td> <?php //echo ucwords($ProductData->product_inv_type) ; 

																?></td> -->

													<td><input type="text" class="form-control input-sm " name="sku" id="sku" value="<?php echo $ProductData->sku; ?>"></td>

													<!-- <td><input type="text" class="form-control input-sm" name="barcode" id="barcode" maxlength="48" value="<?php //echo $ProductData->barcode; 

																																								?>"></td> -->

													<!-- <td><input type="text" class="form-control input-sm" name="weight" id="weight"  value="<?php //echo $ProductData->weight; 

																																				?>" onkeypress="return isNumberKey(event);"></td> -->

													<td><input type="text" class="form-control input-sm" onkeypress="return isNumberKey(event);" name="stock_qty" id="stock_qty" value="<?php echo $ProductStock->qty; ?>" data-ordered_qty="<?php echo $qty;  ?>"></td>

													<td><input type="number" class="form-control input-sm" onkeypress="return isNumberKey(event);" name="cost_price" id="cost_price" value="<?php echo $ProductData->cost_price; ?>" <?php echo $price_input_prop; ?> <?php if ((isset($ProductData->shop_product_id) && $ProductData->shop_product_id > 0) && $Price_permission_by_shopid->can_increase_price == 0) { ?> max="<?php echo $ProductData->cost_price; ?>" <?php } ?> <?php if ((isset($ProductData->shop_product_id) && $ProductData->shop_product_id > 0) && $Price_permission_by_shopid->can_decrease_price == 0) { ?> min="<?php echo $ProductData->cost_price; ?>" <?php } ?>></td>

													<td><input type="number" class="form-control input-sm  " onkeypress="return isNumberKey(event);" onblur="calculate_webshop_price(<?php echo $Rounded_price_flag ?>);" name="price" id="price" value="<?php echo $ProductData->price; ?>" <?php echo $price_input_prop; ?> <?php if ((isset($ProductData->shop_product_id) && $ProductData->shop_product_id > 0) && $Price_permission_by_shopid->can_increase_price == 0) { ?> max="<?php echo $ProductData->price; ?>" <?php } ?> <?php if ((isset($ProductData->shop_product_id) && $ProductData->shop_product_id > 0) && $Price_permission_by_shopid->can_decrease_price == 0) { ?> min="<?php echo $ProductData->price; ?>" <?php } ?>></td>

													<td><input type="number" class="form-control input-sm" onkeypress="return isNumberKey(event);" onblur="calculate_webshop_price(<?php echo $Rounded_price_flag ?>);" name="tax_percent" id="tax_percent" value="<?php echo ($ProductData->tax_percent > 0) ? $ProductData->tax_percent : ''; ?>"></td>

													<td><input type="number" class="form-control input-sm" onkeypress="return isNumberKey(event);" name="webshop_price" id="webshop_price" value="<?php echo $ProductData->webshop_price; ?>"></td>



												</tr>

											</tbody>

										</table>

									<?php } ?>



								</div>

							</div>



						<?php } // product check 

						?>



						<input type="hidden" class="" id="product_type" name="product_type" value="<?php echo $ProductData->product_type; ?>">

						<div class="save-discard-btn">

							<button type="button" class="white-btn" onclick="gotoLocation('<?php echo base_url() ?>seller/warehouse/'); ">Discard</button>

							<?php if (empty($this->session->userdata('userPermission')) || in_array('seller/database/write', $this->session->userdata('userPermission'))) { ?>

								<button type="button" class="white-btn" onclick="IsConfirmRemoveProduct(<?php echo $ProductData->id; ?>); ">Delete</button>

							<?php } ?>

							<input type="submit" value="Save" name="save_product" class="purple-btn">

						</div>







					</div><!-- row -->

				</div><!-- product-details-block -->

			</form>

		</div>



	</div>

</main>



<script type="text/javascript">

	$(function() {

		CKEDITOR.replace('description', {

			extraPlugins: 'justify',

			extraAllowedContent: "span(*)",

			allowedContent: true,

		});

		CKEDITOR.replace('highlights', {

			extraPlugins: 'justify',

			extraAllowedContent: "span(*)",

			allowedContent: true,

		});
		CKEDITOR.replace('lang_description', {

			extraPlugins: 'justify',

			extraAllowedContent: "span(*)",

			allowedContent: true,

		});
		CKEDITOR.replace('lang_highlights', {

			extraPlugins: 'justify',

			extraAllowedContent: "span(*)",

			allowedContent: true,

		});

		CKEDITOR.dtd.$removeEmpty.span = 0;

		CKEDITOR.dtd.$removeEmpty.i = 0;

	});

</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script src="<?php echo SKIN_JS; ?>seller_product_common.js"></script>

<script src="<?php echo SKIN_JS; ?>seller_product__edit.js"></script>



<script src="<?php echo SKIN_JS; ?>bundle.js"></script>



<script type="text/javascript">

		function updatePDFLink(input) {

		const file = input.files[0];



		if (file && file.type === 'application/pdf') {

			// Update the preview link and image

			const previewLink = document.getElementById('pdf_preview_link');

			const previewImage = document.getElementById('pdf_image_preview');



			// Use a temporary URL for the new PDF

			const tempURL = URL.createObjectURL(file);



			previewLink.href = tempURL; // Update the link to the new PDF

			previewImage.src = "<?= htmlspecialchars($pdf_img, ENT_QUOTES, 'UTF-8') ?>"; // Keep the PDF icon

			previewImage.alt = "PDF Uploaded";



			// Ensure the preview elements are visible

			previewLink.style.display = 'inline';

			previewImage.style.display = 'inline';



			// Cleanup the temporary URL when no longer needed

			previewImage.onload = () => URL.revokeObjectURL(tempURL);

		} else {

			alert('Please upload a valid PDF file.');

			input.value = ""; // Clear invalid input



			// Hide the preview link and image

			const previewLink = document.getElementById('pdf_preview_link');

			const previewImage = document.getElementById('pdf_image_preview');

			previewLink.style.display = 'none';

			previewImage.style.display = 'none';

		}

	}

	function AddBundleProduct() {

		var barcode_item = $('#barcode_item').val();

		var sku = $('#sku').val();

		if (barcode_item == '' && sku == '') {

			$('#barcode_item').addClass('error');

			$('#barcode-error').html('Please enter barcode/sku.');

			return false;

		} else {

			$.ajax({

				url: BASE_URL + "BundleProductsController/checkBundleProduct",

				type: "POST",

				data: {

					barcode_code: barcode_item,

					sku: sku

				},

				success: function(response) {

					var obj = JSON.parse(response);

					$('#bundleItemdata').append(obj.data);

					$('#bundleTotal').removeClass('d-none');

					calculate_bundle_webshop_selling_price(<?= $Rounded_price_flag ?>);

					calculate_bundle_selling_price(<?= $Rounded_price_flag ?>);

					calculate_bundle_tax_amount(<?= $Rounded_price_flag ?>);

					return false;

				}

			});

		}

	}



	//Config Product



	$('#sku_config').autocomplete({

		minLength: 3,

		source: function(request, response) {

			$.getJSON(BASE_URL + "BundleProductsController/getProductChildSkuConfig", {

				term: request.term

			}, function(data) {

				var array = data.error ? [] : $.map(data, function(m) {

					return {

						label: m.name + " - " + m.product_code,

						value: m.name + " - " + m.product_code,

						id: m.id,

						parent_id: m.parent_id,

						product_code: m.product_code,

					};

				});

				response(array);

			});

		},

		select: function(event, ui) {

			$('#sku_config').val(ui.item.value); // save selected id to hidden input

			$("#sku_config").attr("product-id", ui.item.id);

			$("#sku_config").attr("product-code", ui.item.product_code);



			if (ui.item.id) {

				var VariantListData = getVariantList(ui.item.id);

			}

			return false;

		},

		focus: function(event, ui) {

			$('#sku_config').val(ui.item.label);

			$("#sku_config").attr("product-id", ui.item.id);

			$("#sku_config").attr("product-code", ui.item.product_code);

			$('#config-data').html('');

			return false;

		},

		change: function(event, ui) {

			$("#sku_config").val(ui.item ? ui.item.value : "");

		},









	});



	function getVariantList(productId) {

		$.ajax({

			url: BASE_URL + "BundleProductsController/getBundleProductVariant",

			type: "POST",

			data: {

				product_id: productId

			},

			success: function(response) {

				var obj = JSON.parse(response);

				$('#config-data').html(obj);

				//return false;

			}

		});

	}





	function myVarientItemLists(productId, VarientId) {

		$.ajax({

			url: BASE_URL + "BundleProductsController/getBundleProductVariantItemList",

			type: "POST",

			data: {

				product_id: productId,

				varient_id: VarientId

			},

			success: function(response) {

				var obj = JSON.parse(response);

				$('#config-data-inner_' + VarientId).html(obj);

			}

		});



	}







	//Add Config Product



	function AddBundleConfigProduct() {

		var varientMainIds = [];

		var finalVarientData = '';

		var totalMainItem = $("input#varientListMainItem:checked").length;

		$('input[id="varientListMainItem"]:checked').each(function() {

			var mainVarient = this.value;

			var totalSeen = $("input#varientListItem_" + mainVarient + ":checked").length;

			if (totalSeen > 0) {

				$('#barcode-error-config').html('');

				var varientItemIds = [];

				$('input[id="varientListItem_' + mainVarient + '"]:checked').each(function() {

					varientItemIds.push(this.value);





				});

				var varientMainId = "'" + mainVarient + "':'" + varientItemIds + "'";

				varientMainIds.push(varientMainId);

			} else {

				$('#barcode-error-config').html('Please checked varient item.');

				return false;

			}

		});



		if (totalMainItem > 0) {

			finalVarientData = "{" + varientMainIds + "}";

		}



		var productId = $("#sku_config").attr("product-id");

		var productCode = $("#sku_config").attr("product-code");

		var sku = $('#sku_config').val();

		if (sku == '' && productId == '') {

			$('#barcode-error-config').html('Please enter valid product name.');

			return false;

		} else {

			$('#barcode-error-config').html('');

			$.ajax({

				url: BASE_URL + "BundleProductsController/checkBundleConfigProduct",

				type: "POST",

				data: {

					sku: sku,

					productId: productId,

					finalVarientData: finalVarientData,

					productCode: productCode

				},

				success: function(response) {

					$('#barcode-error-config').html('');

					var obj = JSON.parse(response);



					$('#bundleItemdata').append(obj.data);

					$('#bundleTotal').removeClass('d-none');

					calculate_bundle_webshop_selling_price(<?= $Rounded_price_flag ?>);

					calculate_bundle_selling_price(<?= $Rounded_price_flag ?>);

					calculate_bundle_tax_amount(<?= $Rounded_price_flag ?>);

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

		$('.required-field').each(function() {

			$(this).rules("add", {

				required: true,

				messages: {

					required: "Field is required",

				}

			});

		});







		$(".single-file").change(function(e) {



			if (this.disabled) {

				return alert('File upload not supported!');

			}



			var F = this.files;

			if (F && F[0]) {

				for (var i = 0; i < F.length; i++) {

					readMediaFiles(F[i]);

					$(this).parent().find('.md-status').html('Uploaded');

				}

			}

		});



		$('.unique-sku').each(function() {

			$(this).rules("add", {

				notEqualToGroup: ['.unique-sku']

			});

		});



		$('.unique-barcode').each(function() {

			$(this).rules("add", {

				notEqualToGroup: ['.unique-barcode']

			});

		});





		jQuery.validator.addMethod("notEqualToGroup", function(value, element, options) {

			// get all the elements passed here with the same class

			var elems = $(element).parents('form').find(options[0]);

			// the value of the current element

			var valueToCompare = value;

			// count

			var matchesFound = 0;

			// loop each element and compare its value with the current value

			// and increase the count every time we find one

			jQuery.each(elems, function() {

				thisVal = $(this).val();

				if (thisVal == valueToCompare) {

					matchesFound++;

				}

			});

			// count should be either 0 or 1 max

			if (this.optional(element) || matchesFound <= 1) {

				//elems.removeClass('error');

				return true;

			} else {

				//elems.addClass('error');

			}

		}, "Duplicate entry found for this column.");



	});

</script>



<?php $this->load->view('common/fbc-user/footer'); ?>