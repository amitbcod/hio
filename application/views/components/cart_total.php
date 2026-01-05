<?php
$currency_conversion_rate = $this->session->userdata('currency_conversion_rate');
$currency_symbol = $this->session->userdata('currency_symbol');
$default_currency_flag = $this->session->userdata('default_currency_flag');

if (isset($CartData) && isset($CartData->cartItems) && count($CartData->cartItems) > 0) {
    $cartItems = $CartData->cartItems;
    $cartDetails = $CartData->cartDetails;
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<ul>
    <li>
        <em><?php echo $this->lang->line('sub_total'); ?></em>
        <strong class="price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->base_subtotal, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->base_subtotal, 2)); ?></strong>
    </li>

    <li>
        <em><?php echo $this->lang->line('taxes'); ?></em>
        <strong class="price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->tax_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->tax_amount, 2)); ?></strong>
    </li>

    <?php if (!empty($cartDetails->coupon_code)) { ?>
        <li>
            <em><?php echo $this->lang->line('discount_label'); ?></em>
            <strong class="price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->base_discount_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->base_discount_amount, 2)); ?></strong>
        </li>
    <?php } ?>

    <?php if (!empty($cartDetails->voucher_code)) { ?>
        <li>
            <em><?php echo $this->lang->line('gift_card_amount'); ?></em>
            <strong class="price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->voucher_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->voucher_amount, 2)); ?></strong>
        </li>
    <?php } ?>

    <li>
        <em><?php echo $this->lang->line('shipping_cost'); ?></em>
        <strong class="price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->shipping_amount, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->shipping_amount, 2)); ?></strong>
    </li>

    <li class="shopping-total-price">
        <em><?php echo $this->lang->line('total_label'); ?></em>
        <strong class="price"><?php echo (($this->session->userdata('currency_code_session') && $default_currency_flag != 1) ? convert_currency_website($cartDetails->grand_total, $currency_conversion_rate, $currency_symbol) : CURRENCY_TYPE . number_format($cartDetails->grand_total, 2)); ?></strong>
    </li>

    <?php if (empty($cartDetails->voucher_code)) { ?>
        <li class="dvcode" id="li-discount-code" style="background-color: #ECEBEB;">
            <em style="margin-bottom:5px;"><?php echo $this->lang->line('discount_code_label'); ?></em>
            <div class="form-group">
                <form id="form-coupon" class="checkout_coupon" method="POST">
                    <input type="hidden" name="coupon_type" value="0">
                    <input id="coupon_code" class="form-control" name="coupon_code" type="text" 
                        value="<?php echo !empty($cartDetails->coupon_code) ? $cartDetails->coupon_code : ''; ?>" 
                        <?php echo !empty($cartDetails->coupon_code) ? 'readonly' : ''; ?>>
                    <?php if (!empty($cartDetails->coupon_code)) { ?>
                        <input type="button" name="apply_coupon" onclick="removeDiscount('<?php echo $cartDetails->coupon_code ?>',0);" value="<?php echo $this->lang->line('remove_label'); ?>" class="btn btn-primary btn-sm">
                    <?php } else { ?>
                        <input type="submit" name="apply_coupon" value="<?php echo $this->lang->line('apply_label'); ?>" class="btn btn-primary btn-sm">
                    <?php } ?>
                    <div id="coupon-message" class="coupon_code_message"></div>
                </form>
            </div>
        </li>
    <?php } ?>

    <li class="dvcode" id="li-giftcard-code" style="background-color: #ECEBEB;">
        <em style="margin-bottom:5px;"><?php echo $this->lang->line('gift_card_label'); ?></em>
        <div class="form-group">
            <form id="form-giftcard" class="checkout_giftcard" method="POST" onsubmit="return false;">
                <input type="hidden" name="session_id" value="<?php echo $this->session->userdata('sis_session_id'); ?>">
                <input id="giftcard_code" class="form-control" name="giftcard_code" type="text" 
                    value="<?php echo !empty($cartDetails->voucher_code) ? $cartDetails->voucher_code : ''; ?>" 
                    <?php echo !empty($cartDetails->voucher_code) ? 'readonly' : ''; ?>>
                <?php if (!empty($cartDetails->voucher_code)) { ?>
                    <button type="button" class="btn btn-primary btn-sm remove_giftcard_btn" data-code="<?= $cartDetails->voucher_code ?>">
                        <?php echo $this->lang->line('remove_label'); ?>
                    </button>
                <?php } else { ?>
                    <button type="button" id="applyGiftCardBtn" class="btn btn-primary btn-sm"><?php echo $this->lang->line('apply_label'); ?></button>
                <?php } ?>
                <div id="giftcard-message" class="giftcard_code_message" style="margin-top:5px;color:#000;"></div>
            </form>
        </div>
    </li>

    <?php if (empty($cartDetails->coupon_code)) { ?>
        <li class="dvcode" id="li-voucher-code" style="background-color: #ECEBEB;">
            <em style="margin-bottom:5px;"><?php echo $this->lang->line('voucher_code_label'); ?></em>
            <strong class="price">
                <form id="form-voucher" class="checkout_voucher" method="POST">
                    <input type="hidden" name="coupon_type" value="1">
                    <input id="voucher_code" class="form-control" name="coupon_code" type="text" 
                        value="<?php echo !empty($cartDetails->voucher_code) ? $cartDetails->voucher_code : ''; ?>" 
                        <?php echo !empty($cartDetails->voucher_code) ? 'readonly' : ''; ?>>
                    <?php if (!empty($cartDetails->voucher_code)) { ?>
                        <input type="button" name="apply_voucher" onclick="removeDiscount('<?php echo $cartDetails->voucher_code ?>',1);" value="<?php echo $this->lang->line('remove_label'); ?>" class="btn btn-primary">
                    <?php } else { ?>
                        <input type="submit" name="apply_voucher" value="<?php echo $this->lang->line('apply_label'); ?>" class="btn btn-primary">
                    <?php } ?>
                    <div id="voucher-message" class="voucher_code_message"></div>
                </form>
            </strong>
        </li>
    <?php } ?>
</ul>
<?php } ?>
<div class="modal fade" id="giftcardRemoveModal" tabindex="-1" aria-labelledby="removeGiftcardLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="removeGiftcardLabel"><?php echo $this->lang->line('remove_gift_card_modal_title'); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo $this->lang->line('close_label'); ?>"></button>
      </div>
      <div class="modal-body">
        <?php echo $this->lang->line('remove_gift_card_modal_body'); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $this->lang->line('cancel_label'); ?></button>
        <button type="button" class="btn btn-danger" id="confirmRemove"><?php echo $this->lang->line('remove_label'); ?></button>
      </div>
    </div>
  </div>
