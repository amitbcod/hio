<?php $this->load->view('common/fbc-user/header'); ?>

<style>
.thumb{
  margin: 24px 5px 20px 0;
  width: 150px;
  float: left;
}
#blah {
  border: 2px solid;
  display: block;
  background-color: white;
  border-radius: 5px;
}

</style>
<?php //print_R($user_details->owner_name); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js" integrity="sha512-TW5s0IT/IppJtu76UbysrBH9Hy/5X41OTAbQuffZFU6lQ1rdcLHzpU5BzVvr/YFykoiMYZVWlr/PX1mDcfM9Qg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <div class="main-inner">
    <h3>B2Webshop Sales Chart</h3><br><br>

    <h5>Sales per Day / Month - Orders</h5>
    <select name="year" id="year_day">
        <option value="">Select year</option>
        <?php for ($i=$data_year; $i <= date('Y'); $i++) { 
            ?>
            <option value="<?php echo $i; ?>" <?php if(date('Y')==$i){ echo "selected=''";} ?>><?php echo $i; ?></option>
            <?php   
        }  ?>

    </select>
    <select name="month" id="month_day">
        <option value="">Select Month</option>
        <option value="01" <?php if(date('m')=='01'){ echo "selected=''";} ?>>January</option>
        <option value="02" <?php if(date('m')=='02'){ echo "selected=''";} ?>>February</option>
        <option value="03" <?php if(date('m')=='03'){ echo "selected=''";} ?>>March</option>
        <option value="04" <?php if(date('m')=='04'){ echo "selected=''";} ?>>April</option>
        <option value="05" <?php if(date('m')=='05'){ echo "selected=''";} ?>>May</option>
        <option value="06" <?php if(date('m')=='06'){ echo "selected=''";} ?>>June</option>
        <option value="07" <?php if(date('m')=='07'){ echo "selected=''";} ?>>July</option>
        <option value="08" <?php if(date('m')=='08'){ echo "selected=''";} ?>>August</option>
        <option value="09" <?php if(date('m')=='09'){ echo "selected=''";} ?>>September</option>
        <option value="10" <?php if(date('m')=='10'){ echo "selected=''";} ?>>October</option>
        <option value="11" <?php if(date('m')=='11'){ echo "selected=''";} ?>>November</option>
        <option value="12" <?php if(date('m')=='12'){ echo "selected=''";} ?>>December</option>
    </select>
    <canvas id="lineChart" style="width:100%;max-width:600px;max-height:300px"></canvas><br><br>

    <h5>Sales per Day / Month - Revenues</h5>
    <select name="year" id="year_day_rev">
        <option value="">Select year</option>
        <?php for ($i=$data_year; $i <= date('Y'); $i++) { 
            ?>
            <option value="<?php echo $i; ?>" <?php if(date('Y')==$i){ echo "selected=''";} ?>><?php echo $i; ?></option>
            <?php   
        }  ?>

    </select>
    <select name="month" id="month_day_rev">
        <option value="01" <?php if(date('m')=='01'){ echo "selected=''";} ?>>January</option>
        <option value="02" <?php if(date('m')=='02'){ echo "selected=''";} ?>>February</option>
        <option value="03" <?php if(date('m')=='03'){ echo "selected=''";} ?>>March</option>
        <option value="04" <?php if(date('m')=='04'){ echo "selected=''";} ?>>April</option>
        <option value="05" <?php if(date('m')=='05'){ echo "selected=''";} ?>>May</option>
        <option value="06" <?php if(date('m')=='06'){ echo "selected=''";} ?>>June</option>
        <option value="07" <?php if(date('m')=='07'){ echo "selected=''";} ?>>July</option>
        <option value="08" <?php if(date('m')=='08'){ echo "selected=''";} ?>>August</option>
        <option value="09" <?php if(date('m')=='09'){ echo "selected=''";} ?>>September</option>
        <option value="10" <?php if(date('m')=='10'){ echo "selected=''";} ?>>October</option>
        <option value="11" <?php if(date('m')=='11'){ echo "selected=''";} ?>>November</option>
        <option value="12" <?php if(date('m')=='12'){ echo "selected=''";} ?>>December</option>
    </select>
    <canvas id="rev_lineChart" style="width:100%;max-width:600px;max-height:300px"></canvas><br><br>

    <h5>Sales per Month / Year - Orders</h5>
    <select name="year" id="year">
        <option value="">Select year</option>
        <?php for ($i=$data_year; $i <= date('Y'); $i++) { 
            ?>
            <option value="<?php echo $i; ?>" <?php if(date('Y')==$i){ echo "selected=''";} ?>><?php echo $i; ?></option>
            <?php   
        }  ?>

    </select>
    <canvas id="barChart" style="width:100%;max-width:600px;max-height:300px"></canvas><br><br>

    <h5>Sales per Month / Year - Revenues</h5>   
    <select name="year_rev" id="year_rev">
        <option value="">Select year</option>
        <?php for ($i=$data_year; $i <= date('Y'); $i++) { 
            ?>
            <option value="<?php echo $i; ?>" <?php if(date('Y')==$i){ echo "selected=''";} ?>><?php echo $i; ?></option>
            <?php   
        }  ?>

    </select>
    <canvas id="barChart_rev" style="width:100%;max-width:600px;max-height:300px"></canvas>

    <br>
    <h5>Best Selling Products</h5>   

    <table id="vat_setting_list" class="table table-bordered table-style">
        <thead>
            <tr>
            <th>Name </th>
            <th>SKU </th>
            <th>Barcode </th>
            <th>Total sales </th>
            <th>Total Revenue</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($best_selling_products as  $item) { 
        ?>
            <tr>
                <td><?php echo $item['product_name'] ?></td>
                <td><?php echo $item['sku'] ?></td>
                <td><?php echo $item['barcode'] ?></td>
                <td><?php echo $item['sales'] ?></td>
                <td><?php echo $user_shop_details->currency_symbol.' '.$item['sales_revenue'] ?></td>
            </tr>

        <?php 
        } 
        ?>
             
        </tbody>
    </table>
  </div>
