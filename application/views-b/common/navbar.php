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


<style>
  .track-order-container{
    position: absolute;
    left: -45%;
    top: 5%;
    padding: 0 10px;
    text-transform: capitalize;
    color: #fff;
    font-size: 14px;
    text-decoration: none !important;
    font-weight: 400;
  }
  
</style>

<header class="site-navbar">
  <div class="site-top-bar">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-11 col-md-11 nav-header-bar text-left">
          <nav class="site-navigation text-right text-md-left" role="navigation">
            <?php (new TopMenu('top-menu'))->render(); ?>
          </nav>
        </div>

        <?php
        // $getCurrency = CurrencyRepository::getCurrencyList(SHOPCODE, SHOP_ID);
        // $currencydata = ((isset($getCurrency) && $getCurrency->statusCode=='200') ? $getCurrency->currencydata : '');
        ?>

        <div class="col-1 col-md-1 order-3 order-md-3 text-right">
          <div class="site-top-icons">
            <ul>
              <?php if (isset($languageData) && isset($multi_languages) && $multi_languages == 1) { ?>
                <li class="select-language">
                  <select class="form-control">
                    <?php foreach ($languageData as $llist) {
                      $selected = '';
                      if ($llist->is_default_language == 1) {
                        $selected = 'selected';
                      }

                    ?>
                      <option <?php echo ($this->session->userdata('lcode') == $llist->code ? 'selected' : $selected) ?> value="<?php echo $llist->id ?>"><?php echo $llist->display_name ?></option>
                    <?php } ?>
                  </select>
                </li>
              <?php } ?>

              <?php if (isset($currencydata) && $this->session->userdata('multi_currency_flag')  == 1) { ?>
                <li class="select-currency">
                  <select class="form-control">
                    <?php foreach ($currencydata as $list) { ?>
                      <option <?php echo ($this->session->userdata('currency_code_session') == $list->code ? 'selected' : '') ?> value="<?php echo $list->id ?>"><?php echo $list->code . " " . $list->symbol . " - " . $list->name ?></option>
                    <?php } ?>
                  </select>
                </li>
              <?php } ?>
              <li class="track-order-container" id="">
              <a href="<?php  echo base_url('order-tracking') ?>">Track Order</a>
              </li>
              <li id="mini-cart-main-container">
                <?php (new MiniCartList())->render(); ?>
              </li>
              <li class="d-inline-block d-md-none ml-md-0"><a href="#" class="site-menu-toggle js-menu-toggle"><span class="icon-menu"></span></a></li>
            </ul>
          </div> <!-- site-top-icons -->
        </div>

      </div>
    </div>
  </div>
  <?php
  // $this->load->library('encryption');
  // $key= $this->config->item('encryption_key');
  // $this->encryption->initialize(array('driver' => 'mcrypt'));

  // $shopcode = SHOPCODE;
  // $shop_id = SHOP_ID;
  $data['webshop_details'] = CommonRepository::get_webshop_details();
  if (isset($data['webshop_details']) && $data['webshop_details']->is_success == 'true') {
    $site_logo = $this->encryption->decrypt($data['webshop_details']->FbcWebShopDetails->site_logo);
  }
  ?>
  <div class="site-navbar-top">
    <div class="container">
      <div class="row align-items-center">

        <div class="col-6 col-md-2 order-1 order-md-1 text-left">
          <div class="site-logo">

            <a href="<?php echo base_url(); ?>" class="js-logo-clone"><img src="<?php echo SITE_LOGO; ?> "></a>

          </div>
        </div>

        <div class="col-12 mb-md-0 col-md-7 order-2 order-md-1 site-search-icon text-center">
          <form action="<?= linkUrl('searchresult') ?>" method="GET" class="site-block-top-search">
            <input type="text" id="search" name="s" class="form-control rounded-0" placeholder="Search" value="<?php echo $search_term; ?>">
            <button type="submit"><span class="icon icon-search2"></span></button>
            <div id="livesearch"></div>
          </form>
        </div>

        <div class="col-6 col-md-3 order-3 order-md-3 text-right">
          <?php if ($this->session->userdata('LoginID')) {
            //   $identifier='restricted_access';
            //   $ApiResponse= GlobalRepository::get_custom_variable($identifier);
            //   print_r($ApiResponse);exit;

            //   if ($ApiResponse->statusCode=='200') {
            //       $RowCV=$ApiResponse->custom_variable;
            //       $restricted_access=$RowCV->value;
            // //  } //else {
            //       $restricted_access='no';
            // } 
          ?>

            <div class="site-top-buttons">
              <div class="user-block"> <a>Hello <span class="username"><?php echo $_SESSION['FirstName'] . ' ' . $_SESSION['LastName']; ?></span> <span class="icon icon-arrow_drop_down" data-toggle="collapse" href="#user-section" role="button" aria-expanded="false" aria-controls="user-section"></span></a> </div>
              <ul class="collapse user-block-inner " id="user-section">
                <li><a href="<?php echo base_url() ?>customer/account"><span><img src="<?php echo TEMP_SKIN_IMG; ?>/my-profile-icon.png"></span>My Profile</a></li>
                <li><a href="<?php echo base_url() ?>customer/my-orders"><span><img src="<?php echo TEMP_SKIN_IMG; ?>/order-icon.png"></span> Orders</a></li>
                <li><a href="<?php echo base_url() ?>customer/wishlist"><span><img src="<?php echo TEMP_SKIN_IMG; ?>/heart-icon.png"></span> Wishlist</a></li>
                <li><a href="<?php echo base_url(); ?>customer/logout"><span class="icon icon-sign-out">&nbsp; </span>Logout</a></li>
              </ul>
            </div>
          <?php } else { ?>

            <div class="site-top-buttons">
              <a href="<?php echo base_url() . 'customer/login' ?>"><button type="button" class="btn btn-blue">Login</button></a>
              <?php
              $identifier = 'restricted_access';
              // $ApiResponse= GlobalRepository::get_custom_variable($shopcode, $shop_id, $identifier);

              // if ($ApiResponse->statusCode=='200') {
              //     $RowCV=$ApiResponse->custom_variable;
              //     $restricted_access=$RowCV->value;
              // } else {
              //     $restricted_access='no';
              // }


              // if ($restricted_access == 'no') { 
              ?>
              <a href="<?php echo base_url() . 'customer/register' ?>"><button type="button" class="btn btn-dark">Register</button></a>
              <?php //} else { 
              ?>
              <!-- <a><button type="button" onclick="openRestrictedAccessPopup()" class="btn btn-dark"><?php echo lang('register'); ?></button></a> -->
              <?php //} 
              ?>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</header><!-- site-navbar -->
<script src="<?php echo SKIN_JS ?>navbar.js"></script>