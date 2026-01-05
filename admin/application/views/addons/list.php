<?php $this->load->view('common/fbc-user/header'); ?> 

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
<div class="main-inner">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name">Addon Services</h1>
        <div class="float-right product-filter-div">
            <button class="purple-btn" onclick="window.location.href='<?= site_url('addons/create') ?>';">+ Add New</button>
        </div>
    </div>

    <div class="content-main form-dashboard">
        <div class="table-responsive text-center">

            <?php if($this->session->flashdata('success')): ?>
                <p style="color:green"><?= $this->session->flashdata('success'); ?></p>
            <?php endif; ?>

            <table class="table table-bordered table-style">
                <thead class="">
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Price (MUR)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($addons)): ?>
                        <?php foreach($addons as $a): ?>
                            <tr>
                                <td><?= $a->id; ?></td>
                                <td><?= $a->category_name; ?></td>
                                <td><?= $a->title; ?></td>
                                <td><?= $a->description; ?></td>
                                <td><?= number_format($a->price, 2); ?></td>
                                <td><?= $a->status ? 'Active' : 'Inactive'; ?></td>
                                <td>
                                    <a href="<?= site_url('addons/edit/'.$a->id); ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="<?= site_url('addons/delete/'.$a->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this addon?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No addons found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>
                    </div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
