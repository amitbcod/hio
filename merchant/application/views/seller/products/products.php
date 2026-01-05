<div id="Warehouse" class="tab-pane fade in active admin-shop-details-table" style="opacity:1;">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name">Product List </h1>
				
				<!-- product filter div -->
			</div>
			<!-- form -->
			<div class="content-main form-dashboard">
				
					<div class="table-responsive text-center">
						<table  class="table table-bordered table-style data-tbl dataTable dtr-inline product-list-tbl" id="DataTables_Table_WProducts" role="grid" aria-describedby="DataTables_Table_WProducts_info" >
							<thead>
								<tr>
									<th>PRODUCT NAME  </th>
									<th>CATEGORIES </th>
									<th>PRODUCT CODE  </th>
									<th>INVENTORY </th>
									<th>PRICE</th>
									<th>ESHOP PRICE</th>
									<th>LAST UPDATED </th>
									<th>DETAILS </th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
						
					</div>
				
			</div>
			<!--end form-->
		</div>
		<script type="text/javascript">
		$(document).ready(function(){
			<?php if(isset($_GET['goto']) && $_GET['goto']=='add_new'){?>
			$('#add_product_link').click();
			<?php } ?>
			
		});
		
		</script>
		<script type="text/javascript" src="<?php echo SKIN_JS; ?>seller_product_list.js"></script>
		