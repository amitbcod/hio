<?php if($change == 'email') { ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
    </head>
    <body>
        <h2><?php echo $PageTitle ?></h2>
        <form name="coupon-code-frm-add" id="change-email" method="POST" action="">
        <div class="customize-add-section">
            <div class="row">
                <div class="left-form-sec coupon-code-select-product-list">
                    <?php if($_SESSION['from_user'] == 'admin_user') { ?>
                        <input type="hidden" name="user_id" id="user_id" value="<?php echo isset($user_id) ? $user_id : '' ?>" />
                    <?php } else { ?>
                        <input type="hidden" name="user_id" id="user_id" value="<?php echo isset($admin_user_id) ? $admin_user_id : '' ?>" />
                    <?php } ?>
                    <div class="col-sm-6 customize-add-inner-sec">
                        <label>Current Email <span class="required">*</span></label> 
                        <input class="form-control" type="email" name="current_email" value="" placeholder="Enter Current Email">
                    </div>
                    
                    <div class="col-sm-6 customize-add-inner-sec">
                        <label>New Email <span class="required">*</span></label> 
                        <input class="form-control" type="email" name="new_email" value="" placeholder="Enter New Email">
                    </div>
                </div>
            </div>
            <div class="download-discard-small mar-top">
                <button class="download-btn" id="change_admin_user_email" type="submit">Save</button>
            </div>
        </div>
        </form>
    </body>
    </html>
<?php } ?>

<?php if($change == 'password') { ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
    </head>
    <body>
        <h2><?php echo $PageTitle ?></h2>
        <form name="coupon-code-frm-add" id="change-password" method="POST" action="">
        <div class="customize-add-section">
            <div class="row">
                <div class="left-form-sec coupon-code-select-product-list">
                <?php if($_SESSION['from_user'] == 'admin_user') { ?>
                        <input type="hidden" name="user_id" id="user_id" value="<?php echo isset($user_id) ? $user_id : '' ?>" />
                    <?php } else { ?>
                        <input type="hidden" name="user_id" id="user_id" value="<?php echo isset($admin_user_id) ? $admin_user_id : '' ?>" />
                    <?php } ?>
                    <div class="col-sm-6 customize-add-inner-sec">
                        <label>Old Password <span class="required">*</span></label> 
                        <input class="form-control" type="password" name="old_password" value="" placeholder="Enter Old Password">
                    </div>
                    
                    <div class="col-sm-6 customize-add-inner-sec">
                        <label>New Password <span class="required">*</span></label> 
                        <input class="form-control" type="password" name="new_password" value="" placeholder="Enter New Password">
                    </div>
                    <div class="col-sm-6 customize-add-inner-sec">
                        <label>Confirm New Password <span class="required">*</span></label> 
                        <input class="form-control" type="password" name="conf_new_password" value="" placeholder="Enter Confirm New Password">
                    </div>
                </div>
            </div>
            <div class="download-discard-small mar-top">
                <button class="download-btn" id="change_admin_user_password" type="submit">Save</button>
            </div>
        </div>
        </form>
    </body>
    </html>
<?php } ?>
<script src="<?php echo SKIN_JS; ?>dashboard.js"></script>