<?php $this->load->view('common/fbc-user/header'); ?>



<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <h1><?= isset($plan) ? 'Edit Plan' : 'Add New Plan' ?></h1>



    <?php if ($this->session->flashdata('success')): ?>

        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>

    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>

        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>

    <?php endif; ?>



    <form method="post" action="<?= isset($plan) ? base_url('subscription/edit_plan/' . $plan['id']) : base_url('subscription/edit_plan') ?>">

        <div class="form-group">

            <label>Plan Name</label>

            <input type="text" name="name" class="form-control" value="<?= isset($plan) ? $plan['name'] : '' ?>" required>

        </div>

        <div class="form-group">

            <label>Price</label>

            <input type="number" name="price" class="form-control" value="<?= isset($plan) ? $plan['price'] : '' ?>" step="0.01" required>

        </div>

        <div class="form-group">

            <label>Yearly Price</label>

            <input type="number" name="yearly_price" class="form-control" value="<?= isset($plan) ? $plan['yearly_price'] : '' ?>" step="0.01" required>

        </div>

        <div class="form-group">
            <label>YM VAT (%)</label>
            <input type="number" name="vat_percent" id="vat_percent" class="form-control"
                value="<?= isset($plan) ? $plan['vat_percent'] : '' ?>" step="0.01">
        </div>

        <div class="form-group">
            <label>Final Price (MUR)</label>
            <input type="text" name="final_price" id="final_price" class="form-control"
                value="<?= isset($plan) ? $plan['final_price'] : '' ?>" readonly>
        </div>

        <div class="form-group">

            <label>Status</label>

            <select name="status" class="form-control">

                <option value="1" <?= (isset($plan) && $plan['status'] == 1) ? 'selected' : '' ?>>Active</option>

                <option value="0" <?= (isset($plan) && $plan['status'] == 0) ? 'selected' : '' ?>>Inactive</option>

            </select>

        </div>

        <button type="submit" class="btn btn-primary"><?= isset($plan) ? 'Update' : 'Save' ?></button>

        <a href="<?= base_url('subscription') ?>" class="btn btn-secondary">Cancel</a>

    </form>

</main>
<script>
    function calculateWebshopPrice(price, percent) {
        price = parseFloat(price) || 0;
        percent = parseFloat(percent) || 0;

        let tax_amount = 0;
        let webshop_price = price;

        if (price > 0 && percent > 0) {
            tax_amount = (percent / 100) * price;
            webshop_price = price + tax_amount;
        }

        return {
            tax_amount,
            webshop_price
        };
    }

    // On VAT or Yearly Price change, recalc final price
    document.addEventListener('DOMContentLoaded', function() {
        const yearlyPriceField = document.querySelector('input[name="yearly_price"]');
        const vatField = document.getElementById('vat_percent');
        const finalField = document.getElementById('final_price');

        function recalc() {
            const price = yearlyPriceField.value;
            const vat = vatField.value;
            const result = calculateWebshopPrice(price, vat);
            finalField.value = result.webshop_price.toFixed(2);
        }

        vatField.addEventListener('blur', recalc);
        yearlyPriceField.addEventListener('blur', recalc);
    });
</script>


<?php $this->load->view('common/fbc-user/footer'); ?>