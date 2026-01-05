<?php 
	$this->load->view('common/fbc-user/header'); 
	$fbc_admin_support_email=$this->CommonModel->getGlobalVariableByIdentifier('fbc-admin-support-email');
	$fbc_admin_email='';
	if(isset($fbc_admin_support_email->value)){ $fbc_admin_email=$fbc_admin_support_email->value;}
?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<div class="tab-content"  >
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <p class="head-name"><span class="huge-name">You don't have access to Invoicing module, please contact <a href="mailto: <?=$fbc_admin_email?>"><?=$fbc_admin_email?></a>.</span></p> 
        </div>
    </div>
		
	</div>
</main>
		
  
<?php $this->load->view('common/fbc-user/footer'); ?>