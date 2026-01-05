<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<ul class="nav nav-pills">
    <li class="active"><a data-toggle="pill" href="<?= base_url('webshop/newsletter-subscriber') ?>">Employee Role </a></li>
    <?php if(empty($this->session->userdata('userPermission')) || in_array('fbc_usermanagement/employee',$this->session->userdata('userPermission'))){ ?>
    <li class=""><a href="<?= base_url('dashboard') ?>">Employee</a></li>     
    <?php } ?>       
    
  </ul>

  <div class="tab-content">
    <div id="shipping-charges" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
		
	<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Employee Role </h1>
          <div class="float-right">
            <a class="btn purple-btn" href="<?php echo'dashboard' ?>">Back</a>
            <a class="btn purple-btn" href="<?php echo'add-employee-role' ?>">Create New</a>
          </div> 
        </div>

		
        <!-- form -->
        <div class="content-main form-dashboard">
            <form>

              <div class="table-responsive text-center">
                <table class="table table-bordered table-style" id="newsletter_subscriber_table">
                  <thead>
                    <tr>
                      <th>Role ID </th>
                      <th>Role Name </th>
                      <th>Resource Aaccess</th>
                      <th>View </th>
                      <th>Delete </th>
                    </tr>
                  </thead>
                  <tbody>                    
                    <?php if(isset($empRole) && $empRole!='')
                    {
                         foreach ($empRole as  $value) {
                          ?>
                                <tr>
                                  <td><?php echo $value['id'];?></td>
                                  <td><?php echo $value['role_name'];?></td>
                                  <td><?php if($value['resource_access'] ==1){ echo "Custom";}else if($value['resource_access'] ==0){ echo "All";}?></td>
                                  <td><a class="link-red" href="<?php echo'employee_role'.'/'.$value['id'] ?>">View</a></td>
                                  <td><a class="link-red delete" href="javascript:void(0)" onclick="deleteRole('<?php echo $value['id']?>')">Delete</a></td>  

                                  
                                </tr>
                       
                          <?php }
                    }?>
                  </tbody>
                </table>
              </div>

            </form>
        </div>
        <!--end form-->
    </div>

  </div>
        

    </main>
 <script src="<?php echo SKIN_JS; ?>employee_role.js"></script>

<script type="text/javascript">
$(document).ready(function(){
	 $('#newsletter_subscriber_table').DataTable(
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

  	 if ($('#newsletter_subscriber_table tr').length < 10) {
  
            $('.dataTables_paginate').hide();
        }

 });

</script>
<?php $this->load->view('common/fbc-user/footer'); ?>