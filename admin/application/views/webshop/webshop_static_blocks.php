<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
    	<!-- <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->
    	<li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>
    	<li><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>
		<li class="active"><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>
		<li><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>
		<li><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
		<li class=""><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>
		
  	</ul>
  	<div class="tab-content">
    	<div id="static-tab" class="tab-pane fade in active common-tab-section  min-height-480 admin-shop-details-table" style="opacity:1;">
      		<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          		<h1 class="head-name">Static Blocks </h1> 
          	<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
		 		<div class="float-right">
					<button class="white-btn" onclick="window.location.href='<?= base_url('webshop/add-static-blocks') ?>';"> +  Add New</button>
		  		</div>
		  	<?php } ?>
        	</div><!-- d-flex -->
		
	        <!-- form -->
	        <div class="content-main form-dashboard">
				<div class="table-responsive text-center">
	                <table class="table table-bordered table-style" id="staticBlocksTable">
	                  	<thead>
	                    	<tr>
	                      		<th>Static Block Names  </th>
	                      		<th>Identifier  </th>
	                      		<th>Last Updated  </th>
	                      		<th>Status  </th>
	                      		<th>Action </th>
						  		<th>Details </th>
	                    	</tr>	
	                  	</thead>
	                  	<tbody>
	                  		<?php foreach ($staticBlocks as $block ) {?>
	                    	<tr>
	                      		<td><?= $block->title ?></td>
	                      		<td><?= $block->identifier ?></td>
	                      		<td>
	                      			<?php 
		                      		if($block->updated_at == 0){
		                      			echo date('d/m/Y',$block->created_at).' | '.date('H:i a',$block->created_at);
		                      		}else{
		                      			echo date('d/m/Y',$block->updated_at).' | '.date('H:i a',$block->updated_at);
		                      		}
		                      		?>
	                      		</td>
	                      		<td>
	                      			<?php 
		                      		if($block->status == 1){
		                      			echo "Published";
		                      		}else{
		                      			echo "On Hold";
		                      		}
		                      		?>
	                      		</td>
						  		<td>
						  			<?php 
		                      		if($block->is_default == 1){ ?>
		                      			- /
		                      		<?php } else { ?>
		                      			<a class="link-purple deleteBlock" data-toggle="modal" data-target="#deleteModal" data-id="<?= $block->id ?>">Delete/</a>
		                      		<?php } ?>

		                      		<?php if($block->type == 4 || $block->type == 5){ ?>
		                      			<?php if($block->type == 4){ ?>
		                      				<a class="link-purple" href="<?= base_url('webshop/static-blocks/menu/'.$block->id) ?>">Manage		</a>
		                      			<?php }elseif($block->type == 5){ ?>
		                      				<?php if($block->identifier != 'homeblock1') {?>
		                      				<a class="link-purple" href="<?= base_url('webshop/static-blocks/banner/'.$block->id) ?>">Manage </a>
		                      			<?php }else{ ?>
		                      				<a class="link-purple" href="<?= base_url('webshop/static-blocks/homeblock/'.$block->id) ?>">Manage </a>
		                      		<?php 	} ?>
		                      			<?php }else{ ?>
		                      				-
		                      			<?php } ?>
	                      				
	                      			<?php }else{ ?>
	                      				-
	                      			<?php } ?>
		                      		
						  		</td>
	                      		<td>
	                      			<?php if($block->type != 4 && $block->type != 5) { ?>
	                      			<a class="link-purple" href="<?=  base_url('webshop/edit-static-blocks/'.$block->id) ?>">View</a>
		                      		 <!-- previously condition // if($block->is_default == 1) -->
		                      		<?php }else{ ?>
		                      			-
	                      		    <?php } ?>
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
			<form id="blockDeleteForm" method="POST" action="<?= base_url('WebshopController/deleteBlock')?>">
				<input type="hidden" name="blockID" id="blockID">
				<div class="modal-header">
					<h1 class="head-name">Are you sure? you want to Delete Block!</h1>
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
	$("#staticBlocksTable").dataTable({
        "language": {
			"infoFiltered": "",
			"search": '',
			"searchPlaceholder": "Search",
			
			"paginate": {
				next: '<i class="fas fa-angle-right"></i>',
				previous: '<i class="fas fa-angle-left"></i>'
			}
		},
		 "order": []
    });
</script>
<?php $this->load->view('common/fbc-user/footer'); ?>