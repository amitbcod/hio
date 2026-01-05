<?php $this->load->view('common/header'); ?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.22/datatables.min.css"/>

<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.22/datatables.min.js"></script>

<div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="<?php echo base_url(); ?>"><?=lang('bred_home')?></a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active"><?=lang('bred_profile')?></li>
				</ul>
			</div>
        </div>
      </div><!-- breadcrum section -->


     <div class="my-profile-page-full">
      <div class="container">
          <div class="row">
				<?php $this->load->view('common/profile_sidebar'); ?>

				<div class="col-md-9 col-lg-9 ">
					<h4 class="manage-add-head"><?=lang('catalog_list')?><a href="<?php echo base_url()."catlog-builder/create" ?>"><button class="black-btn float-right"><?=lang('create_catalog')?></button></a>  <a href="<?php echo base_url()."catlog-builder/scanning" ?>"><button class="black-btn float-right"><?=lang('scanning')?></button></a></h4>

					<div class="personal-info-form col-sm-12 upc-listing">
						<form class="row">
							<div class="table-responsive text-center">
								<table class="table table-bordered table-style " id="catlog_builder_list">
								  <thead>
									<tr>
									  <th><?=lang('catalog_name')?></th>
									  <th><?=lang('customer_name')?></th>
									  <th><?=lang('customer_email')?></th>
									  <th><?=lang('phone_no')?></th>
									  <th><?=lang('created_date')?></th>
									  <th><?=lang('customer_view')?></th>
									</tr>
								  </thead>

								</table>
							  </div>
						</form>
					</div>
				</div><!-- col-md-9 -->

          </div><!-- row -->
      </div><!-- container -->
    </div><!-- my-profile-page-full -->
<script type="text/javascript">
	$(document).ready(function(){

    loadcatloglbuilderlistajax();

    });
</script>
<?php $this->load->view('common/footer'); ?>

 <script src="<?php echo SKIN_JS ?>special_features.js?v=<?php echo CSSJS_VERSION; ?>"></script>
