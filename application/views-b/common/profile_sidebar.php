<div class="col-md-3 col-lg-3 ">
	<div class="my-profile-list">
		<ul>
			<li class="<?php echo (isset($side_tab) && $side_tab == 'my_orders') ? 'active':''; ?>"><a href="<?= BASE_URL?>customer/my-orders"><span class="my-orders-i"> My Orders & Returns</span> </a></li>
			<li class="<?php echo (isset($side_tab) && $side_tab == 'account_info') ? 'active':''; ?>"><a href="<?= BASE_URL?>customer/account"><span class="personal-info-i"> Personal Information</span></a></li>
			<li class="<?php echo (isset($side_tab) && $side_tab == 'my_address') ? 'active':''; ?>"><a href="<?= BASE_URL?>customer/manage-address"><span class="manage-address-i"> Manage Addresses</span> </a></li>
			<li class="<?php echo (isset($side_tab) && $side_tab == 'wishlist') ? 'active':''; ?>"><a href="<?= BASE_URL?>customer/wishlist"><span class="wishlist-i"> Wishlist</span> </a></li>
			<?php if ((isset($_SESSION['special_features']) && $_SESSION['special_features'] == 1)) {?>
			<li class="<?php echo (isset($side_tab) && $side_tab == 'special_features') ? 'active':''; ?>"><a href="<?= BASE_URL?>customer/special-features"><span class="special-features-i"> Special Features</span> </a></li>
		<?php } ?>
		</ul>
	</div><!-- my-profile-list -->
</div><!-- col-md-3 -->