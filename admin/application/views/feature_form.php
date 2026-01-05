<?php $this->load->view('common/fbc-user/header'); ?> 

<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <h1><?= isset($feature) ? 'Edit Feature' : 'Add New Feature' ?></h1>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= isset($feature) ? base_url('subscription/edit_feature/'.$feature['id']) : base_url('subscription/edit_feature') ?>">
        <div class="form-group">
            <label>Feature Name</label>
            <input type="text" name="feature_name" class="form-control" value="<?= isset($feature) ? $feature['feature_name'] : '' ?>" required>
        </div>
        <button type="submit" class="btn btn-primary"><?= isset($feature) ? 'Update' : 'Save' ?></button>
        <a href="<?= base_url('subscription') ?>" class="btn btn-secondary">Cancel</a>
    </form>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
