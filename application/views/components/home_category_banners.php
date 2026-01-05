<?php //print_r($banners); 
?>
<?php if (isset($banners) && $banners->is_success == 'true') {  ?>
    <div <?php if($banner_type != 'topbanner') {?> id="myCarousel" <?php } ?> class="carousel slide" data-ride="carousel">
        <!-- Indicators -->
        <ol class="carousel-indicators">
            <?php foreach ($banners->ShopBannerDetails as $key => $banner) { ?>
                <li data-target="#myCarousel" data-slide-to="<?php echo $key; ?>" class="<?php if ($key == 0) {
                                                                                                echo 'active';
                                                                                            } ?>"></li>
            <?php } ?>
        </ol>
        <div class="carousel-inner" style="box-shadow:0px 12px 5px -6px rgba(0,0,0,0.4);">
            <?php foreach ($banners->ShopBannerDetails as $key => $banner) { ?>
                <div class="item <?php if ($key == 0) {
                                        echo 'active';
                                    } ?>">
                    <?php $btn_link = (isset($banner->link_button_to)) ? $banner->link_button_to : '#'; ?>
                    <a href="<?php echo linkUrl($btn_link) ?>" title="<?php echo $banner->heading; ?>">
                        <img src="<?php echo BANNER_IMG . $banner->banner_image; ?>" class="img-responsive" alt="<?php echo ((isset($banner->lang_homeblock_heading) && $banner->lang_homeblock_heading != '') ? $banner->lang_homeblock_heading : $banner->heading); ?>"  id="copyImage">
                    </a>
                </div>
            <?php } ?>
        </div>

        <!-- <pre id="codeBlock" hidden>IMCM04</pre>

        <script>
            // JavaScript to copy the code when the image is clicked
            document.getElementById("copyImage").addEventListener("click", function(event) {
                event.preventDefault(); // Prevent the link from opening
                
                var code = document.getElementById("codeBlock").textContent; // Get the code text

                // Copy to clipboard using the Clipboard API
                navigator.clipboard.writeText(code).then(function() {
                    alert("Coupon code copied!");

                    // Redirect to the desired URL after copying the coupon code
                    window.location.href = "https://indiamags.com/category/childrens-magazines";
                }).catch(function(err) {
                    console.error('Error copying text: ', err);
                });
            });
        </script> -->

    </div>
<?php } ?>