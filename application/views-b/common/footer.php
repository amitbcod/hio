<?php
  $lang_code= ((!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0) ? $this->session->userdata('lcode') : '');

$footer_identifier3 = 'footer-info';
$footerBlock3 = HomeDetailsRepository::get_static_block($footer_identifier3,$lang_code);
if(isset($footerBlock3) && $footerBlock3->statusCode == '200'){
  foreach($footerBlock3->ShopStaticBlock as $block) {?>
<div class="footer-container">
    <div class="footer">
        <?php echo ((isset($block->lang_static_content) && $block->lang_static_content!='') ? htmlspecialchars_decode(stripslashes($block->lang_static_content)) : htmlspecialchars_decode(stripslashes($block->content)));?>

        <div class="gr-footer">
            <?php
                $footer_identifier1 = 'footer-block-1';
                $footerBlock1 = HomeDetailsRepository::get_static_block($footer_identifier1,$lang_code);
        
                if(isset($footerBlock1) && $footerBlock1->statusCode == '200'){
                    foreach($footerBlock1->ShopStaticBlock as $block) {
                        echo ((isset($block->lang_static_content) && $block->lang_static_content!='') ? htmlspecialchars_decode(stripslashes($block->lang_static_content)) : htmlspecialchars_decode(stripslashes($block->content)));
                    } 
                } 
            ?>

            <div class="newsletter-container">
                <div class="block-7 newsletter-block">
                    <form id="newsletter-subscribe-form" action="<?php echo BASE_URL;?>newsletter" method="POST">
                        <?php
                          $shopcode = SHOPCODE;
                          $website_texts = HomeDetailsRepository::get_website_texts(SHOPCODE);

                          if (isset($website_texts) && $website_texts!='' && $website_texts->statusCode == 200) { 
                        ?>

                        <h3 class="footer-heading mb-4">
                            <?php  echo ((($website_texts->FbcWebsiteTexts)->newsletter_title) != '') ? (($website_texts->FbcWebsiteTexts)->newsletter_title) : 'Newsletter'; ?>
                        </h3>

                        <?php } else { ?>

                        <h3 class="footer-heading mb-4"><?=lang('newsletter')?></h3>

                        <?php } ?>

                        <?php if (isset($website_texts) && $website_texts!='' && $website_texts->statusCode == 200) { ?>
                        <p>
                            <?php  echo ((($website_texts->FbcWebsiteTexts)->newsletter_message) != '') ? (($website_texts->FbcWebsiteTexts)->newsletter_message) : lang('newsletter_msg'); ?>
                        </p>

                        <?php } else { ?>

                        <p><?=lang('newsletter_msg')?></p>

                        <?php } ?>

                        <div class="form-group">
                            <input type="text" class="form-control py-4" name="email_subscribe" id="email_subscribe"
                                placeholder="<?=lang('email_subscribe')?>">
                            <input type="submit" class="btn btn-icon" value="Send" name="email-subscribe-btn"
                                id="email-subscribe-btn">
                        </div>
                    </form>
                    <div class="subscribe_result" id="subscribe_result"></div>
                </div>
            </div>

            <?php
                $footer_identifier2 = 'footer-block-2';
                $footerBlock2 = HomeDetailsRepository::get_static_block($footer_identifier2,$lang_code);
                if(isset($footerBlock2) && $footerBlock2->statusCode == '200'){
                    foreach($footerBlock2->ShopStaticBlock as $block2) {
                        echo ((isset($block2->lang_static_content) && $block2->lang_static_content!='') ? htmlspecialchars_decode(stripslashes($block2->lang_static_content)) : htmlspecialchars_decode(stripslashes($block2->content)));
                    }
                }
            ?>
        </div>

    </div>
</div>
<?php } }  ?>

<?php
    //   if($this->router->fetch_class()=='HomeController' && $this->router->fetch_method()=='index'){
    //       (new HomeCategoryBanners('footer-block-3'))->render();
    //   }
?>
<!-- <footer class="site-footer border-top"> -->
<!-- <div class="container"> -->




<!-- </div> -->

<!--  <div class="wa-btn-desktop">
        <a href="https://web.whatsapp.com/send/?phone=919820047637&text=Hello! Are you there? I need some help :)" target="_blank">
            <img src="<?=base_url('public/images/whatsapp.svg') ?>" height="40" width="40" class="wa-text">
            <?=lang('whats_aap_button_msg')?>
        </a>
    </div>

    <div class="wa-btn-mobile">
        <a href="https://api.whatsapp.com/send/?phone=919820047637&text=Hello! Are you there? I need some help :)" target="_blank">
            <img src="<?=base_url('public/images/whatsapp.svg') ?>" height="40" width="40" class="wa-text">
            <?=lang('whats_aap_button_msg')?>
        </a>
    </div> -->

<!-- </footer> -->
<!-- </div>page close -->

<!-- JS File -->
<!--script src="<?php //echo SKIN_JS;?>jquery.min.js"></script-->

<div id="WebShopCommonModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel"
    aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="fullWidthModalLabel"><?=lang('modal_heading')?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <center>
                    <div class="spinner-border text-primary" role="status"></div>
                </center>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal"><?=lang('close')?></button>
                <button type="button" class="btn btn-primary"><?=lang('save_changes')?></button>
            </div>
        </div><!-- /.modal-content -->
        <input type="hidden" name="booklist_item_id" id="booklist_item_id" value="">
        <input type="hidden" name="subject_id" id="subject_id" value="">
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="WebShopSecondaryModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel"
    aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modal-content-second">
            <div class="modal-header">
                <h4 class="modal-title" id="fullWidthModalLabel"><?=lang('modal_heading')?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <center>
                    <div class="spinner-border text-primary" role="status"></div>
                </center>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal"><?=lang('close')?></button>
                <button type="button" class="btn btn-primary"><?=lang('save_changes')?></button>
            </div>
        </div><!-- /.modal-content -->
        <input type="hidden" name="booklist_item_id" id="booklist_item_id" value="">
        <input type="hidden" name="subject_id" id="subject_id" value="">
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="ajax-spinner" id="ajax-spinner">
    <div class="ajax-spinner-inner"></div>
</div>

<script src="<?php echo SKIN_JS ?>slick.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript" charset="utf-8">
</script>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $(".regular.slider").slick({
        dots: true,
        arrows: false,
        infinite: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 3000
    });

});
</script>
<script src="<?php echo SKIN_JS ?>main.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script src="<?php echo SKIN_JS ?>common.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<?php
        $footerscript = 'footerscript';
        $footerscript = HomeDetailsRepository::get_static_block(SHOPCODE,SHOP_ID,$footerscript);
        if(isset($footerscript) && $footerscript->statusCode == '200'){
          foreach($footerscript->ShopStaticBlock as $block) {
            echo $block->content;
        }
      }
?>
<div class="tw-flex-wrap"></div>
<?php $this->template->load('common/cookieNotice'); ?>