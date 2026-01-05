<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
    	<!-- <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->
    	<li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>
    	<li class=""><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>
		<li><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>
		<li><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>
		<li><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
		<li class="active"><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>

  	</ul>

  	<div class="tab-content">
    	<div id="customize-tab" class="tab-pane fade in active common-tab-section  min-height-480 admin-shop-details-table" style="opacity:1;">
      		<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          		<h1 class="head-name"><!-- <i class="fas  fa-angle-left"></i>  -->Promo Text Banners </h1> 
          	<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
	  			<div class="float-right">
					<button class="white-btn" onclick="window.location.href='<?= base_url('webshop/add-promo-text-banners') ?>';"> +  Add New</button>
	  			</div>
	  		<?php } ?>
        	</div><!-- d-flex -->
		   
        	<!-- form -->
        	<div class="content-main form-dashboard">
				<div class="table-responsive">
	                <table class="table table-bordered table-style" id="cmsListTable">
	                  	<thead>
	                    	<tr>
	                      		<th>Banner Text  </th>
	                      		<th>Background Color </th>
	                      		<th>Text Color </th>
	                      		<th>Status  </th>
	                      		<th>Action </th>
						  		<th>Details </th>
   
	                    	</tr>
	                  	</thead>
	                  <tbody>
	                  	<?php if(isset($promoTextList) && !empty($promoTextList)) {
	                  		 foreach ($promoTextList as  $value) { ?>
		                    <tr>
		                        <td><?php echo $value->banner_text; ?></td>
		                      	<td><?php echo $value->background_color; ?></td>
		                      	<td><?php echo $value->text_color; ?></td> 
		                      	<td>
		                      		<?php 
		                      		if($value->status == 1){
		                      			echo "Published";
		                      		}else{
		                      			echo "On Hold";
		                      		}
		                      		?>
		                      	</td>
							  	<td>
							  		<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?> <!---Bipin--->
							  		<a class="link-purple deletePromo" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo $value->id ?>" >Delete</a></td> 
							  	    <?php }else{ echo '-';}?>
							  	</td>
		                      	<td>
									<?php   $Url = base_url(); 
											$trimUrl = preg_replace( "#^[^:/.]*[:/]+#i", "", $Url );
									?>
									<a class="link-purple" href="<?= base_url('webshop/edit-promo-text-banners/'.$value->id) ?>">View</a>
										
								</td>
		                    </tr>
		                    <?php } } ?>
	                  	</tbody> 
	                </table>
              	</div>
        	</div>
        	<!--end form-->
    	</div>
  	</div>
</main>
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="promoDeleteForm" method="POST" action="<?= base_url('WebshopController/deletePromoTextBanner')?>">
				<input type="hidden" name="promoID" id="promoID">
				<div class="modal-header">
					<h1 class="head-name">Are you sure? you want to Delete Promo Text Banners!</h1>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-footer">
					<button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">No</button>
					<button type="submit" class="purple-btn">Delete</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="<?php echo SKIN_JS; ?>webshop.js"></script>
<script type="text/javascript">
	$("#cmsListTable").dataTable({
        "language": {
			"infoFiltered": "",
			"search": '',
			"searchPlaceholder": "Search",
			"paginate": {
				next: '<i class="fas fa-angle-right"></i>',
				previous: '<i class="fas fa-angle-left"></i>'
			}
		},
    });
</script>
<?php $this->load->view('common/fbc-user/footer'); ?>