<?php

if (isset($shipping_address_data[0]->customer_address_id) && $shipping_address_data[0]->customer_address_id == 0) {

    $shipping_checked = 'checked';

} else if (isset($shipping_address_data[0]->customer_address_id) && $shipping_address_data[0]->customer_address_id > 0) {

    $shipping_checked = '';

} else if ($this->session->userdata('LoginID') && (empty($addressList) && count($addressList) <= 0)) {

    $shipping_checked = 'checked';

} else {

    $shipping_checked = '';

}



if (isset($shipping_address_data[0]->customer_address_id) && $shipping_address_data[0]->customer_address_id > 0) {

    $shipping_aadr_div = 'd-none';

} else if (isset($shipping_address_data[0]->customer_address_id) && $shipping_address_data[0]->customer_address_id == 0) {

    $shipping_aadr_div = '';

} else if (is_array($addressList) && count($addressList) > 0) {

    $shipping_aadr_div = 'd-none';

} else {

    $shipping_aadr_div = '';

}

?>

<div id="shipping-address" class="panel panel-default">

    <div class="panel-heading">

        <h2 class="panel-title <?php echo (THEMENAME == 'theme_zumbawear') ? 'tw-relative' : ''; ?>">

            <button type="button" disabled id="ship-add-tab" class="btn accordion-toggle co-btn" data-toggle="collapse" data-target="#collapseTwoNew">

                <i class="<?php echo (THEMENAME == 'theme_zumbawear') ? 'fa fa-circle-chevron-down' : 'fa icon-plus'; ?>"></i>

                <span class="counter-no"></span> <?= lang('delivery_details') ?></button>

        </h2>

    </div>

    <div id="collapseTwoNew" class="panel-collapse collapse">

        <div class="panel-body">

            <div class="row">

                <?php if ($this->session->userdata('LoginID')) { ?>

                    <?php if (is_array($addressList) && count($addressList) > 0) { ?>

                        <?php foreach ($addressList as $list) { ?>

                            <div class="col-md-4 col-sm-6">

                                <div class=" addrblock">

                                    <div class="addrblock-head">

                                        <label class="radio-label-checkout">
                                            <input class="radio-checkout" type="radio" name="address_options" onclick="SetAddressId(this.value,'<?php echo $list->country;  ?>','<?php echo COUNTRY_CODE; ?>');" value="<?php echo $list->id; ?>" <?php
                                            if (isset($shipping_address_data) && count($shipping_address_data) > 0) {
                                                if ($shipping_address_data[0]->customer_address_id == $list->id) {
                                                    echo 'checked';
                                                }
                                            } else {
                                                if ($list->is_default == 0) {} else { echo 'checked'; }
                                            }
                                            ?>>
                                            <?= $list->first_name . ' ' . $list->last_name ?>
                                            <span class="radio-check"></span>
                                        </label>

                                    </div>

                                    <div class="addrblock-body">

                                        <div class="shipping-address-user">

                                            <p><?= $list->address_line1 ?><br><?= $list->address_line2 ?>
                                                <br><?= $list->city . ', ' . $list->state . ' - ' . $list->pincode . ' ' . $list->country; ?>
                                            </p>

                                            <?php if ($list->mobile_no != '') { ?>
                                                <span class="shipping-user-no"><?= lang('mo') ?> <?php echo $list->mobile_no; ?></span>
                                            <?php } ?>

                                        </div><!-- shipping-address -->

                                    </div>

                                </div>

                            </div><!-- ship-address -->

                        <?php } ?>

                    <?php } ?>

                    <div class="col-md-12">
                        <label class="radio-label-checkout addnewradio">
                            <input class="radio-checkout " type="radio" name="address_options" value="new" onclick="SetAddressId(this.value,'','');" <?php echo $shipping_checked; ?>><?= lang('add_new_address') ?>
                            <span class="radio-check"></span>
                        </label>
                    </div>

                <?php } else {  ?>

                    <div class="shipping-user-details d-none">
                        <label class="radio-label-checkout addnewradio">
                            <input class="radio-checkout" type="radio" name="address_options" value="new" checked><?= lang('add_new') ?>
                            <span class="radio-check"></span>
                        </label>
                    </div>

                <?php } ?>

                <div class="col-md-12">

                    <?php if ($this->session->userdata('LoginID')) { ?>
                        <p class="or  custom-address-shipping  <?php echo (is_array($addressList) && count($addressList) > 0) ? 'd-none' : ''; ?>">
                            <?= lang('or_text') ?>
                        </p>
                    <?php } ?>

                    <div class="row">

                        <div class="col-md-6 ship-address-form custom-address-shipping  <?php echo $shipping_aadr_div; ?>">

                            <input type="hidden" id="allowed_ship_country" name="allowed_ship_country" value="<?php echo (isset($ShipToCountry) && $ShipToCountry->value != '') ? $ShipToCountry->value : ''; ?>">

                            <?php
                            $shipping_first_name = '';
                            $shipping_last_name = '';
                            $shipping_mobile_no = '';
                            $shipping_address_add = '';
                            $shipping_address_1 = '';
                            $shipping_country = '';
                            $shipping_pincode = '';
                            $shipping_state = '';
                            $s_state_dp = '';
                            $shipping_city = '';

                            if (isset($shipping_address_data) && count($shipping_address_data) > 0) {
                                $shipping_address = $shipping_address_data[0];

                                $shipping_first_name = $shipping_address->first_name;
                                $shipping_last_name = $shipping_address->last_name;
                                $shipping_mobile_no = $shipping_address->mobile_no;
                                $shipping_address_add = $shipping_address->address_line1;
                                $shipping_address_1 = $shipping_address->address_line2;
                                $shipping_country = $shipping_address->country;
                                $shipping_pincode = $shipping_address->pincode;
                                $shipping_state = $shipping_address->state;
                                $s_state_dp = $shipping_address->state;
                                $shipping_city = $shipping_address->city;
                            }
                            ?>

                            <h3><?= lang('your_personal_details') ?></h3>

                            <div class="form-group">
                                <label for="firstname-dd"><?= lang('first_name') ?> <span class="require">*</span></label>
                                <input type="text" class="form-control" placeholder="<?= lang('first_name') ?>*" name="shipping_first_name" value="<?php echo $shipping_first_name; ?>" id="shipping_first_name">
                            </div>

                            <div class="form-group">
                                <label for="lastname-dd"><?= lang('last_name') ?> <span class="require">*</span></label>
                                <input type="text" class="form-control" placeholder="<?= lang('last_name') ?>*" name="shipping_last_name" value="<?php echo $shipping_last_name; ?>" id="shipping_last_name">
                            </div>

                            <div class="form-group">
                                <label for="company_name"><?= lang('company_name') ?></label>
                                <input type="text" class="form-control" placeholder="<?= lang('company_name') ?>" name="Shippingcompany_name" value="<?php echo $company_name; ?>" id="Shippingcompany_name">
                            </div>

                            <div class="form-group">
                                <label for="telephone-dd"><?= lang('mobile_number') ?> <span class="require">*</span></label>
                                <input type="text" class="form-control" placeholder="<?= lang('mobile_number') ?>*" name="shipping_mobile_no" value="<?php echo $shipping_mobile_no; ?>" id="shipping_mobile_no">
                            </div>

                        </div>

                        <div class="col-md-6 custom-address-shipping  <?php echo $shipping_aadr_div; ?>">

                            <h3><?= lang('your_address') ?></h3>

                            <div class="form-group">
                                <label for="address1-dd"><?= lang('address_line1') ?></label>
                                <input type="text" class="form-control" placeholder="<?= lang('address_line1') ?>*" name="shipping_address" value="<?php echo $shipping_address_add; ?>" id="shipping_address" maxlength="35">
                            </div>

                            <div class="form-group">
                                <label for="address2-dd"><?= lang('address_line2') ?></label>
                                <input type="text" class="form-control" placeholder="<?= lang('address_line2') ?>" name="shipping_address_1" value="<?php echo $shipping_address_1; ?>" id="shipping_address_1" maxlength="35">
                            </div>

                            <div class="form-group">
                                <label for="country-dd"><?= lang('country') ?> <span class="require">*</span></label>
                                <select class="form-control" name="shipping_country" id="shipping_country">
                                    <?php
                                    if (isset($ShipToCountry) && $ShipToCountry->value != '') {
                                        $selected_ship = explode(',', $ShipToCountry->value);
                                        $selected_ship = array_filter($selected_ship);
                                    } else {
                                        $selected_ship = array();
                                    }

                                    if (isset($countryList) && count($countryList) > 0) {
                                        foreach ($countryList as $value) {
                                            if ($value->country_code == 'MU') {
                                                $selected = '';
                                                if (isset($shipping_country) && $shipping_country == 'MU') {
                                                    $selected = 'selected';
                                                }
                                                if (isset($selected_ship) && in_array('MU', $selected_ship)) {
                                                    $selected = 'selected';
                                                }
                                                ?>
                                                <option value="<?php echo $value->country_code; ?>" <?php echo $selected; ?>>
                                                    <?php echo $value->country_name; ?>
                                                </option>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group s_state_dp_div">
                                <label for="region-state-dd"><?= lang('select_state') ?> <span class="require">*</span></label>
                                <select name="s_state_dp" id="s_state_dp" class="form-control">
                                    <option value=""><?= lang('select_state') ?></option>
                                    <?php if (isset($stateList) && count($stateList) > 0) {
                                        foreach ($stateList as $value) { ?>
                                            <option value="<?php echo $value->id; ?>" <?php if ($value->state_name == $shipping_state) echo "selected"; ?>>
                                                <?php echo $value->state_name; ?>
                                            </option>
                                    <?php } } ?>
                                </select>
                            </div>

                            <div class="form-group s_state_div">
                                <label for="region-state-dd"><?= lang('region_state') ?> <span class="require">*</span></label>
                                <input type="text" class="form-control" placeholder="<?= lang('state_suburb') ?>" name="shipping_state" value="<?php echo $shipping_state; ?>" id="shipping_state">
                            </div>

                            <div class="form-group">
                                <label for="city"><?= lang('city') ?> <span class="require">*</span></label>
                                <select class="form-control" name="shipping_city" id="shipping_city">
                                    <option value=""><?= lang('select_city') ?></option>
                                    <?php if (isset($cityList) && count($cityList) > 0) {
                                        foreach ($cityList as $city) { ?>
                                            <option value="<?php echo $city->id; ?>" data-state="<?php echo $city->state_id; ?>" <?php if ($city->id == $shipping_city) echo "selected"; ?>>
                                                <?php echo $city->city_name; ?>
                                            </option>
                                    <?php } } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="post-code"><?= lang('zipcode') ?> <span class="require">*</span></label>
                                <input type="text" class="form-control" placeholder="<?= lang('zipcode') ?>*" name="shipping_pincode" value="<?php echo $shipping_pincode; ?>" id="shipping_pincode" maxlength="6">
                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-md-12">
                    <button class="btn-primary btn pull-right" type="button" id="address-save"><?= ($this->session->userdata('LoginID')) ? lang('save_continue') : 'Continue'; ?></button>
                </div>

            </div>

        </div>

    </div>

</div>
