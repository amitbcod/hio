<?php $this->load->view('common/header'); ?>
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
	<div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="https://parkmapped.com/">Home</a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active">
					Blogs	
					</li>
				</ul>
			</div>
        </div>
    </div><!-- breadcrum section -->
	  
    <div class="static-page product-list-section" id="product-list-section">
		<div class="container">
        <div class="col-md-12">
          <!-- <div class="container"> -->
				<h1>Blog</h1>
<?php if(isset($main_blog_list) && !empty($main_blog_list)){
			foreach ($main_blog_list->blogData as $key => $value) { ?>
<h5 class="mt-3 pt-3"><a href="<?php echo 'blogs'.'/'.$value->url_key; ?>" class="text-dark"><?php echo $value->title; ?></a></h5>

<p><?php echo $value->description; ?>...<a href="<?php echo 'blogs'.'/'.$value->url_key; ?>" class="text-danger"> Read More</a></p>

<?php }} ?>
		  <!-- </div> --> <!-- container-->
		</div><!-- col-md-12-->
		<?php
					if ($PaginationLink) {
						echo $PaginationLink;
					}
					?>
				</div><!-- container -->
	</div><!-- static pages -->
<?php $this->load->view('common/footer'); ?>
<script type="text/javascript">
	function sort_by(page, ajaxType="", lastpagenum="") {
	window.prevUrl = window.location.href;

	var page = page?page:0;
	var p1 = $("#href_"+page).text();

	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+'BlogController/sort_by/'+page,
		data: {page:p1},
		beforeSend: function () {
			$('#ajax-spinner').show();
			$('#product-list-section').hide();
		},
		success: function (response) {

			$('#ajax-spinner').hide();
			$('#product-list-section').show();
			$('#product-list-section').html(response);

		}
	});
}
</script>



