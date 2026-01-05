<?php
$data['ProductData'] = $ProductData = $this->B2BOrdersModel->HBRListDetails();
// echo "<pre>";
// print_r($ProductData);
// die;
// $OrderData = $data['OrderData'];
// echo "<pre>";
// print_r($OrderData);
// die;
$order_ids = array();
$b2b_order_ids = array();

foreach ($ProductData as $item) {
	$order_ids[] = $item['order_id'];
	$b2b_order_ids[] = $item['order_barcode'];
}
// print_r($order_ids);
// die;
$order_ids_string = implode(',', $order_ids);
$b2b_order_ids_string = implode(',', $b2b_order_ids);

// echo "Order IDs: " . $order_ids_string;
// die;

// echo "B2B Order IDs: " . $b2b_order_ids_string;
// die;


$publication_name = '';
$commision_percent = 0;
$grand_total = 0;
foreach ($PublisherDetails as $Publisher) {
	if (isset($Publisher['publication_name'])) {
		$publication_name = $Publisher['publication_name'];
	} else {
		// Handle the case where the 'publisher_id' property doesn't exist
		// echo 'publication_name does not exist in the current Publisher array.';
	}

	if (isset($Publisher['commision_percent'])) {
		$commision_percent = $Publisher['commision_percent'];
	} else {
		// Handle the case where the 'publisher_id' property doesn't exist
		// echo 'commision_percent does not exist in the current Publisher array.';
	}
}
foreach ($OrderData as $order) {
	if (isset($order['grand_total'])) {
		$grand_total = $order['grand_total'];
	} else {
		// Handle the case where the 'publisher_id' property doesn't exist
		// echo 'grand_total does not exist in the current order array.';
	}

	if (isset($order['publisher_id'])) {
		$publisher_id = $order['publisher_id'];
	} else {
		// Handle the case where the 'publisher_id' property doesn't exist
		// echo 'publisher_id does not exist in the current order array.';
	}
}
?>

<div class="modal-header">
	<h4 class="head-name">Beneficiary Details</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">×</span>
	</button>
