<div class="sidebar col-md-3 col-sm-3">

	<ul class="list-group margin-bottom-25 sidebar-menu">



		<li class="<?php echo (isset($side_tab) && $side_tab == 'account_info') ? 'active' : ''; ?> list-group-item clearfix"><a href="<?= BASE_URL ?>customer/account"><i class="fa fa-angle-right"></i><span class="personal-info-i"> Personal Information</span></a></li>

		<li class="<?php echo (isset($side_tab) && $side_tab == 'my_address') ? 'active' : ''; ?> list-group-item clearfix"><a href="<?= BASE_URL ?>customer/manage-address"><i class="fa fa-angle-right"></i><span class="manage-address-i"> Manage Addresses</span> </a></li>

		<li class="<?php echo (isset($side_tab) && $side_tab == 'my_orders') ? 'active' : ''; ?> list-group-item clearfix"><a href="<?= BASE_URL ?>customer/my-orders"><i class="fa fa-angle-right"></i><span class="my-orders-i"> My Orders & Returns</span> </a></li>
		<li class="<?php echo (isset($side_tab) && $side_tab == 'help_desk') ? 'active' : ''; ?> list-group-item clearfix"><a href="<?= BASE_URL ?>customer/help-desk"><i class="fa fa-angle-right"></i><span class="help-desk-i"> Help desk</span> </a></li>
		<li class="<?php echo (isset($side_tab) && $side_tab == 'messaging') ? 'active' : ''; ?> list-group-item clearfix"><a href="<?= BASE_URL ?>customer/messaging"><i class="fa fa-angle-right"></i><span class="help-desk-i"> Messaging</span> </a></li>
		<li class="<?php echo (isset($side_tab) && $side_tab == 'help_desk') ? 'active' : ''; ?> list-group-item clearfix"><a href="<?= BASE_URL ?>customer/my-giftcards"><i class="fa fa-angle-right"></i><span class="help-desk-i"> My Giftcards</span> </a></li>

		<!--<li class="<?php echo (isset($side_tab) && $side_tab == 'wishlist') ? 'active' : ''; ?> list-group-item clearfix"><a href="<?= BASE_URL ?>customer/wishlist"><i class="fa fa-angle-right"></i><span class="wishlist-i"> Wishlist</span> </a></li>-->

		<?php if ((isset($_SESSION['special_features']) && $_SESSION['special_features'] == 1)) { ?>

			<li class="<?php echo (isset($side_tab) && $side_tab == 'special_features') ? 'active' : ''; ?> list-group-item clearfix"><a href="<?= BASE_URL ?>customer/special-features"><i class="fa fa-angle-right"></i><span class="special-features-i"> Special Features</span> </a></li>

		<?php } ?>

	</ul>

</div><!-- my-profile-list -->