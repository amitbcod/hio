<?php if (isset($reviewResponse) && $reviewResponse != '') { ?>
	<h3><?=lang('customer_review')?></h3>
	<div class="reviewlist-section" id="reviewlist-section">
		<?php foreach ($reviewResponse as $review) {
    $class = ($review->rating == 0.5 ? "half-star" : ($review->rating == 1 ? "one-star" : ($review->rating == 1.5 ? "onehalf-star" : ($review->rating == 2 ? "two-star" : ($review->rating == 2.5 ? "twohalf-star" : ($review->rating == 3 ? "three-star" : ($review->rating == 3.5 ? "threehalf-star" : ($review->rating == 4 ? "four-star" : ($review->rating == 4.5 ? "fourhalf-star" : ($review->rating == 5 ? "five-star" : "")))))))))); ?>	
		<div class="customer-review-box">
			<div class="star-rating"></div><!-- star-rating -->
			<div style="margin-top: 10px">
				<div class="result-container">
					<div class="rate-bg <?php echo $class; ?>"></div>
					<div class="rate-stars"></div>
				</div>
			</div>
			<div class="cusomer-review-paragraph">
				<p><?php echo $review->review; ?></p>
			</div><!-- cusomer-review-paragraph -->
			<div class="rating-comment-customer">
				<p><?=lang('by')?> <?php echo $review->reviwedby; ?>, <?php echo $review->reviewed_on; ?></p>
			</div><!-- rating-comment-customer -->
		</div><!-- customer-review-box -->
		<?php
} ?>
		<?php if ($reviewCountResponse > $limit) { ?>
		<div class="show_more_main_<?php echo $review->id; ?>" onclick="loadMoreReview(<?php echo $review->id; ?>);">
			<button type="button" class="show_more btn btn-blue" title="Load more review"><?=lang('show_more')?></button>
			<span class="loding" style="display: none;"><span class="loding_txt"><?=lang('loading')?></span></span>
		</div>
		<?php } ?>
	</div>
<?php } ?>