</div>




<script>


    $(document).ready(function() {

    // === Apply Gift Card ===
    $('#applyGiftCardBtn').on('click', function(e) {
        e.preventDefault();
        const gift_code = $('#giftcard_code').val().trim();
        const session_id = $('input[name="session_id"]').val();

        if (gift_code === '') {
            showGiftcardAlert('Please enter a gift card code', 'danger');
            return;
        }

        $.ajax({
            url: BASE_URL + "cart/applyGiftCard",
            type: "POST",
            dataType: "json",
            data: { gift_code: gift_code, session_id: session_id },
            beforeSend: function() {
                $('#applyGiftCardBtn').prop('disabled', true).val('Applying...');
            },
            success: function(res) {
                if (res.status === 'success') {
                    showGiftcardAlert(res.message, 'success');
                    $('#giftcard_code').prop('readonly', true);

                    // Replace apply button with remove button
                    $('#applyGiftCardBtn').replaceWith(
                        `<button type="button" class="btn btn-primary remove_giftcard_btn" data-code="${gift_code}">Remove</button>`
                    );

                    // Update cart total if returned
                    if (res.new_cart_total !== undefined) {
                        $('#cart_total').text(parseFloat(res.new_cart_total).toFixed(2));
                    }

                    // Optional: reload page after short delay
                    setTimeout(() => location.reload(), 1500);

                } else {
                    showGiftcardAlert(res.message, 'danger');
                }
            },
            complete: function() {
                $('#applyGiftCardBtn').prop('disabled', false).val('Apply');
            },
            error: function() {
                showGiftcardAlert('Error applying gift card', 'danger');
            }
        });
    });

    // === Remove Gift Card using modal ===
    let giftCardToRemove = null;

    // Delegated event for dynamically created remove button
    $(document).on('click', '.remove_giftcard_btn', function() {
        giftCardToRemove = $(this).data('code');
        $('#giftcardRemoveModal').modal('show'); // Show Bootstrap modal
    });

    // Confirm remove inside modal
    $('#confirmRemove').on('click', function() {
        if (!giftCardToRemove) return;
        const session_id = $('input[name="session_id"]').val();

        $.ajax({
            url: BASE_URL + "cart/removeGiftCard",
            type: "POST",
            dataType: "json",
            data: { gift_code: giftCardToRemove, session_id },
            success: function(res) {
                $('#giftcardRemoveModal').modal('hide');
                if (res.status === 'success') {
                    showGiftcardAlert(res.message || 'Gift card removed', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showGiftcardAlert(res.message, 'danger');
                }
            },
            error: function() {
                $('#giftcardRemoveModal').modal('hide');
                showGiftcardAlert('Error removing gift card', 'danger');
            }
        });
    });

});

// === Bootstrap style alert function ===
function showGiftcardAlert(message, type = 'success') {
    let alertBox = $('#giftcard-message');
    alertBox.html(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
        </button>
            
        </div>
    `);
} 
</script>