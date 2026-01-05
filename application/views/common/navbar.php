
<?php
if ($this->session->userdata('LoginID')) {
  $_sis_session_id = $this->session->userdata('LoginToken');
  $this->session->set_userdata('sis_session_id', $_sis_session_id);
} else {
  if ($this->session->userdata('sis_session_id')) {
    $_sis_session_id = $this->session->userdata('sis_session_id');
  } else {
    $_sis_session_id = generateToken('50');
    $this->session->set_userdata('sis_session_id', $_sis_session_id);
  }
}

$first_segment = $this->uri->segment(1);
$search_term = '';

?>


<!-- BEGIN TOP BAR -->
<div class="pre-header">
  <div class="container-fluid">
    <div class="row">
      <!-- BEGIN TOP BAR LEFT PART -->
      <div class="col-md-6 col-sm-6 additional-shop-info">
        
      </div>
      <!-- END TOP BAR LEFT PART -->
      <!-- BEGIN TOP BAR MENU -->
      <div class="col-md-6 col-sm-6 additional-nav">
        <?php if ($this->session->userdata('LoginID')) {
          
        ?>
          <div class="site-top-buttons">
            
            <ul class="list-unstyled list-inline pull-right" id="user-section">
              <li><a href="<?php echo base_url() ?>customer/account"><span><!--<img src="<?php echo TEMP_SKIN_IMG; ?>/my-profile-icon.png">--></span>My Profile</a></li>
              
              <li><a href="<?php echo base_url(); ?>customer/logout"><span class="icon icon-sign-out">&nbsp; </span>Logout</a></li>
            </ul>
          </div>
        <?php } else { ?>
          <ul class="list-unstyled list-inline pull-right">
            <li><a href="<?php echo base_url() . 'customer/login' ?>">Log In</a></li>
            <li><a href="<?php echo base_url() . 'customer/register' ?>">Register</a></li>
          </ul>
        <?php } ?>
      </div>
      <!-- END TOP BAR MENU -->
    </div>
  </div>
</div>
<!-- END TOP BAR -->

<!-- BEGIN HEADER -->
<div class="header">
  <div class="container-fluid">
   
    <div class="main-logo">
      <a href="<?php echo base_url(); ?>">
        <!-- class="site-logo" -->
        <img src="<?php echo SITE_LOGO ?>" alt="IndiaMags.com">
        <span><i class="fa fa-phone" aria-hidden="true"></i> + 91 8850533661</span>
      </a>
    </div>

    <a href="javascript:void(0);" class="mobi-toggler"><i class="fa fa-bars"></i></a>

    <div class="search-mini">
      <div class="sear-com">
        <form action="<?= linkUrl('searchresult') ?>" method="GET" class="site-block-top-search-M" autocomplete="off">
          <div class="input-group">
            <input type="text" id="search_M" name="s" placeholder="Search" class="form-control" value="<?php echo urldecode($search_term); ?>">
            <span class="input-group-btn">
              <button class="btn btn-primary submit-search-M" type="submit"><i class="fa fa-search search-btn"></i></button>
            </span>

          </div>
          <div id="livesearch_M"></div>
        </form>
      </div>
      <!-- BEGIN CART -->
      <div id="mini-cart-main-container">
        <?php (new MiniCartList())->render(); ?>
      </div>
    </div>

    <!-- <div class="top-phone">
          <span><i class="fa fa-phone" aria-hidden="true"></i> + 91 8097002217</span>
        </div> -->


    <!--END CART -->

    <!-- BEGIN NAVIGATION -->
    <div class="header-navigation">
      <?php (new TopMenu('top-menu'))->render(); ?>

    </div>
   
    <!-- END NAVIGATION -->
  </div>
</div>



<!-- Header END -->