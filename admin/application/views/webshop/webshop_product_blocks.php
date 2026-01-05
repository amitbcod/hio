<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<ul class="nav nav-pills">

    	<!-- <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->
    	<li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>
    	<li><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>

		<li><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>

		<li><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>

		<li class="active"><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
		<li class=""><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>
		

  	</ul>

  	<div class="tab-content">

    	<div id="static-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">

      		<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

          		<h1 class="head-name">Product Blocks </h1> 

        	</div><!-- d-flex -->

		

	        <!-- form -->

	        <div class="content-main form-dashboard">

				<div class="table-responsive text-center">

	                <table class="table table-bordered table-style" id="productBlocksTable">

	                  	<thead>

	                    	<tr>

	                      		<th>Block Name  </th>

	                      		<th>Identifier  </th>

	                      		<th>Action </th>

	                    	</tr>	

	                  	</thead>

	                  	<tbody>

	                  		<?php foreach ($productBlocks as $block ) {?>

	                    	<tr>

	                      		<td><?= $block->block_name ?></td>

	                      		<td><?= $block->block_identifier ?></td>

						  		<td><a class="link-purple" href="<?= base_url('webshop/assign-product-blocks/'.$block->id) ?>">Assign Products</a></td>

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





<script src="<?php echo SKIN_JS; ?>webshop.js"></script>

<script type="text/javascript">

	$("#productBlocksTable").dataTable({

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