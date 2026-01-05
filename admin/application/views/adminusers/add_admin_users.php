<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

  <div class="tab-content">
    <div id="catalogue-discounts-details-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name pad-bt-20">Admin User Details</h1>
        <div class="float-right">
          <?php  if ($_SESSION["LoginID"]) { ?>
				  <!-- <button class="purple-btn" data-toggle="modal" data-target="#change_email_modal" type="button">Change Email</button> -->
          <button class="purple-btn" type="button" onclick="OpenEmailChangePopup();">Change Email</button>
				  <!-- <button class="purple-btn" data-toggle="modal" data-target="#change_pass_modal" type="button">Change Password</button> -->
          <button class="purple-btn" type="button" onclick="OpenPasswordChangePopup();">Change Password</button>
          <?php } ?>
			</div>
      </div><!-- d-flex -->

		  <!-- form -->
      <form name="coupon-code-frm-add" id="user-add" method="POST" action="">

        <?php //echo "<pre>";print_r($details);die();  ?>
  			<div class="customize-add-section">
  				<div class="row">
    				<div class="left-form-sec coupon-code-select-product-list">
              <input type="hidden" name="user_id" id="user_id" value="<?php echo isset($details['id']) ? $details['id'] : '' ?>" />
    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>First Name <span class="required">*</span></label>
    						<input class="form-control" type="text" name="first_name" value="<?php echo isset($details['first_name']) ? $details['first_name'] : '' ?>" placeholder="Enter First name" onkeypress="return /[0-9a-zA-Z]/i.test(event.key)">
    					</div>

                <?php if (is_array($details) || is_object($details)) { ?>
                  <div class="col-sm-6 customize-add-inner-sec">
                  <label>Email</label>
    						<input class="form-control" type="email" name="email" value="<?php echo isset($details['email']) ? $details['email'] : '' ?>" placeholder="Enter Email" readonly onkeypress="return /[0-9a-zA-Z]/i.test(event.key)">
                </div>

              <?php } else { ?>

                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Email</label>
    						<input class="form-control" type="email" name="email" value="" placeholder="Enter Email" onkeypress="return /[0-9a-zA-Z]/i.test(event.key)">
                </div>

              <?php } ?>

              <?php if(isset($details) && !empty($details)) { ?>

                <?php } else { ?>
                  <div class="col-sm-6 customize-add-inner-sec">
                  <label>Password <span class="required">*</span></label>
                  <input class="form-control" type="password" name="password" value="" placeholder="Enter Password">
                </div>
                <?php } ?>

                <?php if (is_array($details) || is_object($details)) { ?>
                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Usertype</label>
			            <select name="usertype" class="form-control" id="usertype">
    			          <option value="">Select RoleType</option>
				            <?php if(isset($roleType) && !empty($roleType)){
					          foreach($roleType as $role):?>
                      <option value="0" <?php echo ($details['role_id'] == 0) ? 'selected' : '' ?>>Super Admin</option>
                      <option value="<?php echo $role['id']?>" <?php echo ($details['role_id'] == $role['id']) ? 'selected' : '' ?>><?php echo $role['role_name']?></option>
				          <?php endforeach; }?>
  			        </select>
			        </div>

              <?php } else { ?>

                <div class="col-sm-6 customize-add-inner-sec">
                  <label>Usertype</label>
			            <select name="usertype" class="form-control" id="usertype">
    			          <option value="">Select RoleType</option>
                      <option value="0">Super Admin</option>
				            <?php if(isset($roleType) && !empty($roleType)){
					          foreach($roleType as $role):?>
                      <option value="<?php echo $role['id']?>"><?php echo $role['role_name']?></option>
				          <?php endforeach; }?>
  			        </select>
			        </div>

              <?php } ?>

                </div>
                <div class="right-form-sec coupon-code-select-product-list">
                <div class="col-sm-6 customize-add-inner-sec">
    						<label>Last Name <span class="required">*</span></label>
    						<input class="form-control" type="text" name="last_name" value="<?php echo isset($details['last_name']) ? $details['last_name'] : '' ?>" placeholder="Enter last name" onkeypress="return /[0-9a-zA-Z]/i.test(event.key)">
    					</div>
                        <div class="col-sm-6 customize-add-inner-sec">
    						<label>Username <span class="required">*</span></label>
    						<input class="form-control" type="text" name="username" value="<?php echo isset($details['username']) ? $details['username'] : '' ?>" placeholder="Enter Username" onkeypress="return /[0-9a-zA-Z]/i.test(event.key)">
    					</div>
              <div class="col-sm-6 customize-add-inner-sec">
                    <label>Status</label>
                    <div class="radio">
                        <label style="width: 138px !important; "><input type="radio" name="cp_radio" <?php echo (isset($details['status']) && $details['status']=='0')?'checked':''; ?> value="0">Enable <span class="checkmark"></span></label>
                    </div><!-- radio -->
                    <div class="radio">
                        <label style="width: 150px !important; "><input type="radio" name="cp_radio"  <?php echo (isset($details['status']) && $details['status']=='1')?'checked':''; ?> value="1">Disable <span class="checkmark"></span></label>
                    </div><!-- radio -->
                </div>
                </div>
                <div class="download-discard-small mar-top">
                      <button class="download-btn" id="save_user" type="submit">Save</button>
                </div>
            </div>
</form>


<script src="<?php echo SKIN_JS; ?>adminusers.js"></script>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>dashboard.js"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>
