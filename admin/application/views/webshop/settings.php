<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<ul class="nav nav-pills">

    	<!-- <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->

    	<li class="active"><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>

    	<li><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>

		<li><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>

		<li><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>

		<li><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
		<li class=""><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>


  	</ul>

  <div class="tab-content">

	<div id="static-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">

  		<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

      		<h1 class="head-name">Settings </h1>

    	</div><!-- d-flex -->


<?php
   if($this->session->flashdata('true')){
 ?>
   <div class="alert text-white bg-info w-50">
     <?php  echo $this->session->flashdata('true'); ?>
   </div>
<?php
} else if($this->session->flashdata('err')){
?>
 <div class = "alert alert-danger">
   <?php echo $this->session->flashdata('err'); ?>
 </div>
<?php } ?>

        <!-- form -->

    <div class="content-main form-dashboard">
		<form method="POST" action="<?php echo base_url(); ?>WebshopController/saveWebshopSettings" id="upload_logo" enctype="multipart/form-data">

    	<?php if(isset($settings_info) && $settings_info !=''){ ?>
			<input type="hidden" name="action" value="update">
    	<?php } else { ?>
			<input type="hidden" name="action" value="save">
    	<?php } ?>
	 		 <div class="form-row">
		 		<input type="hidden" name="webshopID" value="<?php echo $webshopID; ?>">
				<div class="col-sm-6 form-group">
					<label for="sitename">Site Name</label>
					<input class="form-control" type="text" value="<?php if(isset($settings_info) && $settings_info !=''){
						echo $settings_info->site_name;
					} ?>" name="sitename" id="sitename" required="true">
				</div>
				<div class="col-sm-6 form-group">
					<label for="site_email">Site Email Address</label>
					<input class="form-control" type="email" value="<?php
					if(isset($settings_info) && $settings_info !='' && $settings_info->site_contact_email != '')
					{
						echo $settings_info->site_contact_email;
					}elseif(isset($default_settings_data) && $default_settings_data !=''){
						echo $default_settings_data->email;
					} ?>" name="site_email" id="site_email">
				</div>
			</div>

			<div class="form-row">
				<div class="col-sm-6 form-group">
					<label for="meta_title">Meta Title</label>
					<input class="form-control" type="text" value="<?php if(isset($settings_info) && $settings_info !=''){
						echo $settings_info->meta_title;
					} ?>" name="meta_title" id="meta_title">
				</div>
				<div class="col-sm-6 form-group">
					<label for="meta_keywords">Meta Keywords</label>
					<input class="form-control" type="text" value="<?php if(isset($settings_info) && $settings_info !=''){
						echo $settings_info->meta_keywords;
					} ?>" name="meta_keywords" id="meta_keywords">
				</div>
			</div>

			<div class="form-row">

				<div class="col-sm-6 form-group">
					<label for="site_contact_no">Site Contact Number</label>
					<input class="form-control" type="text"  value="<?php
					if(isset($settings_info) && $settings_info !='' && $settings_info->site_contact_no != '')
					{
						echo $settings_info->site_contact_no;
					}elseif(isset($default_settings_data) && $default_settings_data !=''){
						echo $default_settings_data->mobile_no;
					} ?>" name="site_contact_no" id="site_contact_no">
				</div>
				<div class="col-sm-6 form-group">
					<label for="meta_description">Meta Description</label>
					<textarea class="form-control" name="meta_description" id="meta_description" placeholder="Description area" value="<?php if(isset($settings_info) && $settings_info !=''){
						echo $settings_info->meta_description;
					} ?>" ><?php if(isset($settings_info) && $settings_info !=''){
						echo $settings_info->meta_description;
					} ?></textarea>
				</div>
				<!-- <div class="col-sm-6 form-group">
					<label for="site_logo">Site Logo</label>
					<input class="form-control"  type="file" value="" name="site_logo" id="site_logo" accept="image/*">
					<div class="form-group">
						<img id="preview"class="form-group" src="
						<?php //if(isset($settings_info) && $settings_info !='' && $settings_info->site_logo !=''){ ?>
								<?php //echo get_s3_url($this->encryption->decrypt($settings_info->site_logo), $shop_id); ?>
							<?php //} ?>
						" alt=" Logo Preview"  />
					</div>
				</div> -->


			</div>

			<div class="form-row">
				<!-- <div class="col-sm-6 form-group">
					<label for="site_logo">Site Favicon</label>
					<input class="form-control"  type="file" value="" name="site_favicon" id="site_favicon" accept="image/*">
					<div class="form-group">
						<?php //$favivon_url = get_s3_url('favicon/favicon-32x32.png', $shop_id);?>
						<img id="preview"class="form-group" src="<?php //echo $favivon_url; ?>" alt="Favicon Preview"  />
					</div>
				</div> -->

				

			</div>


		<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
			<div class="form-group text-right">
				<input type="submit" name="submit" value="Save" class="pull-right purple-btn">
			</div>
		<?php } ?>

		</form>
			<?php if(!empty($response)) { ?>
				<div class="response <?php echo $response["type"]; ?>
				    ">
				    <?php echo $response["message"]; ?>
				</div>
			<?php }?>


    </div>

    <!--end form-->

	</div>

  </div>
</main>
<script type="text/javascript">
	function readURL(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = function(e) {
      $('#preview').attr('src', e.target.result);
    }

    reader.readAsDataURL(input.files[0]); // convert to base64 string
  }
}

$("#site_logo").change(function() {
  readURL(this);
});
</script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>logoupload.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
