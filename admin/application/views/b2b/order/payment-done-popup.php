<div class="modal-header">
    <h4 class="head-name">Beneficiary Details</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<form id="initiate_payment_form" name="initiate_payment_form" method="post">
    <input type="hidden" name="hidden_order_id" name="hidden_order_id" value="<?php echo $PublisherPayemntData->order_id;; ?>" />
    <input type="hidden" name="hidden_b2b_order_id" name="hidden_b2b_order_id" value="<?php echo $PublisherPayemntData->B2b_order_id; ?>" />
    <input type="hidden" name="hidden_publisher_id" name="hidden_publisher_id" value="<?php echo $PublisherPayemntData->publisher_id; ?>" />
    <div class="modal-body">
        <p class="are-sure-message">Please Fill Beneficary Details</p>
        <div class="message-box-popup col-sm-12">
            <?php
            // $whuso_income = (($PublisherDetails->commision_percent  / 100) * $OrderData->grand_total);
            // $Payable_Amount = $OrderData->grand_total  - $whuso_income;
            ?>

            <div class="row">
                <div class="col-sm-5 customize-add-inner-sec page-content-textarea-small">
                    <label for="bannerHeading">UTR No. <span class="required">*</span></label>
                    <input class="form-control" type="text" name="bene_acc_no" id="bene_acc_no" value="<?php echo $PublisherPayemntData->beneficiary_acc_no ;?>" placeholder="Enter Payment UTR No."  required>
                    <!--<label for="bannerHeading">Beneficiary IFSC Code. <span class="required">*</span></label>
                    <input class="form-control" type="text" name="bene_ifsc_code" id="bene_ifsc_code" value="<?php echo $PublisherPayemntData->beneficiary_ifsc ;?>" placeholder="Enter Beneficiary IFSC code here" maxlength="50" required>
                    <label for="bannerHeading">Amount Payable <span class="required">*</span></label>
                    <input class="form-control" type="text" name="amount_payable" id="amount_payable" value="<?php echo  number_format($PublisherPayemntData->amount_payable , 2); ?>" placeholder="Enter Category Name here" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="50" readonly>
                    <div class="clear pad-bt-40"></div>
                    <label for="bannerDescription"> Remarks</label>
                    <textarea class="form-control" name="remarks" id="remarks" placeholder="Remarks Area" maxlength="250" required><?php echo $PublisherPayemntData->remarks ;?></textarea>-->

                    <div class="clear pad-bt-40"></div>

                    <div class="uploadPreview" id="uploadPreview">
                        <img src="" width="200">
                    </div>
                </div>
                <!-- col-sm-6 -->
                <div class="col-sm-7 customize-add-inner-sec">

                    <label for="bannerHeading">Beneficiary Name <span class="required">*</span></label>
                    <input class="form-control" type="text" name="beneficiary_name" id="beneficiary_name" value="<?php echo $PublisherPayemntData->beneficiary_name ;?>" placeholder="Enter Category Name here" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength="50" required>

                    <div class="clear pad-bt-40"></div>
                    <label for="position">Status <span class="required">*</span> </label>
                    <select name="status" id="status" class="form-control">
                        <option value="N" <?php echo  (isset($PublisherPayemntData->payment_mod) && $PublisherPayemntData->payment_mod == 'N' ? 'Selected' : '')?>>N</option>
                        <option value="I"  <?php  echo (isset($PublisherPayemntData->payment_mod) && $PublisherPayemntData->payment_mod == 'I' ? 'Selected' : '')?>>I</option>
                    </select>

                    <div class="clear pad-bt-40"></div>





                </div>
                <div class="download-discard-small pos-ab-bottom">

                    <!-- <button class="white-btn" >Discard</button> -->
                    <button id="initiate-payment-btn" onclick="paymentdone('<?php echo $PublisherPayment->id?>' );" class="download-btn">Payment Done</button>
                </div>
                <!-- col-sm-6 -->
</form>

</div>
</div>
</div>
<!--<div class="modal-footer">
    <button class="purple-btn" type="button" id="conf-qty-scan-btn" onclick="ConfirmQtyScan(<?php echo $OrderData->order_id; ?>,<?php echo  $OrderData->order_id;  ?>);">Confirm Scan </button>
</div>-->