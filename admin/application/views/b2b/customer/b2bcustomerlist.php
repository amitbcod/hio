
<table class="table table-bordered table-style" id="DataTables_Table_B2BCustomerList">
    <thead>
            <tr>
                <th>Webshop Name </th>
                <th>Owner Name </th>
                <th>Address  </th>
                <th>Email ID </th>
                <th>Taxes Exempted</th>
                <th>Last Purchased On </th>
                <th>Total Purchase </th>
                <th>Details </th>
            </tr>
    </thead>
     <tbody>
          <?php foreach ($customer_listing as  $customer) {
          	if ($customer['tax_exampted'] == 1) {
          		$tax_exampted = "YES";
          	} elseif ($customer['tax_exampted'] == 2) {
          		$tax_exampted = "NO";
          	} else {
          		$tax_exampted = "NOT DEFINED";
          	}
          	?>
          			<tr>
                        <td><?php echo $customer['org_shop_name']; ?></td>
                        <td><?php echo $customer['owner_name']; ?></td>
                        <td><?php echo $customer['bill_state'].','.$customer['country_name']; ?></td>
                        <td><?php echo $customer['email']; ?></td>
                        <td><?php echo $tax_exampted; ?></td>
                        <td><?php echo date("m/d/Y | h:m A", $customer['created_at']);?></td>
                        <td><?php echo $customer['total_purchase']; ?></td>
                      <td><a class="link-purple" href="<?php echo base_url(); ?>B2BController/get_single_b2b_customer_details/<?php  echo $customer['shop_id'];?>">View</a></td>
                    </tr>
            <?php } ?>
     </tbody>
</table>
