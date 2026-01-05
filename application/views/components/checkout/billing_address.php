<?php
    if(isset($billing_address_data[0]->customer_address_id) && $billing_address_data[0]->customer_address_id == 0) {
        $Billing_checked = 'checked';
    } else if(isset($billing_address_data[0]->customer_address_id) && $billing_address_data[0]->customer_address_id > 0) {
        $Billing_checked = '';
    } else if($this->session->userdata('LoginID') && (empty($addressList) && count($addressList) <= 0)) {
        $Billing_checked = 'checked';
    } else {
        $Billing_checked = '';
    }

    if(isset($billing_address_data[0]->customer_address_id) && $billing_address_data[0]->customer_address_id > 0) {
        $Billing_aadr_div = 'd-none';
    } else if(isset($billing_address_data[0]->customer_address_id) && $billing_address_data[0]->customer_address_id == 0) {
        $Billing_aadr_div = '';
    } else if(is_array($addressList) && count($addressList) > 0) {
        $Billing_aadr_div = 'd-none';
    } else {
        $Billing_aadr_div = '';
    }
?>
<div class="card  <?php echo ($this->session->userdata('LoginID'))?'active':''; ?>">
    <div class="card-header" id="billing-address">
        <h2 class="mb-0 <?php echo (THEMENAME=='theme_zumbawear')?'tw-relative':''; ?>">
            <button type="button" disabled id="bill-add-tab" class="btn btn-link " data-toggle="collapse"
                data-target="#collapseTwo">
                <i class="<?php echo (THEMENAME=='theme_zumbawear')?'fa fa-circle-chevron-down':'fa icon-plus'; ?>"></i>
                <span class="counter-no">1</span> Billing Address</button>
        </h2>
    </div>
    <div id="collapseTwo" class="collapse <?php echo ($this->session->userdata('LoginID'))?'show':''; ?>"
        aria-labelledby="billing-address" data-parent="#checkout-accordion">
        <div class="card-body <?php echo (THEMENAME=='theme_zumbawear')?'tw-p-6':''; ?>">

            <?php if ($this->session->userdata('LoginID')) { ?>

            <?php if (is_array($addressList) && count($addressList) > 0) { ?>
            <?php if (isset($restricted_access) && $restricted_access == 'yes') { ?>
            <?php foreach ($addressList as $list) {
    if ($list->is_default == 1) {       ?>
            <div class="ship-address">
                <div class="shipping-user-details">
                    <label class="radio-label-checkout"><input class="radio-checkout" type="radio"
                            data-country="<?php echo $list->country ?>" name="billing_address_options"
                            onclick="SetBillingAddressId(this.value);SetBillingCountry(this.value,'<?php echo $list->country;  ?>,'<?php echo $list->vat_no ?? '' ?>','<?php echo $list->company_name ?? '' ?>');"
                            value="<?php echo $list->id; ?>" <?php
                            if (isset($billing_address_data) && count($billing_address_data) > 0) {
                                if ($billing_address_data[0]->customer_address_id == $list->id) {
                                    echo 'checked';
                                }
                            }else{
                                if($list->is_default == 0){}else{echo 'checked';}
                            }
                        ?>> <?= $list->first_name.' '.$list->last_name ?>
                        <span class="radio-check"></span></label>
                    <div class="shipping-address-user">
                        <p><?= $list->address_line1 ?>
                            <br><?= $list->city.', '.$list->state.' - '.$list->pincode.' '.$list->country; ?>
                        </p>
                        <?php if ($list->mobile_no != '') {?>
                        <span class="shipping-user-no"><?=lang('mo')?> <?php echo $list->mobile_no; ?></span>
                        <?php } ?>
                    </div><!-- shipping-address -->

                    <div class="checkout-company-details-addr-profile">
                        <p class="full-address"><?=lang('vat_no')?> <?= $list->vat_no ?? '-' ?></p>
                        <p class="full-address"><?=lang('company_name')?> <?= $list->company_name ?? '-' ?></p>
                        <?php $cust_id =  ((!empty($this->session->userdata('LoginID'))) ? $this->session->userdata('LoginID') : 0); ?>
                    </div>

                </div>
            </div><!-- ship-address -->

            <?php } ?>
            <?php
} ?>
            <?php } else { ?>
            <?php foreach ($addressList as $list) { ?>
            <div class="ship-address">
                <div class="shipping-user-details">
                    <label class="radio-label-checkout"><input class="radio-checkout"
                            data-country="<?php echo $list->country ?>" type="radio" name="billing_address_options"
                            onclick="SetBillingAddressId(this.value);SetBillingCountry(this.value,'<?php echo $list->country;  ?>','<?php echo $list->vat_no ?>','<?php echo $list->company_name ?>');"
                            value="<?php echo $list->id; ?>" <?php
                                            if (isset($billing_address_data) && count($billing_address_data) > 0) {
                                                if ($billing_address_data[0]->customer_address_id == $list->id) {
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
                        <span class="shipping-user-no"><?=lang('mo')?><?php echo $list->mobile_no; ?></span>
                        <?php } ?>
                    </div><!-- shipping-address -->

                    <div class="checkout-company-details-addr-profile">
                        <p class="full-address"><?=lang('vat_no')?> <?= (($list->vat_no != '') ? $list->vat_no :'-') ?>
                        </p>
                        <p class="full-address"><?=lang('company_name')?>
                            <?= (($list->company_name != '') ? $list->company_name :'-') ?></p>

                    </div>


                </div>
            </div><!-- ship-address -->
            <?php } ?>
            <?php   } ?>

            <?php } ?>
            <?php //if (!isset($restricted_access) || (isset($restricted_access) && $restricted_access == 'no')) { ?>
            <div class="ship-address">
                <div class="shipping-user-details">
                    <label class="radio-label-checkout"><input class="radio-checkout" type="radio"
                            name="billing_address_options" value="new" onclick="SetBillingAddressId(this.value);"
                            <?php echo $Billing_checked; ?>>Add New
                        <span class="radio-check"></span></label>
                </div>

            </div><!-- ship-address -->
            <?php //} ?>

            <?php } else {  ?>
            <div class="shipping-user-details d-none">
                <label class="radio-label-checkout"><input class="radio-checkout" type="radio"
                        name="billing_address_options" value="new" checked><?=lang('add_new')?>
                    <span class="radio-check"></span></label>
            </div>
            <?php } ?>


            <?php if ($this->session->userdata('LoginID')) { ?>
            <p
                class="or  custom-address-billing  <?php echo (is_array($addressList) && count($addressList) > 0)?'d-none':''; ?>">
                - OR -</p>
            <?php } ?>

            <div class="ship-address-form col-sm-12  custom-address-billing  <?php echo $Billing_aadr_div; ?>">
                <?php
                    $billing_first_name='';
                    $billing_last_name='';
                    $billing_email_id='';
                    $billing_mobile_no='';
                    $billing_address='';
                    $billing_address_1='';
                    $billing_country='';
                    $billing_pincode='';
                    $billing_state='';
                    $b_state_dp='';
                    $billing_city='';
                    $company_name='';
                    $vat_no='';
                    $consulation_no='';
                    $res_company_name='';
                    $res_company_address='';
                    if (isset($billing_address_data) && count($billing_address_data) > 0) {
                        $bill_address=$billing_address_data[0];

                        $billing_first_name=$bill_address->first_name;
                        $billing_last_name=$bill_address->last_name;
                        $billing_email_id=$quoteData->customer_email;
                        $billing_mobile_no=$bill_address->mobile_no;
                        $billing_address=$bill_address->address_line1;
                        $billing_address_1=$bill_address->address_line2;
                        $billing_country=$bill_address->country;
                        $billing_pincode=$bill_address->pincode;
                        $billing_state=$bill_address->state;
                        $b_state_dp=$bill_address->state;
                        $billing_city=$bill_address->city;
                        $company_name=$bill_address->company_name;
                        $vat_no=$bill_address->vat_no;
                        $consulation_no=$bill_address->consulation_no;
                        $res_company_name=$bill_address->res_company_name;
                        $res_company_address=$bill_address->res_company_address;
                    }

                ?>
                <div class="row">
                    <div class="row tw-w-full">
                        <div class="col-sm-6 line-1">
                            <input type="text" class="form-control" placeholder="First Name*" name="billing_first_name"
                                value="<?php echo $billing_first_name; ?>" id="billing_first_name">
                        </div><!-- col-sm-6 -->
                        <div class="col-sm-6 line-2">
                            <input type="text" class="form-control" placeholder="Last Name*" name="billing_last_name"
                                value="<?php echo $billing_last_name; ?>" id="billing_last_name">
                        </div><!-- col-sm-6 -->
                        <?php if ($this->session->userdata('LoginID')) {
						}else{ ?>
                        <div class="col-sm-6 <?php echo ($this->session->userdata('LoginID'))?'line-2':'line-1'; ?>">
                            <input type="text" class="form-control" placeholder="Email*" name="billing_email_id"
                                value="<?php echo $billing_email_id; ?>" id="billing_email_id">
                        </div><!-- col-sm-6 -->
                        <?php } ?>
                        <div class="col-sm-6 <?php echo ($this->session->userdata('LoginID'))?'line-1':'line-2'; ?>">
                            <input type="text" class="form-control" placeholder="Mobile Number*" minlength="10"
                                maxlength="10" name="billing_mobile_no" value="<?php echo $billing_mobile_no; ?>"
                                id="billing_mobile_no">
                        </div><!-- col-sm-6 -->
                    </div><!-- first block -->

                    <div class="row tw-w-full">
                        <div class="col-sm-6 <?php echo ($this->session->userdata('LoginID'))?'line-2':'line-1'; ?>">
                            <input type="text" class="form-control" placeholder="Address Line1*" name="billing_address"
                                value="<?php echo $billing_address; ?>" id="billing_address" maxlength="35">
                        </div><!-- col-sm-6 -->

                        <div class="col-sm-6 <?php echo ($this->session->userdata('LoginID'))?'line-1':'line-2'; ?>">
                            <input type="text" class="form-control" placeholder="Address Line2*"
                                name="billing_address_1" value="<?php echo $billing_address_1; ?>"
                                id="billing_address_1" maxlength="35">
                        </div><!-- col-sm-6 -->

                        <div class="col-sm-6 <?php echo ($this->session->userdata('LoginID'))?'line-1':'line-2'; ?>">
                            <input type="text" class="form-control" placeholder="Zipcode*" name="billing_pincode"
                                value="<?php echo $billing_pincode; ?>" id="billing_pincode" maxlength="6">
                        </div><!-- col-sm-6 -->

                        <div class=" col-sm-6 <?php echo ($this->session->userdata('LoginID'))?'line-1':'line-2'; ?>">
                            <input type="text" class="form-control" placeholder="City*" name="billing_city"
                                value="<?php echo $billing_city; ?>" id="billing_city">
                        </div><!-- col-sm-6 -->

                        <div class="col-sm-6 <?php echo ($this->session->userdata('LoginID'))?'line-2':'line-1'; ?>">
                            <select class="form-control" name="billing_country" id="billing_country">
                                <option value="">Select Country</option>
                                <?php if (isset($countryList) && count($countryList)>0) {
							foreach ($countryList as $value) { ?>
                                <option value="<?php echo $value->country_code; ?>"
                                    <?php if($value->country_code==$billing_country){echo "selected";} ?>>
                                    <?php echo $value->country_name; ?></option>
                                <?php } } ?>
                            </select>
                        </div><!-- col-sm-6 -->

                        <div
                            class="col-sm-6 <?php echo ($this->session->userdata('LoginID'))?'line-2':'line-1'; ?> b_state_div">
                            <input type="text" class="form-control" placeholder="State/Suburb" name="billing_state"
                                value="<?php echo $billing_state; ?>" id="billing_state">
                        </div><!-- col-sm-6 -->
                        <div
                            class="col-sm-6 <?php echo ($this->session->userdata('LoginID'))?'line-2':'line-1'; ?> b_state_dp_div">
                            <select name="b_state_dp" id="b_state_dp" class="form-control">
                                <option value="">Select State</option>
                                <?php if (isset($stateList) && count($stateList)>0) {
							foreach ($stateList as $value) {  ?>
                                <option value="<?php echo $value->state_name; ?>"
                                    <?php if($value->state_name==$billing_state){echo "selected";} ?>>
                                    <?php echo $value->state_name; ?></option>
                                <?php } } ?>
                            </select>
                        </div><!-- col-sm-6 -->

                    </div><!-- second block -->

                    <div class="row tw-w-full">
                        <?php if ($restricted_access === 'no') {?>

                        <div
                            class="form-box col-sm-6 <?php echo ($this->session->userdata('LoginID'))?'line-2':'line-1'; ?>">
                            <input class="form-control" type="text" id="company_name" name="company_name"
                                value="<?php echo $company_name; ?>" placeholder="Company Name">
                        </div><!-- form-box -->

                        <div
                            class="form-box col-sm-6 <?php echo ($this->session->userdata('LoginID'))?'line-1':'line-2'; ?>">
                            <input class="form-control common_vat_no" type="text" id="vat_no" name="vat_no"
                                value="<?php echo $vat_no; ?>" placeholder="<?=lang('vatno')?>"
                                onkeyup="this.value = this.value.toUpperCase();">
                            <div class="loaderDiv" style="display: none">
                                <span><?=lang('please_wait')?><div class="loader"></div></span>
                            </div>
                            <input type="hidden" name="vat_flag" id="vatFlag" value="0">
                        </div><!-- form-box -->


                        <input class="form-control consulation_no" type="hidden" id="consulation_no"
                            name="consulation_no" value="<?php echo $consulation_no; ?>" placeholder="Consulation No">

                        <input class="form-control res_company_name" type="hidden" id="res_company_name"
                            name="res_company_name" value="<?php echo $res_company_name; ?>"
                            placeholder="Res Company Name">

                        <input class="form-control res_company_address" type="hidden" id="res_company_address"
                            name="res_company_address" value="<?php echo $res_company_address; ?>"
                            placeholder="Res Company Address">


                        <?php } ?>
                    </div><!-- third block -->

                    <?php if ($this->session->userdata('LoginID')) { ?>
                    <div class="col-sm-12 line-1">
                        <label class="checkbox-label-checkout "><input class="checkbox-checkout" type="checkbox"
                                id="billing_save_in_address_book" name="billing_save_in_address_book"
                                value="1">&nbsp;Save In address book
                            <span class="checkbox-check"></span></label>
                    </div><!-- col-sm-6 -->
                    <?php } ?>

                </div>
            </div><!-- ship-address form -->


            <div class="checkout-btn">
                <label class="checkbox-label-checkout float-left"><input class="checkbox-checkout" type="checkbox"
                        id="same_as_billing" name="same_as_billing" value="1" <?php
                    if (isset($billing_address_data) && count($billing_address_data) > 0) {
                        if ($same_as_billing == 1) {
                            echo 'checked';
                        }
                    }
                ?>>&nbsp;Use this as shipping address
                    <span class="checkbox-check"></span></label>
                        <button class="black-btn tw-button-zumbared" type="button"
                        id="billing-address-save"><?php echo ($this->session->userdata('LoginID'))? 'Save this address & Continue': 'Continue' ?>
                        </button>
            </div><!-- checkout-btn -->

        </div>
    </div>
</div><!-- card -->