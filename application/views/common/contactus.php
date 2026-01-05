<?php $this->load->view('common/header') ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div class="main">
    <div class="container">
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
            <div class="col-md-12">
                <h1>Contact Us</h1>
            </div>
            <?php if (isset($website_texts) && $website_texts != '' && $website_texts->statusCode == 200) {
                if (isset(($website_texts->FbcWebsiteTexts)->other_lang_message) && ($website_texts->FbcWebsiteTexts)->other_lang_message != '') {        ?>
                    <p><?php echo (($website_texts->FbcWebsiteTexts)->other_lang_message); ?></p>
                <?php } else if (isset(($website_texts->FbcWebsiteTexts)->contact_message) && ($website_texts->FbcWebsiteTexts)->contact_message != '') {        ?>
                    <p><?php echo (($website_texts->FbcWebsiteTexts)->contact_message); ?></p>
                <?php } else { ?>
                    <p><?= lang('contact_us_page_sub_heading_msg') ?></p>
                <?php } ?>
            <?php } else { ?>
                <p><?= lang('contact_us_page_sub_heading_msg') ?></p>
            <?php } ?>

            <?php if (isset($_SESSION) && isset($_SESSION['LoginID'])) {
                $fname = $_SESSION['FirstName'];
                $lname = $_SESSION['LastName'];
                $fnln = $fname . ' ' . $lname;
            }
            ?>
            <?php //echo"<pre>";print_r($website_texts);echo"</pre>"; 
            ?>

            <!-- BEGIN CONTENT -->
            <div class="col-md-8">
                <div class="content-page shadow">
                    <form id="contact-form" method="POST" action="<?php echo BASE_URL; ?>contact-us-post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" value="<?= (isset($_SESSION) && isset($fnln)) ? $fnln : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="text" class="form-control" id="email" placeholder="Enter your email" name="email" value="<?= (isset($_SESSION) && isset($_SESSION['EmailID'])) ? $_SESSION['EmailID'] : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Comments</label>
                                    <textarea placeholder="Comments…" class="form-control" rows="5" name="comments" id="comments"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="padding-top-20" style="font-weight: normal;">
                                    <input type="checkbox" class="queryrelated" name="order_flag" id="order_flag" value="1"> This
                                    query is related to an order.
                                </label>
                                <div class="form-group showordernumber padding-top-10" id="conatct_order_no">
                                    <label>Order Number</label>
                                    <input type="text" class="form-control" id="ordernumber" name="order_increment_id" maxlength="4">
                                </div>
                            </div>

                        </div>
                        <div class="form-input">
                            <!-- Google reCAPTCHA box -->
                            <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY_V2; ?>"></div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 padding-top-20">
                                <button class="btn btn-primary" type="submit">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-4">
                <div class="content-page shadow">
                    <?php //print_r($website_texts); 
                    ?>
                    <?php if (isset($website_texts) && $website_texts != '' && $website_texts->statusCode == 200) { ?>
                        <?php if ($website_texts->FbcWebsiteTexts && $website_texts->FbcWebsiteTexts->contact_email_enabled == 1) { ?>
                            <div class="contwrap">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                                <span><a href="mailto:<?php echo (($website_texts->FbcWebsiteTexts)->contact_email); ?>"><?php echo (($website_texts->FbcWebsiteTexts)->contact_email); ?></a></span>
                            </div>
                        <?php } else { ?>
                            <div class="contwrap">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                                <span><a href="mailto:<?= lang('support_email') ?>"> <?= lang('support_email') ?> </a></span>
                            </div>
                        <?php } ?>
                    <?php } ?>

                    <?php if (isset($website_texts) && $website_texts != '' && $website_texts->statusCode == 200) { ?>
                        <?php if ($website_texts->FbcWebsiteTexts && $website_texts->FbcWebsiteTexts->contact_phone_enabled == 1) { ?>
                            <div class="contwrap">
                                <i class="fa fa-phone" aria-hidden="true"></i>
                                <span><a href="tel:+91<?php echo (($website_texts->FbcWebsiteTexts)->contact_phone) ?>"><?php echo "+91 " . (($website_texts->FbcWebsiteTexts)->contact_phone) ?></a></span>
                            </div>
                        <?php } else { ?>
                            <div class="contwrap">
                                <i class="fa fa-phone" aria-hidden="true"></i>
                                <span><a href="tel:<?= lang('phone_number') ?>"> <?php echo "+91 " . lang('phone_number') ?></a></span>
                            </div>
                        <?php } ?>
                    <?php } ?>

                    <?php if (isset($website_texts) && $website_texts != '' && $website_texts->statusCode == 200) { ?>
                        <?php if ($website_texts->FbcWebsiteTexts && $website_texts->FbcWebsiteTexts->contact_address_enabled == 1) { ?>
                            <div class="contwrap">
                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                                <span><?php echo (($website_texts->FbcWebsiteTexts)->contact_address); ?></span>
                            </div>
                        <?php } else { ?>
                            <div class="contwrap">
                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                                <span>A-103, Hinal Heritage, Opp- HDFC Bank, Sardar Vallabhbhai Patel Rd, Borivali
                                    West, Mumbai, Maharashtra 400092</span>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
            <!-- END CONTENT -->
        </div>
        <div class="row margin-bottom-40">
            <div class="col-md-12">
                <div class="content-page shadow">
                    <!-- <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d941.7827469806048!2d72.85597145741399!3d19.23312226568143!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7b1e9ac19a8b5%3A0xaef35fdc0c901985!2sHinal%20Heritage!5e0!3m2!1sen!2sin!4v1684307328303!5m2!1sen!2sin" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe> -->
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3767.1295791038183!2d72.85394727533057!3d19.23318368200407!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7b6526ef306ed%3A0x4c8d35b42a4611e5!2sIndiamags.com!5e0!3m2!1sen!2sin!4v1707742354571!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
        <!-- END SIDEBAR & CONTENT -->
    </div>
</div>
<?php $this->load->view('common/footer') ?>
<script src="<?php echo SKIN_JS ?>contactus.js?v=<?php echo CSSJS_VERSION; ?>"></script>