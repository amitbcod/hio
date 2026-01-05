<?php 
$this->load->view('common/fbc-user/header'); 
?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <?php  $this->load->view('accounting/order/breadcrums'); ?>
  
  <div class="tab-content"  >
    <div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">

      <div class="row sub-tab-style webshop-sub-tab">
        <ul class="nav nav-pills" role="tablist">
          <li class="">
            <a class="active" data-toggle="tab" href="#invoice-req" role="tab" aria-selected="false">Invoices requested by customers</a>
          </li>
          <li>
            <a class="" data-toggle="tab" href="#invoice-not-req" role="tab" aria-selected="false">Invoices Not requested by customers</a>
          </li>
        </ul><!-- Tab panes -->
        <div class="tab-content">
          <div class="tab-pane active" id="invoice-req" role="tabpanel">
            <!-- form -->
              <form id="invoiceListForm" method="POST" action="<?php echo base_url('AccountingWebshopOrdersController/postCheckedInvoice') ?>" enctype="multipart/form-data">
              <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="head-name">Invoices Requested By Customers </h1> 
                <div class="float-left">
                    <div class="input-group date" data-provide="datepicker">
                        <input type="text" class="form-control" readonly id="billDate" name="billDate" value="<?=date('d-m-Y');?>" data-date-format="mm-dd-yyyy">
                        <!-- <input type="text" class="form-control" value="<?=date('d-m-Y');?>" data-date-format="mm-dd-yyyy"> -->
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                    </div>
                    <?php if(empty($this->session->userdata('userPermission')) || in_array('accounting/write',$this->session->userdata('userPermission'))){ ?>
                    <button class="purple-btn" type="submit" id="invoice_check_list" name="invoice_check_list" >Bill Now</button>
                    <?php } ?>
                    <!-- <button class="purple-btn" type="button" d="invoice_check_list" name="invoice_check_list" onclick="invoiceBillNow();">Bill Now</button> -->
                </div>
              </div>
              
                    <div class="content-main form-dashboard">
                       <input type="hidden" id="current_tab" name="current_tab"  value="<?php echo $current_tab; ?>">
                          <div class="table-responsive text-center">
                            <table class="table table-bordered table-style" id="DataTables_Table_AccountingWebshopOrders">
                              <thead>
                                <tr>
                                  <th><input type="checkbox" value="all" name="checkAll" id="checkAll"> All</th>
                                  <th>Order Number </th>
                                  <th>Customer Name </th>
                                  <th>Status </th>
                                  <?php 
                                    $use_advanced_warehouse=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value');

                                    if ($use_advanced_warehouse->value=="yes") {
                                      echo "<th>Warehouse Status</th>";
                                    }
                                  ?>
                                  <th>Billing Customer </th>
                                  <th>Customer Type </th>
                                  <th>Tracking Complete Date </th>
                                  <th>Details </th>
                                </tr>
                              </thead>
                              <tbody>
                              </tbody>
                            </table>
                          </div>

                       
                    </div>
              </form>
            <!--end form-->
          </div>
          <div class="tab-pane" id="invoice-not-req" role="tabpanel">
            <!-- form -->
              <form id="invoiceListForm" method="POST" action="<?php echo base_url('AccountingWebshopOrdersController/postCheckedInvoice') ?>" enctype="multipart/form-data">
              <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="head-name">Invoices Not requested by customers </h1> 
                <div class="float-left">
                    <div class="input-group date" data-provide="datepicker">
                        <input type="text" class="form-control" readonly id="billDate" name="billDate" value="<?=date('d-m-Y');?>" data-date-format="mm-dd-yyyy">
                        <!-- <input type="text" class="form-control" value="<?=date('d-m-Y');?>" data-date-format="mm-dd-yyyy"> -->
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                    </div>
                    <?php if(empty($this->session->userdata('userPermission')) || in_array('accounting/write',$this->session->userdata('userPermission'))){ ?>
                    <button class="purple-btn" type="submit" id="invoice_check_list" name="invoice_check_list" >Bill Now</button>
                    <?php   } ?>
                    <!-- <button class="purple-btn" type="button" d="invoice_check_list" name="invoice_check_list" onclick="invoiceBillNow();">Bill Now</button> -->
                </div>
              </div>
              
                    <div class="content-main form-dashboard">
                       <input type="hidden" id="current_tab" name="current_tab"  value="<?php echo $current_tab; ?>">
                          <div class="table-responsive text-center">
                            <table class="table table-bordered table-style" id="DataTables_Table_AccountingWebshopOrders_not">
                              <thead>
                                <tr>
                                  <th><input type="checkbox" value="all" name="checkAll" id="checkAll"> All</th>
                                  <th>Order Number </th>
                                  <th>Customer Name </th>
                                  <th>Status </th>
                                  <?php 
                                    $use_advanced_warehouse=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value');

                                    if ($use_advanced_warehouse->value=="yes") {
                                      echo "<th>Warehouse Status</th>";
                                    }
                                  ?>
                                  <th>Billing Customer </th>
                                  <th>Customer Type </th>
                                  <th>Tracking Complete Date </th>
                                  <th>Details </th>
                                </tr>
                              </thead>
                              <tbody>
                              </tbody>
                            </table>
                          </div>

                       
                    </div>
              </form>
            <!--end form-->
          </div>
        </div>
      </div>

    </div>
    
  </div>
</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>accounting_webshop_order_list.js"></script>
<script type="text/javascript">
  $('.date').datepicker({
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