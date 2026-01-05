<?php 
$this->load->view('common/fbc-user/header'); 
?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <?php  $this->load->view('report/breadcrums'); ?>
	
	<div class="tab-content"  >
		<div id="new-orders" class="tab-pane fade in active min-height-280  common-tab-section admin-shop-details-table" style="opacity:1;">
      <!-- form -->
        <form id="customersListForm" name="customersListForm" method="POST" action="<?php echo base_url('ReportController/postGenerateProduct') ?>" enctype="multipart/form-data">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <h1 class="head-name">Product Report </h1>
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
                        <label for="" class="">From </label>
                        <div class="input-group date">
                            <input type="text" class="form-control" id="fromDate" readonly name="fromDate" value="<?=$fromDate;?>" data-date-format="mm-dd-yyyy" required="true">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                      </div>
                      <div class="col-sm-6 customize-add-inner-sec">          
                        <label for="" class="">To </label>
                        <div class="input-group date">
                            <input type="text" class="form-control" id="toDate" readonly name="toDate" value="<?=$toDate?>" data-date-format="mm-dd-yyyy" required>
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

            <!--category filter start -->
            <div class="content-main form-dashboard warehouse-setting">       
              <div class="customize-add-section">       
                <div class="row">                    
                  <h1 class="head-name">Select Category for report</h1>
                  <div class="input-group">
                    <ul class="common-list list-gc">
                      <?php if(isset($getAllCategory) >0){
                        foreach($getAllCategory as $parent_cat) { 
                        ?>
                          <li class="list-gc-item ">
                            <div class="custom-control custom-checkbox"><label class="checkbox"><input type="checkbox" name="categoryid[]" class="form-control" value="<?php echo $parent_cat->id; ?>"  id="level_zero_cat_<?php echo $parent_cat->id; ?>"><span class="checked"></span><span class="sis-cat-name"><?php echo $parent_cat->cat_name; ?></span></label>
                            </div>	
                          </li>
                        <?php 
                          }
                        } ?>
                    </ul>
                  </div> 
                </div>
              </div>
            </div>
            <!-- end -->

            <!-- price range filter -->
            <div class="wrapper">
            <h1 class="head-name">Select price range for report</h1>
              <fieldset class="filter-price">
                <div class="price-field">
                  <input type="range" min="0" max="50000" value="0" id="lower">
                  <input type="range" min="0" max="50000" value="0" id="upper">
                </div>
                <div class="price-wrap">
                  <div class="price-container">
                    <div class="price-wrap-1">
                      <label for="one">₹</label>
                      <input id="one" name= "upper">
                    </div>
                    <div class="price-wrap_line">-</div>
                    <div class="price-wrap-2">
                      <label for="two">₹</label>
                      <input id="two" name="lower">
                    </div>
                  </div>
                </div>
              </fieldset>
            </div>
            <!-- end -->

            <div class="row col-sm-12">
                <button class="purple-btn" type="submit" id="generateSaleReport" name="generateReport" value="generateReport" >Generate Report</button>
            </div>
        </form>
         <!--end form-->
    </div>
		
   
	</div>
</main>

<script type="text/javascript" src="<?php echo SKIN_JS; ?>report_list.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script type="text/javascript">
  $('.date').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true,
    maxDate: '+1m',
    minDate: '-20d',
  });
  $('.date1').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true,
    maxDate: '+1m',
    minDate: '-20d',
  });

  
</script>

<script>
var lowerSlider = document.querySelector('#lower');
var  upperSlider = document.querySelector('#upper');

document.querySelector('#two').value=upperSlider.value;
document.querySelector('#one').value=lowerSlider.value;

var  lowerVal = parseInt(lowerSlider.value);
var upperVal = parseInt(upperSlider.value);

upperSlider.oninput = function () {
    lowerVal = parseInt(lowerSlider.value);
    upperVal = parseInt(upperSlider.value);

    if (upperVal < lowerVal + 4) {
        lowerSlider.value = upperVal - 4;
        if (lowerVal == lowerSlider.min) {
        upperSlider.value = 4;
        }
    }
    document.querySelector('#two').value=this.value
};

lowerSlider.oninput = function () {
    lowerVal = parseInt(lowerSlider.value);
    upperVal = parseInt(upperSlider.value);
    if (lowerVal > upperVal - 4) {
        upperSlider.value = lowerVal + 4;
        if (upperVal == upperSlider.max) {
            lowerSlider.value = parseInt(upperSlider.max) - 4;
        }
    }
    document.querySelector('#one').value = this.value
}; 

// alert(upperVal)
</script>
<?php $this->load->view('common/fbc-user/footer'); ?>