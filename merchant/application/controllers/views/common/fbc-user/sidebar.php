<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
   <div class="sidebar-sticky pt-3">
      <div class="side-arrowicon"><img src="<?php echo SKIN_IMG; ?>sidearrow-icon.png"></div>
      <ul class="nav flex-column">
      <li class="nav-item">
            <a class="nav-link  <?php echo (isset($side_menu) && ($side_menu=='dashboard'))?'  has-submenu  show':''; ?>" href="<?php echo base_url() ?>DashboardController/index" >
            Dashboard
            </a>
         </li>
        
            <li class="nav-item <?php echo (isset($side_menu) && ($side_menu=='category' || $side_menu=='warehouse' || $side_menu=='add_product' ||  $side_menu=='edit_product' || $side_menu == 'search'))?'active':''; ?>">
              
               <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='category' || $side_menu=='warehouse' || $side_menu=='add_product' ||  $side_menu=='edit_product' || $side_menu == 'search'))?'has-submenu collapsed':''; ?>" href="#SubMenu1" data-toggle="collapse" data-parent="#SubMenu1">
               Database
               <i class="fas fa-angle-down"></i>
               </a>
              
               <div class="submenu <?php echo (isset($side_menu) && ($side_menu=='category' || $side_menu=='warehouse' || $side_menu=='add_product' ||  $side_menu=='edit_product' || $side_menu == 'search' ))?'collapse show':'collapse'; ?>" id="SubMenu1">
                  <a href="<?php echo base_url(); ?>seller/warehouse/" class="list-submenu" data-parent="#SubMenu1">Product</a>
               </div>
            </li>
         
            <li class="nav-item <?php echo (isset($side_menu) && ($side_menu=='webShop' || $side_menu=='webshopThemes' || $side_menu=='webshopStaticBlocks' ||  $side_menu=='webshopPayment' || $side_menu=='webshopProductBlocks' || $side_menu=='webshopContactUs' || $side_menu=='webshopPromoTextBanners'))?'active':''; ?>">
            <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop',$this->session->userdata('userPermission'))){ ?>
               <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='webShop'  || $side_menu=='webshop'  || $side_menu=='webshopThemes' || $side_menu=='webshopStaticBlocks' || $side_menu=='webshopPayment' || $side_menu=='webshopProductBlocks' || $side_menu=='webshopContactUs' || $side_menu=='webshopPromoTextBanners'))?'has-submenu collapsed':''; ?>" href="#SubMenu2" data-toggle="collapse" data-parent="#SubMenu2">
               Webshop
               <i class="fas fa-angle-down"></i>
               </a>
               <?php } ?>
               <div class="submenu <?php echo (isset($side_menu) && ($side_menu=='webShop' || $side_menu=='webshop' || $side_menu=='webshopThemes' || $side_menu=='webshopStaticBlocks' || $side_menu=='webshopPayment' || $side_menu=='webshopProductBlocks' || $side_menu=='webshopContactUsRequests' || $side_menu=='webshopPromoTextBanners'))?'collapse show':'collapse'; ?>" id="SubMenu2">
               <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/orders',$this->session->userdata('userPermission'))){ ?>
                  <a href="<?= base_url('webshop/orders') ?>" class="list-submenu" data-parent="#SubMenu2">Orders</a>
               <?php } ?>

               <!--  <a href="<?//= base_url('webshop/orders/return-request') ?>" class="list-submenu" data-parent="#SubMenu2">Returns</a>
                  <a href="<?//= base_url('webshop/orders/refund-request') ?>" class="list-submenu" data-parent="#SubMenu2">Refunds</a> -->
               </div>
            </li>

         <!-- <li class="nav-item">
            </li>-->
            <li class="nav-item">
               <a class="nav-link " href="<?php echo base_url() ?>logout" >
               Logout
               </a>
            </li>
      </ul>
   </div>
</nav>
