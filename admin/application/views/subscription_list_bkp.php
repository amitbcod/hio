<?php $this->load->view('common/fbc-user/header'); ?> 

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name">Subscription Plans</h1>
        <div class="float-right product-filter-div">
            <button class="white-btn" onclick="window.location.href='<?= base_url('subscription/add_plan') ?>';"> + Add New Plan</button>
            <button class="white-btn" onclick="window.location.href='<?= base_url('subscription/add_feature') ?>';"> + Add New Feature</button>
        </div>
    </div>

    <div class="content-main form-dashboard">
        <div class="table-responsive text-center">
            <table class="table table-bordered table-style">
                <thead>
                    <tr>
                        <th>Plan Name</th>
                        <th>Price</th>
                        <th>Yearly Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($plans as $plan): ?>
                        <tr>
                            <td><?= $plan['name'] ?></td>
                            <td><?= number_format($plan['price'], 2) ?></td>
                            <td><?= number_format($plan['yearly_price'], 2) ?></td>
                            <td><?= $plan['status'] ? 'Active' : 'Inactive' ?></td>
                            <td>
                                <a href="<?= base_url('subscription/edit_plan/'.$plan['id']) ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="<?= base_url('subscription/delete_plan/'.$plan['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <hr>

            <h2>Features</h2>
            <table class="table table-bordered table-style">
                <thead>
                    <tr>
                        <th>Feature Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($features as $feature): ?>
                        <tr>
                            <td><?= $feature['feature_name'] ?></td>
                            <td>
                                <a href="<?= base_url('subscription/edit_feature/'.$feature['id']) ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="<?= base_url('subscription/delete_feature/'.$feature['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
