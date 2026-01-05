<table id="DataTables_Table_Customer_return_list" class="table table-bordered table-style">
      <thead>
        <tr>
          <th>Return Number </i></th>
          <th>Purchased On  </i></th>
          <th>Customer Name </th>
          <th>Requested On</th>
          <th>Status </th>
          <th>Payment Method </th>
          <th>Details </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($customer_return_order_list as  $return_data) { 
		 
        ?>
        <tr>
          <td><?php echo $return_data['return_order_increment_id']; ?></td>
          <td><?php echo date(SIS_DATE_FM,$return_data['order_created_at']);?></td>
          <td><?php echo $return_data['customer_name']; ?></td>
          <td><?php echo date(SIS_DATE_FM,$return_data['created_at']);?></td>
          <td><?php  echo $this->CommonModel->getReturnOrderStatusLabel($return_data['status']); ?></td>
          <td><?php echo $this->CommonModel->getPaymentTypeLabel($return_data['payment_type']) ; ?></td>
          <td><a class="link-purple" target="_blank" href="<?php echo base_url(); ?>webshop/return-request-order/detail/<?php  echo $return_data['return_order_id']; ?>">View</a></td>
        </tr>
        <?php } ?>
      </tbody>
</table>