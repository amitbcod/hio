<?php
$data['ProductData'] = $ProductData = $this->B2BOrdersModel->HBRListDetails();
// echo "<pre>";
// print_r($PublisherPayemntData);
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

// $beneficiary_acc_nos =  array();
// $beneficiary_names =  array();
// $beneficiary_ifsc_codes =  array();
$ids =  array();

foreach ($PublisherPayemntData as $item) {
	$beneficiary_acc_no = $item['beneficiary_acc_no'];
	$beneficiary_name = $item['beneficiary_name'];
	$beneficiary_ifsc = $item['beneficiary_ifsc'];
	$remarks = $item['remarks'];
	$payment_mod = $item['remarks'];
	$ids[] = $item['id'];
}
$id = implode(',', $ids);

?>
<div class="modal-header">
	<h4 class="head-name">Beneficiary Details</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">×</span>
	</button>
</div>
<form id="payment_done_form" name="initiate_payment_form" method="post">
	<input type="hidden" name="hidden_order_id" name="hidden_order_id" value="<?php echo $order_ids_string; ?>" />
	<!-- <input type="hidden" name="hidden_order_id" name="hidden_order_id" value="<?php echo $b2b_order_ids_string; ?>" /> -->

	<input type="hidden" name="hidden_b2b_order_id" name="hidden_b2b_order_id" value="<?php echo $b2b_order_ids_string; ?>" />
	<input type="hidden" name="hidden_publisher_id" name="hidden_publisher_id" value="<?php echo $publisher_id; ?>" />
	<input type="hidden" name="id" name="id" value="<?php echo $id; ?>" />
	<div class="modal-body">
		<p class="are-sure-message">Please Fill Beneficary Details</p>
		<div class="message-box-popup col-sm-12">
			<?php
			// $whuso_income = (($PublisherDetails->commision_percent  / 100) * $OrderData->grand_total);
			// $Payable_Amount = $OrderData->grand_total  - $whuso_income;



			$totalGrandTotal = 0;
			$hidden_totalGrandTotal = 0;
			// foreach ($get_order_details as &$item) {


			if ($publication_name == 'Harvard Business Review') { // Harvard Business Review
				foreach ($get_order_details as &$item) {
					$current_order_id = $item['order_id'];
					$order_ids = explode(',', $order_ids_string);
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

			<div class="row">
				<div class="col-sm-5 customize-add-inner-sec page-content-textarea-small">
					<label for="bannerHeading">Beneficiary Acc No. <span class="required">*</span></label>
					<input class="form-control" type="text" name="bene_acc_no" id="bene_acc_no" value="<?php echo $beneficiary_acc_no; ?>" placeholder="Enter Beneficiary Acc No." onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="50" required readonly>
					<label for="bannerHeading">Beneficiary IFSC Code. <span class="required">*</span></label>
					<input class="form-control" type="text" name="bene_ifsc_code" id="bene_ifsc_code" value="<?php echo $beneficiary_ifsc; ?>" placeholder="Enter Beneficiary IFSC code here" maxlength="50" required readonly>
					<label for="bannerHeading">Amount Payable <span class="required">*</span></label>
					<input class="form-control" type="text" name="amount_payable" id="amount_payable" value="<?php echo  number_format($totalGrandTotal, 2); ?>" placeholder="Enter Category Name here" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="50" readonly>

					<input type="hidden" name="hidden_amount_payable" id="hidden_amount_payable" value="<?php echo  number_format($hidden_totalGrandTotal, 2); ?>">
					<div class="clear pad-bt-40"></div>
					<label for="bannerDescription"> Remarks</label>
					<textarea class="form-control" name="remarks" id="remarks" placeholder="Remarks Area" maxlength="250" required readonly><?php echo $remarks; ?></textarea>

					<div class="clear pad-bt-40"></div>

					<div class="uploadPreview" id="uploadPreview">
						<img src="" width="200">
					</div>
				</div>
				<!-- col-sm-6 -->
				<div class="col-sm-7 customize-add-inner-sec">

					<label for="bannerHeading">Beneficiary Name <span class="required">*</span></label>
					<input class="form-control" type="text" name="beneficiary_name" id="beneficiary_name" value="<?php echo $beneficiary_name; ?>" placeholder="Enter Beneficiary Name here" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength="50" required readonly>



					<div class="clear pad-bt-40"></div>
					<label for="position">Status <span class="required">*</span> </label>
					<select name="status" id="status" class="form-control" readonly>
						<option value="N" <?php echo (isset($payment_mod) && $payment_mod == 'N' ? 'Selected' : '') ?>>Not Initiated</option>
						<option value="I" <?php echo (isset($payment_mod) && $payment_mod == 'I' ? 'Selected' : '') ?>>Initiated</option>
					</select>
					<label for="bannerHeading">UTR NO <span class="required">*</span></label>
					<input class="form-control" type="text" name="utr_no" id="utr_no" value="" placeholder="Enter UTR NO here" maxlength="50" required>
					<div class="clear pad-bt-40"></div>
				</div>
				<div class="download-discard-small pos-ab-bottom">

					<!-- <button class="white-btn" >Discard</button> -->
					<button id="initiate-payment-btn" onclick="paymentdone('<?php echo $id ?>' );" class="download-btn">Payment Done</button>
				</div>
				<!-- col-sm-6 -->
</form>

</div>
</div>
</div>
<!--<div class="modal-footer">
    <button class="purple-btn" type="button" id="conf-qty-scan-btn" onclick="ConfirmQtyScan(<?php echo $OrderData->order_id; ?>,<?php echo  $OrderData->order_id;  ?>);">Confirm Scan </button>
</div>-->
