<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">

   <div class="sidebar-sticky pt-3">

      <div class="side-arrowicon"><img src="<?php echo SKIN_IMG; ?>sidearrow-icon.png"></div>

      <ul class="nav flex-column">

         <li class="nav-item">

            <a class="nav-link  <?php echo (isset($side_menu) && ($side_menu == 'dashboard')) ? '  has-submenu  show' : ''; ?>" href="<?php echo base_url() ?>DashboardController/index">

               Dashboard

            </a>

         </li>

         <?php if (empty($this->session->userdata('userPermission')) || in_array('database', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item <?php echo (isset($side_menu) && ($side_menu == 'category' || $side_menu == 'warehouse' || $side_menu == 'add_product' ||  $side_menu == 'edit_product' || $side_menu == 'search')) ? 'active' : ''; ?>">

               <?php if (empty($this->session->userdata('userPermission')) || in_array('database', $this->session->userdata('userPermission'))) { ?>

                  <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'category' || $side_menu == 'warehouse' || $side_menu == 'add_product' ||  $side_menu == 'edit_product' || $side_menu == 'search')) ? 'has-submenu collapsed' : ''; ?>" href="#SubMenu1" data-toggle="collapse" data-parent="#SubMenu1">

                     Cataloging

                     <i class="fas fa-angle-down"></i>

                  </a>

               <?php } ?>

               <div class="submenu <?php echo (isset($side_menu) && ($side_menu == 'category' || $side_menu == 'warehouse' || $side_menu == 'add_product' ||  $side_menu == 'edit_product' || $side_menu == 'search')) ? 'collapse show' : 'collapse'; ?>" id="SubMenu1">

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('database/product', $this->session->userdata('userPermission'))) { ?>

                     <a href="<?php echo base_url(); ?>seller/warehouse/" class="list-submenu" data-parent="#SubMenu1">Product</a>

                  <?php } ?>

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('database/category', $this->session->userdata('userPermission'))) { ?>

                     <a class="list-submenu " data-parent="#SubMenu4" href="<?= base_url('category') ?>"> Category

                     </a>

                  <?php } ?>

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('database/attributes', $this->session->userdata('userPermission'))) { ?>

                     <a class="list-submenu " data-parent="#SubMenu4" href="<?php echo base_url('attribute') ?>">

                        Attributes

                     </a>

                  <?php } ?>

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('database/variants', $this->session->userdata('userPermission'))) { ?>

                     <a class="list-submenu " data-parent="#SubMenu4" href="<?php echo base_url('variants') ?>">

                        Variants

                     </a>

                  <?php } ?>

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('database/publishers', $this->session->userdata('userPermission'))) { ?>

                     <a class="list-submenu " data-parent="#SubMenu4" href="<?php echo base_url('publishers') ?>">

                        Merchants

                     </a>

                  <?php } ?>

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('database/gift_master', $this->session->userdata('userPermission'))) { ?>

                     <a class="list-submenu " data-parent="#SubMenu4" href="<?php echo base_url('giftMaster') ?>">

                        Gift Master

                     </a>

                  <?php } ?>

               </div>

            </li>

         <?php } ?>



         <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item <?php echo (isset($side_menu) && ($side_menu == 'webShop' || $side_menu == 'webshopThemes' || $side_menu == 'webshopStaticBlocks' ||  $side_menu == 'webshopPayment' || $side_menu == 'webshopProductBlocks' || $side_menu == 'webshopContactUs' || $side_menu == 'webshopPromoTextBanners')) ? 'active' : ''; ?>">

               <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop', $this->session->userdata('userPermission'))) { ?>

                  <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'webShop'  || $side_menu == 'webshop'  || $side_menu == 'webshopThemes' || $side_menu == 'webshopStaticBlocks' || $side_menu == 'webshopPayment' || $side_menu == 'webshopProductBlocks' || $side_menu == 'webshopContactUs' || $side_menu == 'webshopPromoTextBanners')) ? 'has-submenu collapsed' : ''; ?>" href="#SubMenu2" data-toggle="collapse" data-parent="#SubMenu2">

                     Eshop

                     <i class="fas fa-angle-down"></i>

                  </a>

               <?php } ?>

               <div class="submenu <?php echo (isset($side_menu) && ($side_menu == 'webShop' || $side_menu == 'webshop' || $side_menu == 'webshopThemes' || $side_menu == 'webshopStaticBlocks' || $side_menu == 'webshopPayment' || $side_menu == 'webshopProductBlocks' || $side_menu == 'webshopContactUsRequests' || $side_menu == 'webshopPromoTextBanners')) ? 'collapse show' : 'collapse'; ?>" id="SubMenu2">

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/webshop', $this->session->userdata('userPermission'))) { ?>

                     <a href="<?= base_url('webshop') ?>" class="list-submenu" data-parent="#SubMenu2">EShop</a>

                  <?php } ?>

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration', $this->session->userdata('userPermission'))) { ?>

                     <a href="<?= base_url('webshop/settings') ?>" class="list-submenu" data-parent="#SubMenu2">Website Configuration</a>

                  <?php } ?>

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/orders', $this->session->userdata('userPermission'))) { ?>

                     <a href="<?= base_url('webshop/orders') ?>" class="list-submenu" data-parent="#SubMenu2">Orders</a>

                  <?php } ?>

             

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/orders', $this->session->userdata('userPermission'))) { ?>

							<a href="<?= base_url('webshop/abundant-carts') ?>" class="list-submenu" data-parent="#SubMenu2">Abandoned Cart</a>

						<?php } ?>

                  <!--  <a href="<? //= base_url('webshop/orders/return-request') 

                                 ?>" class="list-submenu" data-parent="#SubMenu2">Returns</a>

                  <a href="<? //= base_url('webshop/orders/refund-request') 

                           ?>" class="list-submenu" data-parent="#SubMenu2">Refunds</a> -->

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/customers_type', $this->session->userdata('userPermission'))) { ?>

                     <a href="<?= base_url('customertype') ?>" class="list-submenu" data-parent="#SubMenu2">Customers Type</a>

                  <?php } ?>



                  <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/customers', $this->session->userdata('userPermission'))) { ?>

                     <a href="<?php echo base_url(); ?>customers" class="list-submenu" data-parent="#SubMenu2">Customers</a>

                  <?php } ?>



                  <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/discounts', $this->session->userdata('userPermission'))) { ?>

                     <a href="<?= base_url('webshop/catalogue-discounts') ?>" class="list-submenu" data-parent="#SubMenu2">Discounts</a>

                  <?php } ?>



                  <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/product_reviews', $this->session->userdata('userPermission'))) { ?>

                     <a href="<?php echo base_url(); ?>product-reviews" class="list-submenu" data-parent="#SubMenu2">Product Reviews</a>

                  <?php } ?>



                  <!-- <?php //if(empty($this->session->userdata('userPermission')) || in_array('webshop/coming_soon_products_notify',$this->session->userdata('userPermission'))){ 

                        ?>

                     <a href="<?php //echo base_url(); 

                              ?>products_notify" class="list-submenu" data-parent="#SubMenu2">Coming Soon Products Notify</a>

                  <?php //} 

                  ?> -->



                  <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/contact_us_requests', $this->session->userdata('userPermission'))) { ?>

                     <a href="<?= base_url('webshop/contact-us-requests') ?>" class="list-submenu" data-parent="#SubMenu2">Contact us requests</a>

                  <?php } ?>



                  <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/newsletter_subscriber', $this->session->userdata('userPermission'))) { ?>

                     <a href="<?= base_url('webshop/newsletter-subscriber') ?>" class="list-submenu" data-parent="#SubMenu2">Newsletter Subscriber</a>

                  <?php } ?>



                  <?php if (empty($this->session->userdata('userPermission')) || in_array('webshop/search_terms', $this->session->userdata('userPermission'))) { ?>

                     <a href="<?= base_url('webshop/search-term') ?>" class="list-submenu" data-parent="#SubMenu2">Search Terms</a>

                  <?php } ?>

               </div>

            </li>

         <?php } ?>



         <!-- <?php if (empty($this->session->userdata('userPermission')) || in_array('sale_analytics', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item <?php echo (isset($side_menu) && ($side_menu == 'webshopreports' || $side_menu == 'b2webshopreports')) ? 'active' : ''; ?>">

            <?php if (empty($this->session->userdata('userPermission')) || in_array('sale_analytics', $this->session->userdata('userPermission'))) { ?>

               <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'webshopreports' || $side_menu == 'b2webshopreports')) ? 'has-submenu collapsed' : ''; ?>" href="#sale_SubMenu1" data-toggle="collapse" data-parent="#sale_SubMenu1">

               Sales Analytics

               <i class="fas fa-angle-down"></i>

               </a>

            <?php } ?>

               <div class="submenu <?php echo (isset($side_menu) && ($side_menu == 'webshopreports' || $side_menu == 'b2webshopreports')) ? 'collapse show' : 'collapse'; ?>" id="sale_SubMenu1">

               <?php if (empty($this->session->userdata('userPermission')) || in_array('sale_analytics/webshop_reports', $this->session->userdata('userPermission'))) { ?>

                  <a href="<?php echo base_url(); ?>dashboard/webshopreports" class="list-submenu" data-parent="#sale_SubMenu1">Webshop Reports</a>

               <?php } ?>

               </div>

            </li>

         <?php } ?> -->

         <li class="nav-item">
            <a class="nav-link  <?php echo (isset($side_menu) && ($side_menu == 'faqs')) ? '  has-submenu  show' : ''; ?>" href="<?php echo base_url() ?>faqs">
               Faqs
            </a>
         </li>
         <li class="nav-item">
            <a class="nav-link  <?php echo (isset($side_menu) && ($side_menu == 'help_desk')) ? '  has-submenu  show' : ''; ?>" href="<?php echo base_url() ?>help_desk">
               Help desk list
            </a>
         </li>

         <?php if (empty($this->session->userdata('userPermission')) || in_array('reports', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item">

               <a class="nav-link  <?php echo (isset($side_menu) && ($side_menu == 'reports')) ? '  has-submenu  show' : ''; ?>" href="<?php echo base_url() ?>reports">

                  Reports

               </a>

            </li>

         <?php } ?>





         <?php if (empty($this->session->userdata('userPermission')) || in_array('system', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item <?php echo (isset($side_menu) && ($side_menu == 'System')) ? 'active' : ''; ?>">

               <?php if (empty($this->session->userdata('userPermission')) || in_array('system', $this->session->userdata('userPermission'))) { ?>

                  <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'System')) ? 'has-submenu collapsed' : ''; ?>" href="#SubMenu4" data-toggle="collapse" data-parent="#SubMenu4">

                     System

                     <i class="fas fa-angle-down"></i>

                  </a>

               <?php } ?>

               <div class="submenu <?php echo (isset($side_menu) && ($side_menu == 'System')) ? 'collapse show' : 'collapse'; ?>" id="SubMenu4">

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('system/email_template', $this->session->userdata('userPermission'))) { ?>

                     <a href="<?php echo base_url(); ?>email-template" class="list-submenu" data-parent="#SubMenu4">Email Template</a>

                  <?php } ?>

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('system/settings', $this->session->userdata('userPermission'))) { ?>

                     <a class="list-submenu " data-parent="#SubMenu4" href="<?php echo base_url() ?>UserController/settings">

                        Settings

                     </a>

                  <?php } ?>

                  <?php if (empty($this->session->userdata('userPermission')) || in_array('system/publisher_commission', $this->session->userdata('userPermission'))) { ?>

                     <a class="list-submenu " data-parent="#SubMenu4" href="<?php echo base_url() ?>publisher_commission">

                        Merchant Commission

                     </a>

                  <?php } ?>

                     <a class="list-submenu " data-parent="#SubMenu4" href="<?php echo base_url() ?>subscription">

                        Subscription Plans

                     </a>

                 
                     <a class="list-submenu " data-parent="#SubMenu4" href="<?php echo base_url() ?>driver">

                        Driver Management

                     </a>

               </div>

            </li>

         <?php } ?>



         <li class="nav-item <?php echo (isset($side_menu) && ($side_menu == 'Addons')) ? 'active' : ''; ?>">

            <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'Addons')) ? 'has-submenu collapsed' : ''; ?>" 

               href="#SubMenuAddons" data-toggle="collapse" data-parent="#SubMenuAddons">

               Add-ons Services

               <i class="fas fa-angle-down"></i>

            </a>



            <div class="submenu <?php echo (isset($side_menu) && ($side_menu == 'Addons')) ? 'collapse show' : 'collapse'; ?>" id="SubMenuAddons">

               <a class="list-submenu" data-parent="#SubMenuAddons" href="<?php echo base_url(); ?>addons">

                     Addon Services

               </a>

               <a class="list-submenu" data-parent="#SubMenuAddons" href="<?php echo base_url(); ?>addonCategories">

                     Addon Categories

               </a>

            </div>

         </li>
         <li class="nav-item">

               <a class="nav-link <?php echo (isset($side_menu) && ($side_menu=='productbadges')) ? 'has-submenu show' : ''; ?>" 

                  href="<?php echo base_url('productbadges'); ?>">

                 Product Badges

               </a>

            </li>


         <?php if (empty($this->session->userdata('userPermission')) || in_array('admin_user_role', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item">

               <a class="nav-link  <?php echo (isset($side_menu) && ($side_menu == 'adminuserrole')) ? '  has-submenu  show' : ''; ?>" href="<?php echo base_url() ?>adminuserrole/edit-user-role">

                  Admin User Role

               </a>

            </li>

         <?php } ?>



         <?php if (empty($this->session->userdata('userPermission')) || in_array('admin_user', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item">

               <a class="nav-link  <?php echo (isset($side_menu) && ($side_menu == 'adninuser')) ? '  has-submenu  show' : ''; ?>" href="<?php echo base_url() ?>adminuser/user-lists">

                  Admin User

               </a>

            </li>

         <?php } ?>



         <?php if (empty($this->session->userdata('userPermission')) || in_array('testimonials', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item">

               <a class="nav-link  <?php echo (isset($side_menu) && ($side_menu == 'testimonials')) ? '  has-submenu  show' : ''; ?>" href="<?php echo base_url() ?>testimonials/testimonial-lists">

                  Testimonials

               </a>

            </li>

         <?php } ?>



         <?php if (empty($this->session->userdata('userPermission')) || in_array('blogs', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item">

               <a class="nav-link  <?php echo (isset($side_menu) && ($side_menu == 'blogs')) ? '  has-submenu  show' : ''; ?>" href="<?php echo base_url() ?>blogs/blogs-lists">

                  Blogs

               </a>

            </li>

         <?php } ?>

         <!-- <?php if (empty($this->session->userdata('userPermission')) || in_array('blogs', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item">

               <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'blogs')) ? '  has-submenu  show' : ''; ?>" href="javascript:void(0)" onclick="Business_World_login()">

                  Business World

               </a>

            </li>

         <?php } ?> -->

         <!-- <?php if (empty($this->session->userdata('userPermission')) || in_array('blogs', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item">

               <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'blogs')) ? '  has-submenu  show' : ''; ?>" href="javascript:void(0)" onclick="next_gen_publishing_login()">

                  Next Gen Publishing

               </a>

            </li>

         <?php } ?>

         <?php if (empty($this->session->userdata('userPermission')) || in_array('blogs', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item">

               <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'blogs')) ? '  has-submenu  show' : ''; ?>" href="javascript:void(0)" onclick="spenta_multimedia_login()">

                  Spenta Multimedia

               </a>

            </li>

         <?php } ?> -->

         <!-- <?php if (empty($this->session->userdata('userPermission')) || in_array('blogs', $this->session->userdata('userPermission'))) { ?>

            <li class="nav-item">

               <a class="nav-link <?php echo (isset($side_menu) && ($side_menu == 'blogs')) ? '  has-submenu  show' : ''; ?>" href="javascript:void(0)" onclick="mediastar_login()">

                  MediaStar

               </a>

            </li>

         <?php } ?> -->

         <!-- <li class="nav-item">

            </li>-->

         <li class="nav-item">

            <a class="nav-link " href="<?php echo base_url() ?>logout">

               Logout

            </a>

         </li>

      </ul>

   </div>

</nav>

<script>

   function Business_World_login() {

      // var inputEmail = $('#inputEmail').val();

      // var inputPassword = $('#inputPassword').val();



      $.ajax({

         // type: "POST",

         url: BASE_URL + "UserController/Business_World_login",

         // data: {

         // 	inputEmail: inputEmail,

         // 	inputPassword: inputPassword

         // },

         dataType: 'json', // Set the expected data type

         success: function(response) {

            console.log(response);

            console.log(response.redirect);





            if (response.status == 200) {

               // Redirect to the specified URL

               window.open(response.redirect, '_blank')

               window.location.href = response.redirect;

            } else {

               // Display an error message (you can customize this part)

               // console.error(response.msg);

               console.error('An unexpected error occurred.', response);



            }

         },

         error: function(xhr, status, error) {

            // Handle AJAX errors here

            console.error("AJAX Error:", status, error);

         }

      });

   }

   function next_gen_publishing_login() {

      // var inputEmail = $('#inputEmail').val();

      // var inputPassword = $('#inputPassword').val();



      $.ajax({

         // type: "POST",

         url: BASE_URL + "UserController/next_gen_publishing_login",

         // data: {

         // 	inputEmail: inputEmail,

         // 	inputPassword: inputPassword

         // },

         dataType: 'json', // Set the expected data type

         success: function(response) {

            console.log(response);

            console.log(response.redirect);





            if (response.status == 200) {

               // Redirect to the specified URL

               window.open(response.redirect, '_blank')

               window.location.href = response.redirect;

            } else {

               // Display an error message (you can customize this part)

               // console.error(response.msg);

               console.error('An unexpected error occurred.', response);



            }

         },

         error: function(xhr, status, error) {

            // Handle AJAX errors here

            console.error("AJAX Error:", status, error);

         }

      });

   }

   function spenta_multimedia_login() {

      // var inputEmail = $('#inputEmail').val();

      // var inputPassword = $('#inputPassword').val();



      $.ajax({

         // type: "POST",

         url: BASE_URL + "UserController/spenta_multimedia_login",

         // data: {

         // 	inputEmail: inputEmail,

         // 	inputPassword: inputPassword

         // },

         dataType: 'json', // Set the expected data type

         success: function(response) {

            console.log(response);

            console.log(response.redirect);





            if (response.status == 200) {

               // Redirect to the specified URL

               window.open(response.redirect, '_blank')

               window.location.href = response.redirect;

            } else {

               // Display an error message (you can customize this part)

               // console.error(response.msg);

               console.error('An unexpected error occurred.', response);



            }

         },

         error: function(xhr, status, error) {

            // Handle AJAX errors here

            console.error("AJAX Error:", status, error);

         }

      });

   }

   function mediastar_login() {

      // var inputEmail = $('#inputEmail').val();

      // var inputPassword = $('#inputPassword').val();



      $.ajax({

         // type: "POST",

         url: BASE_URL + "UserController/mediastar_login",

         // data: {

         // 	inputEmail: inputEmail,

         // 	inputPassword: inputPassword

         // },

         dataType: 'json', // Set the expected data type

         success: function(response) {

            console.log(response);

            console.log(response.redirect);





            if (response.status == 200) {

               // Redirect to the specified URL

               window.open(response.redirect, '_blank')

               window.location.href = response.redirect;

            } else {

               // Display an error message (you can customize this part)

               // console.error(response.msg);

               console.error('An unexpected error occurred.', response);



            }

         },

         error: function(xhr, status, error) {

            // Handle AJAX errors here

            console.error("AJAX Error:", status, error);

         }

      });

   }

</script>