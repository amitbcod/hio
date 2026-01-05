<?php $this->load->view('common/fbc-user/header'); ?>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.3/themes/ui-lightness/jquery-ui.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('seller/products/breadcrums'); ?>

    <div class="tab-content">
    
            <div id="inbound" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="head-name">Inbound </h1> 
                </div>
                
                
                <!-- form -->
                <div class="content-main form-dashboard admin-shop-details-table new-height">
                    <form>
                    <div class="barcode-qty-box row">
                        <div class="col-sm-4 order-id">
                            <p><span class="huge-name">Name : </span> <input type="text" name="name" id="name" value="" placeholder=""></p>
                            
                        </div>
                        <div class="col-sm-4 order-id">
                            <p><span class="huge-name">Total Products :</span> 0
                            
                        </p></div>

                        <div class="col-sm-4 order-id">
                            <p><span>Total Price :</span> <?php echo  $currency_code." 0" ?></p>
                        </div>
                    <?php $customVarData = $this->InboundModel->getCustomData();
                     if(isset($customVarData) && $customVarData->value == 'yes') { ?>
                         <div class="col-sm-4 order-id date pd-t">
                           <p><span class="huge-name">Warehouse Eta : </span><input type="date" class="form-control" id="warehouse_eta"  name="warehouse_eta" value="<?php echo date('Y-m-d'); ?>"></p>
                          
                        </div> 
                        <div class="col-sm-4 order-id pd-t">
                            <p><span class="huge-name">Carrier : </span> <input type="text" name="carrier" id="carrier" value="" placeholder=""></p>     
                        </div>
                        <div class="col-sm-4 order-id pd-t">
                            <p><span class="huge-name">Carrier Refrence : </span> <input type="text" name="carrier_reference" id="carrier_reference" value="" placeholder=""></p>
                        </div>
                        <div class="col-sm-4 order-id pd-b">
                            <p><span class="huge-name">Carton Count : </span> <input onkeypress="return onlyNumberKey(event)" type="text" name="carton_count" id="carton_count" value="" placeholder=""></p>
                            
                        </div>
                        <div class="col-sm-4 order-id pd-b">
                            <p><span class="huge-name">Po Refrence : </span> <input type="text" name="po_reference" id="po_reference" value="" placeholder=""></p>
                            
                        </div>
                    <?php } ?>
                        <div class="row col-sm-12 checkbox-label print-box">
                            <div class="col-sm-8 pad-zero"><label class="checkbox"><input type="checkbox" id="product_label"> Print inbound product labels <span class="checked"></span></label></div>
                        </div>


                        <div class="row col-sm-12 dropdown-label import-month" style="display:none">                    
                            <div class="col-sm-2 pad-zero">            
                                <select class="form-control" name="select_month" id="select_month">
                                    <option value="">Select Month</option>
                                    
                                    <?php

                                        for($m=1; $m<=12; $m++){
                                          $monthList = date('F', mktime(0, 0, 0, $m));    
                                        echo '<option value="'.$monthList.'">'.$monthList.'</option>';
                                        }
                                    ?>
                                </select>
                                <br>
                            </div>

                            <div class="col-sm-2 pad-zero">
                                <select class="form-control" name="select_year" id="select_year">
                                    <option value="">Select Year</option>
                                    <?php

                                        $date_future = date("Y", strtotime('+20 year'));
                                        $date_year = date("Y");
                                        for($i=$date_year;$i<$date_future;$i++){
                                            echo "<option value=\"$i\">$i</option> \n"; 
                                    
                                        }
    

                                    ?>
                                </select>   
                            </div>

                            <div class="col-sm-2 pad-zero">
                                <select class="form-control" name="label_size" id="label_size">
                                    <option value="">Select Size</option>
                                    <option value="10x15">10 cm x 15 cm</option>
                                    <option value="10x7.5">10 cm x 7.5 cm</option>
                                </select>              
                            </div>
                            
                        </div>

                        
                        <div class="row col-sm-12">
                            <div class="col-sm-3 pad-zero">
                            <input type="text" class="form-control" id="barcode_item" name="barcode_item" placeholder="Barcode" onmouseover="this.focus();" autofocus><br>
                            <input type="text" class="form-control" id="sku" name="sku" placeholder="Product Name - SKU">
                            </div>
                            <div class="col-sm-3 pad-zero"><input value="1" type="text" name="qty" id="qty" class="form-control pos-top-25" placeholder="Quantity"></div>
                        <?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
                            <div class="col-sm-3 pad-zero"><button class="purple-btn pos-top-25" onclick="ScanBarcodeManually(); return false;">    Enter</button>
                            </div>
                        <?php } ?>
                            <label class="error" id="barcode-error"></label>
                        </div>
                    </div><!-- barcode-qty-box -->

                    
                    <div class="table-responsive text-center">
                        <table class="table table-bordered table-style" id="datatableInboundProducts">
                        <thead>
                            <tr>
                            <th>PRODUCT NAME  <i class="float-right fa fa-fw fa-sort"></i></th>
                            <th>SKU</th>
                            <th>Color  </th>
                            <th>Size  </th>
                            <th>Qty Scanned </th>
                            <th>Price <i class="float-right fa fa-fw fa-sort"></i></th>
                            <th>Total Price </th>
                            <th>Location</th>
                            <th>Restock </th>
                            <th>Type </th>
                            <th>Action </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="odd"><td valign="top" colspan="10" class="dataTables_empty">No data available in table</td></tr>
                        </tbody>
                        </table>
                    </div>

                    <!--div class="download-discard-small inbound-box">
                            <button class="white-btn">Save Draft </button>
                            <button class="download-btn auto-width">Create Inbound</button>
                    </div>-->

                    </form>
                </div>
                <!--end form-->
            </div> <!-- dropshipping-products -->

    </div>
    
</main>


<script>

$('#sku').autocomplete({
    minLength: 3,
    source: function(request, response) {
        $.getJSON(BASE_URL+"InboundController/getProductSku", {
            term: request.term
        }, function(data) {    
            var array = data.error ? [] : $.map(data, function(m) {
        
                return {
                    label: m.name+" - "+m.sku,
                    value: m.sku,
                    
                };
            });
    
            response(array);
         });
    },
    select: function (event, ui) {
        
        $('#sku').val(ui.item.value); // save selected id to hidden input
        return false;
    },
    focus: function( event, ui ) {

        $('#sku').val(ui.item.label);
        return false;
    },	
    change: function( event, ui ) {
        $( "#sku" ).val( ui.item? ui.item.value : "" );
    }


});
</script>


<script type="text/javascript" src="<?php echo SKIN_JS; ?>seller-inbound-process.js"></script> 

  
<?php $this->load->view('common/fbc-user/footer'); ?>