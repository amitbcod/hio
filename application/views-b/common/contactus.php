<?php $this->load->view('common/header'); ?>
    <div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="<?php echo BASE_URL; ?>">Home</a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active">Contact Us</li>					
				</ul>
			</div>
        </div>
    </div><!-- breadcrum section -->

    <div class="contact-page-full">
      <div class="container">
        <div class="col-md-12">
          <div class="row">
				<div class="col-md-8 col-lg-8 contact-us-section">

					<h2>Leave a Message</h2>
<?php
    if (isset($website_texts) && $website_texts!='' && $website_texts->statusCode == 200) {
        if (isset(($website_texts->FbcWebsiteTexts)->other_lang_message) && ($website_texts->FbcWebsiteTexts)->other_lang_message!='') {		?>
			<p><?php  echo(($website_texts->FbcWebsiteTexts)->other_lang_message); ?></p>
        <?php } else if (isset(($website_texts->FbcWebsiteTexts)->contact_message) && ($website_texts->FbcWebsiteTexts)->contact_message!='') {		?>
			<p><?php  echo(($website_texts->FbcWebsiteTexts)->contact_message); ?></p>
		<?php } else { ?>
			<p><?=lang('contact_us_page_sub_heading_msg')?></p>
		<?php } ?>
<?php } else { ?>
		<p><?=lang('contact_us_page_sub_heading_msg')?></p>
<?php } ?>
					<?php if (isset($_SESSION) && isset($_SESSION['LoginID'])) {
        $fname = $_SESSION['FirstName'];
        $lname = $_SESSION['LastName'];
        $fnln = $fname.' '.$lname;
    }
                    ?>
					<div class="contact-form col-sm-12">
						<form id="contact-form" method="POST" action="<?php echo BASE_URL;?>contact-us-post" class="row">
							<div class="col-sm-6">
								<label>Name</label>
								<input type="text" class="form-control" name="name" id="name" placeholder="Enter Name" value="<?= (isset($_SESSION) && isset($fnln)) ? $fnln : '' ?>">
							</div>

							<div class="col-sm-6">

								<label>Email</label>

								<input type="text" class="form-control" name="email" id="email" placeholder="Enter Email"

								value="<?= (isset($_SESSION) && isset($_SESSION['EmailID'])) ? $_SESSION['EmailID'] : '' ?>">

							</div>

							<div class="col-sm-6 order-email-section">
								<input type="checkbox"  id="order_flag" name="order_flag" value="1">
								<label>I have a question related to an order</label>
						
							</div>
							<div class="col-sm-6" id="conatct_order_no">
								<label>Order No.</label>
								<input type="text" class="form-control " name="order_increment_id" id="order_increment_id" placeholder="Enter Order No." >

							</div>

							<div class="col-sm-12">

								<label>Comments</label>

								<textarea placeholder="Comments..." class="form-control validate-msg" name="comments" id="comments"></textarea>

							</div>
							
							<div class="col-sm-12">
							  <input type="text" name="nickname" id="nickname" style="display: none;" value="">
							</div>

							<div class="signin-btn">
								<button class="black-btn">Submit</button>

							</div><!-- signin-btn -->

						</form>

					</div><!-- contact-form -->

				</div><!-- col-sm-8 -->



				<div class="col-md-4 col-lg-4 ">

					<div class="contact-us-support">
	<?php
    if (isset($website_texts) && $website_texts!='' && $website_texts->statusCode == 200) {
        if (($website_texts->FbcWebsiteTexts)->contact_email_enabled == 1) {
            ?>
						<div class="suppot-block">
							<div class="section-contact">
							<span><img src="<?php echo TEMP_SKIN_IMG; ?>/email.png"></span>
							</div><!-- icon-section-contact -->
							<div class="section-content">
								<label>Email</label>
								<p><a href="mailto:"> <?php  echo(($website_texts->FbcWebsiteTexts)->contact_email); ?></a> </p>
							</div><!-- icon-section-content -->
						</div><!-- suppot-block -->
	<?php
        }
    } else { ?>
						<div class="suppot-block">
							<div class="section-contact">
							<span><img src="<?php echo TEMP_SKIN_IMG; ?>/email.png"></span>
							</div><!-- icon-section-contact -->
							<div class="section-content">
								<label>Email</label>
								<p><a href="mailto:"> <?=lang('support_email')?> </a> </p>
							</div><!-- icon-section-content -->
						</div><!-- suppot-block -->
	<?php  }?>

	<?php
    if (isset($website_texts) && $website_texts!='' && $website_texts->statusCode == 200) {
        if (($website_texts->FbcWebsiteTexts)->contact_phone_enabled == 1) {
            ?>
						<div class="suppot-block">
							<div class="section-contact">
								<span><img src="<?php echo TEMP_SKIN_IMG; ?>/contact.png"></span>
							</div><!-- icon-section-contact -->
							<div class="section-content">
								<label>Phone</label>
								<p><a href="tel:<?php  echo(($website_texts->FbcWebsiteTexts)->contact_phone); ?>"> <?php  echo(($website_texts->FbcWebsiteTexts)->contact_phone); ?> </a> <br/>
								<?=lang('contact_us_working')?>
							</p>
							</div><!-- icon-section-content -->
						</div><!-- suppot-block -->
	<?php
        }
    } else { ?>
						<div class="suppot-block">
							<div class="section-contact">
								<span><img src="<?php echo TEMP_SKIN_IMG; ?>/contact.png"></span>
							</div><!-- icon-section-contact -->
							<div class="section-content">
								<label>Phone</label>
								<p><a href="tel:<?=lang('phone_number')?>"> <?=lang('phone_number')?> </a> </p>
							</div><!-- icon-section-content -->
						</div><!-- suppot-block -->
	<?php }?>

	<?php
    if (isset($website_texts) && $website_texts!='' && $website_texts->statusCode == 200) {
        if (($website_texts->FbcWebsiteTexts)->contact_address_enabled == 1) {
            ?>
						<div class="suppot-block">
							<div class="section-contact">
								<span><img src="<?php echo TEMP_SKIN_IMG; ?>/location.png"></span>
							</div><!-- icon-section-contact -->
							<div class="section-content">
								<label><?=lang('main_office')?></label>
								<p><?php  echo(($website_texts->FbcWebsiteTexts)->contact_address); ?> </p>
							</div><!-- icon-section-content -->
						</div><!-- suppot-block -->
	<?php
        }
    } else {  ?>
						<div class="suppot-block">
							<div class="section-contact">
								<span><img src="<?php echo TEMP_SKIN_IMG; ?>/location.png"></span>
							</div><!-- icon-section-contact -->
							<div class="section-content">
								<label>Main Office</label>
								<p>Main Office Address</p>
							</div><!-- icon-section-content -->
						</div><!-- suppot-block -->
	<?php }?>
					</div><!--contact-us-support -->

				</div><!-- col-sm-4 -->



          </div><!-- row -->

        </div><!-- col-md-12 -->

      </div><!-- container -->

    </div><!-- cart-page -->





    <?php $this->load->view('common/footer'); ?>

	<script src="<?php echo SKIN_JS ?>contactus.js?v=<?php echo CSSJS_VERSION; ?>"></script>

	</body>

</html>