</main>

<script type="text/javascript">
$(document).ready(function(e) {
    // Sales per Month / Year - Orders
        const barChart_ctx = document.getElementById('barChart');
        const my_barChart = new Chart(barChart_ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Des'],
                datasets: [{
                    label: 'B2 Sales per Month / Year - Orders',
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(25, 226, 46, 0.2)',
                        'rgba(255, 306, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(154, 162, 25, 0.2)',
                        'rgba(15, 36, 86, 0.2)',
                        'rgba(115, 136, 86, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(125, 109, 44, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(25, 226, 46, 1)',
                        'rgba(255, 306, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(154, 162, 25, 1)',
                        'rgba(15, 36, 86, 1)',
                        'rgba(115, 136, 86, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(125, 109, 44, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
                

        $('#year').change(function(e) {

            var year = $('#year').val();           
            $.ajax({
                type: "POST",
                url: "<?php echo base_url('ReportController/b2webshop_chart_Report_ajax'); ?>",
                data: {
                    year:year 
                },
                success: function(data) {
                response = jQuery.parseJSON(data);
                my_barChart.data.datasets[0].data = response.data; 
                my_barChart.update();
                // console.log(response.data);
                }  

            });
        }); 

    // Sales per Month / Year - Revenues
        const barChart_rev_ctx = document.getElementById('barChart_rev');
        const my_barChart_rev = new Chart(barChart_rev_ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Des'],
                datasets: [{
                    label: 'B2 Sales per Month / Year - Revenues',
                    data: <?php echo json_encode($data_rev); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(25, 226, 46, 0.2)',
                        'rgba(255, 306, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(154, 162, 25, 0.2)',
                        'rgba(15, 36, 86, 0.2)',
                        'rgba(115, 136, 86, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(125, 109, 44, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(25, 226, 46, 1)',
                        'rgba(255, 306, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(154, 162, 25, 1)',
                        'rgba(15, 36, 86, 1)',
                        'rgba(115, 136, 86, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(125, 109, 44, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
                

        $('#year_rev').change(function(e) {

            var year = $('#year_rev').val();
            $.ajax({
                type: "POST",
                url: "<?php echo base_url('ReportController/b2webshop_chart_Report_ajax_rev'); ?>",
                data: {
                    year:year 
                },
                success: function(data) {
                response = jQuery.parseJSON(data);
                my_barChart_rev.data.datasets[0].data = response.data_rev; 
                my_barChart_rev.update();
                // console.log(response.data);
                }  

            });
        });  

    // Sales per Month / day - sales
        const lineChart_ctx = document.getElementById('lineChart');
        const my_lineChart = new Chart(lineChart_ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($data_by_day_dates); ?>,
                datasets: [{
                    label: 'B2 Sales per Day / Month - Orders',
                    data: <?php echo json_encode($data_by_day_sales); ?>,
                    fill: false,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });  

        $('#year_day, #month_day').change(function(e) {
            // alert('working');
            var year = $('#year_day').val();    
            var month = $('#month_day').val();           

            $.ajax({
                type: "POST",
                url: "<?php echo base_url('ReportController/b2webshop_line_chart_Report_ajax'); ?>",
                data: {
                    year:year,
                    month:month
                },
                success: function(data) {
                response = jQuery.parseJSON(data);
                my_lineChart.data.labels = response.data_date; 
                my_lineChart.data.datasets[0].data = response.data; 
                my_lineChart.update();
                // console.log(response.data);
                }  

            });
        }); 

    // Sales per Month / day - revenue
        const rev_lineChart_ctx = document.getElementById('rev_lineChart');
        const rev_my_lineChart = new Chart(rev_lineChart_ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($data_by_day_dates_rev); ?>,
                datasets: [{
                    label: 'B2 Sales per Day / Month - Revenue <?php echo $user_shop_details->currency_symbol;?>',
                    data: <?php echo json_encode($data_by_day_sales_rev); ?>,
                    fill: false,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });  

        $('#year_day_rev, #month_day_rev').change(function(e) {
            // alert('working');
            var year = $('#year_day_rev').val();    
            var month = $('#month_day_rev').val();           

            $.ajax({
                type: "POST",
                url: "<?php echo base_url('ReportController/b2webshop_line_chart_Report_ajax_rev'); ?>",
                data: {
                    year:year,
                    month:month
                },
                success: function(data) {
                response = jQuery.parseJSON(data);
                rev_my_lineChart.data.labels = response.data_date; 
                rev_my_lineChart.data.datasets[0].data = response.data; 
                rev_my_lineChart.update();
                // console.log(response.data);
                }  

            });
        });       

});
</script>

    
    
<script type="text/javascript" src="<?php echo SKIN_JS; ?>webshop_chart_Report.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>