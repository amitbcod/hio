<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="profile-details busniess-details">
        <div class="row">
            <form id="submitShopAccess" method="POST" action="<?= base_url('WebshopController/submitShopAccess') ?>">
                <div class="col-md-12">
                    <h2>Webshop  Details</h2>
                    <div class="row">
                       
                        <!-- col-sm-6 -->
                
                        <!-- <div class="col-sm-6 profile-details-inner">
                            <label>Allow Access to Admin</label>
                            <div class="switch-onoff">
                            <label class="checkbox">
                            <input type="checkbox" name="accessAdmin" id="accessAdmin" autocomplete="off"> 
                                <span class="checked"></span>
                            </label>
                            </div>
                        </div> --><!-- col-sm-6 -->
                    
                        <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec">

                            
                            <!-- profile-inside-box -->
                            
                            <div class="profile-inside-box">
                                <label>Make Website Live</label>
                                <div class="switch-onoff">
                                <label class="checkbox">
                                <?php 
                                $websiteLive = 'checked';
                                if($shopData->website_live == 0){
                                    $websiteLive = '';
                                }
                                ?>
                                <input type="hidden"  name="live_check" id="live_check" value="<?php echo $shopData->website_live; ?>">
                                <input type="checkbox" name="websiteLive" id="websiteLive" autocomplete="off" <?= $websiteLive?>> 
                                    <span class="checked"></span>
                                </label>
                                </div>
                            </div><!-- profile-inside-box -->
                               
                            <div class="profile-inside-box">
                                <p>&nbsp;</p>
                            </div><!-- profile-inside-box -->
                            <div class="profile-inside-box">
                                <label>Put Website on Test Mode</label>
                                <div class="switch-onoff">
                                <label class="checkbox">
                                <?php 
                                $enable_test_mode = '';
                                $display_ips = 'style="display: none;"';
                                $required='';
                                if($shopData->enable_test_mode == 1){
                                    $enable_test_mode = 'checked';
                                    $display_ips = '';
                                    $required='required';
                                }
                                ?>
                                 <input type="hidden"  name="test_check" id="test_check" value="<?php echo $shopData->enable_test_mode; ?>">
                                <input type="checkbox" name="test_mode" id="test_mode" autocomplete="off" <?= $enable_test_mode?>> 
                                    <span class="checked"></span>
                                </label>
                                </div>
                            </div>

                            <div class="profile-inside-box ip_addresses_sec" <?= $display_ips ?>>
                                <label for="ip_addresses">Enter Ip addresses from which you are going to access the website</label>
                                <input class="form-control" type="text" name="ip_addresses"  id="ip_addresses" value="<?php echo $shopData->test_mode_access_ips; ?>" <?php // $required; ?> placeholder="" ><span class="your_ip">Your Ip Address is:<?php echo $_SERVER['REMOTE_ADDR']; ?></span><br><br>
                                <span class="note">More than One IP address, then separate them with comma.</span>
                            </div>
                        </div><!-- col-sm-6 -->
                        
                        <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec">
                         
                            
                            <div class="profile-inside-box">
                                <label for="domainName">Website Domain Name</label>
                                <div>
                                    <input class="form-control" type="text"  name="domainName" id="domainName" value="<?php echo $shopData->website_domain_name; ?>" placeholder="Website Domain name">
                                </div>
                                
                            </div><!-- profile-inside-box -->
                            <div class="profile-inside-box">
                                <label for="domainName">Website Default URL</label>
                                <div>
                                    <?php  $url= base_url(); ?>
                                    <input class="form-control" type="text" readonly="readonly" name="default_url" id="default_url" value="<?php echo $shopData->website_default_url; ?>" placeholder="Website Default Url">
                                </div>
                                
                            </div><!-- profile-inside-box -->
                        
                            
                        </div><!-- col-sm-6 -->

                    </div>
              
                    <div class="save-discard-btn">
                        <input type="submit" value="Save" class="purple-btn">
                    </div>
              
                </div><!-- row -->
            </form>
        </div><!-- profile-details-block -->
    </div>
</main>
<script src="<?php echo SKIN_JS; ?>webshop.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>