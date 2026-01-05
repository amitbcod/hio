<table class="table table-bordered table-style" id="Datatable_special_pricing">
                  <thead>
                    <tr>
                      <th>
                         <input type="checkbox" id="ckbCheckAllSP"> Select All
                      </th>
                      <th>SKU </th>
                      <th>Product Name  </th>
                      <th>Variant  </th>
                      <th>Webshop <br>Price </th>
                      <th>Customer Type</th>
                      <th>Special Price </th>
                      <th>Start Date </th>
                      <th>End Date</th>
                      <th>Status </th>
                      <th>Action </th>
                    </tr>
                  </thead>
                  <tbody>

                    <?php if(isset($products_special_prices_info) && $products_special_prices_info !='' ) {
                     foreach ($products_special_prices_info as $key => $value) { ?>
                       <tr>
                          <td>
                            <label class="checkbox">
                              <input type="checkbox"  name="chk_sp[]" value="<?php echo $value->id; ?>" >
                              <span class="checked"></span>
                            </label>
                         </td>
                          <td><?php echo  $value->sku; ?> </td>
                          <td><?php echo  $value->name; ?></td>
                          <td><?php
                          if(isset($value->variant)){
                              foreach ($value->variant as $value1) {
                                echo $value1['attr_name'].' : '.$value1['attr_options_name'].'<br/>';
                              }
                          }else
                          {
                            echo "-";
                          }
                             ?>
                          </td>
                          <td><?php echo $currency_symbol.' '. $value->webshop_price; ?></td>
                          <td><?php
                        $customer_type= $this->CustomerModel->get_single_customer_type_details($value->customer_type_id);
                         print_r($customer_type['name']);
                          // echo  $value->customer_type_id; ?></td>
                          <td><?php echo $currency_symbol.' '. $value->special_price; ?></td>
                          <td><?php echo  date("d-m-Y", $value->special_price_from); ?></td>
                          <td><?php echo  date("d-m-Y", $value->special_price_to); ?></td>
                          <td><?php
                          $current_date= date("Y-m-d");
                          $from_date= date("Y-m-d", $value->special_price_from);
                          $to_date= date("Y-m-d", $value->special_price_to);
                          if($current_date >= $from_date && $current_date <=  $to_date )
                          {
                            echo "Active";
                          }elseif($to_date < $current_date)
                          {
                            echo "Inactive";
                          }elseif($from_date > $current_date)
                          {
                            echo "Upcomming";
                          }
                          ?></td>
                          <td><a class="link-purple" href="<?php echo base_url();?>webshop/edit-special-pricing/<?php if(isset($value->id) && $value->id !='') echo $value->id; ?>">View</a> / <a class="link-purple trash" data-toggle="modal" data-target="#deleteModalForRow" data-id="<?php if(isset($value->id) && $value->id !='') echo $value->id; ?>">Delete</a></td>
                        </tr>
                    <?php } } ?>

                  </tbody>
                </table>
