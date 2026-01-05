<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('seller/products/breadcrums'); ?>

    <div class="tab-content">
        <div id="inbound" class="tab-pane fade common-tab-section admin-shop-details-table" style="opacity:1; display:block;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                 <h1 class="head-name">Inbound </h1>
            <?php if(empty($this->session->userdata('userPermission')) || in_array('seller/database/write',$this->session->userdata('userPermission'))){ ?>
                <div class="float-right">
                    <button class="purple-btn" onclick="window.location.href='<?php echo base_url().'seller/inbound/add'?>';" >New</button>
                    <button class="white-btn" onclick="window.location.href='<?= base_url('seller/inbound/import') ?>';" >Import</button>
                </div>
            <?php } ?>
            </div>


            <!-- form -->
             <div class="content-main form-dashboard">
                <form>
                    <div class="table-responsive text-center">
                        <table class="table table-bordered table-style" id="datatableInboundList">
                        <thead>
                            <tr>
                            <th>Inbound Id</th>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Total Product</th>
                            <th>Status</th>
                            <?php 
                                $use_advanced_warehouse=$this->CommonModel->getSingleShopDataByID('custom_variables',array('identifier'=>'use_advanced_warehouse'),'value');

                                if ($use_advanced_warehouse->value=="yes") {
                                    echo "<th>Warehouse Status</th>";
                                }
                            ?>
                            <th>Details </th>
                            </tr>
                        </thead>

                        </table>
                    </div>
                </form>
            </div>
            <!--end form-->
        </div> <!-- dropshipping-products -->
    </div>
</main>


<script>
    $(document).ready(function(){
        FilterInboundDataTable();
    });
</script>

<script type="text/javascript" src="<?php echo SKIN_JS; ?>seller-inbound-process.js"></script>


<?php $this->load->view('common/fbc-user/footer'); ?>
