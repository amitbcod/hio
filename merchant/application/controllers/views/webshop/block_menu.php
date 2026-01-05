<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <ul class="nav nav-pills">
    <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li>
    <li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>
    <li><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>
    <li class="active"><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>
    <li><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>
    <li><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
    <li class="active"><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>

  </ul>

  <div class="tab-content">
    <div id="menu-tab" class="tab-pane fade in active common-tab-section min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <?php if($blockID != 9) { ?>
          <h1 class="head-name">&nbsp; Top Menu -  Edit</h1>
        <?php }else{ ?>
          <h1 class="head-name">&nbsp; Sidebar - Browse By Category Menu -  Edit</h1>
        <?php } ?>

      </div><!-- d-flex -->
		  <!-- form -->

      <div class="content-main form-dashboard webshop-menu">
        <div class="radio row">
    			<div class="col-sm-4">
            <label>
              <input type="radio" name="top_menu_selection" <?php echo ($menuType->menu_type==1) ? 'checked':'' ?> value="1">Only Category Menu
                <?php
                  if(isset($menuType)){
                    if($menuType->menu_type == 1 && (isset($categoryMenu) && !empty($categoryMenu)) ) { ?>
                       <span class="menu-status current"><a>Current</a></span>
                    <?php }else if($menuType->menu_type != 1 && (isset($categoryMenu) && !empty($categoryMenu)) ) { ?>
                      <span class="menu-status use">
                        <a class="menuClass" data-toggle="modal" data-target="#menuUse" data-id="1">Use</a>
                      </span>
                    <?php } } ?>
              <span class="checkmark"></span>
            </label>
    			</div>
          <?php if($blockID != 9){ ?>
            <div class="col-sm-4">
            <label>
              <input type="radio" name="top_menu_selection" <?php echo ($menuType->menu_type==2) ? 'checked':'' ?> value="2">Custom Menu
              <?php
                if(isset($menuType)){
               
                  if($menuType->menu_type == 2 && (isset($customMenu) && !empty($customMenu)) ) { ?>
                     <span class="menu-status current"><a>Current</a></span>
                  <?php }else if($menuType->menu_type != 2 && (isset($customMenu) && !empty($customMenu)) ) { ?>
                    <span class="menu-status use"><a class="menuClass" data-toggle="modal" data-target="#menuUse" data-id="2">Use</a></span>
                  <?php } } ?>
                <span class="checkmark"></span>
            </label>
          </div>
         <?php  } ?>

    		</div><!-- radio -->

    		<div class="parent-menu-list" id="category_menu_list" style="<?php echo ($menuType->menu_type==1) ? 'display: block':'display: none' ?>">
          <?php include('category_menu.php'); ?>
        </div>
        <div class="parent-menu-list" id="cust_menu_list" style="<?php echo ($menuType->menu_type==2) ? 'display: block':'display: none' ?>">
          <?php include('custom_menu_list.php'); ?>
        </div>

      </div>
        <!--end form-->
    </div>
	</div>
</main>

<div class="modal fade" id="menuUse" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="z-index: 99999999;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form id="menuForm" method="POST" action="<?= base_url('WebshopController/changeMenu')?>">
        <input type="hidden" name="menuType" id="menuType">
        <input type="hidden" name="block_ID" id="block_ID" value="<?php echo (isset($menuType->id) ? $menuType->id : '' ); ?>">
        <div class="modal-header">
          <h1 class="head-name">Are you sure? you want to change menu!</h1>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">No</button>
          <button class="purple-btn">Change</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form id="blockDeleteForm" method="POST" action="<?= base_url('WebshopController/deleteCustomMenu')?>">
        <input type="hidden" name="blockID" id="blockID">
        <div class="modal-header">
          <h1 class="head-name">Are you sure? you want to Delete Menu!</h1>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">No</button>
          <button type="submit" class="purple-btn">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="<?php echo SKIN_JS; ?>webshop.js"></script>
<script type="text/javascript">
  $("#customMenuTable").dataTable({
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
