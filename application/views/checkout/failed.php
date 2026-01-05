<?php $this->load->view('common/header'); ?>
<div class="main">
	<div class="container">
		<ul class="breadcrumb">
			<li><a href="<?php echo base_url(); ?>">home<?= lang('home') ?></a></li>
			<!-- <li><span class="icon icon-keyboard_arrow_right"></span></li> -->
			<li class="">checkout<?= lang('checkout') ?></li>
			<!-- <li><span class="icon icon-keyboard_arrow_right"></span></li> -->
			<li class="active">order failed<?= lang('order_failed') ?></li>
		</ul>
		<?php

		if (isset($_GET['sessionId'])) {
			$increment_id = base64_decode($_GET['keys']);
		} elseif ($_GET['key']) {
			$increment_id = base64_decode($_GET['key']);
		} else {
			$increment_id = '';
		}
		$divHide = ""; // hide div
		$divHideCod = ""; // hide div
		$msgImg = 'thankyou-check.png';
		if ($paymentMethodcod == 'cod' && $shop_flag == 2) {
			$divHide = "hide";
		} else {
			$divHideCod = "hide";
		}

		?>
		<div class="row margin-bottom-40">
			<div class="col-md-12">
				<div class="content-page shadow">
					<div class="PymtGtwMsgContainer">
						<img id="processImg" src="<?php echo SKIN_URL; ?>images/process-tick.png" alt="Thank You" class="img-responsive <?= $divHideCod ?> thankyou-check">
						<img id="thankyouImg" src="<?php echo SKIN_URL; ?>images/failure.png" alt="Thank You" class="img-responsive <?= $divHide ?> thankyou-check">
						<h2 class="cod <?= $divHideCod ?>"><?= lang('cod_verification_required') ?></h2>
						<h3 class="thanky <?= $divHide ?>">Something Went Wrong Please Try Again </h3>
						<a class="btn btn-primary" role="button" href="<?php echo base_url(); ?>">Continue Shopping</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('common/footer'); ?>

<script>

</script>

</body>

</html>