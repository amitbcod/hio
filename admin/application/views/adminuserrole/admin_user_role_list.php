
<?php $this->load->view('common/fbc-user/header'); ?>
<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<link href='//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css' rel='stylesheet' type='text/css'>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
<ul class="nav nav-pills bank-head" style="padding-left: 0;">
      <li class="active"><a href="<?php echo base_url(); ?>adminuserrole/edit-user-role"">Admin Users Role</a></li>
          <li><a href="<?php echo base_url(); ?>adminuser/user-lists">Admin Users</a></li>
      </ul>
  <div class="tab-content">
    <div id="catalogue-discounts-tab" class="tab-pane fade in active common-tab-section  min-height-480 admin-shop-details-table" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name"><?= $pageTitle; ?></h1>
  		  <div class="float-right">
            <a class="purple-btn delete-all-btn" href="<?= $add_admin_user_role; ?>">Create New</a>
  		  </div>
      </div>
      <div class="table-responsive text-center">
                <table class="table table-bordered table-style" id="admin_user_role_table" name="admin_user_role_table">
                  <thead>
                    <tr>
                      <th>Role ID </th>
                      <th>Role Name </th>
                      <th>Resource Access</th>
					  <th>View </th>	
					  <th>Delete</th>	
                    </tr>
                  </thead>
                  <tbody>
                    
					
<?php	
if(is_array($userdetails) && !empty($userdetails))	
					{
						foreach($userdetails as $key=>$val)
						{
	
?>
							<tr>
							  <td><?php echo $val['id']; ?></td>
							  <td><?php echo $val['role_name']?></td>
							  <td><?php echo ($val['resource_access'] == 0) ? 'All' : 'Custom'?></td>
							  <td><a class="link-red" href="<?php echo base_url();?>adminuserrole/add-admin-user-role/<?php echo  $val['id'] ?>">View</a></td> 
                <td><a class="link-red" href="" onclick="deleteAdminUserRole('<?php echo $val['id']?>')">Delete</a></td>
					
<?php				
						}
					}
?>
					  
                    
                  </tbody>
                </table>
              </div>
    </div>
	</div>
</main>
<script src="<?php echo SKIN_JS; ?>admin_user_role.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>

<script type="text/javascript">
$(document).ready(function(){
	 $('#admin_user_role_table').DataTable(
        {
          "info" : false,
          "lengthMenu": [[50, -1], [50, "All"]],
          "order": [[ 2, "desc" ]],
          "language": {                
          "paginate": {
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'  
                       },
          "search": ""

         },
        } );

  	 if ($('#admin_user_role_table tr').length < 10) {
  
            $('.dataTables_paginate').hide();
        }

 });

</script>
