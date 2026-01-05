<?php
$getProducts = $data['ProductData'];
$getProductDetails = $data['ProductDetails'];
$getAddressDetails = $data['getAddressDetails'];

// echo "<pre>";
// print_r($getProducts);
// die;
$jsonString = '{"product_variants": [{"Subscription": "1 Time"}]}';

// Decode the JSON string into a PHP array
$data = json_decode($jsonString, true);

// Access the product_variants array and extract the Subscription value
$subscription = $data['product_variants'][0]['Subscription'];

// Format the output
$product_variants = "Subscription - $subscription";

// Display the result
// echo $formattedOutput;
// echo "<pre>";

// print_r($getProductDetails);
// die;
?>
<?php $this->load->view('common/fbc-user/header'); ?>
<style>
	.order-id p span {
		width: 100px;
		display: inline-block;
		font-weight: 500;
	}

	.barcode-qty-box.row.order-details-sec-top span {
		width: 180px;
		vertical-align: top;
	}
</style>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">


	<div class="tab-content">
		<div id="" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">

			<!-- form -->
			<div class="content-main form-dashboard">

				<form id="">

				<?php foreach ($getProducts as $Products) { 
					$formattedDate = date('d/m/Y | h:i A', $Products['created_at']);
					?>
					<input type="hidden" id="id" name="id" value="<?= $Products['quote_id'] ?>">

					<h1 class="head-name">Order Details </h1>
					<div class="barcode-qty-box row order-details-sec-top">
						<div class="col-sm-6 order-id">
							<p><span>Order Number :</span><?= $Products['quote_id'] ?></p>
							<p><span>Purchased on :</span> <?= $formattedDate ?></p>
							<?php
							if ($Products['email_id'] !== null && $Products['email_id'] !== "") { ?>
								<p><span>Email Id :</span> <?= $Products['email_id'] ?></p>
							<?php } else { ?>
								<p><span>Email Id :</span> - </p>
							<?php }
							?>
							<?php
							if ($Products['mobile_no'] !== null && $Products['mobile_no'] !== "") { ?>
								<p><span>Mobile No :</span> <?= $Products['mobile_no'] ?></p>
							<?php } else { ?>
								<p><span>Mobile No :</span> - </p>
							<?php }
							?>

							<!-- <p class="ship-ad-para"><span>Shipping Address :</span> <span class="order-address-inner">
								<?= $getAddressDetails['first_name'] ?> <?= $getAddressDetails['last_name'] ?><br>
								<?= $getAddressDetails['address_line1'] ?> <br>
								<?= $getAddressDetails['city'] ?>, <?= $getAddressDetails['state'] ?> <br>
								<?= $getAddressDetails['country'] ?> <?= $getAddressDetails['pincode'] ?> <br>
								Mob: <?= $getAddressDetails['mobile_no'] ?></span>
								
							</p> -->

							<!-- <p><span>Order Total Quantity :</span> 2</p> -->

						</div>
						<div class="col-sm-6 order-id">
							<?php
							if ($Products['customer_name'] !== null && $Products['customer_name'] !== "") { ?>
								<p><span>Customer Name :</span> <?= $Products['customer_name'] ?></p>
							<?php } else { ?>
								<p><span>Customer Name :</span> - </p>
							<?php }
							?>


							<!-- <p><span class="huge-name">Customer Name :</span><?= $Products['customer_firstname'] ?> <?= $Products['customer_lastname'] ?></p> -->
							<!-- <p><span class="huge-name">Customer Name :</span> Vasu Agarwal  </p> -->
							<p><span class="huge-name">Checkout Method :</span><?= $Products['checkout_method'] ?> </p>

							<!-- <p class="ship-ad-para"><span>Billing Address :</span> <span class="order-address-inner">
								<?= $getAddressDetails['first_name'] ?> <?= $getAddressDetails['last_name'] ?><br>
								<?= $getAddressDetails['address_line1'] ?> <br>
								<?= $getAddressDetails['city'] ?>, <?= $getAddressDetails['state'] ?> <br>
								<?= $getAddressDetails['country'] ?> <?= $getAddressDetails['pincode'] ?> <br>
								Mob: <?= $getAddressDetails['mobile_no'] ?></span>
								

							</p> -->

						</div>
						<?php

						if (count($getAddressDetails) == 2) {
							$shippingAddress = $getAddressDetails[0];
							$billingAddress = $getAddressDetails[1];

							echo '<div class="col-sm-6 order-id">';
							echo '<p class="ship-ad-para"><span>Shipping Address :</span> <span class="order-address-inner">';
							echo $shippingAddress['first_name'] . ' ' . $shippingAddress['last_name'] . '<br>';
							echo $shippingAddress['address_line1'] . '<br>';
							echo $shippingAddress['city'] . ', ' . $shippingAddress['state'] . '<br>';
							echo $shippingAddress['country'] . ' ' . $shippingAddress['pincode'] . '<br>';
							echo 'Mob: ' . $shippingAddress['mobile_no'] . '</span></p>';
							echo '</div>';

							echo '<div class="col-sm-6 order-id">';
							echo '<p class="ship-ad-para"><span>Billing Address :</span> <span class="order-address-inner">';
							echo $billingAddress['first_name'] . ' ' . $billingAddress['last_name'] . '<br>';
							echo $billingAddress['address_line1'] . '<br>';
							echo $billingAddress['city'] . ', ' . $billingAddress['state'] . '<br>';
							echo $billingAddress['country'] . ' ' . $billingAddress['pincode'] . '<br>';
							echo 'Mob: ' . $billingAddress['mobile_no'] . '</span></p>';
							echo '</div>';
						} else {
							echo '<div class="col-sm-6 order-id">';
							echo '<p class="ship-ad-para"><span>Shipping Address :</span> - </p>';
							echo '</div>';

							echo '<div class="col-sm-6 order-id">';
							echo '<p class="ship-ad-para"><span>Billing Address :</span> - </p>';
							echo '</div>';
						}

						?>

					</div>
					<div class="div">
						<div class="table-responsive text-center">
							<table class="table table-bordered table-style" id="DataTables_Table_WebshopOrders">
								<thead>
									<tr>
										<th>SKU</th>
										<th>Product Name</th>
										<th>Variants </th>
										<th>Qty Ordered </th>
										<!-- <th>Inventory</th> -->
										<th>Price/Piece </th>
										<th>Total Price </th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($getProductDetails as $ProductDetails) { ?>
										<tr>
											<td><?php echo $ProductDetails['sku']; ?></td>
											<td><?php echo $ProductDetails['product_name']; ?></td>
											<td><?php echo $product_variants ?></td>
											<td><?php echo $ProductDetails['qty_ordered']; ?></td>
											<td><?php echo $ProductDetails['price']; ?></td>
											<td><?php echo $ProductDetails['total_price']; ?></td>
										</tr>
									<?php  } ?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="save-discard-btn pad-bottom-20 ">
						<button type="button" class="purple-btn" onclick="Sent_Mail(<?= $Products['quote_id'] ?>)">Sent Mail</button>
					</div>
					
					<?php } ?>
				</form>
			</div>

			<!--end form-->
		</div>
	</div>
</main>
<script>
	function Sent_Mail(quote_id) {
		// alert(BASE_URL);
		// console.log(BASE_URL);
		$.ajax({
			url: BASE_URL + "WebshopOrdersController/Sent_Mail",
			type: "POST",
			dataType: "json",
			data: {
				quote_id: quote_id,
			},
			success: function(response) {
				console.log(response);
				// alert(data);
				if (response.status === 200) {
					swal({
							title: "",
							text: "Email Sent Successfully",
							type: "success",
						},
						function() {
							location.href = BASE_URL + "WebshopOrdersController/abundantCartList";
						})
				} else {
					console.log("error");
				}
			},
			error: function(error) {
				console.log(error);
			},
		});
	}
</script>
<!-- <script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_order_detail.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop-order-item.js?v=<?php echo CSSJS_VERSION; ?>"></script> -->

<?php $this->load->view('common/fbc-user/footer'); ?>