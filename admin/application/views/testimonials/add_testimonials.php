<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

  <div class="tab-content">
    <div id="catalogue-discounts-details-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name pad-bt-20"><?php echo $pageTitle; ?></h1>
      </div><!-- d-flex -->

		  <!-- form -->
      <form name="coupon-code-frm-add" id="testimonial-add" method="POST" action="">

        <?php //echo "<pre>";print_r($details);die();  ?>
  			<div class="customize-add-section">
  				<div class="row">
    				<div class="left-form-sec coupon-code-select-product-list">
                    <input type="hidden" name="testimonial_id" id="testimonial_id" value="<?php echo isset($details['id']) ? $details['id'] : '' ?>" />
    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>Client Name <span class="required">*</span></label>
    						<input class="form-control" type="text" name="client_name" id="client_name" value="<?php echo isset($details['client_name']) ? $details['client_name'] : '' ?>" placeholder="Enter Client Name" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength="50">
    					</div>

                        <div class="col-sm-6 customize-add-inner-sec">
    						<label>Client Description <span class="required">*</span></label>
    						<input class="form-control" type="text" name="client_description" id="client_description" value="<?php echo isset($details['client_description']) ? $details['client_description'] : '' ?>" placeholder="Enter Client Description">
    					</div>

                        <div class="col-sm-6 customize-add-inner-sec">
    						<label>Client Company <span class="required">*</span></label>
    						<input class="form-control" type="text" name="client_company"  id="client_company" value="<?php echo isset($details['client_company']) ? $details['client_company'] : '' ?>" placeholder="Enter Client Company" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength="100">
    					</div>

                        <div class="col-sm-6 customize-add-inner-sec">
    						<label>Website</label>
    						<input class="form-control" type="url" name="website"  id="website" value="<?php echo isset($details['website']) ? $details['website'] : '' ?>" placeholder="Enter Website" pattern="https?://.+">
    					</div>

						<div class="row col-sm-6">
							<div class="col-sm-6 customize-add-inner-sec">
								<label>Image</label>
								<input type="file" id="custImages"  name="custImages" multiple>
								<br>
								<div>
								<small><i class="fa fa-exclamation-triangle">(*.jpg, *.png, *.gif)</i></small>
								</div>
							</div>
							<?php if (is_array($details) || is_object($details)) { ?>
								<div class="col-sm-6 customize-add-inner-sec">
                  <?php if(is_null($details['image_path'] !== "")) { ?>
									<div class="uploadPreview" id="uploadPreview">
										<img src="<?= base_url().'../'.$details['image_path'] ?>" width="200">
									</div>
                  <?php } ?>
								</div>
							<?php } ?>
						</div>

                        <div class="col-sm-6 customize-add-inner-sec">
    						<label>Video URL</label>
    						<input class="form-control" type="text" name="video_url"  id="video_url" value="<?php echo isset($details['video_url']) ? $details['video_url'] : '' ?>" placeholder="Enter Video URL">
    					</div>

                        <div class="row">
							<div class="col-sm-6 customize-add-inner-sec">
								<label>Attach PDF</label>
								<input type="file" id="custPdfs"  name="custPdfs" multiple>
								<br>
								<div>
								<small><i class="fa fa-exclamation-triangle">(*.pdf)</i></small>
								</div>
							</div>
							<?php if (is_array($details) || is_object($details)) { ?>
							<div class="col-sm-6 customize-add-inner-sec">
                <?php if(is_null($details['pdf_path'] !== "")) { ?>
								<div class="uploadPreviewPdf" id="uploadPreviewPdf">
								<iframe src="<?php echo base_url().'../'.$details['pdf_path']; ?>" width="100%" height="300px">
								</iframe>
								</div>
              <?php } ?>
							</div>
							<?php } ?>
						</div>

                        <div class="col-sm-6 customize-add-inner-sec">
			                <label>Testimonial<span class="required">*</span></label>
			                <textarea class="form-control" id="testimonial" name="testimonial"><?php echo (isset($details['testimonial']) && $details['testimonial']!='')?$details['testimonial']:''; ?></textarea></div>

                        <?php if (is_array($details) || is_object($details)) { ?>
                        <div class="col-sm-6 customize-add-inner-sec">
    						<label>Status</label>
    						<select name="status" class="form-control" id="status">
    			                <option value="">Select RoleType</option>
                                <option value="1" <?php echo ($details['status'] == 1) ? 'selected' : '' ?>>Enabled</option>
                                <option value="2" <?php echo ($details['status'] == 2) ? 'selected' : '' ?>>Disabled</option>
                            </select>
    					</div>
                        <?php } else { ?>
                            <div class="col-sm-6 customize-add-inner-sec">
    						<label>Status</label>
    						<select name="status" class="form-control" id="status">
    			                <option value="">Select RoleType</option>
                                <option value="1">Enabled</option>
                                <option value="2">Disabled</option>
                            </select>
    					</div>
                        <?php } ?>

                        </div>
                    </div>
                <div class="download-discard-small mar-top">
                      <button class="download-btn" id="save_testimonials" type="submit">Save</button>
                </div>
            </div>
</form>

<script type="text/javascript">
	$(function () {
      	CKEDITOR.replace('testimonial', {
	     extraPlugins :'justify',
	     extraAllowedContent : "span(*)",
     		allowedContent: true,
	    });
      	CKEDITOR.dtd.$removeEmpty.span = 0;
      	CKEDITOR.dtd.$removeEmpty.i = 0;
    });
</script>

<script src="<?php echo SKIN_JS; ?>testimonials.js"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>
