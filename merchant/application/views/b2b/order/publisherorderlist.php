<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php $this->load->view('webshop/order/breadcrums'); ?>

	<div class="tab-content">
		<div id="new-orders" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name">B2B Order Details publisher wise </h1>
				<div class="float-right product-filter-div ">
					<div class="search-div d-none" id="pro-search-div">
						<input class="form-control form-control-dark top-search" id="custome-filter" type="text" placeholder="Search" aria-label="Search">
						<button type="button" class="btn btn-sm search-icon" onclick="FilterProductDataTable();"><i class="fas fa-search"></i></button>
					</div>
					<!-- filter section start -->
					<div class="filter">
						<button>
							<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z" />
							</svg>
							Filter
						</button>
					</div>
					<div class="filter-section">
						<span class="reset-arrow"><a href="javascript:void(0);" onclick="location.reload();">Reset</a></span>
						<div class="close-arrow"> <i class="fa fa-angle-left"></i> </div>

						<div class="filter filter-inside">
							<button>
								<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd" d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"></path>
								</svg>
								Filter
							</button>
						</div>

						<div class="justify-content-center my-4 status-box">
							<h3>Order Status</h3>
							<div class="col-md-12">
								<select class="form-control" name="order_status" id="order_status">
									<option value="">--Select--</option>
									<?php if ($current_tab == 'shipped-orders') { ?>
										<option value="4">Tracking Missing</option>
										<option value="5">Tracking Incomplete</option>
										<option value="6">Tracking Complete</option>
									<?php } else { ?>
										<option value="0">To be processed</option>
										<option value="1">Processing</option>
										<option value="2">Complete</option>
										<option value="3">Cancelled</option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="justify-content-center my-4 status-box">
						
						</div>
						<!-- <div class="justify-content-center my-4 price-range">
							<h3>Grand Total Price Range</h3>
							<form class="range-field w-100">
								<input id="slider11" class="border-0" value="0" type="range" min="0" max="100000" />
							</form>
							<span class="zero-value">0</span>
							<span class="font-weight-bold text-primary ml-2 mt-1 valueSpan"></span>
						</div> -->

						<!-- range-box -->
						<!-- <div class="justify-content-center my-4 supplier-box">
							<h3>Shipment Type</h3>
							<div class="col-md-6"><label class="checkbox"><input type="checkbox" class="form-control" name="shipment_type[]" value="1"><span class="checked"></span> Buy In</label></div>
							<div class="col-md-6"><label class="checkbox"><input type="checkbox" class="form-control" name="shipment_type[]" value="2"><span class="checked"></span> Dropship</label></div>
						</div> -->
						<!-- range-box -->
						<div class="justify-content-center my-4 last-updated">
							<h3>Last Updated</h3>
							<div class="col-md-5"><input type="text" class="form-control" id="from_date"></div>
							<div class="col-md-2">To</div>
							<div class="col-md-5"><input type="text" class="form-control" id="to_date"></div>
						</div>
						<!-- range-box -->
						<div class="filter-btn-box">
							<button class="filter-btn" onclick="FilterOrdersDataTablePublisher();">Filter</button>
						</div>
					</div>
					<!-- filter section -->
					<!-- filter section close -->
				</div>
				<!-- product filter div -->
			</div>
			<!-- form -->
			<div class="content-main form-dashboard">
				<input type="hidden" id="current_tab" name="current_tab" value="<?php echo $current_tab; ?>">
				<div class="table-responsive text-center">
					<table class="table table-bordered table-style" id="DataTables_Table_B2BOrderspublisher">
						<thead>
							<tr>
                                <th>#</th>
                              
								<th>Order Number </th>
								<th>Webshop Order No.</th>
								<th>Purchased On </th>
								<th>Customer Name</th>
								<th>Quantity </th>
								<th>B2B order total </th>
								<th>Discount (0.00%)</th>
								<th>B2B Taxes amount</th>
								<th>Shipping Amount</th>
								<th>Publisher Commision</th>
								<th>Whuso income</th>
								<th>B2B Net Payable Amount</th>
								<th>Status</th>
								<!-- <th>Invoice ID</th>  -->
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
			<!--end form-->
			<div id="order-data-table" style="display:none;">
    <table border="1">
        <thead>
            <tr>
				<th>Order Number </th>
				<th>Webshop Order No.</th>
				<th>Purchased On </th>
				<th>Customer Name</th>
				<th>Quantity </th>
				<th>B2B order total </th>
				<th>Discount (0.00%)</th>
				<th>B2B Taxes amount</th>
				<th>Shipping Amount</th>
				<th>Publisher Commision</th>
				<th>Whuso income</th>
				<th>B2B Net Payable Amount</th>
            </tr>
        </thead>
        <tbody id="order-table-body">
            <!-- Rows will be added here dynamically -->
        </tbody>
    </table>
</div>
		</div>

<!-- <button class="white-btn" >Discard</button> -->
<button id="initiate-payment-btn-publisher"  class="download-btn">Initiate Payment</button>

	</div>
	

	<script>
$(document).ready(function() {
    $('#initiate-payment-btn-publisher').click(function() {
        const selectedOrders = {};

        $('input[name="checkboxes[]"]:checked').each(function() {
            const row = $(this).closest('tr');
            const publisher = $(this).val(); // Publisher value from checkbox
            const orderData = {
                orderNumber: row.find('td:eq(1)').text(), // Order Number
                webshopOrderNo: row.find('td:eq(2)').text(), // Webshop Order No.
                purchaseDate: row.find('td:eq(3)').text(), // Purchased On
                customerName: row.find('td:eq(4)').text(), // Customer Name
                quantity: parseInt(row.find('td:eq(5)').text()) || 0, // Quantity
                baseSubtotal: parseFloat(row.find('td:eq(6)').text().replace(/[^0-9.-]+/g,"")) || 0, // B2B order total
                discountAmount: parseFloat(row.find('td:eq(7)').text().replace(/[^0-9.-]+/g,"")) || 0, // Discount
                taxAmount: parseFloat(row.find('td:eq(8)').text().replace(/[^0-9.-]+/g,"")) || 0, // B2B Taxes amount
                shippingAmount: parseFloat(row.find('td:eq(9)').text().replace(/[^0-9.-]+/g,"")) || 0, // Shipping Amount
                publisherCommission: parseFloat(row.find('td:eq(10)').text().replace(/[^0-9.-]+/g,"")) || 0, // Publisher Commision
                whusoIncome: parseFloat(row.find('td:eq(11)').text().replace(/[^0-9.-]+/g,"")) || 0, // Whuso income
                netPayableAmount: parseFloat(row.find('td:eq(12)').text().replace(/[^0-9.-]+/g,"")) || 0, // B2B Net Payable Amount
                orderStatus: row.find('td:eq(13)').text() // Order Status
            };

            if (!selectedOrders[publisher]) {
                selectedOrders[publisher] = {
                    orders: [],
                    total: 0 // Initialize total for this publisher
                };
            }
            selectedOrders[publisher].orders.push(orderData);
            selectedOrders[publisher].total += orderData.netPayableAmount; // Sum up net payable amount
        });

        if (Object.keys(selectedOrders).length > 0) {
            $.ajax({
                url: BASE_URL + "B2BOrderspublisherController/initiatepaymentpublisher",
                type: 'POST',
                dataType: 'json',
                data: JSON.stringify(selectedOrders),
                success: function(data) {
                    if (data.status === 200) {
                        // Clear existing table rows
                        $('#order-table-body').empty();

                        // Populate the table with data grouped by publisher
                        for (const publisher in selectedOrders) {
                            // Append publisher name as a header
                            $('#order-table-body').append(`<tr><td colspan="12" style="font-weight: bold;">Publisher: ${publisher}</td></tr>`);
                            
                            // Append each order for this publisher
                            selectedOrders[publisher].orders.forEach(order => {
                                $('#order-table-body').append(`
                                    <tr>
                                        <td>${order.orderNumber}</td>
                                        <td>${order.webshopOrderNo}</td>
                                        <td>${order.purchaseDate}</td>
                                        <td>${order.customerName}</td>
                                        <td>${order.quantity}</td>
                                        <td>${order.baseSubtotal.toFixed(2)}</td>
                                        <td>${order.discountAmount.toFixed(2)}</td>
                                        <td>${order.taxAmount.toFixed(2)}</td>
                                        <td>${order.shippingAmount.toFixed(2)}</td>
                                        <td>${order.publisherCommission.toFixed(2)}%</td>
                                        <td>${order.whusoIncome.toFixed(2)}</td>
                                        <td>${order.netPayableAmount.toFixed(2)}</td>
                                       
                                    </tr>
                                `);
                            });

                            // Append total price for this publisher
                            $('#order-table-body').append(`
                                <tr>
                                    <td colspan="11" style="font-weight: bold;">Total Price for ${publisher}: </td>
                                    <td style="font-weight: bold;">${(selectedOrders[publisher].total).toFixed(2)}</td>
                                </tr>
                            `);
                        }

                        // Show the table
                        $('#order-data-table').show();
                    } else {
                        alert('Error occurred: ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
    console.error('AJAX Error:', error);
    console.error('Response Status:', xhr.status);
    console.error('Response Text:', xhr.responseText);
    alert('An error occurred: ' + xhr.responseText);
}
            });
        } else {
            alert('Please select at least one order.');
        }
    });
});
</script>



</main>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>b2b_order_list.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>