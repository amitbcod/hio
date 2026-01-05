<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

      <ul class="nav nav-pills">

            <!-- <li><a href="<?= base_url('merchants') ?>">Merchants</a></li> -->

            <li class="active"><a>Edit Merchant</a></li>

      </ul>

      <div class="main-inner min-height-480">

            <form id="publisherForm" method="POST" action="<?php echo base_url('PublisherController/submitMerchant'); ?>" enctype="multipart/form-data">

                  <input type="hidden" name="publisher_id" value="<?= $publisher->id ?>">

                  <input type="hidden" name="passwordCheck" value="">

                  <input type="hidden" name="type" value="1">
                  <div class="variant-common-block variant-list">

                  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

					<h1 class="head-name pad-bottom-20">Merchants</h1>

					<div class="float-right">

						<?php if ($_SESSION["LoginID"]) { ?> <!-- <button class="purple-btn" data-toggle="modal" data-target="#change_pass_modal" type="button">Change Password</button> -->

							<button class="purple-btn" type="button" onclick="OpenPasswordChangePopup();">Change Password</button>

						<?php } ?>

					</div>



				</div>

                        <div class="form-group row">

                              <label for="variant_name" class="col-sm-2 col-form-label font-500">Email</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter email id', 'id' => "email", 'name' => 'email', 'value' => "$publisher->email", 'required' => 'true', 'readonly' => 'true']);  ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <!-- <label for="" class="col-sm-2 col-form-label font-500">Edit Password</label>

                        <div class="col-sm-6">

                        <label class="checkbox">

						<input type="checkbox" id="passwordCheck"  name="passwordCheck" value="check" >  <span class="checked"></span>

						</label>

                        </div> -->

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <!-- <label for="" class="col-sm-2 col-form-label font-500"></label>

                        <div class="col-sm-3">

                        <?php echo form_password(['class' => 'form-control', 'placeholder' => 'Enter password', 'id' => "password", 'name' => 'password', 'value' => "$publisher->password", 'required' => 'true', 'disabled' => 'true']); ?>

                        </div> -->

                        </div><!-- form-group -->



                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Merchant Name*</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter publication name', 'id' => "publication_name", 'name' => 'publication_name', 'value' => "$publisher->publication_name", 'required' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->



                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Vendor Name*</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter vendor name', 'id' => "vendor_name", 'name' => 'vendor_name', 'value' => "$publisher->vendor_name", 'required' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->



                        

                        <!-- New Shop Image Field -->

                        <div class="form-group row">

                              <label for="shop_image" class="col-sm-2 col-form-label font-500">Shop Image</label>

                              <div class="col-sm-3">

                                    <input type="file" name="shop_image" id="shop_image" class="form-control">

                                    <?php if(!empty($publisher->shop_image)) { ?>

                                          <img src="<?= base_url('public/images/shop_images/'.$publisher->shop_image) ?>" alt="Shop Image" style="margin-top:10px; max-width:150px;">

                                    <?php } ?>

                              </div>

                        </div><!-- form-group -->



                        <div class="form-group row">

                        <label for="merchant_cat" class="col-sm-2 col-form-label font-500">Business Category*</label>

                        <div class="col-sm-3">

                              <select name="merchant_cat" id="merchant_cat" class="form-control" required>

                                    <option value="">-- Select your Business Category --</option>

                                    <option value="Apps - Software" 

                                    <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Apps - Software") ? 'selected' : ''; ?>>

                                    Apps - Software

                                    </option>

                                    <option value="Arts, Crafts, And Sewing" 

                                    <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Arts, Crafts, And Sewing") ? 'selected' : ''; ?>>

                                    Arts, Crafts, And Sewing

                                    </option>

                                    <option value="Auto Accessories" 

                                    <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Auto Accessories") ? 'selected' : ''; ?>>

                                    Auto Accessories

                                    </option>

                                    <option value="Auto and Parts" 

                                    <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Auto and Parts") ? 'selected' : ''; ?>>

                                    Auto and Parts

                                    </option>

                                    <option value="Baby Supplies" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Baby Supplies") ? 'selected' : ''; ?>>Baby Supplies</option>

                                    <option value="Beauty and Cosmetics" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Beauty and Cosmetics") ? 'selected' : ''; ?>>Beauty and Cosmetics</option>

                                    <option value="Beverages" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Beverages") ? 'selected' : ''; ?>>Beverages</option>

                                    <option value="Books" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Books") ? 'selected' : ''; ?>>Books</option>

                                    <option value="Computers" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Computers") ? 'selected' : ''; ?>>Computers</option>

                                    <option value="Computers Accessories" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Computers Accessories") ? 'selected' : ''; ?>>Computers Accessories</option>

                                    <option value="Consumer Electronics" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Consumer Electronics") ? 'selected' : ''; ?>>Consumer Electronics</option>

                                    <option value="Electronics - Audio and Video" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Electronics - Audio and Video") ? 'selected' : ''; ?>>Electronics - Audio and Video</option>

                                    <option value="Electronics - Network - Wireless" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Electronics - Network - Wireless") ? 'selected' : ''; ?>>Electronics - Network - Wireless</option>

                                    <option value="Electronics - Wearables" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Electronics - Wearables") ? 'selected' : ''; ?>>Electronics - Wearables</option>

                                    <option value="Fashion Accessories" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Fashion Accessories") ? 'selected' : ''; ?>>Fashion Accessories</option>

                                    <option value="Food and Groceries" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Food and Groceries") ? 'selected' : ''; ?>>Food and Groceries</option>

                                    <option value="Furnitures" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Furnitures") ? 'selected' : ''; ?>>Furnitures</option>

                                    <option value="Games" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Games") ? 'selected' : ''; ?>>Games</option>

                                    <option value="Garden" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Garden") ? 'selected' : ''; ?>>Garden</option>

                                    <option value="Health" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Health") ? 'selected' : ''; ?>>Health</option>

                                    <option value="Home Appliances" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Home Appliances") ? 'selected' : ''; ?>>Home Appliances</option>

                                    <option value="Home Deco" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Home Deco") ? 'selected' : ''; ?>>Home Deco</option>

                                    <option value="Household Essentials" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Household Essentials") ? 'selected' : ''; ?>>Household Essentials</option>

                                    <option value="Jewellery" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Jewellery") ? 'selected' : ''; ?>>Jewellery</option>

                                    <option value="Kids Clothing" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Kids Clothing") ? 'selected' : ''; ?>>Kids Clothing</option>

                                    <option value="Kids Products" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Kids Products") ? 'selected' : ''; ?>>Kids Products</option>

                                    <option value="Kitchen Equipment" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Kitchen Equipment") ? 'selected' : ''; ?>>Kitchen Equipment</option>

                                    <option value="Luggage - Bags" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Luggage - Bags") ? 'selected' : ''; ?>>Luggage - Bags</option>

                                    <option value="Media" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Media") ? 'selected' : ''; ?>>Media</option>

                                    <option value="Medical" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Medical") ? 'selected' : ''; ?>>Medical</option>

                                    <option value="Men's Fashion" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Men's Fashion") ? 'selected' : ''; ?>>Men's Fashion</option>

                                    <option value="Mobile Phones" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Mobile Phones") ? 'selected' : ''; ?>>Mobile Phones</option>

                                    <option value="Office Equipment" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Office Equipment") ? 'selected' : ''; ?>>Office Equipment</option>

                                    <option value="Office Stationery" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Office Stationery") ? 'selected' : ''; ?>>Office Stationery</option>

                                    <option value="Outdoor Equipment" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Outdoor Equipment") ? 'selected' : ''; ?>>Outdoor Equipment</option>

                                    <option value="Personal Care" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Personal Care") ? 'selected' : ''; ?>>Personal Care</option>

                                    <option value="Pet Products" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Pet Products") ? 'selected' : ''; ?>>Pet Products</option>

                                    <option value="Shoes" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Shoes") ? 'selected' : ''; ?>>Shoes</option>

                                    <option value="Sports and Fitness" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Sports and Fitness") ? 'selected' : ''; ?>>Sports and Fitness</option>

                                    <option value="Tools and Home Improvement" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Tools and Home Improvement") ? 'selected' : ''; ?>>Tools and Home Improvement</option>

                                    <option value="Toys and Hobbies" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Toys and Hobbies") ? 'selected' : ''; ?>>Toys and Hobbies</option>

                                    <option value="Travel Gear" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Travel Gear") ? 'selected' : ''; ?>>Travel Gear</option>

                                    <option value="Virtual" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Virtual") ? 'selected' : ''; ?>>Virtual</option>

                                    <option value="Women's Fashion" <?php echo (!empty($publisher->merchant_cat) && $publisher->merchant_cat == "Women's Fashion") ? 'selected' : ''; ?>>Women's Fashion</option>

                              </select>

                        </div>

                        </div><!-- form-group -->



                           <div class="form-group row">
    <label for="shipment_type" class="col-sm-2 col-form-label font-500">Shipment Type *</label>
    <div class="col-sm-3">
        <?php if (!empty($publisher->shipment_type)) : ?>
            <!-- Show readonly style like normal input -->
            <input type="text" class="form-control" value="<?= ($publisher->shipment_type == '1') ? 'Own Delivery' : 'YM Delivery'; ?>" readonly>
            <input type="hidden" name="shipment_type" value="<?= $publisher->shipment_type; ?>">
            <small class="text-muted">Shipment type cannot be changed once selected.</small>
        <?php else : ?>
            <!-- First time selection -->
            <select name="shipment_type" id="shipment_type" class="form-control" required>
                <option value="">-- Select Shipment Type --</option>
                <option value="1">Own Delivery</option>
                <option value="2">YM Delivery</option>
            </select>
        <?php endif; ?>
    </div>
