<style>
.link-disabled {
    pointer-events: none;
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
<?php


$CI =& get_instance();   // get CI superobject
$CI->load->model('UserModel');
$UserDetails = $CI->UserModel->getUserByMerchantId($this->session->userdata('LoginID'));


$profile_incomplete = false;

$requiredFields = [
    'publication_name',
    'merchant_cat',
    'bank_name',
    'bank_branch_number',
    'beneficiary_acc_no',
    'beneficiary_name',
    'beneficiary_ifsc_code',
    'vendor_name',
    'shop_image',
    'commision_percent',
    'phone_no',
    'company_name',
    'company_address',
    'location',
    'state',
    'city',
    'zipcode'
];

foreach ($requiredFields as $field) {
    if (empty($UserDetails->$field)) {
        $profile_incomplete = true;
        break;
    }
}

if ($UserDetails->vat_status == 'registered' && empty($UserDetails->vat_no)) {
    $profile_incomplete = true;
}
?>


<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse"> 
   <div class="sidebar-sticky pt-3">

      <div class="side-arrowicon"><img src="<?php echo SKIN_IMG; ?>sidearrow-icon.png"></div>

      <ul class="nav flex-column">

         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='dashboard'))?'has-submenu show':''; ?>
               <?php echo $profile_incomplete ? ' link-disabled' : ''; ?>"
               href="<?php echo !$profile_incomplete ? base_url('DashboardController/index') : 'javascript:void(0);' ?>">
               Dashboard
            </a>
         </li>

         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='subscription'))?'has-submenu show':''; ?>
               <?php echo $profile_incomplete ? ' link-disabled' : ''; ?>"
               href="<?php echo !$profile_incomplete ? base_url('subscription') : 'javascript:void(0);' ?>">
              Subscriptions
            </a>
         </li>

         <li class="nav-item <?php echo (isset($side_menu) && in_array($side_menu,['category','warehouse','add_product','edit_product','search']))?'active':''; ?>">
            <a class="nav-link <?php echo (isset($side_menu) && in_array($side_menu,['category','warehouse','add_product','edit_product','search']))?'has-submenu collapsed':''; ?>
               <?php echo $profile_incomplete ? ' link-disabled' : ''; ?>"
               href="<?php echo !$profile_incomplete ? '#SubMenu1' : 'javascript:void(0);'; ?>" 
               data-toggle="collapse" data-parent="#SubMenu1">
               Cataloging
               <i class="fas fa-angle-down"></i>
            </a>

            <div class="submenu <?php echo (isset($side_menu) && in_array($side_menu,['category','warehouse','add_product','edit_product','search']))?'collapse show':'collapse'; ?>" id="SubMenu1">
               <a href="<?php echo !$profile_incomplete ? base_url('seller/warehouse/') : 'javascript:void(0);'; ?>" 
                  class="list-submenu <?php echo $profile_incomplete ? ' link-disabled' : ''; ?>" 
                  data-parent="#SubMenu1">Product</a>
            </div>
         </li>

         <li class="nav-item <?php echo (isset($side_menu) && in_array($side_menu,['webShop','webshopThemes','webshopStaticBlocks','webshopPayment','webshopProductBlocks','webshopContactUs','webshopPromoTextBanners']))?'active':''; ?>">
            <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop',$this->session->userdata('userPermission'))){ ?>
               <a class="nav-link <?php echo (isset($side_menu) && in_array($side_menu,['webShop','webshop','webshopThemes','webshopStaticBlocks','webshopPayment','webshopProductBlocks','webshopContactUs','webshopPromoTextBanners']))?'has-submenu collapsed':''; ?>
                  <?php echo $profile_incomplete ? ' link-disabled' : ''; ?>"
                  href="<?php echo !$profile_incomplete ? '#SubMenu2' : 'javascript:void(0);'; ?>" 
                  data-toggle="collapse" data-parent="#SubMenu2">
                  EShop
                  <i class="fas fa-angle-down"></i>
               </a>
            <?php } ?>

            <div class="submenu <?php echo (isset($side_menu) && in_array($side_menu,['webShop','webshop','webshopThemes','webshopStaticBlocks','webshopPayment','webshopProductBlocks','webshopContactUsRequests','webshopPromoTextBanners']))?'collapse show':'collapse'; ?>" id="SubMenu2">
               <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders',$this->session->userdata('userPermission'))){ ?>
                  <a href="<?php echo !$profile_incomplete ? base_url('webshop/b2b-orders') : 'javascript:void(0);'; ?>" 
                     class="list-submenu <?php echo $profile_incomplete ? ' link-disabled' : ''; ?>" 
                     data-parent="#SubMenu2">Orders</a>
               <?php } ?>
               <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/special-pricing', $this->session->userdata('userPermission'))) { ?>
                     <a href="<?= base_url('webshop/special-pricing') ?>" class="list-submenu" data-parent="#SubMenu2">Discounts</a>
                  <?php } ?>
            </div>
         </li>

         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='addons')) ? 'has-submenu show' : ''; ?>
               <?php echo $profile_incomplete ? ' link-disabled' : ''; ?>"
               href="<?php echo !$profile_incomplete ? base_url('addons') : 'javascript:void(0);'; ?>">
               Add-ons Services
            </a>
         </li>

         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='mydocuments')) ? 'has-submenu show' : ''; ?>
               <?php echo $profile_incomplete ? ' link-disabled' : ''; ?>"
               href="<?php echo !$profile_incomplete ? base_url('mydocuments') : 'javascript:void(0);'; ?>">
               My Documents
            </a>
         </li>

         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='messaging')) ? 'has-submenu show' : ''; ?>
               <?php echo $profile_incomplete ? ' link-disabled' : ''; ?>"
               href="<?php echo !$profile_incomplete ? base_url('messaging') : 'javascript:void(0);'; ?>">
               Messaging
            </a>
         </li>

         <li class="nav-item">
            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='productbadges')) ? 'has-submenu show' : ''; ?>
               <?php echo $profile_incomplete ? ' link-disabled' : ''; ?>"
               href="<?php echo !$profile_incomplete ? base_url('productbadges') : 'javascript:void(0);'; ?>">
               Product Badges
            </a>
         </li>

         <li class="nav-item">
            <a class="nav-link" href="<?php echo base_url('logout'); ?>">
               Logout
            </a>
         </li>

      </ul>
   </div>
</nav>

