<?php $this->load->view('common/fbc-user/header'); ?> 

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
<div class="main-inner">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name">Addon Categories</h1>
        <div class="float-right product-filter-div">
            <a href="<?= site_url('addonCategories/create'); ?>" class="purple-btn addnew-btn">+ Add New Addon Category</a>
        </div>
    </div>

    <div class="content-main form-dashboard">
        <div class="table-responsive text-center">

            <?php if($this->session->flashdata('success')): ?>
                <p style="color:green"><?= $this->session->flashdata('success'); ?></p>
            <?php endif; ?>

            <table class="table table-bordered table-style">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Addon Category Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($categories)): ?>
                        <?php foreach($categories as $c): ?>
                            <tr>
                                <td><?= $c->id; ?></td>
                                <td><?= $c->name; ?></td>
                                <td><?= $c->status ? 'Active' : 'Inactive'; ?></td>
                                <td>
                                    <a href="<?= site_url('addonCategories/edit/'.$c->id); ?>" class="btn btn-sm btn-primary">Edit</a> 
                                    <a href="<?= site_url('addonCategories/delete/'.$c->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete Addon Category?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">No addon categories found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
