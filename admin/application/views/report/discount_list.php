<?php if(empty($DiscountList)){ ?>
<B>No Records Found.</B>
<?php } else { ?>

    <table id="DataTables_Discount_list" class="table table-bordered table-style">
        <thead>
            <tr>
                <!-- <th>
                    <input type="checkbox" id="ckbCheckAllSP"> Select All
                </th> -->
                <th>Redeem Date </th>
                <th>Coupon Code </th>
                <th>Order # </th>
                <th>Customer Type </th>
                <th>Customer Name </th>
                <th>Email </th>
                <th>Discount Redeem </th>
                <th>Status </th>
                <!-- <th>Voucher Amount </th> -->
            </tr>
        </thead>
        <tbody>
            <?php if(is_array($DiscountList)): ?>
            <?php foreach ($DiscountList as $review) {

				if($review['customer_id'] == 0){
					$review['customer_login_type']='Not Logged In';
				}

            //  echo date("d/m/Y" ,$review['created_at']); ?>
            <tr>
                <td>
                    <?php if(isset($review['created_at']) && $review['created_at'] !='') { echo date("d/m/Y" ,$review['created_at']); } ?>
                </td>
                <td><?php echo $review['coupon_code']; ?></td>
                <td><?php echo $review['increment_id']; ?></td>
                <td><?php echo $review['customer_login_type']; ?></td>
                <td><?php echo $review['customer_firstname']. ' '.$review['customer_lastname']; ?></td>
                <td><?php echo $review['customer_email']; ?></td>
                <!-- <td> <?php //echo $currency_code.' '.number_format($review['discount_amount'],2);?></td> -->
                <td> <?php echo "&#x20b9;".' '.number_format($review['discount_amount'],2);?></td>
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
