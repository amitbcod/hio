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

   .post-meta.details a,
   .blog-home li a {
      color: #ff0000;
   }
</style>
<div class="breadcrum-section">
   <div class="container">
      <ul class="breadcrumb">
         <li><a href="https://indiamags.com/">Home</a></li>
         <li><a href="<?php echo '/blogs'; ?>">Blog</a></li>
         <li class="active">
            <?php echo $blogBredcrumb; ?></li>
      </ul>
   </div>
</div>
<!-- breadcrum section -->
<div class="static-page product-list-section" id="product-list-section">
   <div class="container">
      <div class="col-md-12">
         <!-- <div class="container"> -->
         <h2><?php echo $blogBredcrumb; ?></h2>
         <p class="mt-4"><?php echo $MainBlogDescription; ?></p>
         <?php if (isset($blogDetails->blogData->blogChildDetails) && !empty($blogDetails)) {
            $key = 0;
            // echo "<pre>";
            // print_r($blogDetails);die;
            foreach ($blogDetails->blogData->blogChildDetails as $key => $value) {
               $key++;
         ?>            
               <div class="row">
                  <div class="col-lg-9">
                     <h2 class="mt-3 pt-3">
                        <a href="<?php echo '/product-detail' . '/' . $value->url_key[0] ?>" target="_blank" rel="noopener noreferrer" class="text-dark"><?php echo $key . '. ' . $value->title; ?></a>
                     </h2>
                     <p><?php echo $value->description; ?></p>
                     <ul style="list-style:none">
    <?php 
    if (isset($value->variant_options) && !empty($value->variant_options)) {
        foreach ($value->variant_options as $key => $variant) {
            $productId = $variant->product_id;
            $price = $variant->webshop_price;

            // Check if special price exists
            if (isset($value->allSpecialPrices->$productId)) {
                $special = $value->allSpecialPrices->$productId;

                $now = time();
                if ($now >= $special->special_price_from && $now <= $special->special_price_to) {
                    $price = $special->special_price;
                }
            }
            ?>
            <li>
                <?php echo $variant->attr_options_name; ?> 
                <span class="font-weight-bold"><?php echo 'Rs ' . number_format($price, 2); ?></span>
            </li>
        <?php 
        }
    } 
    ?>
</ul>

                     <p class="subscription-form"><a href="<?php echo '/product-detail' . '/' . $value->url_key[0] ?>" target="_blank" rel="noopener noreferrer">SUBSCRIBE NOW</a></p>
                  </div>
                  <div class="col-lg-3">
                  <a href="<?php echo '/product-detail' . '/' . $value->url_key[0] ?>" target="_blank" rel="noopener noreferrer"><img src="<?php echo PRODUCT_THUMB_IMG.$value->productImage[0]->base_image ?>" style="float: right;width: 200px;padding: .53em; border: 1px solid #e8e4e3;background: #fff;max-width: 100%;height: auto !important;display: block;"></a>
                  </div>
               </div>
               
               <br>
         <?php }
         } ?>
         <!-- </div> --> <!-- container-->

      </div>
      <div class="row">
         <div class="col-md-6">
            <?php if (isset($prev_url) && $prev_url != '') { ?>
               <p class="post-meta details">
                  <span class="post-skips">
                     <a href="<?php echo $prev_url; ?>" class="post-skip post-skip-previous" title="Top 10 Entertainment Magazines in India 2021">← Previous Post</a>
                  </span>
               </p>
            <?php } ?>
         </div>
         <div class="col-md-6 ">
            <?php if (isset($next_url) && $next_url != '') { ?>
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
   function sort_by(page, ajaxType = "", lastpagenum = "") {
      window.prevUrl = window.location.href;
      var page = page ? page : 0;
      var p1 = $("#href_" + page).text();

      $.ajax({
         type: "POST",
         dataType: "html",
         url: BASE_URL + 'BlogController/sort_by/' + page,
         data: {
            page: p1
         },
         beforeSend: function() {
            $('#ajax-spinner').show();
            $('#product-list-section').hide();
         },
         success: function(response) {

            $('#ajax-spinner').hide();
            $('#product-list-section').show();
            $('#product-list-section').html(response);

         }
      });
   }
</script>