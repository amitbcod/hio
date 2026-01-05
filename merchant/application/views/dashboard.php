<?php $this->load->view('common/fbc-user/header');

$CI = &get_instance();

?>

<link href="<?php echo SKIN_CSS; ?>dashboard1.css?v=<?php echo CSSJS_VERSION; ?>" rel="stylesheet">

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<div class="tab-content common-tab-section min-height-480">



		<div id="live-tab" class="tab-pane fade" style="opacity:1; display:block;">

			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

				<h1 class="head-name">Dashboard </h1>

			</div>

			<!-- form -->

			<div class="content-main form-dashboard">

				<div class="dashboard-section">

					<ul class="dashboard-list">

						<!-- <li><a href=""><p><span><?php echo  isset($user_count) ? $user_count : 'No Users found'; ?></span>User Count</p></a></li> -->

						<li>

							<a href="<?php echo base_url() . "seller/warehouse" ?>">

								<p><span><?php echo  isset($product_count) ? $product_count : 'No Products found'; ?></span>Products</p>

							</a>

						</li>



					

						<li>

							<p>

							 	Total Active Daily Deals&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo $daily_deals_limit; ?>

								</br>

								Current Active Daily Deals&nbsp;&nbsp;&nbsp; : <?php echo $current_active_daily_count; ?>

								</br>

								Remaining Daily Deals Slots : <?php echo $remaining_daily_deals; ?>

							</p>

						</li>



						<li>

							<p>

								Total Active Flash Sale&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo $flash_sale_limit; ?><br>

								Current Active Flash Sale&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo $current_active_flash_count; ?><br>

								Remaining Flash Sale Slots : <?php echo $remaining_flash_sale; ?>

							</p>

						</li>


					</ul>
					<?php 
						$avg = round($rating->avg_rating); // average rating from model

						// Only assign badge if avg rating is 3 or above
						$badge = '';
						if ($avg >= 3) {
							if ($avg == 3) {
								$badge = BASE_URL2 . '/uploads/trust_badges/ym_trusted_badges_3_stars.svg';
							} elseif ($avg == 4) {
								$badge = BASE_URL2 . '/uploads/trust_badges/ym_trusted_badges_4_stars.svg';
							} elseif ($avg == 5) {
								$badge = BASE_URL2 . '/uploads/trust_badges/ym_trusted_badges_5_stars.svg';
							}
						}

					?>

					<?php if($badge): ?>
						<p>My Achievements</p>
						<img src="<?= $badge; ?>" alt="Trust Badge" width="100">
					<?php endif; ?>

				</div>

						<!-- <li><a href="<?php echo base_url() . "publishers" ?>"><p><span><?php echo  isset($publisher_count) ? $publisher_count : 'No Publishers found'; ?></span>Publishers</p></a></li> -->

						<!-- <li><a href="<?php echo base_url() . "customers" ?>"><p><span><?php echo  isset($customer_count) ? $customer_count : 'No Customers found'; ?></span>Customers</p></a></li> -->

					</ul>

				</div>

			</div>

			<!--end form-->

		</div> <!-- dropshipping-products -->



	</div>

	

</main>

<script>

	$(document).ready(function() {

		$("#datatableattribute").dataTable({

			"order": [],

			"aaSorting": [],

			"ordering": [],

			"language": {

				"infoFiltered": "",

				"search": '',

				"searchPlaceholder": "Search",

				"paginate": {

					next: '<i class="fas fa-angle-right"></i>',

					previous: '<i class="fas fa-angle-left"></i>'

				}

			},

			stateSave: true,

			ordering: false,

		});

	});

</script>



<?php $this->load->view('common/fbc-user/footer'); ?>

