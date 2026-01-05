<table id="DataTables_Table_Customer_order_list" class="table table-bordered table-style">
      <thead>
        <tr>
          <th>Order Number </i></th>
          <th>Purchased On  </i></th>
          <th>Customer Name </th>
          <th>Status </th>
          <th>Payment Method </th>
          <th>Invoice</th>
          <!-- <th>Details </th> -->
        </tr>
      </thead>
      <tbody>
        <?php foreach ($customer_order_details as  $order) { 
		$order_id=$order['order_id'];
			if(($order['parent_id']==0  && $order['main_parent_id']==0) && !in_array($order['status'],array('4','5','6'))){
						  $order_url=base_url().'webshop/order/detail/'.$order_id;
					  }else if(($order['parent_id']==0  && $order['main_parent_id']==0) && in_array($order['status'],array('4','5','6'))){
						   $order_url=base_url().'webshop/shipped-order/detail/'.$order_id;
					  }else if($order->parent_id>0){
						   $order_url=base_url().'webshop/split-order/detail/'.$order_id;
					  }else{
						  $order_url=base_url().'webshop/customers';
					  }
		
		?>
        <tr>
          <td><?php echo $order['increment_id']; ?></td>
          <td><?php echo date(SIS_DATE_FM,$order['created_at']);?></td>
          <td><?php echo $order['customer_firstname'].' '.$order['customer_lastname']; ?></td>
          <td><?php  echo $this->CommonModel->getOrderStatusLabel($order['status']); ?></td>
          <td><?php echo isset($order['payment_method_name']) ? $order['payment_method_name'] : 'Voucher' ; ?></td>
          <td>
          <?php 
          if ($order['invoice_no'] != '') {
            echo $order['invoice_no'];
          }else{
            if ($order['invoice_self'] == 0) {
              echo 'Not Requested';
            }else{
              echo ' ';
            }
            
          } ?>
          </td>
          <!-- <td><a class="link-purple" target="_blank" href="<?php echo $order_url; ?>">View</a></td> -->
        </tr>
        <?php } ?>
      </tbody>
</table>