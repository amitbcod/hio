<?php if(empty($ReportList)){ ?>
<B>No Records Found.</B>
<?php } else { ?>

    <table id="DataTables_Report_list" class="table table-bordered table-style">
        <thead>
            <tr>
                <!-- <th>
Redeem Date. Voucher Code, Order #, Customer Name, Email, Amount that was Redeem, Voucher Actual Amount
                    <input type="checkbox" id="ckbCheckAllSP"> Select All
                </th> -->
                <th>Redeem Date </th>
                <th>Voucher Code </th>
                <th>Order # </th>
                <th>Customer Type </th>
                <th>Customer Name </th>
                <th>Email </th>
                <th>Amount Redeem </th>
                <th>Status</th>
                <!-- <th>Voucher Amount </th> -->
            </tr>
        </thead>
        <tbody>
            <?php if(is_array($ReportList)): ?>
            <?php foreach ($ReportList as $review) {
                if($review['customer_id'] == 0){
					$review['customer_login_type']='Not Logged In';
				}
            //  echo "<pre>", print_r($review); echo "</pre>";
            //  echo date("d/m/Y" ,$review['created_at']); ?>
            <tr>
                <!-- <td><input type="checkbox"  name="chk_sp[]" value="<?php //echo $review['id']; ?>" > </td> -->
                <!-- <td><a href="<?php //echo base_url().'seller/product/edit/'.$review['product_id'];?>"><?php //echo $review['name']; ?></a></td> -->
                <!-- <td><a href="<?php //echo base_url().'customer-details/'.$review['customer_id'];?>"><?php //echo $review['first_name']. ' '.$review['last_name']; ?></a></td> -->

                <td>
                    <?php if(isset($review['created_at']) && $review['created_at'] !='') { echo date("d/m/Y" ,$review['created_at']); } ?>
                </td>
                <td><?php echo $review['voucher_code']; ?></td>
                <td><?php echo $review['increment_id']; ?></td>
                <td><?php echo $review['customer_login_type']; ?></td>
                <td><?php echo $review['customer_firstname']. ' '.$review['customer_lastname']; ?></td>
                <td><?php echo $review['customer_email']; ?></td>
                <!-- <td> <?php // echo $currency_code.' '.number_format($review['discount_amount'],2);?></td> -->
                <!-- <td> <?php //echo $currency_code.' '.number_format($review['voucher_amount'],2);?></td> -->
                <td> <?php echo "&#x20b9;".' '.number_format($review['voucher_amount'],2);?></td>
                <td>
                    <?php
                    $status_id = $review['status'];
                    $status_value = $this->ReportModel->getStatus($status_id);
                    echo  $status_value;
                    ?>
                </td>

            </tr>
            <?php } ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php } ?>

<!-- //model\\ -->
