<?php
// echo "<pre>";
// print_r($OrderItemData);die;
$newSubStartDate = date(SIS_DATE_FM, $OrderItemData->sub_start_date);
$newSubEndDate = date(SIS_DATE_FM, $OrderItemData->sub_end_date);

// if($newSubEndDate == '' || $newSubEndDate == '0' || $newSubEndDate == '01/01/1970' || $newSubEndDate == '-3600'){
// 	// print_r("hiiii");
// 	$newSubEndDate= $newSubStartDate;
// }

// if($newSubStartDate == '' || $newSubStartDate == '0' || $newSubStartDate == '01/01/1970' || $newSubStartDate == '-3600' || $newSubStartDate == '1'){
// 	// print_r("hiiii");
// 	$newStartDate=  new DateTime();
// 	$newSubStartDate = $newStartDate->format(SIS_DATE_FM);
// 	// $newConvertedStartDate = date(SIS_DATE_FM, $newSubStartDate);
// }
// if($newSubEndDate == '' || $newSubEndDate == '0' || $newSubEndDate == '01/01/1970' || $newSubEndDate == '-3600' || $newSubEndDate == '1'){
// 	// print_r("hiiii");
// 	$newEndDate=  new DateTime();
// 	$newSubEndDate = $newEndDate->format(SIS_DATE_FM);

// 	$newSubEndDate= $newSubStartDate;
// }

// print_r($newSubStartDate);
// print_r($newSubEndDate);die;
?>

<!-- <script>
	var newSubStartDate = "<?= $newSubStartDate ?>";

	var newSubEndDate = "<?= $newSubEndDate ?>";
	console.log(newSubStartDate);
	console.log(newSubEndDate);
</script> -->


<div class="modal-header">
	<h4 class="head-name">Product Variants</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">×</span>
	</button>

</div>
<div class="modal-body">
	<!-- <p class="are-sure-message">Variants</p> -->
	<div class="row">

		<div class="message-box-popup col-sm-6">
			<p>Start Date</p>
			<input type="text" class="form-control start_date" id="start_date" name="start_date" value="<?php echo ($OrderItemData->sub_start_date > 0) ? $newSubStartDate : date('d/m/Y'); ?>">
		</div>
		<div class="message-box-popup col-sm-6">
			<p>End Date</p>
			<input type="text" class="form-control end_date" id="end_date" name="end_date" value="<?php echo ($OrderItemData->sub_end_date > 0) ? $newSubEndDate : date('d/m/Y'); ?>">
		</div>
	</div>
</div>
<div class="modal-footer">
	<button class="purple-btn" type="button" id="conf-var-btn" onclick="ConfirmVariants(<?php echo $item_id; ?>);">Confirm</button>
</div>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_order_detail.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<!-- <script type="text/javascript">
	var startdate;
	var enddate;
	// set default dates
	var start = new Date();
	$(document).ready(function() {
		console.log("asdasdadasda",newSubStartDate);
		console.log(newSubEndDate);

		$('#start_date').datepicker({
			endDate: start,
			format: 'dd/mm/yyyy',
			// format: 'dd-mm-yyyy',
			autoclose: true,
			setDate: new Date(newSubStartDate),
			// update "toDate" defaults whenever "startdateDate" changes
		}).on('changeDate', function() {
			// set the "toDate" start to not be later than "startdateDate" ends:
			var converted_date = $(this).val();
			console.log("Start date ",converted_date);
			converted_date = converted_date.split('-');
			converted_date = converted_date[2] + '-' + converted_date[1] + '-' + converted_date[0];
			$('#end_date').datepicker('setStartDate', new Date(converted_date));
		}).attr('readonly', 'readonly');


		$('#end_date').datepicker({
			startDate: new Date(),
			// endDate: new Date(),
			format: 'dd/mm/yyyy',
			// setEndDate
			// format: 'dd-mm-yyyy',
			autoclose: true,
			setDate: new Date(newSubEndDate),
			// update "startdateDate" defaults whenever "toDate" changes
		}).on('changeDate', function() {
			// set the "startdateDate" end to not be later than "toDate" starts:
			var converted_date = $(this).val();
			converted_date = converted_date.split('-');
			converted_date = converted_date[2] + '-' + converted_date[1] + '-' + converted_date[0];
			$('#start_date').datepicker('setEndDate', new Date(converted_date));
		}).attr('readonly', 'readonly');
	})
</script> -->