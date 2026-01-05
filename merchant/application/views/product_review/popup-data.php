<div class="row">
	<div class="barcode-qty-box row order-details-sec-top">
		<div class="col-sm-12 order-id">
		<p><span>Product Name :</span> <?php echo $ProductReview['name']; ?></p>
		<p><span>Customer Name :</span> <?php echo $ProductReview['first_name']. ' '.$ProductReview['last_name']; ?></p>
		<p><span>Review Given :</span><?php echo $ProductReview['review']; ?> </p>
		<p><span>Rating Given :</span> <?php echo $ProductReview['rating']; ?></p>
		<p><span>Review Date :</span> <?php if(isset($ProductReview['created_at']) && $ProductReview['created_at'] !='') { echo date("d/m/Y" ,$ProductReview['created_at']); } ?></p>
		</div>
	</div>
</div>

