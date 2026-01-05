<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <?php $this->load->view('webshop/order/breadcrums'); ?>

    <div class="tab-content">
        <div id="new-orders" class="tab-pane fade in active min-height-480 common-tab-section admin-shop-details-table" style="opacity:1;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="head-name">
                    <?php if($current_tab == 'pickup-orders'){ ?>
                        B2B Pickup Details
                    <?php } else if ($current_tab == 'delivery-orders'){ ?>
                        B2B Delivery Details
                    <?php } else { ?>
                        B2B Orders Details
                    <?php } ?>
                </h1>

                <div class="float-right product-filter-div">
                    <!-- Search & Filter Section (keep existing UI) -->
                    <div class="search-div d-none" id="pro-search-div">
                        <input class="form-control form-control-dark top-search" id="custome-filter" type="text" placeholder="Search" aria-label="Search">
                        <button type="button" class="btn btn-sm search-icon" onclick="FilterProductDataTable();"><i class="fas fa-search"></i></button>
                    </div>
                    <?php // Keep additional filters if any ?>
                </div>
            </div>

            <!-- Table -->
            <div class="content-main form-dashboard">
                <input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">

                <div class="table-responsive text-center">
                    <table class="table table-bordered table-style" id="DataTables_Table_B2BOrders">
                        <thead>
                            <tr>
                                <th>Order <br> Number</th>
                                <th>Webshop <br>Order No.</th>
                                <th>Purchased <br>On</th>
                                <th>Customer<br> Name</th>
                                <th>Merchant<br> Name</th>
                                <th>Order <br>Status</th>
                                <th <?php if($current_tab == 'pickup-orders'){ ?>style=""<?php } else { ?>style="display:none"<?php } ?>>Pickup <br> Status</th>
                                <th <?php if($current_tab == 'delivery-orders'){ ?>style=""<?php } else { ?>style="display:none"<?php } ?>>Delivery <br> Status</th>
                                <th>Details</th>
                                <th <?php if($current_tab == 'pickup-orders'){ ?>style=""<?php } else { ?>style="display:none"<?php } ?>>Action</th>
                                <th <?php if($current_tab == 'delivery-orders'){ ?>style=""<?php } else { ?>style="display:none"<?php } ?>>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $limit = 10; // records per page
                            $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $offset = ($current_page - 1) * $limit;
                            $paged_orders = array_slice($orders, $offset, $limit);

                            foreach ($paged_orders as $order): ?>
                                <tr>
                                    <?php foreach ($order['row_data'] as $col): ?>
                                        <td><?php echo $col; ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php
                $total_pages = ceil(count($orders) / $limit);
                if($total_pages > 1):
                ?>
                    <nav class="pagination-wrapper text-center mt-3">
                        <ul class="pagination justify-content-center">
                            <?php for($i=1; $i<=$total_pages; $i++): ?>
                                <li class="page-item <?php echo ($i==$current_page)?'active':''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            </div>
        </div>
    </div>
</main>

<!-- Delivery Attempts Modal -->
<div class="modal fade" id="deliveryPopupModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" id="deliveryPopupContent">
      <!-- AJAX content will be loaded here -->
    </div>
  </div>
</div>

<script type="text/javascript" src="<?php echo SKIN_JS; ?>b2b_order_list.js"></script>
<script>
function openDeliveryPopup(order_id) {
    $('#deliveryPopupModal').modal('show');
    $('#deliveryPopupContent').html('<p class="text-center">Loading...</p>');

    $.ajax({
        url: BASE_URL + "B2BOrdersController/getDeliveryAttemptsPopup",
        type: "POST",
        data: { order_id: order_id },
        success: function(response) {
            $('#deliveryPopupContent').html(response);
        },
        error: function() {
            $('#deliveryPopupContent').html('<p class="text-danger text-center">Failed to load delivery data.</p>');
        }
    });
}
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
