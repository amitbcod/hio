<?php
    if(isset($shipping_address_data[0]->customer_address_id) && $shipping_address_data[0]->customer_address_id == 0) {
        $shipping_checked = 'checked';
    } else if(isset($shipping_address_data[0]->customer_address_id) && $shipping_address_data[0]->customer_address_id > 0) {
        $shipping_checked = '';
    } else if($this->session->userdata('LoginID') && (empty($addressList) && count($addressList) <= 0)) {
        $shipping_checked = 'checked';
    } else {
        $shipping_checked = '';
    }

    if(isset($shipping_address_data[0]->customer_address_id) && $shipping_address_data[0]->customer_address_id > 0) {
        $shipping_aadr_div = 'd-none';
    } else if(isset($shipping_address_data[0]->customer_address_id) && $shipping_address_data[0]->customer_address_id == 0) {
        $shipping_aadr_div = '';
    } else if(is_array($addressList) && count($addressList) > 0) {
        $shipping_aadr_div = 'd-none';
    } else {
        $shipping_aadr_div = '';
    }
?>
<div class="card  <?php //echo ($this->session->userdata('LoginID'))?'active':'';?>">
    <div class="card-header" id="shipping-address">
        <h2 class="mb-0 <?php echo (THEMENAME=='theme_zumbawear')?'tw-relative':''; ?>">
            <button type="button" disabled id="ship-add-tab" class="btn btn-link " data-toggle="collapse"
                data-target="#collapseTwoNew">
                <i class="<?php echo (THEMENAME=='theme_zumbawear')?'fa fa-circle-chevron-down':'fa icon-plus'; ?>"></i>
                <span class="counter-no">2</span> Shipping Address</button>
        </h2>
    </div>
    <div id="collapseTwoNew" class="collapse <?php //echo ($this->session->userdata('LoginID'))?'show':'';?>"
        aria-labelledby="shipping-address" data-parent="#checkout-accordion">
        <div class="card-body <?php echo (THEMENAME=='theme_zumbawear')?'tw-p-6':''; ?>">

            <?php if ($this->session->userdata('LoginID')) { ?>

            <?php if (is_array($addressList) && count($addressList) > 0) { ?>
            <?php foreach ($addressList as $list) {?>
            <div class="ship-address">
                <h4><?=lang('ship_to_this_address')?></h4>

                <div class="shipping-user-details">
                    <label class="radio-label-checkout"><input class="radio-checkout" type="radio"
                            name="address_options"
                            onclick="SetAddressId(this.value,'<?php echo $list->country;  ?>','<?php echo COUNTRY_CODE; ?>');"
                            value="<?php echo $list->id; ?>" <?php
                            if (isset($shipping_address_data) && count($shipping_address_data) > 0) {
                                if ($shipping_address_data[0]->customer_address_id == $list->id) {
                                    echo 'checked';
                                }
                            }else{
                                if($list->is_default == 0){}else{echo 'checked';}
                            }
                        ?>> <?= $list->first_name.' '.$list->last_name?>
                        <span class="radio-check"></span></label>
                    <div class="shipping-address-user">
                        <p><?= $list->address_line1 ?>
                            <br><?= $list->city.', '.$list->state.' - '.$list->pincode.' '.$list->country; ?>
                        </p>
                        <?php if ($list->mobile_no != '') {?>
                        <span class="shipping-user-no"><?=lang('mo')?> <?php echo $list->mobile_no; ?></span>
                        <?php } ?>
                    </div><!-- shipping-address -->

                </div>
            </div><!-- ship-address -->

            <?php } ?>
            <?php } ?>
            <div class="ship-address">
                <div class="shipping-user-details">
                    <label class="radio-label-checkout"><input class="radio-checkout" type="radio"
                            name="address_options" value="new" onclick="SetAddressId(this.value,'','');"
                            <?php echo $shipping_checked; ?>>Add New
                        <span class="radio-check"></span></label>
                </div>

            </div><!-- ship-address -->

            <?php } else {  ?>
            <div class="shipping-user-details d-none">
                <label class="radio-label-checkout"><input class="radio-checkout" type="radio" name="address_options"
                        value="new" checked><?=lang('add_new')?>
                    <span class="radio-check"></span></label>
            </div>
            <?php } ?>


            <?php if ($this->session->userdata('LoginID')) { ?>
            <p
                class="or  custom-address-shipping  <?php echo (is_array($addressList) && count($addressList) > 0)?'d-none':''; ?>">
                - OR -</p>
            <?php } ?>

            <div class="ship-address-form col-sm-12  custom-address-shipping  <?php echo $shipping_aadr_div; ?>">
                <input type="hidden" id="allowed_ship_country" name="allowed_ship_country"
                    value="<?php echo (isset($ShipToCountry) && $ShipToCountry->value!='')?$ShipToCountry->value:''; ?>">
                <?php
                    $shipping_first_name='';
                    $shipping_last_name='';
                    $shipping_mobile_no='';
                    $shipping_address_add='';
                    $shipping_address_1='';
                    $shipping_country='';
                    $shipping_pincode='';
                    $shipping_state='';
                    $s_state_dp='';
                    $shipping_city='';

                    if (isset($shipping_address_data) && count($shipping_address_data) > 0) {
                        $shipping_address=$shipping_address_data[0];

                        $shipping_first_name=$shipping_address->first_name;
                        $shipping_last_name=$shipping_address->last_name;
                        $shipping_mobile_no=$shipping_address->mobile_no;
                        $shipping_address_add=$shipping_address->address_line1;
                        $shipping_address_1=$shipping_address->address_line2;
                        $shipping_country=$shipping_address->country;
                        $shipping_pincode=$shipping_address->pincode;
                        $shipping_state=$shipping_address->state;
                        $s_state_dp=$shipping_address->state;
                        $shipping_city=$shipping_address->city;

                    }

                ?>
                <div class="row">
                    <div class="row tw-w-full">
                        <div class="col-sm-6 line-1">
                            <input type="text" class="form-control" placeholder="Fist Name*" name="shipping_first_name"
                                value="<?php echo $shipping_first_name; ?>" id="shipping_first_name">
                        </div><!-- col-sm-6 -->
                        <div class="col-sm-6 line-2">
                            <input type="text" class="form-control" placeholder="Last Name*" name="shipping_last_name"
                                value="<?php echo $shipping_last_name; ?>" id="shipping_last_name">
                        </div><!-- col-sm-6 -->
                        <div class="col-sm-6 line-1">
                            <input type="text" class="form-control" placeholder="Mobile Number*"
                                name="shipping_mobile_no" minlength="10" maxlength="10"
                                value="<?php echo $shipping_mobile_no; ?>" id="shipping_mobile_no">
                        </div><!-- col-sm-6 -->
                    </div><!-- first block -->

                    <div class="row tw-w-full">
                        <div class="col-sm-6 line-2">
                            <input type="text" class="form-control" placeholder="Adddress Line*" name="shipping_address"
                                value="<?php echo $shipping_address_add; ?>" id="shipping_address" maxlength="35">
                        </div><!-- col-sm-6 -->

                        <div class="col-sm-6 line-1">
                            <input type="text" class="form-control" placeholder="Address Line2"
                                name="shipping_address_1" value="<?php echo $shipping_address_1; ?>"
                                id="shipping_address_1" maxlength="35">
                        </div><!-- col-sm-6 -->

                        <div class="col-sm-6 line-1">
                            <input type="text" class="form-control" placeholder="Zipcode*" name="shipping_pincode"
                                value="<?php echo $shipping_pincode; ?>" id="shipping_pincode" maxlength="50">
                        </div><!-- col-sm-6 -->
                        <div class="col-sm-6 line-1">
                            <input type="text" class="form-control" placeholder="City*" name="shipping_city"
                                value="<?php echo $shipping_city; ?>" id="shipping_city">
                        </div><!-- col-sm-6 -->

                        <div class="col-sm-6 line-2">

                            <select class="form-control" name="shipping_country" id="shipping_country">
                                <option value="">Select Country</option>
                                <?php
							if (isset($ShipToCountry) && $ShipToCountry->value!='') {
								$selected_ship=explode(',', $ShipToCountry->value);
								$selected_ship=array_filter($selected_ship);
							} else {
								$selected_ship=array();
							}

							if (isset($countryList) && count($countryList)>0) {
								foreach ($countryList as $value) {
									if (is_array($selected_ship) && count($selected_ship)>0 && in_array($value->country_code, $selected_ship)) { ?>
                                <option value="<?php echo $value->country_code; ?>"
                                    <?php if($value->country_code==$shipping_country){echo "selected";} ?>>
                                    <?php echo $value->country_name; ?></option>
                                <?php }  }

								if (empty($selected_ship) || count($selected_ship)<=0) {
									foreach ($countryList as $value) { ?>
                                <option value="<?php echo $value->country_code; ?>"
                                    <?php if($value->country_code==$shipping_country){echo "selected";} ?>>
                                    <?php echo $value->country_name; ?></option>
                                <?php
									}
								}
							}
							?>
                            </select>
                        </div><!-- col-sm-6 -->
                        <div class="col-sm-6 line-2 s_state_div">
                            <input type="text" class="form-control" placeholder="<?=lang('state_suburb')?>"
                                name="shipping_state" value="<?php echo $shipping_state; ?>" id="shipping_state">
                        </div><!-- col-sm-6 -->
                        <div class="col-sm-6 line-2 s_state_dp_div">
                            <select name="s_state_dp" id="s_state_dp" class="form-control">
                                <option value="">Select State</option>
                                <?php if (isset($stateList) && count($stateList)>0) {
								foreach ($stateList as $value) {
									?>
                                <option value="<?php echo $value->state_name; ?>"
                                    <?php if($value->state_name==$shipping_state){echo "selected";} ?>>
                                    <?php echo $value->state_name; ?></option>
                                <?php
								}
							}?>
                            </select>
                        </div><!-- col-sm-6 -->
                    </div><!-- second block -->
                    <?php if ($this->session->userdata('LoginID')) { ?>
                    <div class="col-sm-12 line-1">
                        <label class="checkbox-label-checkout"><input class="checkbox-checkout" type="checkbox"
                                id="save_in_address_book" name="save_in_address_book" value="1">&nbsp; Save In address
                            book
                            <span class="checkbox-check"></span></label>
                    </div><!-- col-sm-6 -->
                    <?php } ?>

                </div>
            </div><!-- ship-address form -->


            <div class="checkout-btn">
                <button class="black-btn tw-button-zumbared" type="button"
                    id="address-save"><?php echo ($this->session->userdata('LoginID'))? 'Save this address & Continue':'Continue'; ?></button>
            </div><!-- checkout-btn -->

        </div>
    </div>
</div><!-- card -->