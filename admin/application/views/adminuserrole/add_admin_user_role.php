<?php $this->load->view('common/fbc-user/header'); ?>
<?php
    $checkedArray=array();
	if(isset($roleId)){
      $getAllData = $this->AdminuserroleModel->child_data_by_id($roleId);
      foreach ($getAllData as $value) {

         $checkedArray[] = $value['resource_id'];

         }
	}
?>
    <main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <ul class="nav nav-pills bank-head" style="padding-left: 0;">
      <li class="active"><a href="<?php echo base_url(); ?>adminuserrole/edit-user-role"">Admin Users Role</a></li>
          <li><a href="<?php echo base_url(); ?>adminuser/user-lists">Admin Users</a></li>
      </ul>
  <div class="tab-content">
    <div id="Special Pricing" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">

        <!-- form -->
        <form method="POST" id="">
			<div class="customize-add-section pad-t-20">
				<div class="row">
				<div class="left-form-sec">

					<div class="col-sm-6 customize-add-inner-sec">
						<label >Role Name<span class="text-danger"> *</span></label>
						<input type="text" class="form-control" placeholder="Role Name" name="role_name" value="<?php echo (isset($singleRole)) ? $singleRole->role_name : ''; ?>" id="role_name" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" required>
					</div><!-- col-sm-6 -->

                    <div class="col-sm-6 customize-add-inner-sec">
						<label>Resource Access<span class="text-danger"> *</span></label>
						<select id="resource_access" onchange="changeValue()" name="resource_access" value="" class="form-control" required>
							<option value="">Select Resource Access </option>
            					<option value="0"  <?php echo (isset($singleRole) && !empty($singleRole) && $singleRole->resource_access =='0') ? 'selected' : ''; ?>>All</option>
            					<option value="1" <?php echo (isset($singleRole) && !empty($singleRole) && $singleRole->resource_access =='1') ? 'selected' : ''; ?>>Custom</option>
            			</select>
					</div>
				</div>
				</div><!-- row -->
			</div><!-- customize-add-section -->

	  <div class="content-main form-dashboard" id="hidediv">
   <div class="webshop-static-edit-block ">
      <div class="row">
      	<?php foreach($parentData as $pid) {
              if(!empty($pid)) {
        ?>
         <div class="col-sm-4 customize-add-inner-sec page-content-textarea-small custom-wise-menu border-right-new">
            <div class="col-sm-12" id="category-tree">
               <div class="accordion custom-accordion " id="custom-accordion-one">
                  <ul class="common-list list-gc">
                     <li class="list-gc-item ">
                        <div class="custom-control custom-checkbox"><label class="checkbox"><input type="checkbox" id="chk_sidebar[]"] name="main_role_<?php echo $pid['id'];?>" class="form-control" <?php echo (in_array($pid['id'], $checkedArray)) ? 'checked' : ''; ?> value="<?php echo $pid['id'];?>" ><span class="checked"></span><span class="sis-cat-name"><strong><?php echo $pid['resource_name'];?></strong></span></label></div>
                      <?php $all_child=$this->AdminuserroleModel->get_child_data_l1($pid['id']);
                       foreach($all_child as $data){
                       	if($data['parent_id'] == $pid['id']){?>
                        <div id="subCatOuter_99" class=""  style="">
                           <ul class="common-list list-gc1">
                              <li class="list-gc-item ">
                                 <div class="custom-control custom-checkbox"><label class="checkbox"><input id="chk_sidebar[]"] type="checkbox"  name="sub_role_<?php echo $data['id'];?>" <?php echo (in_array($data['id'], $checkedArray)) ? 'checked' : ''; ?>  class="form-control" value="<?php echo $data['id'];?>"><span class="checked"></span><span class="sis-cat-name"><?php echo $data['resource_name'];?> </span></label></div>

                                 <?php $second_level_child=$this->AdminuserroleModel->get_child_data_l2($data['id']);
                                 if (is_array($second_level_child) || is_object($second_level_child)) {
                                 foreach ($second_level_child as $level2) {
                                                if(count($level2) > 0) {
    											if($level2['parent_id'] == $data['id']){ ?>
		                                 <div id="subCatOuter_99" class=""  style="">
					                           <ul class="common-list list-gc1">
					                              <li class="list-gc-item ">
					                                 <div class="custom-control custom-checkbox"><label class="checkbox"><input id="chk_sidebar[]"] type="checkbox" <?php echo (in_array($level2['id'], $checkedArray)) ? 'checked' : ''; ?>  name="level2_<?php echo $level2['id'];?>"  class="form-control" value="<?php echo $level2['id'];?>"><span class="checked"></span><span class="sis-cat-name"><?php echo $level2['resource_name'];?> </span></label></div>
					                              </li>
					                           </ul>
					                        </div>
					                     <?php }}}} ?>

                              </li>
                           </ul>
                        </div>
                    <?php } }?>
                     </li>
                  </ul>
               </div>
            </div>
            <div class="clear pad-bt-40"></div>
         </div>
     <?php }} ?>
      </div>
      <!-- row -->
   </div>
   <!-- customize-edit -->
</div>
				<div class="download-discard-small mar-top">
                    <input type="hidden" name="roleId" id="roleId" value="<?php echo (isset($singleRole)) ? $singleRole->id : ''; ?>">
          <a href="<?php echo base_url()?>adminuserrole/edit-user-role" class="btn white-btn">Discard</a>
					<button class="download-btn" name="submit" id="roleSubmit" value="save" >Save</button>

			 	</div><!-- download-discard-small  -->
		</form>
        <!--end form-->
    </div>



  </div>



    </main>
 <script src="<?php echo SKIN_JS; ?>admin_user_role.js"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>