</div>
<form id="initiate_payment_form" name="initiate_payment_form" method="post" onsubmit="initiatepaymentform('<?php echo $order_ids_string; ?>','<?php echo $b2b_order_ids_string; ?>')">

	<input type="hidden" name="hidden_order_id" name="hidden_order_id" value="<?php echo $order_ids_string; ?>" />
	<!-- <input type="hidden" name="hidden_order_id" name="hidden_order_id" value="<?php echo $b2b_order_ids_string; ?>" /> -->

	<input type="hidden" name="hidden_b2b_order_id" name="hidden_b2b_order_id" value="<?php echo $b2b_order_ids_string; ?>" />
	<input type="hidden" name="hidden_publisher_id" name="hidden_publisher_id" value="<?php echo $publisher_id; ?>" />
	<!-- <input type="hidden" name="hidden_publisher_id" name="hidden_publisher_id" value="<?php echo $PublisherPayemntData->publisher_id; ?>" /> -->

	<div class="modal-body">
		<p class="are-sure-message">Please Fill Beneficary Details</p>
		<div class="message-box-popup col-sm-12">
			<?php
			// $whuso_income = (($PublisherDetails->commision_percent  / 100) * $OrderData->grand_total);
			// $Payable_Amount = $OrderData->grand_total  - $whuso_income;



			$totalGrandTotal = 0;
			$hidden_totalGrandTotal = 0;
			// foreach ($get_order_details as &$item) {
			// echo "<pre>";
			// print_r($get_order_details);
			// die;
			if ($publication_name == 'Harvard Business Review') {
				foreach ($get_order_details as &$item) {
					// echo "<pre>";
					// print_r($item);
					// die;
					$current_order_id = $item['order_id'];
					// print_r($current_order_id);

					$order_ids = explode(',', $order_ids_string);
					// print_r($order_ids);
					// die;
					if (in_array($current_order_id, $order_ids)) {
						$whuso_income = ($commision_percent / 100) * $item['grand_total'];
						$Payable_Amount = $item['grand_total'] - $whuso_income;
						// print_r($Payable_Amount);

						// Update the item array with calculated values
						$item['whuso_income'] = $whuso_income;
						$item['Payable_Amount'] = $Payable_Amount;

						// Accumulate total grand_total
						$totalGrandTotal += $Payable_Amount;
						// echo "Total Grand Total: " . number_format($totalGrandTotal, 2);
					} else {
					}
				}
			} else {
			}
			$whuso_income = ($commision_percent / 100) * $grand_total;
			// $Payable_Amount = $OrderData->grand_total - $whuso_income;

			$hidden_totalGrandTotal = $grand_total - $whuso_income;
			// }
			?>
			<?php
			// print_r($totalGrandTotal);
			// die;

			?>

			<div class="row">
				<div class="col-sm-5 customize-add-inner-sec page-content-textarea-small">
					<label for="bannerHeading">Beneficiary Acc No. <span class="required">*</span></label>
					<input class="form-control" type="text" name="bene_acc_no" id="bene_acc_no" value="<?= $PublisherPayemntData->beneficiary_acc_no ?? '' ?>" placeholder="Enter Beneficiary Acc No." onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="50" required>

					<label for="bannerHeading">Beneficiary IFSC Code. <span class="required">*</span></label>
					<input class="form-control" type="text" name="bene_ifsc_code" id="bene_ifsc_code" value="<?= $PublisherPayemntData->beneficiary_ifsc_code ?? '' ?>" placeholder="Enter Beneficiary IFSC code here" maxlength="50" required>

					<label for="bannerHeading">Amount Payable <span class="required">*</span></label>
					<input class="form-control" type="text" name="amount_payable" id="amount_payable" value="<?php echo  number_format($totalGrandTotal, 2); ?>" placeholder="Enter Category Name here" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="50" readonly>

					<input type="hidden" name="hidden_amount_payable" id="hidden_amount_payable" value="<?php echo  number_format($hidden_totalGrandTotal, 2); ?>">

					<div class="clear pad-bt-40"></div>
					<label for="bannerDescription"> Remarks</label>
					<textarea class="form-control" name="remarks" id="remarks" placeholder="Remarks Area" maxlength="250" required></textarea>

					<div class="clear pad-bt-40"></div>

					<div class="uploadPreview" id="uploadPreview">
						<img src="" width="200">
					</div>
				</div>
				<!-- col-sm-6 -->
				<div class="col-sm-7 customize-add-inner-sec">

					<label for="bannerHeading">Beneficiary Name <span class="required">*</span></label>
					<input class="form-control" type="text" name="beneficiary_name" id="beneficiary_name" value="<?= $PublisherPayemntData->beneficiary_name ?? '' ?>" placeholder="Enter Category Name here" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength="50" required>

					<div class="clear pad-bt-40"></div>
					<label for="position">Status <span class="required">*</span> </label>
					<select name="status" id="status" class="form-control">
						<option value="N">N</option>
						<option value="I">I</option>
					</select>

					<div class="clear pad-bt-40"></div>





				</div>
				<div class="download-discard-small pos-ab-bottom">

					<!-- <button class="white-btn" >Discard</button> -->
					<input type="submit" class="download-btn" name="InitiateFormsubmit" id="InitiateFormsubmit" value="Save">
					<!-- <button type="submit" id="categorybtn" class="download-btn">Save</button> -->

				</div>
				<!-- col-sm-6 -->
</form>

</div>
</div>
</div>
<!--<div class="modal-footer">
    <button class="purple-btn" type="button" id="conf-qty-scan-btn" onclick="ConfirmQtyScan(<?php echo $OrderData->order_id; ?>,<?php echo  $OrderData->order_id;  ?>);">Confirm Scan </button>
</div>-->