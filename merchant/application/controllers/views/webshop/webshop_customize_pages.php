<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
    	<!-- <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->
    	<li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>
    	<li class="active"><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>
		<li><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>
		<li><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>
		<li><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
		<li class=""><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>
		
  	</ul>

  	<div class="tab-content">
    	<div id="customize-tab" class="tab-pane fade in active common-tab-section  min-height-480 admin-shop-details-table" style="opacity:1;">
      		<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          		<h1 class="head-name"><!-- <i class="fas  fa-angle-left"></i>  -->Pages </h1> 
          	<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
	  			<div class="float-right">
					<button class="white-btn" onclick="window.location.href='<?= base_url('webshop/pages/add') ?>';"> +  Add New</button>
	  			</div>
	  		<?php } ?>
        	</div><!-- d-flex -->
		   
        	<!-- form -->
        	<div class="content-main form-dashboard">
				<div class="table-responsive">
	                <table class="table table-bordered table-style" id="cmsListTable">
	                  	<thead>
	                    	<tr>
	                      		<th>Title  </th>
	                      		<th>Identifier  </th>
	                      		<th>Last Updated </th>
	                      		<th>Status  </th>
	                      		<th>Action </th>
						  		<th>Details </th>
	                    	</tr>
	                  	</thead>
	                  	<tbody>
	                  		<?php foreach ($pages as $pag) { ?>
		                    <tr>
		                      	<td><?= $pag->title ?></td>
		                      	<td><?= $pag->identifier ?></td>
		                      	<td>
		                      		<?php 
		                      		if($pag->updated_at != 0){
		                      			echo date('d/m/Y',$pag->updated_at).' | '.date('H:i a',$pag->updated_at);
		                      		}else{
		                      			echo date('d/m/Y',$pag->created_at).' | '.date('H:i a',$pag->created_at);
		                      		}
		                      		?>
		                      	</td>
		                      	<td>
		                      		<?php 
		                      		if($pag->status == 1){
		                      			echo "Published";
		                      		}else{
		                      			echo "On Hold";
		                      		}
		                      		?>
		                      	</td>
							  	<td>
							  		<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
							  		<a class="link-purple deleteClass" data-toggle="modal" data-target="#deleteModal" data-id="<?= $pag->id ?>" >Delete</a> <?php }else{ echo '-';}?>

							  	</td>
		                      	<td>
									<?php   $Url = base_url(); 
											$trimUrl = preg_replace( "#^[^:/.]*[:/]+#i", "", $Url );
									?>
									<a class="link-purple" href="<?= base_url('webshop/pages/edit/'.$pag->id) ?>">View</a>
									<?php if(isset($shopDetail) && $shopDetail->webshop_status == 1){
												if($shopDetail->org_website_address != "" && $shopDetail->org_website_address != Null){
													    $previewUrl = $shopDetail->org_website_address; 
													}
												else{
													    $previewUrl = 'https://shop'.$_SESSION['ShopID'].'.'.$trimUrl.'page/'.$pag->identifier;
												    } ?>
												/ <a class="link-purple" href="<?php echo $previewUrl; ?>" target="_blank">Preview</a>	
									<?php 		}	
									?>		
								</td>
		                    </tr>
		                    <?php } ?>
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
			<form id="cmsDeleteForm" method="POST" action="<?= base_url('WebshopController/deleteCMAPage')?>">
				<input type="hidden" name="cmsPageID" id="cmsPageID">
				<div class="modal-header">
					<h1 class="head-name">Are you sure? you want to Delete Page!</h1>
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