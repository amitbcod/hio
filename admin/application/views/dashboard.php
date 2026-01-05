<?php
// $daily_sale_count = 0;
// $daily_earnings_count = 0;

// echo "<pre>";
// print_r($daily_earnings_count);
// die
?>
<?php $this->load->view('common/fbc-user/header');

$CI = &get_instance();
?>

<link href="<?php echo SKIN_CSS; ?>dashboard1.css?v=<?php echo CSSJS_VERSION; ?>" rel="stylesheet">
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <div class="tab-content common-tab-section min-height-480">

<?php 
$pending_merchants = isset($pending_merchants) ? (int)$pending_merchants : 0;
$pending_products  = isset($pending_products)  ? (int)$pending_products  : 0;

//echo $pending_products.'---------';exit;
?>

<?php if ($pending_merchants > 0 || $pending_products > 0): ?>
  <div id="live-tab" class="tab-pane fade" style="opacity:1; display:block;">

      <?php if ($pending_merchants > 0): ?>
          <div class="alert alert-warning mt-3">
              <strong>
                  <a href="<?= base_url('publishers?status=0') ?>" class="text-dark" style="text-decoration:none;">
                      You have <?= $pending_merchants ?> pending merchant approval request<?= ($pending_merchants > 1 ? 's' : '') ?>.
                  </a>
              </strong>
          </div>
      <?php endif; ?>

      <?php if ($pending_products > 0): ?>
          <div class="alert alert-warning mt-3">
              <strong>
                  <a href="<?= base_url('seller/warehouse?approval_status=pending') ?>" class="text-dark" style="text-decoration:none;">
                      You have <?= $pending_products ?> pending product approval request<?= ($pending_products > 1 ? 's' : '') ?>.
                  </a>
              </strong>
          </div>
      <?php endif; ?>

  </div>
