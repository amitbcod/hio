
<style type="text/css">
	#dash {
		width: 400px;
		height: 60px;
		overflow: hidden;
	}

	#dash p {
		padding: 10px;
		margin: 0;
	}
</style>


<div class="static-page product-list-section" id="product-list-section">
	<div class="container">
		<div class="blogList col-md-12">
			<!-- <div class="container"> -->
			<h1>Blog</h1>
			<?php 
			
			// Debug removed after confirmation
			if (isset($main_blog_list->blogData->blogData) && !empty($main_blog_list->blogData->blogData)) { 
				foreach ($main_blog_list->blogData->blogData as $key => $value) { ?>
					<div class="blog-wrapper">
						<h5 class="mt-3 pt-3">
							<a href="<?php echo 'blogs/' . $value->url_key; ?>" class="text-dark">
								<?php echo $value->title; ?>
							</a>
						</h5>

						<p>
							<?php echo $value->description; ?>
							<a href="<?php echo 'blogs/' . $value->url_key; ?>" class="text-danger"> Read More</a>
						</p>
					</div>
			<?php 
				} 
			} else {
				echo "<p>No blogs found.</p>";
			}
			?>

			<!-- </div> --> <!-- container-->
		</div><!-- col-md-12-->
		<?php
		if ($PaginationLink) {
			echo $PaginationLink;
		}
		?>
	</div><!-- container -->
</div><!-- static pages -->
<script type="text/javascript">
	function sort_by(page = 1) {
		let page_size = 3; // Define it consistently
		$.ajax({
			type: "POST",
			url: BASE_URL + "BlogController/sort_by",
			data: {
				page: page,
				page_size: page_size
			},
			beforeSend: function () {
				$("#product-list-section").html('<div class="loading">Loading...</div>');
			},
			success: function (html) {
				$("#product-list-section").html(html);
			},
			error: function () {
				$("#product-list-section").html('<div class="error">Something went wrong. Please try again.</div>');
			}
		});
	}
</script>




