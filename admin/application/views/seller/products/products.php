<div id="Warehouse" class="tab-pane fade in active admin-shop-details-table" style="opacity:1;">

			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

				<h1 class="head-name">Product List </h1>

				<!-- <button class="btn btn-primary" id="resize_btn" name="resize_btn">  images to be  Resized</button> -->

				<!-- <div class="float-right product-filter-div">

					<div class="search-div d-none"  id="pro-search-div">

						<input class="form-control form-control-dark top-search" id="custome-filter" type="text" placeholder="Search" aria-label="Search">

						<button type="button" class="btn btn-sm search-icon" onclick="FilterProductDataTable();"><i class="fas fa-search"></i></button>

					</div>

					<div class="filter">

						<button>

							<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter" fill="currentColor" xmlns="http://www.w3.org/2000/svg">

								<path fill-rule="evenodd" d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"/>

							</svg>

							Filter

						</button>

					</div>

					<div class="filter-section">

					<span class="reset-arrow"><a  href="javascript:void(0);" onclick="location.reload();">Reset</a></span>

						<div class="close-arrow"> <i class="fa fa-angle-left"></i> </div>

						

						<div class="filter filter-inside">

							<button>

								<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter" fill="currentColor" xmlns="http://www.w3.org/2000/svg">

									<path fill-rule="evenodd" d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"></path>

								</svg>

								Filter

							</button>

						</div>

						<div class="justify-content-center my-4 image-box">

							<div class="col-md-12">

								<label class="checkbox"><input type="checkbox" class="form-control" name="image_filter[]" value="image_filter"><span class="checked"></span> Products Without Image</label>

							</div>

						</div>

						<div class="justify-content-center my-4 price-range">

							<h3>Price Range</h3>

							<form class="range-field w-100">

								<input id="slider11" class="border-0"  value="0" type="range" min="0" max="1000" />

							</form>

							<span class="zero-value">0</span>

							<span class="font-weight-bold text-primary ml-2 mt-1 valueSpan"></span>

						</div>

						<div class="justify-content-center range-box">

							<h3>Inventory </h3>

							<form class="range-field w-100">

								<input id="slider12" class="border-0 slider" value="0" type="range"  min="0" max="1000" />

							</form>

							<span class="zero-value">0</span>

							<span class="font-weight-bold text-primary ml-2 mt-1 valueSpan2"></span>

						</div>

						<div class="justify-content-center my-4 supplier-box">

							<h3>Supplier</h3>

							<div class="col-md-6"><label class="checkbox"><input type="checkbox" class="form-control" name="supplier_filter[]" value="Self"><span class="checked"></span> Self</label></div>

							<div class="col-md-6"><label class="checkbox"><input type="checkbox" class="form-control"  name="supplier_filter[]" value="B2B"><span class="checked"></span> B2B</label></div>

						</div>

						<div class="justify-content-center my-4 last-updated">

							<h3>Last Updated</h3>

							<div class="col-md-5"><input type="text" class="form-control"  id="from_date"></div>

							<div class="col-md-2">To</div>

							<div class="col-md-5"><input type="text" class="form-control"  id="to_date"></div>

						</div>

						<div class="filter-btn-box">

							<button class="filter-btn" onclick="FilterProductDataTable();">Filter</button>

						</div>

					</div>

				</div> -->

				<!-- product filter div -->

			</div>

			<!-- form -->

			<div class="content-main form-dashboard">

				<?php $approval_status = $this->input->get('approval_status'); ?>


				<div class="table-responsive text-center">

					<table  class="table table-bordered table-style data-tbl dataTable dtr-inline product-list-tbl" id="DataTables_Table_WProducts" role="grid" aria-describedby="DataTables_Table_WProducts_info" >
						<thead>
							<tr>
								<?php if ($approval_status === 'pending'): ?>
									<th><input type="checkbox" class="select-all"></th>
								<?php endif; ?>
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
				<?php if ($approval_status === 'pending'): ?>
					<div class="text-end">
						<button type="button" class="btn btn-success btn-sm approve-btn">Approve</button>
						<button type="button" class="btn btn-danger btn-sm reject-btn">Reject</button>
					</div>
				<?php endif; ?>
			</div>

			<!--end form-->

		</div>

		<script type="text/javascript">

		$(document).ready(function(){

			<?php if(isset($_GET['goto']) && $_GET['goto']=='add_new'){?>

			$('#add_product_link').click();

			<?php } ?>

			

		});



	// 	$("#resize_btn").click(function(){

    //       alert("clicked");

	// 	  $.ajax({

	// 		type: 'GET',

	// 		url: BASE_URL+"ImageresizeController/imageresize",

            

    //         success: function (response) {

    //             console.log(response);

    //             location.reload();

    //             /*console.log(response.voption);

    //             console.log("Sub-cat", sub_cat);

    //             $("#variant_tbody").append(response.voption);

    //             $("#product_variant_table").html(''); 

    //             $("#product_variant_table").empty();

    //             $("#product_variant_table").append(response.voption);

    //             $('#FBCUserCommonModal').modal('toggle');*/

                

    //             }

    //         });

    //   });

		

		</script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		<script type="text/javascript" src="<?php echo SKIN_JS; ?>seller_product_list.js"></script>

		