<?php endif; ?>

      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name">Dashboard </h1>
      </div>
     
      <!-- form -->
      <div class="content-main form-dashboard">
        <div class="dashboard-section">
          <ul class="dashboard-list">
            <!-- <li><a href="">
                <p><span><?php echo  isset($user_count) ? $user_count : 'No Users found'; ?></span>User Count</p>
              </a></li> -->
            <li><a href="<?php echo base_url() . "seller/warehouse" ?>">
                <p><span><?php echo  isset($product_count) ? $product_count : 'No Products found'; ?></span>Products</p>
              </a></li>
            <li><a href="<?php echo base_url() . "publishers" ?>">
                <p><span><?php echo  isset($publisher_count) ? $publisher_count : 'No Publishers found'; ?></span>Merchants</p>
              </a></li>
            <li><a href="<?php echo base_url() . "customers" ?>">
                <p><span><?php echo  isset($customer_count) ? $customer_count : 'No Customers found'; ?></span>Customers</p>
              </a></li>

            <li>
              <a href="<?php echo base_url() . "webshop/orders" ?>">
                <p>
                  <span>
                    <?php echo isset($daily_order_count) && $daily_order_count  !== null ? $daily_order_count : '0'; ?>
                  </span>Daily Order
                </p>
              </a>
            </li>
            <li>
              <a href="<?php echo base_url() . "webshop/orders" ?>">
                <p>
                  <span>
                    <?php echo isset($daily_sale_count) && $daily_sale_count  !== null ? '₹ ' . $daily_sale_count : '0'; ?>
                  </span>Daily Sale
                </p>
              </a>
            </li>
            <li>
              <a href="">
                <p>
                  <span>
                    <?php echo isset($daily_earnings_count) && $daily_earnings_count !== null ? '₹ ' . $daily_earnings_count : '0'; ?>
                  </span>Daily Earning
                </p>
              </a>
            </li>
          </ul>
        </div>
      </div>
      <!--end form-->

    </div> <!-- dropshipping-products -->

  </div>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<script>
  $(document).ready(function() {
    $("#datatableattribute").dataTable({
      "order": [],
      "aaSorting": [],
      "ordering": [],
      "language": {
        "infoFiltered": "",
        "search": '',
        "searchPlaceholder": "Search",
        "paginate": {
          next: '<i class="fas fa-angle-right"></i>',
          previous: '<i class="fas fa-angle-left"></i>'
        }
      },
      stateSave: true,
      ordering: false,
    });
    var weeklyData = <?php echo json_encode($weekly_sale_count); ?>;
    // console.log(weeklyData);
    var ctx = document.getElementById("weeklyChart").getContext('2d');

    var saleDates = weeklyData.map(item => item.sale_date);
    var saleCounts = weeklyData.map(item => item.weekly_sale_count);
    var labels = getCurrentWeekLabels(weeklyData);

    var data = [];
    for (var i = 0; i < labels.length; i++) {
      var index = saleDates.indexOf(labels[i]);
      if (index !== -1) {
        data.push(parseFloat(saleCounts[index]));
      } else {
        data.push(0);
      }
    }
    // console.log(data);



    function getCurrentWeekLabels() {
      var today = new Date();
      var currentDay = today.getDay(); // 0 for Sunday, 1 for Monday, ..., 6 for Saturday

      // Calculate the start of the week (Monday)
      var startOfWeek = new Date(today);
      startOfWeek.setDate(today.getDate() - currentDay + 1); // Move to the start of the week

      var labels = [];
      for (var i = 0; i < 7; i++) {
        var date = new Date(startOfWeek);
        date.setDate(startOfWeek.getDate() + i);
        var dateString = formatDate(date); // Format the current date

        labels.push(dateString);
      }
      return labels;
    }

    function formatDate(date) {
      var month = date.getMonth() + 1;
      var day = date.getDate();
      var year = date.getFullYear();
      return (day < 10 ? '0' : '') + day + '/' + (month < 10 ? '0' : '') + month + '/' + year;
    }
    var myChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Weekly Sales',
          data: data,
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });


    var monthlySalesData = <?php echo json_encode($monthly_sale_count); ?>;

    function prepareChartData(monthlySalesData) {
      // Define an array of all months
      var allMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

      // Initialize arrays for months and sales counts
      var months = [];
      var salesCounts = [];

      // Loop through all months
      for (var i = 0; i < allMonths.length; i++) {
        var month = allMonths[i];

        // Check if the current month exists in the provided data
        if (monthlySalesData.hasOwnProperty(month)) {
          // If the month exists, push its sales count to the respective array
          months.push(month);
          salesCounts.push(monthlySalesData[month]);
        } else {
          // If the month is missing, push it to the months array and set its sales count to 0
          months.push(month);
          salesCounts.push(0);
        }
      }

      // Return an object containing months and sales counts
      return {
        months: months,
        salesCounts: salesCounts
      };
    }

    function renderMonthlyChart() {
      // Prepare data for the chart
      var chartData = prepareChartData(monthlySalesData);

      // Get the canvas element to render the chart
      var ctx = document.getElementById('monthlyChart').getContext('2d');

      // Create the chart
      var monthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: chartData.months, // Months on X-axis
          datasets: [{
            label: 'Monthly Sales',
            data: chartData.salesCounts, // Sales counts on Y-axis
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true
              }
            }]
          }
        }
      });
    }

    renderMonthlyChart();

    var yearlySalesData = <?php echo json_encode($yearly_sale_count); ?>;

    // Extract years and sales counts from yearlySalesData
    var years = Object.keys(yearlySalesData);
    var salesCounts = Object.values(yearlySalesData);

    // Get the canvas element to render the chart
    var ctx = document.getElementById('yearlyChart').getContext('2d');

    // Create the chart
    var yearlyChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: years, // Years on X-axis
        datasets: [{
          label: 'Yearly Sales',
          data: salesCounts, // Sales counts on Y-axis
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });
  });

  function handleRenewalClick(item_id, order_id) {
    // alert(order_id);
    // return false;
    $.ajax({
      url: BASE_URL + "DashboardController/handleRenewalClick",
      type: "POST",
      data: {
        item_id: item_id,
        // customer_id: customer_id,
        order_id: order_id
      },
      success: function(response) {
        console.log(response);
        // alert(data);
        if (response.status === 200) {
          swal({
              title: "",
              text: "Email Sent Successfully",
              type: "success",
            },
            function() {
              location.href = BASE_URL + "/dashboard";
            })
        } else {
          console.log("error");
        }
      },
      error: function(error) {
        console.log(error);
      },
    });
    // if (item_id != ''  order_id != '') {
    // } else {
    // 	return false;
    // }

  }
</script>
<?php $this->load->view('common/fbc-user/footer'); ?>