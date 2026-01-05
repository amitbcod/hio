<?php $this->load->view('common/fbc-user/header'); ?> 

<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <h1 class="head-name"><?= isset($plan) ? 'Edit Plan' : 'Add New Plan' ?></h1>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= isset($plan) ? base_url('subscription/edit_plan/'.$plan['id']) : base_url('subscription/edit_plan') ?>">
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
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="1" <?= (isset($plan) && $plan['status']==1) ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= (isset($plan) && $plan['status']==0) ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><?= isset($plan) ? 'Update' : 'Save' ?></button>
        <a href="<?= base_url('subscription') ?>" class="btn btn-secondary">Cancel</a>
    </form>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
