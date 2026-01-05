<div class="modal-header">
    <h4 class="head-name">Beneficiary Details</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<form id="initiate_payment_form" name="initiate_payment_form" method="post" onsubmit="initiatepaymentform()">
    <input type="hidden" name="hidden_order_id" name="hidden_order_id" value="<?php echo $order_id; ?>" />
    <input type="hidden" name="hidden_b2b_order_id" name="hidden_b2b_order_id" value="<?php echo $OrderData->increment_id; ?>" />
    <input type="hidden" name="hidden_publisher_id" name="hidden_publisher_id" value="<?php echo $OrderData->publisher_id; ?>" />
    <div class="modal-body">
        <p class="are-sure-message">Please Fill Beneficary Details</p>
        <div class="message-box-popup col-sm-12">
            <?php
            // echo "<pre>";
            // print_r($OrderData);
            // die;
            $B2b_items = $this->B2BOrdersModel->getSingleDataByID('b2b_order_items', array('order_id' => $OrderData->order_id), '');
			$getCategory = $this->B2BOrdersModel->getCategory($B2b_items->parent_product_id);
            $OrderItems = $this->B2BOrdersModel->getOrderItems($OrderData->order_id);
            $product_name = '';

            $product_data = [];
            $new_product_data=[];
            // echo "<pre>";
            //     print_r($OrderItems);die;

            if (isset($OrderItems) && count($OrderItems) > 0) {
                foreach ($OrderItems as $item) {
                    $product_details = $this->B2BOrdersModel->getSingleDataByID('products', array('id' => $item->parent_product_id), '');
                    $new_product_details = $this->B2BOrdersModel->getSingleDataByID('products', array('id' => $item->product_id), '');
                    $product_data[] = $product_details;
                    $new_product_data[] = $new_product_details;

                }
            }
            // echo "<pre>";
            // print_r($product_details);
            // die;

            foreach ($product_data as $keyprod => $valprod) {
                $product_name = $valprod->name ?? null;
            }
            $total_webshop_price = 0;

            foreach ($new_product_data as $keyprod => $valprod) {
                if (isset($valprod->webshop_price)) {
                    $total_webshop_price += (float)$valprod->webshop_price;
                }
            }
            
            $publisherdetails = $this->B2BOrdersModel->getPublisherDetails($OrderData->publisher_id);
            $publication_name = '';

            $publisher_commision_per = 0; // Initialize $publisher_commision_per outside the conditions

            $publication_name = $PublisherDetails->publication_name ?? null;

            if ($publication_name === 'Amar Chitra Katha (Books)') {
                $total_grand_=$total_webshop_price;
            } elseif($getCategory){
				$total_grand_ = $total_webshop_price;
			}else{
				$total_grand_ = $OrderData->grand_total - ($OrderData->shipping_amount);
			}

            if ($publication_name === 'The Institute of Cost Accountants of India ') {
                $publisher_commision_per = 0;
                $whuso_income = 100 * $OrderData->total_qty_ordered;
            } else {
                // echo "hi1";
                $whuso_income = (($PublisherDetails->commision_percent / 100) * ($total_grand_));
            }
                // print_r($whuso_income);
                // die;
            if ($product_name === 'Financial Times Asia (Print Edition)') {
                // echo "hi2";
                $publisher_commision_per = 10;
                $whuso_income = (($publisher_commision_per / 100) * ($total_grand_));
                // print_r($whuso_income);
            }

            if ($product_name === 'Financial Times Weekend Newspaper with Magazine') {
                // echo "hi2";
                $publisher_commision_per = 10;
                $whuso_income = (($publisher_commision_per / 100) * ($total_grand_));
                // print_r($whuso_income);
            }
            // Assuming $product_name is defined elsewhere in your code

            if ($OrderData->order_id == '1471') {
                // $publisher_commision_per = 0;
                $whuso_income = 4340;
            }
            if($OrderData->order_id == '1726'){
				// $publisher_commision_per = 0;
				$whuso_income = 1230;
			}

            if($OrderData->order_id == '1895' ||  $OrderData->order_id == '1732' || $OrderData->order_id == '1739' || $OrderData->order_id == '1737' || $OrderData->order_id == '1735' || $OrderData->order_id == '1745' || $OrderData->order_id == '1682'){
				$total_grand_ = $OrderData->grand_total;
				$whuso_income = (($publisher_commision_per / 100) * ($total_grand_));
			}

            if($OrderData->order_id == '1979' || $OrderData->order_id == '1999'){
                $publisher_commision_per = 10;
				$total_grand_ = $OrderData->grand_total;
				$whuso_income = (($publisher_commision_per / 100) * ($total_grand_));
			}
            if($OrderData->order_id == '2007' || $OrderData->order_id == '2008'){
                $publisher_commision_per = 20;
				$total_grand_ = $OrderData->grand_total;
				$whuso_income = (($publisher_commision_per / 100) * ($total_grand_));
			}
            if ($product_name === 'Business Manager Magazine' || $product_name === 'Business Manager Magazine Digital') {
				// echo "hi2";
				$total_grand_=$OrderData->subtotal - $OrderData->shipping_amount ;
				// print_r($whuso_income);
			}


            if ($product_name === 'Down To Earth Magazine' || $product_name === 'Down To Earth Hindi Magazine') {
				// echo "hi2";
				$total_grand_ = $OrderData->grand_total;
				$whuso_income = (($PublisherDetails->commision_percent / 100) * ($OrderData->grand_total));
				// print_r($whuso_income);die;
			}
            if($OrderData->order_id == '3137'){
                $OrderData->shipping_amount = 0;
			}

            // $Payable_Amount = ($OrderData->grand_total  - $whuso_income) // commented on 20-08-24 
            $Payable_Amount = ($total_grand_  - $whuso_income) + ($OrderData->shipping_amount); // updated on 20-08-24 
            // print_r($Payable_Amount);die;
            // previous code 
            // $whuso_income = (($PublisherDetails->commision_percent  / 100) * ($OrderData->grand_total - $OrderData->shipping_amount));
            // $Payable_Amount = $OrderData->grand_total  - $whuso_income;
            ?>

            <div class="row">
                <div class="col-sm-5 customize-add-inner-sec page-content-textarea-small">
                    <label for="bannerHeading">Beneficiary Acc No. <span class="required">*</span></label>
                    <input class="form-control" type="text" name="bene_acc_no" id="bene_acc_no" value="<?= $PublisherPayemntData->beneficiary_acc_no ?? '' ?>" placeholder="Enter Beneficiary Acc No." onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="50" required>
                    <label for="bannerHeading">Beneficiary IFSC Code. <span class="required">*</span></label>
                    <input class="form-control" type="text" name="bene_ifsc_code" id="bene_ifsc_code" value="<?= $PublisherPayemntData->beneficiary_ifsc_code ?? '' ?>" placeholder="Enter Beneficiary IFSC code here" maxlength="50" required>
                    <label for="bannerHeading">Amount Payable <span class="required">*</span></label>
                    <input class="form-control" type="text" name="amount_payable" id="amount_payable" value="<?php echo  number_format($Payable_Amount, 2); ?>" placeholder="Enter Category Name here" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="50" readonly>
                    <div class="clear pad-bt-40"></div>
                    <label for="bannerDescription"> Remarks</label>
                    <textarea class="form-control" name="remarks" id="remarks" placeholder="Remarks Area" maxlength="250" required></textarea>

                    <div class="clear pad-bt-40"></div>

                    <div class="uploadPreview" id="uploadPreview">
                        <img src="" width="200">
                    </div>
                </div>
                <!-- col-sm-6 -->
                <div class="col-sm-7 customize-add-inner-sec">

                    <label for="bannerHeading">Beneficiary Name <span class="required">*</span></label>
                    <input class="form-control" type="text" name="beneficiary_name" id="beneficiary_name" value="<?= $PublisherPayemntData->beneficiary_name ?? '' ?>" placeholder="Enter Category Name here" onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)" maxlength="50" required>

                    <div class="clear pad-bt-40"></div>
                    <label for="position">Status <span class="required">*</span> </label>
                    <select name="status" id="status" class="form-control">
                        <option value="N">N</option>
                        <option value="I">I</option>
                    </select>

                    <div class="clear pad-bt-40"></div>





                </div>
                <div class="download-discard-small pos-ab-bottom">

                    <!-- <button class="white-btn" >Discard</button> -->
                    <input type="submit" class="download-btn" name="InitiateFormsubmit" id="InitiateFormsubmit" value="Save">
                    <!-- <button type="submit" id="categorybtn" class="download-btn">Save</button> -->

                </div>
                <!-- col-sm-6 -->
</form>

</div>
</div>
</div>
<!--<div class="modal-footer">
    <button class="purple-btn" type="button" id="conf-qty-scan-btn" onclick="ConfirmQtyScan(<?php echo $OrderData->order_id; ?>,<?php echo  $OrderData->order_id;  ?>);">Confirm Scan </button>
</div>-->