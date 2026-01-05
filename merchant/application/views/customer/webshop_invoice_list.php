<table id="DataTables_invoice_list" class="table table-bordered table-style">
      <thead>
        <tr>
          <th>Invoice  Number </th>
          <th>Invoiced  On  </th>
          <th>Status </th>
          <th>Invoice To </th>
          <th>Resend </th>
          <th>Details </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($InvoiceGenerateList as  $invoiceList) {
		  //$order_id=$order['order_id'];
			/*if(($order['parent_id']==0  && $order['main_parent_id']==0) && !in_array($order['status'],array('4','5','6'))){
						  $order_url=base_url().'webshop/order/detail/'.$order_id;
					  }else if(($order['parent_id']==0  && $order['main_parent_id']==0) && in_array($order['status'],array('4','5','6'))){
						   $order_url=base_url().'webshop/shipped-order/detail/'.$order_id;
					  }else if($order->parent_id>0){
						   $order_url=base_url().'webshop/split-order/detail/'.$order_id;
					  }else{
						  $order_url=base_url().'webshop/customers';
					  }*/
            $pdf_url='';
            if($invoiceList['invoice_file']){
		          $pdf_url='<a class="link-purple" target="_blank" href="'.get_s3_url('invoices/'.$invoiceList['invoice_file']).'">View</a>';
              //<a class="link-purple" target="_blank" href="">View</a>
            }
            $resendStatus="No";
            $invoide_date='';
            if($invoiceList['resent_flag']==1){
              $resendStatus="Yes";
            }
            if($invoiceList['invoice_date']){
              $invoide_date=date(SIS_DATE_FM,$invoiceList['invoice_date']);
            }
		?>
        <tr>
          <td><?php echo $invoiceList['invoice_no']; ?></td>
          <td><?php echo $invoide_date;?></td>
          <td><?php  //echo $this->CommonModel->getOrderStatusLabel($order['status']); ?></td>
          <td><?php echo $invoiceList['customer_first_name'].' '.$invoiceList['customer_last_name']; ?></td>
          <td><?php echo $resendStatus; ?></td>
          <td><?php echo $pdf_url; ?></td>
        </tr>
        <?php } ?>
      </tbody>
</table>