</div><!-- form-group -->






                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Commission % *</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'number', 'placeholder' => 'Enter commission', 'id' => "commision_percent", 'name' => 'commision_percent', 'value' => "$publisher->commision_percent", 'required' => 'true', 'readonly' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->



                        <!-- New VAT Section -->

                        <div class="form-group row">

                        <label for="vat_status" class="col-sm-2 col-form-label font-500">VAT Status *</label>

                        <div class="col-sm-3">

                              <select name="vat_status" id="vat_status" class="form-control" required>

                                    <option value="">-- Select VAT Status --</option>

                                    <option value="registered" <?= ($publisher->vat_status == 'registered') ? 'selected' : '' ?>>Registered</option>

                                    <option value="exempted" <?= ($publisher->vat_status == 'exempted') ? 'selected' : '' ?>>Exempted</option>

                              </select>

                        </div>

                        </div><!-- form-group -->



                        <div id="vat_details" style="display: none;">

                        <div class="form-group row">

                              <label for="vat_no" class="col-sm-2 col-form-label font-500">VAT No *</label>

                              <div class="col-sm-3">

                                    <?php echo form_input([

                                    'class' => 'form-control',

                                    'type' => 'text',

                                    'placeholder' => 'Enter VAT Number',

                                    'id' => "vat_no",

                                    'name' => 'vat_no',

                                    'value' => "$publisher->vat_no"

                                    ]); ?>

                              </div>

                        </div><!-- form-group -->



                        <div class="form-group row">

                              <label for="vat_percent" class="col-sm-2 col-form-label font-500">VAT % *</label>

                              <div class="col-sm-3">

                                    <?php echo form_input([

                                    'class' => 'form-control',

                                    'type' => 'number',

                                    'step' => '0.01',

                                    'placeholder' => 'Enter VAT Percentage',

                                    'id' => "default_vat_percentage",

                                    'name' => 'default_vat_percentage',

                                    'value' => "$publisher->default_vat_percentage"

                                    ]); ?>

                              </div>

                        </div><!-- form-group -->

                        </div>



                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">BRN Number</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'placeholder' => 'Enter BRN Number', 'id' => "brn_no", 'name' => 'brn_no', 'value' => "$publisher->brn_no", 'required' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->



                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Phone No*</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'tel',  'placeholder' => 'Enter phone no', 'id' => "phone_no", 'name' => 'phone_no', 'value' => "$publisher->phone_no", 'required' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Landline No</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'number', 'pattern' => "[0-9]{3}[0-9]{3}[0-9]{4}", 'maxlength' => "12", 'placeholder' => 'Enter landline no', 'id' => "landline_no", 'name' => 'landline_no', 'value' => "$publisher->landline_no"]); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Description</label>

                              <div class="col-sm-3">

                                    <?php echo form_textarea(['class' => 'form-control', 'placeholder' => 'Enter description', 'name' => 'description', 'value' => "$publisher->description"]); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Company Name*</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Company Name', 'id' => "company_name", 'name' => 'company_name', 'value' => "$publisher->company_name", 'required' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Location*</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Location', 'id' => "location", 'name' => 'location', 'value' => "$publisher->location", 'required' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Company Address*</label>

                              <div class="col-sm-3">

                                    <?php echo form_textarea(['class' => 'form-control', 'placeholder' => 'Enter Company Address', 'name' => 'company_address', 'value' => "$publisher->company_address", 'required' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->



                        <div class="form-group row">

                              <label for="state" class="col-sm-2 col-form-label font-500">State*</label>

                              <div class="col-sm-3">

                                    <select name="state" id="state" class="form-control" required>

                                          <option value="">-- Select State --</option>

                                          <?php foreach ($state as $s): ?>

                                                <option value="<?php echo $s['id']; ?>"

                                                      <?php echo (!empty($publisher->state) && $publisher->state == $s['id']) ? 'selected' : ''; ?>>

                                                      <?php echo $s['state_name']; ?>

                                                </option>

                                          <?php endforeach; ?>

                                    </select>



                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <label for="city" class="col-sm-2 col-form-label font-500">City*</label>

                              <div class="col-sm-3">

                                    <select name="city" id="city" placeholder="Select City" class="form-control" required>

                                          <option value="">-- Select city --</option>

                                          <?php foreach ($city as $c): ?>

                                          <option value="<?php echo $c['id']; ?>" 

                                                <?php echo (!empty($publisher->city) && $publisher->city == $c['id']) ? 'selected' : ''; ?>>

                                                <?php echo $c['city_name']; ?>

                                          </option>

                                          <?php endforeach; ?>

                                    </select>

                              </div>

                        </div><!-- form-group -->



                        <div class="form-group row">

                              <label for="" class="col-sm-2 col-form-label font-500">Zipcode*</label>

                              <div class="col-sm-3">

                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'maxlength' => "12", 'placeholder' => 'Enter zip code', 'id' => "zipcode", 'name' => 'zipcode', 'value' => "$publisher->zipcode", 'required' => 'true']); ?>

                              </div>

                        </div><!-- form-group -->

                        <div class="form-group row">

                              <!-- <label for="" class="col-sm-2 col-form-label font-500">Status</label> -->

                              <div class="col-sm-3">

                                    <?php  //$status = $publisher->status; 

                                    ?>

                                    <input type="hidden" name="status" value="1">

                                    <!-- <label>Active</label><br>

                            <input type="radio" name="status" value="0" <?php if ($status == 0) {

                                                                              echo "checked";

                                                                        } ?>>

                            <label>In Active</label> -->

                              </div>

                        </div><!-- form-group -->

                        <div class="download-discard-small pos-ab-bottom">

                              <button class="white-btn" type="button" data-dismiss="modal" onclick="window.location.href='<?= base_url('DashboardController/index') ?>';">Discard</button>

                              <button type="submit" class="download-btn">Update</button>

                        </div><!-- download-discard-small -->

                  </div><!-- -common-block -->

                   <h1 class="head-name pad-bottom-20">Bank Details</h1>
                          <div class="form-group row">
                              <label for="" class="col-sm-2 col-form-label font-500">Bank Name*</label>
                              <div class="col-sm-3">
                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Bank Name', 'name' => 'bank_name', 'required' => 'true', 'value' => $publisher_payment_details->bank_name ?? '']); ?>
                              </div>
                        </div>
                           <div class="form-group row">
                              <label for="" class="col-sm-2 col-form-label font-500">Bank Branch No.*</label>
                              <div class="col-sm-3">
                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Bank Branch No', 'name' => 'bank_branch_number','required' => 'true', 'value' => $publisher_payment_details->bank_branch_number ?? '']); ?>
                              </div>
                        </div>
                        <div class="form-group row">
                              <label for="" class="col-sm-2 col-form-label font-500"> Bank Swift Code*</label>
                              <div class="col-sm-3">
                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Bank Swift Code', 'name' => 'beneficiary_ifsc_code','required' => 'true', 'value' => $publisher_payment_details->beneficiary_ifsc_code ?? '']); ?>
                              </div>
                        </div>
                        <div class="form-group row">
                              <label for="" class="col-sm-2 col-form-label font-500">Beneficiary Acc No.*</label>
                              <div class="col-sm-3">
                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Beneficiary Acc No', 'name' => 'beneficiary_acc_no','required' => 'true', 'value' => $publisher_payment_details->beneficiary_acc_no ?? '']); ?>
                              </div>
                        </div>
                        <div class="form-group row">
                              <label for="" class="col-sm-2 col-form-label font-500">Beneficiary Name*</label>
                              <div class="col-sm-3">
                                    <?php echo form_input(['class' => 'form-control', 'type' => 'text', 'placeholder' => 'Enter Beneficiary Name', 'name' => 'beneficiary_name','required' => 'true', 'value' => $publisher_payment_details->beneficiary_name ?? '']); ?>
                              </div>
                        </div>
                      

                  <div class="div">
                  <h1 class="head-name pad-bottom-20">My Documents</h1>

                       <div class="form-group form-setion-new">
                              <label for="">Delivery Policy</label>
                              <input type="file" id="delivery_policy" name="delivery_policy" class="form-control">

                              <?php if (!empty($publisher->delivery_policy)) : ?>
                                    <?php 
                                          $ext = pathinfo($publisher->delivery_policy, PATHINFO_EXTENSION);
                                          $fileUrl = BASE_URL2 . '/uploads/delivery_policy/' . $publisher->delivery_policy;

                                          if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) : 
                                    ?>
                                          <img src="<?= $fileUrl ?>" alt="Delivery Policy" style="margin-top:10px; max-width:150px;">
                                    <?php else: ?>
                                          <a href="<?= $fileUrl ?>" target="_blank" style="display:block; margin-top:10px;">
                                          <i class="fa fa-download fa-fw" style="margin-left:46px;"></i>
                                          </a>
                                    <?php endif; ?>
                              <?php endif; ?>
                        </div>

                        <div class="form-group form-setion-new">
                              <label for="">Return Policy</label>
                              <input type="file" id="return_policy" name="return_policy" class="form-control">

                              <?php if (!empty($publisher->return_policy)) : ?>
                                    <?php 
                                          $ext = pathinfo($publisher->return_policy, PATHINFO_EXTENSION);
                                          $fileUrl = BASE_URL2 . '/uploads/return_policy/' . $publisher->return_policy;

                                          if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) : 
                                    ?>
                                          <img src="<?= $fileUrl ?>" alt="Return Policy" style="margin-top:10px; max-width:150px;">
                                    <?php else: ?>
                                          <a href="<?= $fileUrl ?>" target="_blank" style="display:block; margin-top:10px;">
                                          <i class="fa fa-download fa-fw" style="margin-left:46px;"></i>
                                          </a>
                                    <?php endif; ?>
                              <?php endif; ?>
                        </div>

                        <div class="form-group form-setion-new">
                              <label for="">Refund Policy</label>
                              <input type="file" id="refund_policy" name="refund_policy" class="form-control">

                              <?php if (!empty($publisher->refund_policy)) : ?>
                                    <?php 
                                          $ext = pathinfo($publisher->refund_policy, PATHINFO_EXTENSION);
                                          $fileUrl = BASE_URL2 . '/uploads/refund_policy/' . $publisher->refund_policy;

                                          if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) : 
                                    ?>
                                          <img src="<?= $fileUrl ?>" alt="Refund Policy" style="margin-top:10px; max-width:150px;">
                                    <?php else: ?>
                                          <a href="<?= $fileUrl ?>" target="_blank" style="display:block; margin-top:10px;">
                                          <i class="fa fa-download fa-fw" style="margin-left:46px;"></i>
                                          </a>
                                    <?php endif; ?>
                              <?php endif; ?>
                        </div>

                        <div class="form-group form-setion-new">
                              <label for="">Banner Image</label>
                              <input type="file" id="banner_img" name="banner_img" class="form-control">

                              <?php if (!empty($publisher->banner_img)) : ?>
                                    <?php 
                                          $ext = pathinfo($publisher->banner_img, PATHINFO_EXTENSION);
                                          $fileUrl = BASE_URL2 . '/uploads/banner_img/' . $publisher->banner_img;

                                    ?>
                                    <img src="<?= $fileUrl ?>" alt="Banner Image" style="margin-top:10px; max-width:150px;">
                                    
                              <?php endif; ?>
                        </div>


                  </div>

      </div><!-- add new tab -->

      </div>

      </div>

</main>

<?php $this->load->view('common/fbc-user/footer'); ?>

<script src="<?php echo SKIN_JS; ?>publisher.js"></script>

<script>

document.addEventListener("DOMContentLoaded", function() {

    const vatStatus = document.getElementById("vat_status");

    const vatDetails = document.getElementById("vat_details");



    function toggleVatFields() {

        if (vatStatus.value === "registered") {

            vatDetails.style.display = "block";

        } else {

            vatDetails.style.display = "none";

        }

    }



    vatStatus.addEventListener("change", toggleVatFields);



    // Trigger on page load if already selected

    toggleVatFields();

});

</script>