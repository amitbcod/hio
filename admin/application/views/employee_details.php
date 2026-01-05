<?php $this->load->view('common/fbc-user/header'); ?>
<?php  $show_permission = $this->uri->segment(1); ?>
<style>
   .thumb{
   margin: 24px 5px 20px 0;
   width: 150px;
   float: left;
   }
   #blah {
   border: 2px solid;
   display: block;
   background-color: white;
   border-radius: 5px;
   }
</style>
<?php //print_R($user_details->owner_name); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
   <div class="profile-details">
      <div class="row">
         <div class="col-md-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
               <h2>Employee Details</h2>
               <?php	if(isset($emp_personal_details) && isset($emp_details))
                  {
                  ?>
               <div class="float-right">
                  <button class="purple-btn" data-toggle="modal" data-target="#change_email_modal" type="button">Change Email</button>
                  <button class="purple-btn" data-toggle="modal" data-target="#emp_change_pass_modal" type="button">Change Password</button>
               </div>
               <?php	}?>
            </div>
            <form id="employee_details" name="employee_details" role="form" data-toggle="validator" action="<?php echo base_url();?>DashboardController/insert_employee_details">
               <input type="hidden" name="shop_id" id="shop_id" value="<?php echo (isset($user_details) ? $user_details->shop_id : ''  ) ;?>">
               <input type="hidden" name="parent_id" id="parent_id" value="<?php echo (isset($user_details) && isset($user_shop_details) ? $user_shop_details->fbc_user_id : '' ) ;?>">
               <input type="hidden" name="company_name" id="company_name" value="<?php echo (isset($user_details) && isset($user_shop_details) ? $user_shop_details->company_name : ''  ) ;?>">
               <input type="hidden" name="identifier" id="identifier" value="<?php echo (isset($user_details) && isset($user_shop_details) ? url_title($user_shop_details->org_shop_name , "dash", TRUE): ''  ) ;?>">
               <div class="row">
                  <div class="col-sm-6 profile-details-inner">
                     <label>Name<span class="required">*</span> </label></label>
                     <input class="form-control validate-char" type="text" id="emp_name" name="emp_name" value="<?php echo (isset($emp_personal_details) && isset($emp_details) ? $emp_details->owner_name : '') ;?>" placeholder="" required>
                  </div>
                  <!-- col-sm-6 -->
                  <div class="col-sm-6 profile-details-inner">
                     &nbsp;
                  </div>
                  <!-- col-sm-6 -->
                  <div class="col-sm-6 profile-details-inner">
                     <label>Email ID <span class="required">*</span> </label></label>
                     <input class="form-control" type="email" id="emp_email" name="emp_email" value="<?php echo (isset($emp_personal_details) && isset($emp_details) ? $emp_details->email : ''  ) ;?>" placeholder="" required <?php echo (isset($emp_personal_details) && isset($emp_details) ? 'readonly' : '' );?> required>
                  </div>
                  <!-- col-sm-6 -->
                  <div class="col-sm-6 profile-details-inner password-block-edit">
                     <?php	if(!isset($emp_personal_details)){?>
                     <label>Password <span class="required">*</span> </label></label>
                     <input class="form-control" type="password" id="emp_password" name="emp_password" value="" placeholder="" required>
                     <?php	}?>
                  </div>
                  <!-- col-sm-6 -->
                  <div class="col-sm-6 profile-details-inner">
                     <label>Residential Address</label>
                     <textarea  id="emp_address" name="emp_address" class="form-control"><?php echo (isset($emp_personal_details) && isset($emp_details) ? $emp_personal_details->residential_address : ''  ) ;?></textarea>
                  </div>
                  <!-- col-sm-6 -->
                  <div class="col-sm-6 profile-details-inner">
                     <label>Mobile Number<span class="required">*</span> </label></label>
                     <input class="form-control" type="tel"  id="emp_mobile" name="emp_mobile" value="<?php echo (isset($emp_personal_details) && isset($emp_details) ? $emp_details->mobile_no : ''  ) ;?>" placeholder="" required>
                  </div>
                  <!-- col-sm-6 -->
                  <div class="col-sm-6 profile-details-inner role-in-company">
                     <label>Role in company</label>
                     <?php if(empty($this->session->userdata('userPermission')) || in_array('fbc_usermanagement/employee',$this->session->userdata('userPermission'))){ ?>
                     <select name="emp_role" id="emp_role"  class="country form-control">
                        <?php
                           $display_option='';
                           foreach($emp_roles as $key=>$val){
                           if($user_shop_details->shop_flag==1 && $val['role_name']=='Zumbashop India User'){
                           	$display_option='display:block;';
                           }else{
                           	$display_option='display:none;';
                           }
                       ?>
                        <option value="<?php echo $val['id'] ;?>" <?php echo ((isset($emp_personal_details->role_in_company) && $emp_personal_details->role_in_company != null && $val['id'] == $emp_personal_details->role_in_company ) ? 'selected' : '' ); ?>  style="<?php echo ($val['role_name']=='Zumbashop India User')?$display_option:''; ?>"> <?php echo $val['role_name'] ;?></option>
                        <?php	}  ?>
                     </select>
                      <?php }else{
	                     $role_name = $this->EmployeeModel->getSingleRoleNameByID($emp_personal_details->role_in_company);	 ?>
	                    <input class="form-control" readonly type="text"  id="display_rolename" name="" value="<?php echo $role_name->role_name; ?>" placeholder="">
	                     <input type="hidden" name="emp_role" id="emp_role" value="<?php echo $emp_personal_details->role_in_company; ?>">
	                    <?php }  ?>
                  </div>
                  <!-- col-sm-6 -->
               </div>

         </div>
         <!-- col-md-12 -->
         <div class="save-discard-btn">
         <?php
            if(isset($emp_personal_details) && isset($emp_details))
            {

            ?>
         <input type="hidden" name="fbc_user_id" id="fbc_user_id" value="<?php  echo (isset($fbc_user_id) && isset($fbc_user_id) ? $fbc_user_id : '' );?>">
         <input type="hidden" name="action" id="action" value="update">
         <input type="submit" name ="Update" id="Update" value="Save" class="purple-btn">
         <?php
            }
            else if(isset($user_details))
            {

            ?>
         <input type="hidden" name="action" id="action" value="insert">
         <input type="submit" name ="insert" id="insert" value="Add" class="purple-btn">
         <?php
            }
            ?>
         </div>
         </form>
         <?php if($show_permission!='employee_details' ) {?>
         <div class="col-md-12 no-space-padding">
            <div class="row">
               <ul class="nav nav-pills bank-head" style="padding-left: 0;">
                  <?php if(empty($this->session->userdata('userPermission')) || in_array('fbc_usermanagement/employee',$this->session->userdata('userPermission'))){ ?>
                  <li class="active"><a href="">Employee</a></li>
                  <?php } ?>
                  <?php if(empty($this->session->userdata('userPermission')) || in_array('fbc_usermanagement/employee_role',$this->session->userdata('userPermission'))){ ?>
                  <li><a href="<?php echo base_url()?>employee_role">Employee Role</a></li>
                  <?php } ?>
               </ul>
            </div>
            <?php if(empty($this->session->userdata('userPermission')) || in_array('fbc_usermanagement/employee',$this->session->userdata('userPermission'))){ ?>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
               <h2 class="bank-head">Employee Details </h2>
               <a href="<?php echo base_url()?>employee_details">
               <button class="border-button">Create New</button></a>
            </div>
            <!-- d-flex -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
               <input type="hidden" id="base" value="<?php echo base_url(); ?>">
               <label>
                  Show
                  <select id="page_length" name="page_length">
                     <option value="6">6</option>
                     <option value="10">10</option>
                     <option value="15">15</option>
                  </select>
               </label>
               <div class="float-right product-filter-div">
                  <div class="search-div" style="margin-right:0;">
                     <input class="form-control form-control-dark top-search" name="emp_search" id="emp_search" type="text" placeholder="Search" aria-label="Search">
                     <button type="button" class="btn btn-sm search-icon"><i class="fas fa-search"></i></button>
                  </div>
               </div>
            </div>
            <!-- d-flex -->
            <div class="table-responsive text-center">
               <table class="table table-bordered table-style" id="employee_details_table" name="employee_details_table">
                  <thead>
                     <tr>
                        <th>Employee Name </th>
                        <th>Login ID </th>
                        <th>Role</th>
                        <th>Details </th>
                        <th>Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php		if(!empty($shop_employees))
                        {
                        	foreach($shop_employees as $key=>$val)
                        	{ ?>
                     <tr>
                        <td><?php echo $val['owner_name']?></td>
                        <td><?php echo $val['email']?></td>
                        <td><?php echo $val['role_name']?></td>
                        <td><a class="link-red" href="<?php echo base_url();?>employee_details/<?php echo  $val['fbc_user_id'] ?>">View</a></td>
                        <?php	if($val['status'] == 1)
                           { ?>
                        <td><button type="button" id="employee_status_update" name="employee_status_update" class="btn btn-danger" onclick="change_employee_status('D',<?php echo $val['fbc_user_id'] ;?>)">Disable</button></td>
                        <?php }else  if($val['status'] == 2)
                           { ?>
                        <td><button type="button" id="employee_status_update" name="employee_status_update" class="btn btn-success" onclick="change_employee_status('E',<?php echo $val['fbc_user_id'] ;?>)">Enable</button></td>
                        <?php } ?>
                     </tr>
                     <?php
                        }
                        }
                        ?>
                  </tbody>
               </table>
            </div>
            <?php } ?>
         </div>
         <?php } ?>
         <div class="modal fade show" tabindex="-1" id="emp_change_pass_modal" name="change_pass_modal" role="dialog">
            <div class="modal-dialog change-pass-modal" role="document">
               <div class="modal-content">
                  <div class="modal-header">
                     <h4 class="head-name">Password Change</h4>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                     </button>
                  </div>
                  <div class="modal-body">
                     <div class="row">
                        <form id="emp_change_password" name="change_password" method="post"  data-toggle="validator" action="<?php echo base_url();?>DashboardController/emp_change_password/<?php echo (isset($emp_personal_details) && isset($emp_details) ? $emp_details->fbc_user_id : '' );?>">
                           <?php
                              if(isset($_SESSION['LoginID']) && isset($emp_details->fbc_user_id) && $this->session->userdata('LoginID') == $emp_details->fbc_user_id){
                              ?>
                           <div class="form-group row col-sm-12">
                              <input type="hidden" name="fbc_user_id" id="fbc_user_id" value="<?php  echo (isset($fbc_user_id) && isset($fbc_user_id) ? $fbc_user_id : '' );?>">
                              <label for="" class="col-sm-12 col-form-label">Old Password</label>
                              <div class="col-sm-12">
                                 <input type="password" class="form-control" id="old_password" name="old_password" placeholder="" required>
                                 <div class="error-msg"></div>
                              </div>
                           </div>
                           <?php
                              }
                              ?>
                           <div class="form-group row col-sm-12">
                              <label for="" class="col-sm-12 col-form-label">New Password</label>
                              <div class="col-sm-12">
                                 <input type="password" class="form-control" id="new_password" name="new_password" placeholder="" required>
                                 <div class="error-msg"></div>
                              </div>
                           </div>
                           <div class="form-group row col-sm-12">
                              <label for="" class="col-sm-12 col-form-label">Confirm New Password</label>
                              <div class="col-sm-12">
                                 <input type="password" class="form-control" id="con_new_password" name="con_new_password" placeholder="" required>
                                 <div class="error-msg"></div>
                              </div>
                           </div>
                           <div class="modal-footer col-sm-12 ">
                              <button type="submit" class="purple-btn">CONFIRM PASSWORD </button>
                              <!--<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>-->
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal fade show" tabindex="-1" id="change_email_modal" name="change_email_modal" role="dialog">
            <div class="modal-dialog change_email_modal" role="document">
               <div class="modal-content">
                  <div class="modal-header">
                     <h4 class="head-name">Email Change</h4>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                     </button>
                  </div>
                  <div class="modal-body">
                     <div class="row">
                        <form id="change_email" name="change_email" method="post"  data-toggle="validator" action="<?php echo base_url();?>DashboardController/change_email/<?php echo $emp_details->fbc_user_id ;?>">
                           <div class="form-group row col-sm-12">
                              <input type="hidden" name="fbc_user_id" id="fbc_user_id" value="<?php  echo (isset($fbc_user_id) && isset($fbc_user_id) ? $fbc_user_id : '' );?>">
                              <label for="" class="col-sm-12 col-form-label">Current Email</label>
                              <div class="col-sm-12">
                                 <input type="email" class="form-control" id="current_email" name="current_email" placeholder="" required>
                                 <div class="error-msg"></div>
                              </div>
                           </div>
                           <div class="form-group row col-sm-12">
                              <label for="" class="col-sm-12 col-form-label">New Email</label>
                              <div class="col-sm-12">
                                 <input type="email" class="form-control" id="new_email" name="new_email" placeholder="" required>
                                 <div class="error-msg"></div>
                              </div>
                           </div>
                           <div class="modal-footer col-sm-12 ">
                              <button type="submit" class="purple-btn">CHANGE EMAIL </button>
                              <!--<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>-->
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- row -->
   </div>
   <!-- profile-details-block -->
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>employee_details.js"></script>

<?php if($show_permission!='employee_details' ) {?>
	<script type="text/javascript" src="<?php echo SKIN_JS; ?>dashboard.js"></script>
<?php } ?>
<?php $this->load->view('common/fbc-user/footer'); ?>
