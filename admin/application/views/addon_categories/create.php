<?php $this->load->view('common/fbc-user/header'); ?> 

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
<div class="main-inner">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name">Create Addon Category</h1>
        <div class="float-right product-filter-div">
            <a href="<?= site_url('addonCategories'); ?>" class="white-btn back-btn-line">Back to List</a>
        </div>
    </div>

    <div class="content-main form-dashboard">

        <?php if($this->session->flashdata('success')): ?>
            <p style="color:green"><?= $this->session->flashdata('success'); ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" class="form-control" value="<?= set_value('name'); ?>" required>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <select name="status" class="form-control">
                    <option value="1" <?= set_value('status') == 1 ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?= set_value('status') == 0 ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
        </form>

    </div>
</div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
