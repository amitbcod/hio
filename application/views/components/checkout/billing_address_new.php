<?php

if (isset($billing_address_data[0]->customer_address_id) && $billing_address_data[0]->customer_address_id == 0) {

    $Billing_checked = 'checked';

} else if (isset($billing_address_data[0]->customer_address_id) && $billing_address_data[0]->customer_address_id > 0) {

    $Billing_checked = '';

} else if ($this->session->userdata('LoginID') && (empty($addressList) && count($addressList) <= 0)) {

    $Billing_checked = 'checked';

} else {

    $Billing_checked = '';

}



if (isset($billing_address_data[0]->customer_address_id) && $billing_address_data[0]->customer_address_id > 0) {

    $Billing_aadr_div = 'd-none';

} else if (isset($billing_address_data[0]->customer_address_id) && $billing_address_data[0]->customer_address_id == 0) {

    $Billing_aadr_div = '';

} else if (is_array($addressList) && count($addressList) > 0) {

    $Billing_aadr_div = 'd-none';

} else {

    $Billing_aadr_div = '';

}

?>

<div id="payment-address" class="panel panel-default">

    <form method="POST" name="order-frm" id="order-frm" action="<?php echo base_url(); ?>CheckoutController/placeorder">

        <input type="hidden" name="quote_id" id="quote_id" value="<?php echo $QuoteId; ?>">

        <input type="hidden" name="temp_bill_country" id="temp_bill_country" value="">

        <input type="hidden" class="common_vat_no" name="vat_no" id="vat_no" value="">

        <input type="hidden" name="hidden_company_name" id="hidden_company_name" value="">

        <input type="hidden" class="consulation_no" name="consulation_no" id="consulation_no" value="">

        <input type="hidden" class="res_company_name" name="res_company_name" id="res_company_name" value="">

        <input type="hidden" class="res_company_address" name="res_company_address" id="res_company_address" value="">

        <?php if (empty($this->session->userdata('LoginID'))) { ?>

            <input type="hidden" name="checkout_method" id="checkout_method" value="guest">

        <?php } else {  ?>

            <input type="hidden" name="checkout_method" id="checkout_method" value="login">

        <?php  }  ?>

        <div class="panel-heading <?php echo ($this->session->userdata('LoginID')) ? 'active' : ''; ?>">

            <h2 class="panel-title <?php echo (THEMENAME == 'theme_zumbawear') ? 'tw-relative' : ''; ?>">

                <button type="button" disabled id="bill-add-tab" class="btn accordion-toggle co-btn" data-toggle="collapse" data-target="#collapseTwo">

                    <i class="<?php echo (THEMENAME == 'theme_zumbawear') ? 'fa fa-circle-chevron-down' : 'fa icon-plus'; ?>"></i>

                    <span class="counter-no"></span> <?php echo lang('account_billing_details'); ?>

                </button>

                <!-- <a data-toggle="collapse" data-parent="#checkout-page" href="#payment-address-content" class="accordion-toggle">

            Step 2: Account &amp; Billing Details

        </a> -->

            </h2>

        </div>

        <div id="collapseTwo" class="panel-collapse collapse <?php echo ($this->session->userdata('LoginID')) ? 'show' : ''; ?>">

            <div class="panel-body">

                <div class="row">

                    <?php if ($this->session->userdata('LoginID')) { ?>



                        <?php if (is_array($addressList) && count($addressList) > 0) { ?>

                            <?php if (isset($restricted_access) && $restricted_access == 'yes') { ?>

                                <?php foreach ($addressList as $list) {

                                    if ($list->is_default == 1) {       ?>

                                        <div class=" col-md-4 col-sm-6">

                                            <div class=" addrblock">

                                                <div class="addrblock-head">

                                                    <label class="radio-label-checkout"><input class="radio-checkout" type="radio" data-country="<?php echo $list->country ?>" name="billing_address_options" onclick="SetBillingAddressId(this.value);SetBillingCountry(this.value,'<?php echo $list->country;  ?>,'<?php echo $list->vat_no ?? '' ?>','<?php echo $list->company_name ?? '' ?>');" value="<?php echo $list->id; ?>" <?php

                                                                                                                                                                                                                                                                                                                                                                                                                                        if (isset($billing_address_data) && count($billing_address_data) > 0) {

                                                                                                                                                                                                                                                                                                                                                                                                                                            if ($billing_address_data[0]->customer_address_id == $list->id) {

                                                                                                                                                                                                                                                                                                                                                                                                                                                echo 'checked';

                                                                                                                                                                                                                                                                                                                                                                                                                                            }

                                                                                                                                                                                                                                                                                                                                                                                                                                        } else {

                                                                                                                                                                                                                                                                                                                                                                                                                                            if ($list->is_default == 0) {

                                                                                                                                                                                                                                                                                                                                                                                                                                            } else {

                                                                                                                                                                                                                                                                                                                                                                                                                                                echo 'checked';

                                                                                                                                                                                                                                                                                                                                                                                                                                            }

                                                                                                                                                                                                                                                                                                                                                                                                                                        }

                                                                                                                                                                                                                                                                                                                                                                                                                                        ?>> <?= $list->first_name . ' ' . $list->last_name ?>

                                                        <span class="radio-check"></span></label>

                                                </div>

                                                <div class="addrblock-body">

                                                    <div class="shipping-address-user">

                                                        <p><?= $list->address_line1 ?><br> <?= $list->address_line2 ?>

                                                            <br><?= $list->city . ', ' . $list->state . ' - ' . $list->pincode . ' ' . $list->country; ?>

                                                        </p>

                                                        <?php if ($list->mobile_no != '') { ?>

                                                            <span class="shipping-user-no"><?= lang('mo') ?> <?php echo $list->mobile_no; ?></span>

                                                        <?php } ?>

                                                    </div><!-- shipping-address -->

                                                </div>



                                                <div class="checkout-company-details-addr-profile">

                                                    <p class="full-address"><?= lang('vat_no') ?> <?= $list->vat_no ?? '-' ?></p>

                                                    <p class="full-address"><?= lang('company_name') ?> <?= $list->company_name ?? '-' ?></p>

                                                    <?php $cust_id =  ((!empty($this->session->userdata('LoginID'))) ? $this->session->userdata('LoginID') : 0); ?>

                                                </div>



                                            </div>

                                        </div><!-- ship-address -->



                                    <?php } ?>

                                <?php

                                } ?>

                            <?php } else { ?>

                                <?php foreach ($addressList as $list) { ?>

                                    <div class="col-md-4 col-sm-6">

                                        <div class="addrblock">

                                            <div class="addrblock-head">

                                                <label class="radio-label-checkout"><input class="radio-checkout" data-country="<?php echo $list->country ?>" type="radio" name="billing_address_options" onclick="SetBillingAddressId(this.value);SetBillingCountry(this.value,'<?php echo $list->country;  ?>','<?php echo $list->vat_no ?>','<?php echo $list->company_name ?>');" value="<?php echo $list->id; ?>" <?php

                                                                                                                                                                                                                                                                                                                                                                                                                        if (isset($billing_address_data) && count($billing_address_data) > 0) {

                                                                                                                                                                                                                                                                                                                                                                                                                            if ($billing_address_data[0]->customer_address_id == $list->id) {

                                                                                                                                                                                                                                                                                                                                                                                                                                echo 'checked';

                                                                                                                                                                                                                                                                                                                                                                                                                            }

                                                                                                                                                                                                                                                                                                                                                                                                                        } else {

                                                                                                                                                                                                                                                                                                                                                                                                                            if ($list->is_default == 0) {

                                                                                                                                                                                                                                                                                                                                                                                                                            } else {

                                                                                                                                                                                                                                                                                                                                                                                                                                echo 'checked';

                                                                                                                                                                                                                                                                                                                                                                                                                            }

                                                                                                                                                                                                                                                                                                                                                                                                                        }

                                                                                                                                                                                                                                                                                                                                                                                                                        ?>> <?= $list->first_name . ' ' . $list->last_name ?>

                                                    <span class="radio-check"></span></label>

                                            </div>

                                            <div class="addrblock-body">

                                                <div class="shipping-address-user">

                                                    <p><?= $list->address_line1 ?><br><?= $list->address_line2 ?>

                                                        <br><?= $list->city . ', ' . $list->state . ' - ' . $list->pincode . ' ' . $list->country; ?>

                                                    </p>

                                                    <?php if ($list->mobile_no != '') { ?>

                                                        <span class="shipping-user-no"><?= lang('mo') ?><?php echo $list->mobile_no; ?></span>

                                                    <?php } ?>

                                                </div><!-- shipping-address -->

                                            </div>



                                            <div class="checkout-company-details-addr-profile">

                                                <?php if ($list->vat_no != '') {

                                                ?>

                                                    <p class="full-address"><?= lang('vat_no') ?> <?= (($list->vat_no != '') ? $list->vat_no : '-') ?>

                                                    </p>

                                                <?php

                                                }

                                                ?>

                                                <?php if ($list->company_name != '') {

                                                ?>

                                                    <p class="full-address"><?= lang('company_name') ?>

                                                        <?= (($list->company_name != '') ? $list->company_name : '-') ?></p>

                                                <?php

                                                }

                                                ?>

                                            </div>





                                        </div>

                                    </div><!-- ship-address -->

                                <?php } ?>

                            <?php   } ?>



                        <?php } ?>

                        <?php //if (!isset($restricted_access) || (isset($restricted_access) && $restricted_access == 'no')) { 

                        ?>

                        <div class="col-md-12">

                            <label class="radio-label-checkout addnewradio"><input class="radio-checkout " type="radio" name="billing_address_options" value="new" onclick="SetBillingAddressId(this.value);" <?php echo $Billing_checked; ?>><?php echo lang('add_new_address'); ?>

                                <span class="radio-check"></span>

                            </label>

                        </div><!-- ship-address -->

                        <?php //} 

                        ?>



                    <?php } else {  ?>

                        <div class="shipping-user-details d-none">

                            <label class="radio-label-checkout addnewradio"><input class="radio-checkout " type="radio" name="billing_address_options" value="new" checked><?php echo lang('add_new'); ?>

                                <span class="radio-check"></span></label>

                        </div>

                    <?php } ?>



                    <div class="col-md-12">

                        <?php if ($this->session->userdata('LoginID')) { ?>

                            <p class="or  custom-address-billing  <?php echo (is_array($addressList) && count($addressList) > 0) ? 'd-none' : ''; ?>">

                                <?php echo lang('or_text'); ?></p>

                        <?php } ?>

                        <div class="row">

                            <div class="col-md-6 custom-address-billing <?php echo $Billing_aadr_div; ?>">

                                <?php

                                $billing_first_name = '';

                                $billing_last_name = '';

                                $billing_email_id = '';

                                $billing_mobile_no = '';

                                $billing_address = '';

                                $billing_address_1 = '';

                                $billing_country = '';

                                $billing_pincode = '';

                                $billing_state = '';

                                $b_state_dp = '';

                                $billing_city = '';

                                $company_name = '';

                                $vat_no = '';

                                $consulation_no = '';

                                $res_company_name = '';

                                $res_company_address = '';

                                if (isset($billing_address_data) && count($billing_address_data) > 0) {

                                    $bill_address = $billing_address_data[0];



                                    $billing_first_name = $bill_address->first_name;

                                    $billing_last_name = $bill_address->last_name;

                                    $billing_email_id = $quoteData->customer_email;

                                    $billing_mobile_no = $bill_address->mobile_no;

                                    $billing_address = $bill_address->address_line1;

                                    $billing_address_1 = $bill_address->address_line2;

                                    $billing_country = $bill_address->country;

                                    $billing_pincode = $bill_address->pincode;

                                    $billing_state = $bill_address->state;

                                    $b_state_dp = $bill_address->state;

                                    $billing_city = $bill_address->city;

                                    $company_name = $bill_address->company_name;

                                    $vat_no = $bill_address->vat_no;

                                    $consulation_no = $bill_address->consulation_no;

                                    $res_company_name = $bill_address->res_company_name;

                                    $res_company_address = $bill_address->res_company_address;

                                }



                                ?>

                                <h3><?php echo lang('your_personal_details'); ?></h3>

                                <div class="form-group">

                                    <label for="firstname"><?php echo lang('first_name'); ?> <span class="require">*</span></label>

                                    <input type="text" class="form-control" placeholder="<?php echo lang('first_name'); ?>*" name="billing_first_name" value="<?php echo $billing_first_name; ?>" id="billing_first_name">

                                </div>

                                <div class="form-group">

                                    <label for="lastname"><?php echo lang('last_name'); ?> <span class="require">*</span></label>

                                    <input type="text" class="form-control" placeholder="<?php echo lang('last_name'); ?>*" name="billing_last_name" value="<?php echo $billing_last_name; ?>" id="billing_last_name">

                                </div>

                                <div class="form-group">

                                    <label for="company_name"><?php echo lang('company_name'); ?> </label>

                                    <input type="text" class="form-control" placeholder="<?php echo lang('company_name'); ?>" name="company_name" value="<?php echo $company_name; ?>" id="company_name">

                                </div>

                                <?php if ($this->session->userdata('LoginID')) {

                                } else { ?>

                                    <div class="form-group <?php echo ($this->session->userdata('LoginID')) ? 'line-2' : 'line-1'; ?>">

                                        <label for="email"><?php echo lang('email'); ?> <span class="require">*</span></label>

                                        <input type="text" class="form-control" placeholder="<?php echo lang('email'); ?>*" name="billing_email_id" value="<?php echo $billing_email_id; ?>" id="billing_email_id">

                                    </div>

                                <?php } ?>

                                <div class="form-group <?php echo ($this->session->userdata('LoginID')) ? 'line-2' : 'line-1'; ?>">

                                    <label for="telephone"><?php echo lang('telephone'); ?> <span class="require">*</span></label>

                                    <input type="text" class="form-control" placeholder="<?php echo lang('mobile_number'); ?>*" name="billing_mobile_no" value="<?php echo $billing_mobile_no; ?>" id="billing_mobile_no">

                                </div>

                            </div>

                            <div class="col-md-6 custom-address-billing <?php echo $Billing_aadr_div; ?>">

                                <h3><?php echo lang('your_address'); ?></h3>

                                <!-- <div class="form-group">

                        <label for="company">Company</label>

                        <input type="text" id="company" class="form-control">

                        </div> -->

                                <div class="form-group <?php echo ($this->session->userdata('LoginID')) ? 'line-1' : 'line-2'; ?>">

                                    <label for="address1"><?php echo lang('address_line1'); ?></label>

                                    <input type="text" class="form-control" placeholder="<?php echo lang('address_line1'); ?>*" name="billing_address" value="<?php echo $billing_address; ?>" id="billing_address" maxlength="35">

                                </div>

                                <div class="form-group <?php echo ($this->session->userdata('LoginID')) ? 'line-1' : 'line-2'; ?>">

                                    <label for="address2"><?php echo lang('address_line2'); ?></label>

                                    <input type="text" class="form-control" placeholder="<?php echo lang('address_line2'); ?>*" name="billing_address_1" value="<?php echo $billing_address_1; ?>" id="billing_address_1" maxlength="35">

                                </div>



                                <div class="form-group  <?php echo ($this->session->userdata('LoginID')) ? 'line-2' : 'line-1'; ?>">

                                    <label for="country"><?php echo lang('country'); ?> <span class="require">*</span></label>

                                    <select class="form-control" name="billing_country" id="billing_country">

                                        <!-- <option value="">Select Country</option> -->

                                        <?php if (isset($countryList) && count($countryList) > 0) {

                                            foreach ($countryList as $value) {

                                                if ($value->country_code == 'MU') {

                                        ?>

                                                    <option value="<?php echo $value->country_code; ?>" selected <?php if ($value->country_code == $billing_country) {

                                                                                                                        echo "selected";

                                                                                                                    } ?>>

                                                        <?php echo $value->country_name; ?></option>

                                        <?php

                                                }

                                            }

                                        } ?>

                                    </select>

                                </div>

                                <div class="form-group <?php echo ($this->session->userdata('LoginID')) ? 'line-2' : 'line-1'; ?> b_state_div">

                                    <label for="region-state"><?php echo lang('region_state'); ?> <span class="require">*</span></label>

                                    <input type="text" class="form-control" placeholder="<?php echo lang('state_suburb'); ?>" name="billing_state" value="<?php echo $billing_state; ?>" id="billing_state">

                                </div>

                                <div class="form-group <?php echo ($this->session->userdata('LoginID')) ? 'line-2' : 'line-1'; ?> b_state_dp_div">

                                    <label for="region-state"><?php echo lang('region_state'); ?> <span class="require">*</span></label>

                                    <select name="b_state_dp" id="b_state_dp" class="form-control">

                                        <option value=""><?php echo lang('select_state'); ?></option>

                                        <?php if (isset($stateList) && count($stateList) > 0) {

                                            foreach ($stateList as $value) {  ?>

                                                <option value="<?php echo $value->id; ?>" <?php if ($value->state_name == $billing_state) {

                                                                                                        echo "selected";

                                                                                                    } ?>>

                                                    <?php echo $value->state_name; ?></option>

                                        <?php }

                                        } ?>

                                    </select>

                                </div>
                                 <div class="form-group <?php echo ($this->session->userdata('LoginID')) ? 'line-1' : 'line-2'; ?>">

                                    <label for="city"><?php echo lang('city'); ?> <span class="require">*</span></label>

                                    <select class="form-control" name="billing_city" id="billing_city">
                                        <option value=""><?php echo lang('select_city'); ?></option>
                                        <?php if (isset($cityList) && count($cityList) > 0) {
                                            foreach ($cityList as $city) { ?>
                                                <option value="<?php echo $city->id; ?>"
                                                        data-state="<?php echo $city->state_id; ?>"
                                                        <?php if ($city->id == $billing_city) echo "selected"; ?>>
                                                    <?php echo $city->city_name; ?>
                                                </option>
                                        <?php } } ?>
                                    </select>


                                </div>
                                <div class="form-group <?php echo ($this->session->userdata('LoginID')) ? 'line-1' : 'line-2'; ?>">

                                    <label for="post-code"><?php echo lang('post_code'); ?> <span class="require">*</span></label>

                                    <input type="text" class="form-control" placeholder="<?php echo lang('zipcode'); ?>*" name="billing_pincode" value="<?php echo $billing_pincode; ?>" id="billing_pincode" maxlength="6">

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-12">

                        <?php if ($this->session->userdata('LoginID')) { ?>

                            <div class="checkbox">

                                <label>

                                    <input class="checkbox-checkout" type="checkbox" id="billing_save_in_address_book" name="billing_save_in_address_book" value="1">&nbsp;<?php echo lang('save_in_address_book'); ?>

                                </label>

                            </div>

                        <?php } ?>

                        <div class="checkbox">

                            <label>

                                <input class="checkbox-checkout" type="checkbox" id="same_as_billing" name="same_as_billing" value="1" <?php

                                                                                                                                        if (isset($billing_address_data) && count($billing_address_data) > 0) {

                                                                                                                                            if ($same_as_billing == 1) {

                                                                                                                                                echo 'checked';

                                                                                                                                            }

                                                                                                                                        }

                                                                                                                                        ?>> <?php echo lang('delivery_same_as_billing'); ?>

                            </label>

                        </div>

                        <button class="btn btn-primary  pull-right" type="button" id="billing-address-save"><?php echo ($this->session->userdata('LoginID')) ? lang('save_continue') : lang('continue') ?></button>

                        <div class="checkbox pull-right">

                            <!-- <label>

                    <input type="checkbox"> I have read and agree to the <a title="Privacy Policy" href="javascript:;">Privacy Policy</a> &nbsp;&nbsp;&nbsp; 

                </label> -->

                        </div>

                    </div>

                </div>

            </div>

        </div>



</div>
