<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
    	<!-- <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->
    	<li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>
    	<li><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>
		<li ><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>
		<li class="active"><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>
		<li><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
		<li class=""><a href="<?= base_url('webshop/promo-text-banners') ?>">Promo Text Banners</a></li>
		
  	</ul>
	<div class="tab-content">
    <div id="payment-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name"><i class="fas  fa-angle-left"></i> &nbsp; Payments </h1> 
        </div><!-- d-flex -->
		
		<div class="row">
			<div class="col-md-12 select-country-dropdown">
				<select>
					<option value="Mauritius" selected>Mauritius</option>
					
				</select>
			</div>
		</div>
		
		<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 ">
          <label>Show <select name="page_length" id="page_length">
						<option value="10">10</option>
						<option value="20">20</option>
						<option value="30">30</option>

						
					</select>
		  
		  </label>
		  
		  <div class="float-right product-filter-div inner-search">
			  <div class="search-div">
				  <input class="form-control form-control-dark top-search" type="text" id="gateway_search" name="gateway_search"  placeholder="Search or Scan barcode" aria-label="Search">
				  <button type="button" class="btn btn-sm search-icon"><i class="fas fa-search"></i></button>
			 </div>
			</div>
        </div><!-- d-flex -->
		
        <!-- form -->
        <div class="content-main form-dashboard">
            
			<div class="table-responsive text-center">
                <table class="table table-bordered table-style" id="webshop_gateway_details" name="webshop_gateway_details">
                  <thead>
                    <tr>
                      <th>Payment Gateway <!--<i class="float-right fa fa-fw fa-sort">--></i></th>
                      <th>Payment Type  <!--<i class="float-right fa fa-fw fa-sort"></i>--></th>
                      <th class="no-sort">Status  </th>
                      <th class="no-sort">Integrate with Website </th>
					  <th class="no-sort">Details </th>
                    </tr>
                  </thead>
                  <tbody>
                   
<?php     
					foreach($get_payment_details as $key_payment=>$val_payment)
					{
						$payment_type =  '' ;
						if($val_payment['payment_type'] == 1)
						{
							$payment_type = 'Direct Payment';
						}
						else if($val_payment['payment_type'] == 2)
						{
							$payment_type = 'Split Payment';
						}
	
?>
					<tr>
                      <td><?php echo $val_payment['payment_gateway'] ;?></td>
                      <td><?php echo $payment_type ;?></td>
                      <td><?php echo ($val_payment['status'] == 0) ? 'Details Incomplete' : 'Details Completed';?></td>

           <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>           
					  <td>
					  	<?php if($val_payment['id']!=7){ ?>
						   <div class="switch-onoff">
								<label class="checkbox">
									<?php 
										$stripeStatus='0';
										if($val_payment['id']==6){
											//$webshopPaymentsStripe=$this->CommonModel->getSingleShopDataByID('webshop_payments_stripe',array('payment_id'=>$val_payment['id']),'status');
											if(isset($webshopPaymentsStripe) && $webshopPaymentsStripe->status){
												$stripeStatus=$webshopPaymentsStripe->status;
											}
											if($stripeStatus!=2){
									?>
										<input type="checkbox"  name="integrate_with_us" id="integrate_with_us"  autocomplete="off" disabled> 
									<span class="checked"></span>
									
									<?php

											}else{
									?>
									<input type="checkbox"  name="integrate_with_us" id="integrate_with_us" value="<?php echo $val_payment['integrate_with_ws']; ?>" data-id="<?php echo ($val_payment['id']) ;?>" autocomplete="off" <?php echo ($val_payment['status'] == 0) ? 'disabled' : '';?>
									<?php echo ($val_payment['integrate_with_ws'] == 0 || $val_payment['integrate_with_ws'] == NULL ) ? '' : 'checked' ;?>
									 onchange="paymentOnOff(<?php echo $val_payment['id']; ?>,this.value)"> 
									<span class="checked"></span>
								    <?php } }else{ ?>
								    	<input type="checkbox"  name="integrate_with_us" id="integrate_with_us" value="<?php echo $val_payment['integrate_with_ws']; ?>" data-id="<?php echo ($val_payment['id']) ;?>" autocomplete="off" <?php echo ($val_payment['status'] == 0) ? 'disabled' : '';?>
									<?php echo ($val_payment['integrate_with_ws'] == 0 || $val_payment['integrate_with_ws'] == NULL ) ? '' : 'checked' ;?>
									 onchange="paymentOnOff(<?php echo $val_payment['id']; ?>,this.value)"> 
									<span class="checked"></span>

									<?php } ?>
								</label>
							</div><!-- switch-onoff -->
							<?php } ?>
					  </td>
					  <?php }else{ ?>
						<td>-</td>
					<?php } ?>
					
                      <td><?php if($val_payment['id']!=7){ ?><a class="link-purple" href="<?php echo base_url();?>webshop/payment_details/<?php echo $val_payment['id']?>">View</a> <?php } ?></td>
                    </tr>
<?php 
					}
?>					
					
					
                  </tbody>
                </table>
              </div>
			
        </div>
        <!--end form-->
    </div>
	
	

  </div>
	
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_payment.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script>
function paymentOnOff(id,status){
	//alert(id);
	var integrate = '';
	console.log(id);
	console.log(status);
	console.log(integrate);
	if(status == 0)
	{ 
        //alert("Yes");
		integrate = 1 ;
		swal({
            title: "",
            text: "Are you sure you want to allow access?", 
            buttons: true,
			className: 'swal-height',
			showCancelButton: true
        }).then((willDelete) => {
			if (willDelete) 
			{
				$.ajax({
					type:"POST",
					url:BASE_URL+'WebshopController/integrate_with_us',
					dataType:"json",
					data:{integrate:integrate,id:id},
					success:function(response){
						if( response.flag == 1)
						{
							location.reload();
						}
					}
				});
			}else{
				window.location.reload(); 
			}
	    });
    }
	else if(status == 1)
	{
		//alert("No");
		integrate = 0 ;
		swal({
            title: "",
            text: "Are you sure you want to deny access?", 
            buttons: true,
			className: 'swal-height'
        }).then((willDelete) => {
			if (willDelete) 
			{
				$.ajax({
					type:"POST",
					url:BASE_URL+'WebshopController/integrate_with_us',
					dataType:"json",
					data:{integrate:integrate,id:id},
					success:function(response){
						if( response.flag == 1)
						{
							location.reload();
						}
					}
				});
			}else{
				window.location.reload(); 
			}
	    });
	}
	
}
</script>	
	

<?php $this->load->view('common/fbc-user/footer'); ?>