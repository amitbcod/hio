<?php $this->load->view('common/header'); ?>
<style type="text/css">
   .subscription-form a {
   background: #e21228;
   color: #fff;
   padding: 5px 17px;
   border-radius: 15px;
   margin-top: 0;
   display: inline-block;
   }
   .post-meta.details a, .blog-home li a {
    color: #ff0000;
}
</style>
<div class="breadcrum-section">
   <div class="container">
      <div class="breadcrum">
         <ul>
            <li><a href="<?php echo '/blogs'; ?>">Blog</a></li>
            <li><span class="icon icon-keyboard_arrow_right"></span></li>
            <li class="active">
               <?php echo $blogBredcrumb; ?>
            </li>
         </ul>
      </div>
   </div>
</div>
<!-- breadcrum section -->
<div class="static-page product-list-section" id="product-list-section">
   <div class="container">
      <div class="col-md-12">
         <!-- <div class="container"> -->
         <h2><?php echo $blogBredcrumb; ?></h2>
         <p class="mt-4"><?php echo $MainBlogDescription; ?></p>
         <?php if(isset($blogDetails->blogData->blogChildDetails) && !empty($blogDetails)){
            $key = 0;
            		foreach ($blogDetails->blogData->blogChildDetails as $key => $value) { 
            			$key ++;
            			 ?>
         <h2 class="mt-3 pt-3"><?php echo $key.'. '.$value->title; ?></h2>
         <p><?php echo $value->description; ?></p>
         <ul style="list-syle:none">
            <?php if(isset($value->variant_options) && !empty($value->variant_options)){
               foreach ($value->variant_options as $key => $variants) {
               	?>
            <li><?php echo $variants->attr_options_name; ?><span class="font-weight-bold"><?php echo ' Rs '.$variants->webshop_price; ?></span></li>
            <?php }} ?>
         </ul>
         <p class="subscription-form"><a href="<?php echo '/product-detail'.'/'.$value->url_key[0] ?>" target="_blank" rel="noopener noreferrer">SUBSCRIBE NOW</a></p>
         <?php }} ?>
         <!-- </div> --> <!-- container-->

      </div>
      <div class="row">
         <div class="col-md-6">
      <?php if (isset($prev_url) && $prev_url!='') { ?>
         <p class="post-meta details">
            <span class="post-skips">
            <a href="<?php echo $prev_url; ?>" class="post-skip post-skip-previous" title="Top 10 Entertainment Magazines in India 2021">← Previous Post</a>
            </span>
         </p>
      <?php } ?>
      </div>
      <div class="col-md-6 ">
      <?php if (isset($next_url) && $next_url!='') { ?>
         <p class="post-meta details float-right">
            <span class="post-skips">
            <a href="<?php echo $next_url; ?>" class="post-skip post-skip-next right" title="Top 10 most widely read magazine in world?">Next Post →</a>
            </span>
         </p>
      <?php } ?>
      </div>
      </div>

      <!-- col-md-12-->
   </div>
   <!-- container -->
</div>
<!-- static pages -->
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