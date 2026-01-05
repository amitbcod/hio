
<?php $this->load->view('common/fbc-user/header'); ?>
<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<link href='//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css' rel='stylesheet' type='text/css'>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
<ul class="nav nav-pills bank-head" style="padding-left: 0;">
          <li><a href="<?php echo base_url(); ?>adminuserrole/edit-user-role">Admin Users Role</a></li>
          <li class="active"><a href="<?php echo base_url(); ?>adminuser/user-lists">Admin Users</a></li>
      </ul>

  <div class="tab-content">
    <div id="catalogue-discounts-tab" class="tab-pane fade in active common-tab-section  min-height-480 admin-shop-details-table" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name">Admin Users List</h1>
  		  <div class="float-right">
            <a class="purple-btn delete-all-btn" href="<?= $add_admin_user; ?>">Add New</a>
  		  </div>
      </div>
      <div class="content-main form-dashboard admin-user-details-table new-height">
        <div class="table-responsive text-center">
          <table class="table table-bordered table-style" id="adminUserLists">
            <thead>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Username</th>
                <th>Usertype</th>
                <th>Status </th>
                <th>Action </th>
              </tr>
            </thead>
            <tbody>

            </tbody>
          </table>
        </div>
			</div><!--end form-->
    </div>
	</div>
</main>
<script src="<?php echo SKIN_JS; ?>adminusers.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script src="<?php echo SKIN_JS; ?>adminusers_list.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>
