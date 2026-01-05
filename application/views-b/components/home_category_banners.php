<?php

    //if (isset($banners->is_success) && $banners->is_success == 'true') { 

        if($banner_type=='homeblock1'){ 
?>
            <div class="site-section site-blocks-2 two-boxes-block">
              <div class="container">
                <div class="row align-items-center">
                   <?php if (isset($banners) && $banners->is_success == 'true') {

                    foreach ($banners->ShopBannerDetails as $block) { ?>
                        <div class="col-sm-6 col-md-6 col-lg-6 mb-4 mb-lg-0">
                            <?php $btn_link = (isset($block->link_button_to)) ? $block->link_button_to : '#'; ?>
                             <a class="block-2-item" href="<?php echo linkUrl($btn_link) ?>" >
                                <figure class="image">
                                  <img data-src="<?php echo BANNER_IMG.'/uploads/banners/'.$block->banner_image; ?>" alt="" class="img-fluid lazy">
                                </figure>
                               <?php if ((isset($block->heading) && $block->heading!='') || (isset($block->button_text) && $block->button_text!='')) { ?>
                                 <div class="text text-center">
                                    <h3><?php echo ((isset($block->lang_homeblock_heading) && $block->lang_homeblock_heading !='') ? $block->lang_homeblock_heading : $block->heading); ?></h3>
                                    <span class="btn btn-border">
                                        <?php echo ((isset($block->lang_homeblock_button_text) && $block->lang_homeblock_button_text !='') ? $block->lang_homeblock_button_text : $block->button_text); ?>
                                    </span>   
                                 </div>
                               <?php } ?>
                            </a>
                        </div>
                       <?php
                        }
                    } 
                ?>
              </div>
            </div>
          </div><!-- Exclusive offers -->

<?php
        }elseif($banner_type=='footer-block-3'){
?>
            <div class="site-section site-blocks-1 bkg-light three-icons-block">
                  <div class="container">
                    <div class="row">
                       <?php if (isset($banners) && $banners->is_success == 'true') {
                        foreach ($banners->ShopBannerDetails as $block) { ?>

                      <div class="col-md-4 col-lg-4 d-lg-flex mb-4 mb-lg-0 pl-4 align-items-center justify-content-center border-right ">
                            <div class="icon mr-4">
                              <img src="<?php echo BANNER_IMG.$block->banner_image; ?>">
                            </div>

                            <div class="text">
                                <h2 class="text-uppercase">
                                    <?php echo ((isset($block->lang_homeblock_heading) && $block->lang_homeblock_heading !='') ? $block->lang_homeblock_heading : $block->heading); ?>
                                </h2>
                            </div>
                      </div>

                    <?php } } ?>

                    </div>
                </div>
            </div><!-- three section close track order -->

<?php
        }else{
?>
            <div class="banner-section">
                <div class="<?php echo ($category_id != '') ? 'container' : '' ?>">
                  <div class="regular slider">   
                    <?php foreach ($banners->ShopBannerDetails as $value) { ?>
                        <div>
                            <?php if (isset($value->link_button_to) && $value->link_button_to !='') {
                            $btn_link = (isset($value->link_button_to)) ? $value->link_button_to : '#'; ?>
                            <a href="<?= linkUrl($btn_link) ?>" class="home-banner-link">
                            <?php } ?>
                            <img class="lazy" data-src="<?php echo IMAGE_URL.'/'.'uploads/banners/'.$value->banner_image; ?>">
                            <div class="banner-content">
                                <h1><?php echo ((!empty($value->lang_homeblock_heading) && $value->lang_homeblock_heading !='') ? $value->lang_homeblock_heading : $value->heading);?></h1>
                                <p><?php echo ((!empty($value->lang_homeblock_description) && $value->lang_homeblock_description !='') ? $value->lang_homeblock_description : $value->description); ?> </p>
                            <?php 
                                if(!empty($value->lang_homeblock_button_text) && $value->lang_homeblock_button_text !=''){ ?>
                                    <span class="btn btn-blue"><?php echo $value->lang_homeblock_button_text; ?></span>
                                <?php  }else if(isset($value->button_text) && $value->button_text !=''){ ?> 
                                    <span class="btn btn-blue"><?php echo $value->button_text; ?></span>
                            <?php } ?>
                            </div>
                            <?php echo (isset($value->link_button_to) && $value->link_button_to !='') ? '</a>' : '' ?>
                        </div>
                  <?php } ?>
                  </div>
                </div>
            </div><!-- banner section -->
<?php 
        } 
   // } 
?>