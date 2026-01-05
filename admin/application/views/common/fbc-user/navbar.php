
 <?php
 $ci = get_instance();
 $id	=	$this->session->userdata('LoginID');
 $FBCData=$this->CommonModel->getSingleDataByID('adminusers',array('id'=>$id),'email,id');
 
 ?>
 <nav class="navbar sticky-top flex-md-nowrap p-0">

  <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="<?php echo base_url();?>dashboard"><img src="<?php echo base_url();?>public/images/holidays-io-logo.png" width="100px"></a>
  <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"><i class="fa fa-bars"></i></span>
  </button>
  
  <div class="d-flex top-right right-nav-top">
    
    <div class="left-line pro-info">
    <a href="<?php echo base_url();?>adminuser/edit_user/<?php echo $_SESSION["LoginID"];?>"> <div class="d-flex align-items-center"><span class="pro-name"><?php echo $FBCData->email;?></span><i class="fa fa-user"></i> </div></a>
    </div>

 
	
	


	<div class="left-line bell-right">
        <a href="<?php echo base_url(); ?>logout" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </div>
</nav>
<div class="ajax-spinner" id="ajax-spinner"><div class="ajax-spinner-inner"></div></div>
