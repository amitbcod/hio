<?php
// echo "<pre>";
// print_r($OrderItems);
// die;
?>
<?php $item_count = count($OrderItems); ?>
<?php if (($current_tab == 'split-order' && $OrderData->system_generated_split_order == 0) || ($current_tab == 'create-shipment')  || ($current_tab == 'shipped-order')) { ?>
	<?php } else {
	if ($OrderData->status == 3) {
	} else {
	?>
		<!-- <div class="barcode-qty-box row order-details-sec-top" id="barcode-qty-box">
			<div class="row col-sm-12">
				<div class="col-sm-3 pad-zero barcode-order-details-entry">
					<form method="POST" id="barcode-form" action="<?php echo base_url() ?>B2BOrdersController/scanbarcodemanually">
						<input type="hidden" id="order_id" name="order_id" value="<?php echo $OrderData->order_id; ?>">
						<input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">
						<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/orders/write', $this->session->userdata('userPermission'))) { ?>
							<input type="text" class="form-control barcode_item" placeholder="Barcode" value="" id="barcode_item" name="barcode_item" maxlength="255" onmouseover="this.focus();" autofocus>
							<button type="submit" onclick="return ScanBarcodeManually();" class="btn btn-sm barcode-icon"><i class="fas fa-search"></i></button>
							<label class="error" id="barcode-error"></label>
						<?php } ?>
					</form>
				</div>
			</div>
		</div> -->
<?php }
} ?>
<div class="table-responsive text-center">
	<table class="table table-bordered table-style" id="DT_B2BOrderItems">
		<thead>
			<tr>
				<?php if ($current_tab != 'create-shipment') { ?>
					<!-- <th>Action</th> -->
				<?php } ?>
				<th>SKU</th>
				<th>Product Name</th>
				<th>Variants </th>
				<th>Start Date </th>
				<th>End Date </th>
				<th>Qty Ordered </th>
				<!-- <th>Gift</th> -->
				<th class="<?php echo ($current_tab == 'split-order' && $OrderData->system_generated_split_order == 1) ? '' : 'd-none'; ?>">Qty Delivered </th>
				<th class="<?php echo ($current_tab == 'order') ? '' : 'd-none'; ?>">Inventory</th>
				<th class="<?php echo ($current_tab == 'split-order' && $OrderData->system_generated_split_order == 1) ? '' : 'd-none'; ?>">Qty Pending</th>
				<!-- <th>Qty Scanned </th> -->
				<th>Location </th>
				<th>Price/Piece </th>
				<th>Total Price </th>
			</tr>
		</thead>
		<tbody>
			<?php if (isset($OrderItems) && count($OrderItems) > 0) {
				foreach ($OrderItems as $item) {
					$total_price = 0;
					$item_class = '';
					$location_prod = $this->B2BOrdersModel->getProdLocation($item->product_id);
					if ($current_tab == 'split-order') {
						$main_oi_qty = $this->B2BOrdersModel->getMainOrderItemQty($OrderData->main_parent_id, $item->product_id);
						$qty_ordered = $main_oi_qty;
						$pending_qty = $this->B2BOrdersModel->getPendingQtyToBeScanned($OrderData->main_parent_id, $item->product_id, 1);
						$TotalRowShipped = $this->B2BOrdersModel->getShippedSingleOrderItems($OrderData->main_parent_id, $item->product_id, 1);
						$delivered_qty = $TotalRowShipped->qty_scanned;
					} else {
						$salesOrderItemsData = $this->B2BOrdersModel->getsalesOrderItemsData($OrderData->webshop_order_id, $item->product_id);
						$salesOrderItemsID = $salesOrderItemsData['item_id'];

						$qty_ordered = $item->qty_ordered;
						$pending_qty = $this->B2BOrdersModel->getPendingQtyToBeScanned($OrderData->order_id, $item->product_id, '');
						$TotalRowShipped = $this->B2BOrdersModel->getShippedSingleOrderItems($OrderData->order_id, $item->product_id);
						$delivered_qty = $TotalRowShipped->qty_scanned;
					}

					if ($item->qty_scanned <= 0) {
						$item_class = 'black-row';
					} elseif (($current_tab == 'order') && ($item->qty_scanned == $qty_ordered)) {
						$item_class = 'green-row';
					} elseif (($current_tab == 'split-order') && ($item->qty_scanned == $qty_ordered)) {
						$item_class = 'green-row';
					} elseif ($item->qty_scanned < $qty_ordered) {
						$item_class = 'orange-row';
					}

					if (($current_tab == 'split-order' && $OrderData->system_generated_split_order == 0) || ($current_tab == 'create-shipment')) {
						$item_class = 'black-row';
					}

					if (isset($salesOrderItemsData) && isset($salesOrderItemsData['sub_start_date'])) {
						$newSubStartDate = date(SIS_DATE_FM, $salesOrderItemsData['sub_start_date']);
					} else {
						$newSubStartDate = '-';
					}

					if (isset($salesOrderItemsData) && isset($salesOrderItemsData['sub_end_date'])) {
						$newSubEndDate = date(SIS_DATE_FM, $salesOrderItemsData['sub_end_date']);
					} else {
						$newSubEndDate = '-';
					}


					if ($item->qty_scanned <= 0) {
						$total_price = 0;
					} else {
						$total_price = $item->price * $item->qty_scanned;
					}
					$variant_html = '';
					if ($item->product_type == 'conf-simple') {
						$product_variants = $item->product_variants;
						if (isset($product_variants) && $product_variants != '') {
							$variants = json_decode($product_variants, true);
							if (isset($variants) && count($variants) > 0) {
								foreach ($variants as $pk => $single_variant) {
									foreach ($single_variant as $key => $val) {
										$variant_html .= '<span class="variant-item">' . $key . ' - ' . $val . '</span><br>';
									}
								}
							}
						} else {
							$variants = '-';
						}
					} else {
						$variants = '-';
					}
				?>
					<tr class="<?php echo $item_class; ?>" id="oi-single-<?php echo $item->item_id; ?>">
						<?php
						if ($OrderData->status == 3) {
							// echo '<td></td>';
						} else {
							if ($current_tab != 'create-shipment') { ?>
								<!-- <td><?php if ($item->qty_ordered == $item->qty_scanned || ($item->qty == 0)) { ?>
									<?php } else { ?>
										<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/orders/write', $this->session->userdata('userPermission'))) { ?>
											<button type="button" class="purple-btn btn-sm" onclick="OpenScanWtihQtyPopup(<?php echo $OrderData->order_id; ?>,<?php echo $item->item_id; ?>);">Scan</button>
										<?php } ?>
									<?php } ?>
								</td> -->
						<?php }
						} ?>
						<td><?php echo $item->sku; ?></td>
						<td><?php echo $item->product_name; ?></td>
						<td><?php echo ($item->product_type == 'conf-simple') ? $variant_html : '-'; ?>
							<button type="button" class="btn btn-primary change-add-n-btn" style="position: static;right: 0;margin-left: 5px;" onclick="OpenVariantPopup(<?php echo $salesOrderItemsID; ?>);">
								<i class="fas fa-edit"></i>
							</button>
						</td>
						<td><?php echo $newSubStartDate; ?></td>
						<td><?php echo $newSubEndDate; ?></td>
						<td><?php echo  $qty_ordered; ?></td>
						<!-- <td><?php //echo ($item->gift_name) ?></td> -->
						<td class="<?php echo ($current_tab == 'split-order' && $OrderData->system_generated_split_order == 1) ? '' : 'd-none'; ?>"><?php echo ($delivered_qty > 0) ? $delivered_qty : 0; ?></td>
						<td class="<?php echo ($current_tab == 'order') ? '' : 'd-none'; ?>"><?php echo $item->qty; ?></td>
						<td class="<?php echo ($current_tab == 'split-order' && $OrderData->system_generated_split_order == 1) ? '' : 'd-none'; ?>"><?php echo ($pending_qty > 0) ? $pending_qty : 0; ?></td>
						<!-- <td><?php echo $item->qty_scanned; ?></td> -->
						<td><?php echo $location_prod; ?></td>
						<td><?php echo $currency_code . ' ' . number_format($item->price, 2); ?></td>
						<td><?php echo ($item->qty_scanned <= 0) ? '0' : $currency_code . ' ' . number_format($total_price, 2); ?></td>
					</tr>
			<?php }
			} ?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		$("#barcode_item").focus();
		$("#barcode_item").keypress(function() {
			$(this).removeClass('error');
			$('#barcode-error').html('');

		});
		$("#barcode-form").submit(function(event) {
			event.preventDefault();
			return false;
		});
		<?php
		if ((($current_tab == 'split-order') && $OrderData->system_generated_split_order == 0) || (isset($pending_scanned_qty) && $pending_scanned_qty <= 0)) {
		?>
			$('#confirm-order-btn').removeAttr('disabled');
			$('#split-order-btn').attr('disabled', true);
			$('#barcode-qty-box').hide();

		<?php } else { ?>
			$('#confirm-order-btn').attr('disabled', true);
			$('#split-order-btn').removeAttr('disabled');
			$('#barcode-qty-box').show();

		<?php } ?>

	});

	function ScanBarcodeManually() {
		$('#barcode-error').html('');
		$('#barcode_item').removeClass('error');
		var order_id = $('#order_id').val();
		var current_tab = $('#current_tab').val();
		var barcode_item = $('#barcode_item').val();
		if (barcode_item == '') {
			$('#barcode_item').addClass('error');
			$('#barcode-error').html('Please enter barcode');
			return false;
		} else {
			$.ajax({
				url: BASE_URL + "B2BOrdersController/scanbarcodemanually",
				type: "POST",
				data: {
					order_id: order_id,
					barcode_item: barcode_item,
					current_tab: current_tab
				},
				beforeSend: function() {},
				success: function(response) {
					$('#barcode_item').val('');
					$("#barcode_item").focus();
					var obj = JSON.parse(response);
					if (obj.status == 200) {
						play('beep-success');
						if (obj.item_id != '') {}
						RefreshOrderItems(order_id, current_tab);
						$("#barcode_item").focus();
						return true;
					} else {
						play('beep-error');
						$('#barcode_item').addClass('error');
						$('#barcode-error').html(obj.message);
						return false;
					}
				}
			});
		}
	}

	function RefreshOrderItems(order_id, current_tab = '') {
		if (order_id != '') {
			$.ajax({
				url: BASE_URL + "B2BOrdersController/refreshOrderItems",
				type: "POST",
				data: {
					order_id: order_id,
					current_tab: current_tab
				},
				success: function(response) {
					if (response != 'error') {
						$('#order-item-outer').html(response);
					} else {
						$('#barcode-error').html('Something went wrong.');
						return false;
					}

				}
			});
		} else {
			return false;
		}

	}


	function play(flag) {
		if (flag == 'beep-success') {
			var beepsound = new Audio(BASE_URL + 'public/beepsounds/beep-success.wav');
		} else {
			var beepsound = new Audio(BASE_URL + 'public/beepsounds/beep-error.wav');
		}
		beepsound.play();
	}


	function OpenScanWtihQtyPopup(order_id, item_id) {
		if (order_id != '' && item_id != '') {
			$.ajax({
				url: BASE_URL + "B2BOrdersController/openScanQtyPopup",
				type: "POST",
				data: {
					order_id: order_id,
					item_id: item_id
				},
				success: function(response) {
					if (response != 'error') {
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					} else {
						return false;
					}

				}
			});
		} else {
			return false;
		}

	}

	function ConfirmQtyScan(order_id, item_id) {
		var qty_scan = $('#qty_scan').val();
		var current_tab = $('#current_tab').val();
		if (qty_scan != '') {
			$('#conf-qty-scan-btn').prop('disabled', true);

			$.ajax({
				url: BASE_URL + "B2BOrdersController/scanitemwithqty",
				type: "POST",
				data: {
					order_id: order_id,
					item_id: item_id,
					qty: qty_scan,
					current_tab: current_tab
				},
				success: function(response) {
					$("#barcode_item").focus();

					var obj = JSON.parse(response);
					if (obj.status == 200) {
						$("#FBCUserCommonModal").modal('hide');
						play('beep-success');
						RefreshOrderItems(order_id, current_tab);
						swal('Success', obj.message, 'success');
						return true;
					} else {
						$('#conf-qty-scan-btn').prop('disabled', false);
						play('beep-error');
						swal('Error', obj.message, 'error');
						return false;
					}

				}
			});

		} else {
			return false;
		}
	}
	function OpenVariantPopup(item_id) {
		if (item_id != '') {
			$.ajax({
				url: BASE_URL + "B2BOrdersController/openVariantPopup",
				type: "POST",
				data: {
					//   order_id:order_id,
					item_id: item_id
				},
				success: function(response) {
					if (response != 'error') {
						$("#FBCUserCommonModal").modal();
						$("#modal-content").html(response);
					} else {
						return false;
					}

				}
			});
		} else {
			return false;
		}

	}

	function ConfirmVariants(item_id) {
		var start_date = $('#start_date').val();
		// console.log(start_date);

		var end_date = $('#end_date').val();
		// console.log(end_date);
		// return false;
		$.ajax({
			url: BASE_URL + "B2BOrdersController/itemwithvar",
			type: "POST",
			dataType: "json",
			data: {
				item_id: item_id,
				start_date: start_date,
				end_date: end_date
			},
			success: function(response) {

				// var obj = JSON.parse(response);
				if (response.status = 200) {
					$("#FBCUserCommonModal").modal('hide');
					// play('beep-success');
					// RefreshOrderItems(item_id);
					swal({
							title: "Updated",
							text: "Product variant updated",
							type: "success",
						},
						function() {
							// location.href = BASE_URL + "/dashboard";
							// location.href = BASE_URL + "WebshopOrdersController/orders";

							location.reload();
						}
					);
				} else {
					$('#conf-var-btn').prop('disabled', false);
					// play('beep-error');
					swal('Error', obj.message, 'error');
					return false;

				}


			}
		});

	}
</script>