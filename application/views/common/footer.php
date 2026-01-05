<footer class="page-footer">
  <div class="footer-container footer-style-1">
    <div class="footer-wrapper">
      <div class="footer-middle">
        <div class="container">
          <div class="middle-block">
            <div class="row">
              <!-- CONDITIONS -->
              <div class="col-xl-2 col-lg-6 col-md-6">
                <div class="block-footer">
                  <div class="block-footer-title"><?= lang('conditions') ?></div>
                  <div class="block-footer-content">
                    <ul>
                      <li><a href="/page/terms-conditions" target="_blank" rel="noopener"><?= lang('terms_conditions') ?></a></li>
                      <li><a href="/page/privacy-policy" target="_blank" rel="noopener"><?= lang('privacy_policy') ?></a></li>
                      <li><a href="/page/delivery-policy" target="_blank" rel="noopener"><?= lang('delivery_policy') ?></a></li>
                      <li><a href="/page/return-policy" target="_blank" rel="noopener"><?= lang('return_policy') ?></a></li>
                      <li><a href="/page/refund-and-replacements" target="_blank" rel="noopener"><?= lang('refund_guide') ?></a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- HELP YOU -->
              <div class="col-xl-2 col-lg-6 col-md-6">
                <div class="block-footer">
                  <div class="block-footer-title"><?= lang('help_you') ?></div>
                  <div class="block-footer-content">
                    <ul>
                      <li><a href="/page/support" target="_blank"><?= lang('support') ?></a></li>
                      <li><a href="/customer/account/"><?= lang('my_account') ?></a></li>
                      <li><a href="/customer/my-orders"><?= lang('my_orders') ?></a></li>
                      <li><a href="/marketplace/account/register" target="_blank"><?= lang('join_marketplace') ?></a></li>
                      <li><a href="<?= BASE_URL; ?>faqs" target="_blank"><?= lang('faq') ?></a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- INFORMATION -->
              <div class="col-xl-2 col-lg-6 col-md-6">
                <div class="block-footer">
                  <div class="block-footer-title"><?= lang('information') ?></div>
                  <div class="block-footer-content">
                    <ul>
                      <li><a href="/page/about-us"><?= lang('about_us') ?></a></li>
                      <li><a href="/page/career" target="_blank"><?= lang('careers') ?></a></li>
                      <li><a href="/page/blogs" target="_blank"><?= lang('blogs') ?></a></li>
                      <li><a href="/page/competitions" target="_blank"><?= lang('competitions') ?></a></li>
                      <li><a href="/page/delivery-information" target="_blank"><?= lang('delivery_info') ?></a></li>
                      <li><a href="/page/press-and-news" target="_blank"><?= lang('news_press') ?></a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- MERCHANTS -->
              <div class="col-xl-2 col-lg-6 col-md-6">
                <div class="block-footer">
                  <div class="block-footer-title"><?= lang('our_merchants') ?></div>
                  <div class="block-footer-content">
                    <ul>
                      <li><a href="/daily-deals/"><?= lang('daily_deals') ?></a></li>
                      <li><a href="/flash-sale"><?= lang('flash_sale') ?></a></li>
                      <li><a href="/trending-products"><?= lang('trending_products') ?></a></li>
                      <li><a href="/giftcards/"><?= lang('gift_cards') ?></a></li>
                      <li><a href="<?= BASE_URL; ?>shops"><?= lang('merchants_directory') ?></a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- NEWSLETTER -->
              <div class="col-xl-4 col-lg-12 col-md-12">
                <div class="block-footer">
                  <?php
                  $shopcode = SHOPCODE;
                  $website_texts = HomeDetailsRepository::get_website_texts(SHOPCODE);
                  ?>
                  
                  <h2>
                    <?php
                       echo lang('newsletter');
                   /* if (isset($website_texts) && $website_texts != '' && $website_texts->statusCode == 200) {
                      echo (($website_texts->FbcWebsiteTexts)->newsletter_title) ?: lang('newsletter');
                    } else {
                      echo lang('newsletter');
                    }*/
                    ?>
                  </h2>

                  <?php
                  if (isset($website_texts) && $website_texts != '' && $website_texts->statusCode == 200 && (($website_texts->FbcWebsiteTexts)->newsletter_message) != '') {
                    echo (($website_texts->FbcWebsiteTexts)->newsletter_message);
                  }
                  ?>

                  <div class="block-footer-content">
                    <p class="newsletter-description">
                      <?= lang('newsletter_description') ?? "Enter your email address for our mailing list to keep yourself updated" ?>
                    </p>
                    <div class="block-subscribe-footer">
                      <form id="newsletter-subscribe-form" action="<?= BASE_URL; ?>newsletter" method="POST">
                        <div class="input-group">
                          <input type="email" placeholder="<?= lang('newsletter_placeholder') ?>" class="form-control" id="newsletter-loader" name="email_subscribe" required>
                          <span class="input-group-btn">
                            <button class="btn btn-primary" type="submit" name="email-subscribe-btn"><?= lang('newsletter_subscribe') ?></button>
                          </span>
                        </div>
                        <div class="email-subscribe-error"></div>
                      </form>
                      <div class="subscribe_result" id="subscribe_result" style="color: #fdf505ff;"></div>
                    </div>
                  </div>

                  <br><br>
                  <div class="block-footer-title dummy-text-online"><?= lang('become_merchant') ?></div>
                  <a href="/merchants/register/">
                    <img src="<?= TEMP_SKIN_IMG.'/new/online-merchant-signup-517x70.jpg'; ?>" alt="merchant signup">
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- FOOTER BOTTOM -->
        <div class="footer-bottom">
          <div class="container">
            <div class="row">
              <div class="col-lg-4">
                <div class="footer-payment">
                  <p><img src="<?= TEMP_SKIN_IMG.'/new/payment-footer.jpg'; ?>" loading="lazy" alt="Payment"></p>
                </div>
              </div>
              <div class="col-lg-8">
                <div class="copyright-footer">
                 <address><?= $this->lang->line('footer_text'); ?> © <?= date('Y'); ?> Yellow Markets. <?= $this->lang->line('all_rights_reserved'); ?></address>

                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</footer>

<!-- MODALS -->
<div id="WebShopCommonModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><?= lang('modal_heading') ?></h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <center><div class="spinner-border text-primary" role="status"></div></center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal"><?= lang('close') ?></button>
        <button type="button" class="btn btn-primary"><?= lang('save_changes') ?></button>
      </div>
    </div>
  </div>
</div>

<div id="WebShopSecondaryModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="modal-content-second">
      <div class="modal-header">
        <h4 class="modal-title"><?= lang('modal_heading') ?></h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <center><div class="spinner-border text-primary" role="status"></div></center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal"><?= lang('close') ?></button>
        <button type="button" class="btn btn-primary"><?= lang('save_changes') ?></button>
      </div>
    </div>
  </div>
</div>

<!-- Spinner -->
<div class="ajax-spinner" id="ajax-spinner">
  <div class="ajax-spinner-inner"></div>
</div>

<?php $this->template->load('common_for_all/header_script'); ?>
<script src="<?= SKIN_JS ?>quick_view_details.js?v=<?= CSSJS_VERSION; ?>"></script>
</body>
</html>
