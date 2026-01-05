<?php $this->load->view('common/fbc-user/header'); ?>



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <div class="main-inner">

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">

            <h1 class="head-name">Create Addon Service</h1>

            <div class="float-right product-filter-div">

                <a href="<?= site_url('addons'); ?>" class="white-btn back-btn-line">Back to List</a>

            </div>

        </div>



        <div class="content-main form-dashboard">

            <div class="table-responsive">



                <?php if ($this->session->flashdata('success')): ?>

                    <p style="color:green"><?= $this->session->flashdata('success'); ?></p>

                <?php endif; ?>



                <form method="post">

                    <div class="form-group">

                        <label>Category:</label>

                        <select name="category_id" class="form-control" required>

                            <option value="">-- Select Category --</option>

                            <?php foreach ($categories as $c): ?>

                                <option value="<?= $c->id; ?>" <?= set_value('category_id') == $c->id ? 'selected' : ''; ?>>

                                    <?= $c->name; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>



                    <div class="form-group">

                        <label>Title:</label>

                        <input type="text" name="title" class="form-control" value="<?= set_value('title'); ?>" required>

                    </div>



                    <div class="form-group">

                        <label>Description:</label>

                        <textarea name="description" class="form-control"><?= set_value('description'); ?></textarea>

                    </div>



                    <div class="form-group">

                        <label>Price (MUR):</label>

                        <input type="number" step="0.01" name="price" class="form-control" value="<?= set_value('price'); ?>" required>

                    </div>

                    <div class="form-group">
                        <label>YM VAT (%):</label>
                        <input type="number" step="0.01" name="vat_percent" id="vat_percent"
                            class="form-control" value="<?= set_value('vat_percent'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Final Price (MUR):</label>
                        <input type="text" name="final_price" id="final_price"
                            class="form-control" value="<?= set_value('final_price'); ?>" readonly>
                    </div>




                    <div class="form-group">

                        <label>Status:</label>

                        <select name="status" class="form-control">

                            <option value="1" <?= set_value('status') == 1 ? 'selected' : ''; ?>>Active</option>

                            <option value="0" <?= set_value('status') == 0 ? 'selected' : ''; ?>>Inactive</option>

                        </select>

                    </div>



                    <div class="form-group">

                        <button type="submit" class="btn btn-success">Save</button>

                    </div>

                </form>



            </div>

        </div>

    </div>

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

    // On VAT or Price change, recalc final price
    document.addEventListener('DOMContentLoaded', function() {
        const priceField = document.querySelector('input[name="price"]');
        const vatField = document.getElementById('vat_percent');
        const finalField = document.getElementById('final_price');

        function recalc() {
            const price = priceField.value;
            const vat = vatField.value;
            const result = calculateWebshopPrice(price, vat);
            finalField.value = result.webshop_price.toFixed(2);
        }

        vatField.addEventListener('blur', recalc);
        priceField.addEventListener('blur', recalc);
    });
</script>


<?php $this->load->view('common/fbc-user/footer'); ?>