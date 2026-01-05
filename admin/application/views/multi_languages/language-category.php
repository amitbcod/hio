<?php $this->load->view('common/fbc-user/header'); ?>         
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <div class="tab-content">
    <div id="menu-tab" class="tab-pane fade in active common-tab-section min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        
        
      </div><!-- d-flex -->
		  <!-- form -->
    

    		<div class="parent-menu-list" id="category_menu_list" style="">
          <?php include('category_menu.php'); ?>
        </div>
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

<script src="<?php echo SKIN_JS; ?>Multi_Languages_setting.js"></script>
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
