<div class="modal-header">
    <h4 class="head-name">Beneficiary Details</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<form id="initiate_refund_form" name="initiate_refund_form" method="post">
    <input type="hidden" name="hidden_order_id" name="hidden_order_id" value="<?php echo $order_id; ?>" />
    <input type="hidden" name="hidden_increment_id" name="hidden_increment_id" value="<?php echo $OrderData->increment_id; ?>" />
    <!--<input type="hidden" name="hidden_publisher_id" name="hidden_publisher_id" value="<?php echo $OrderData->publisher_id; ?>" />-->
    <div class="modal-body">
        <p class="are-sure-message">Please Fill Beneficary Details</p>
        <div class="message-box-popup col-sm-12">
            <?php
          //  $whuso_income = (($PublisherDetails->commision_percent  / 100) * $OrderData->grand_total);
            //$Payable_Amount = $OrderData->grand_total  - $whuso_income;
            ?>

            <div class="row">
                <div class="col-sm-5 customize-add-inner-sec page-content-textarea-small">
                    <label for="bannerHeading">Beneficiary Acc No. <span class="required">*</span></label>
                    <input class="form-control" type="text" name="bene_acc_no" id="bene_acc_no" value="" placeholder="Enter Beneficiary Acc No." onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="50" required>
                    <label for="bannerHeading">Beneficiary IFSC Code. <span class="required">*</span></label>
                    <input class="form-control" type="text" name="bene_ifsc_code" id="bene_ifsc_code" value="" placeholder="Enter Beneficiary IFSC code here" maxlength="50" required>
                    <label for="bannerHeading">Amount Payable <span class="required">*</span></label>
                    <input class="form-control" type="text" name="amount_payable" id="amount_payable" value="" placeholder="Enter Amount Payable here" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="50" >
                    <div class="clear pad-bt-40"></div>
                   <label for="bannerDescription"> Bank address</label>
                    <textarea class="form-control" name="bank_address" id="bank_address" placeholder="Bank Address" maxlength="250" required></textarea>

                    <div class="clear pad-bt-40"></div>

                    <div class="uploadPreview" id="uploadPreview">
                        <img src="" width="200">
                    </div>
                </div>
                <!-- col-sm-6 -->
                <div class="col-sm-7 customize-add-inner-sec">

                    <label for="bannerHeading">Beneficiary Name <span class="required">*</span></label>
                    <input class="form-control" type="text" name="beneficiary_name" id="beneficiary_name" value="<?php echo $BillingAddress->first_name.' '.$BillingAddress->last_name?>" placeholder="Enter Benificiary Name here" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength="50"   required>

                    <div class="clear pad-bt-40"></div>
                    <!--<label for="position">Status <span class="required">*</span> </label>
                    <select name="status" id="status" class="form-control">
                        <option value="N">N</option>
                        <option value="I">I</option>
                    </select>-->
                    <label for="bannerHeading">Account Type <span class="required">*</span></label>
                    <input class="form-control" type="text" name="amount_type" id="amount_type" value="" placeholder="Enter Account Type here" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength="50" >
                    <div class="clear pad-bt-40"></div>
                    <label for="bannerHeading">Bank Name <span class="required">*</span></label>
                    <input class="form-control" type="text" name="bank_name" id="bank_name" value="" placeholder="Enter Bank Namew here" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength="50" >




                </div>
                <div class="download-discard-small pos-ab-bottom">

                    <!-- <button class="white-btn" >Discard</button> -->
                    <button id="categorybtn" class="download-btn">Initiate Refund</button>
                </div>
                <!-- col-sm-6 -->
</form>

</div>
</div>
</div>
<!--<div class="modal-footer">
    <button class="purple-btn" type="button" id="conf-qty-scan-btn" onclick="ConfirmQtyScan(<?php echo $OrderData->order_id; ?>,<?php echo  $OrderData->order_id;  ?>);">Confirm Scan </button>
</div>-->