<?php 
$this->load->view('common/fbc-user/header'); 
// if($shop_flag==2){}else{ }
?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('accounting/order/breadcrums'); ?>
	
	<div class="tab-content"  >
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
      <!-- form -->
        <form id="salesListForm" name="salesListForm" method="POST" action="<?php echo base_url('AccountingWebshopOrdersController/postGenerateReport') ?>" enctype="multipart/form-data">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <h1 class="head-name">Sales Report </h1>
          </div>
            <p class="danger"><?php if(isset($errorMsg)){ echo $errorMsg;} ?></p>
          <?php
            if(isset($fromDate) && isset($toDate)){
              $fromDate=$fromDate;
              $toDate=$toDate;
            }else{
              $fromDate=date('d-m-Y');
              $toDate=date('d-m-Y');
            }
          ?>
            <!-- start -->
            <div class="content-main form-dashboard warehouse-setting">       
              <div class="customize-add-section">       
                <div class="row">           
                    <div class="right-form-sec">          
                      <div class="col-sm-6 customize-add-inner-sec">          
                        <label for="currency" class="">From </label>
                        <div class="input-group date">
                            <input type="text" class="form-control" id="fromDate" readonly name="fromDate" value="<?=$fromDate;?>" data-date-format="mm-dd-yyyy">
                            <!-- <input type="text" class="form-control" value="<?=date('d-m-Y');?>" data-date-format="mm-dd-yyyy"> -->
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                      </div>
                      <div class="col-sm-6 customize-add-inner-sec">          
                        <label for="currency" class="">To </label>
                        <div class="input-group date">
                            <input type="text" class="form-control" id="toDate" readonly name="toDate" value="<?=$toDate?>" data-date-format="mm-dd-yyyy">
                            <!-- <input type="text" class="form-control" value="<?=date('d-m-Y');?>" data-date-format="mm-dd-yyyy"> -->
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                      </div>
                    </div>

                </div>
              </div>
            </div>
            <!-- end -->
            <div class="col-sm-6">
                <button class="purple-btn" type="submit" id="generateSaleReport" name="generateReport" >Generate Report</button>
            </div>

        </form>
        <!--end form-->
    </div>
		
	</div>
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>accounting_webshop_order_list.js"></script>
<script type="text/javascript">

  $(document).ready(function() {
      $("#generateSaleReport").click(function(){
          $('.danger').html('');
      }); 
  });

  $('.date').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true,
    maxDate: '+1m',
    minDate: '-20d',
    // minDate: '-10d',

  });

  $('.date1').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true,
    maxDate: '+1m',
    minDate: '-20d',
    // minDate: '-10d',

  });
  
    /*var myDate = new Date($("#currentDate").val()); //ENTER VALUE IN mm/dd/yy FORMAT
    //console.log(myDate);
    var mymaxDate = new Date(myDate +7);
    $('.date').datepicker({
        // inline: true,
        autoclose: true,
        format: 'dd-mm-yyyy',        
        defaultDate:myDate,
        minDate: myDate,
        maxDate: mymaxDate
    });*/

</script>
  
<?php $this->load->view('common/fbc-user/footer'); ?>