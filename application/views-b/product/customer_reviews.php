<div class="customer-review">
	<div class="review-container-main">
		<?php if (isset($reviewResponse) && $reviewResponse != '') { ?>
		<h3>Customers Reviews</h3>
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
					<p>By <?php echo $review->reviwedby; ?>, <?php echo $review->reviewed_on; ?></p>
				</div><!-- rating-comment-customer -->
			</div><!-- customer-review-box -->
			<?php
} ?>
			<?php if ($reviewCountResponse > $limit) { ?>
			<div class="show_more_main_<?php echo $review->id; ?>" onclick="loadMoreReview(<?php echo $review->id; ?>);">
				<button type="button" class="show_more btn btn-blue" title="Load more review">Show More</button>
				<span class="loding" style="display: none;"><span class="loding_txt">Loading</span></span>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
	<div class="customer-review-form">
		<form id="review-rating-form" method="POST" action="<?php echo BASE_URL;?>review">
			<h2>Give Your Review</h2>
			<div class="star-rating-select">
				<input type="hidden" name="product_slug" value="<?php echo $product_slug.'?'.$_SERVER['QUERY_STRING']; ?>">
				<fieldset class="rating">
					<input type="radio" id="star5" name="rating" value="5" />
					<label class="full" for="star5" title="Awesome - 5 stars"></label>  			
					
					<input type="radio" id="star4" name="rating" value="4" />
					<label class="full" for="star4" title="Pretty good - 4 stars"></label>
					<input type="radio" id="star3" name="rating" value="3" />
					<label class="full" for="star3" title="Meh - 3 stars"></label> 
					<input type="radio" id="star2" name="rating" value="2" />
					<label class="full" for="star2" title="Kinda bad - 2 stars"></label>
					<input type="radio" id="star1" name="rating" value="1" />
					<label class="full" for="star1" title="Sucks big time - 1 star"></label>
				</fieldset>

			
			</div><!-- star-rating select -->
			<textarea class="form-control " placeholder="Write here" name="review_content"></textarea>
			<input class="submit-ratings" type="submit" value="Submit">
		</form>
	</div>
	  
	</div><!-- customer-review -->