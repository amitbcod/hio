<ul class="nav nav-pills">
	<!-- <li class="<?php echo (isset($current_tab) && $current_tab == 'specialPricing')?'active':''?> "><a href="<?= base_url('webshop/special-pricing') ?>">Special Pricing</a></li> -->
	<li class="<?php echo (isset($current_tab) && $current_tab == 'catDiscount')?'active':''?> "><a href="<?= base_url('webshop/catalogue-discounts') ?>">Catalogue Discounts</a></li>
	<li class="<?php echo (isset($current_tab) && $current_tab == 'prodDiscount')?'active':''?> "><a href="<?= base_url('webshop/product-discounts') ?>">Product Discounts</a></li>
	<li class="<?php echo (isset($current_tab) && $current_tab == 'cpCode')?'active':''?> "><a href="<?= base_url('webshop/coupon-discounts') ?>">Coupon Code</a></li>
	<li class="<?php echo (isset($current_tab) && $current_tab == 'emlCoupon')?'active':''?> "><a href="<?= base_url('webshop/email-coupon') ?>">Email Coupon</a></li>
</ul>