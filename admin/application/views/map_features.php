<?php $this->load->view('common/fbc-user/header'); ?> 

<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <h1 class="head-name">Map Features to Plans</h1>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('subscription/save_feature_mapping') ?>">
        <div class="table-responsive">
            <table class="table table-bordered table-style text-center">
                <thead>
                    <tr>
                        <th>Feature Name</th>
                        <?php foreach($plans as $plan): ?>
                            <th><?= $plan['name'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($features as $feature): ?>
                        <tr>
                            <td><?= $feature['feature_name'] ?></td>
                            <?php foreach($plans as $plan): ?>
                                <td>
                                    <input type="text" name="mapping[<?= $feature['id'] ?>][<?= $plan['id'] ?>]" 
                                           value="<?= isset($mapped[$feature['id']][$plan['id']]) ? $mapped[$feature['id']][$plan['id']] : '' ?>" 
                                           class="form-control" placeholder="Enter value">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="actions-toolbar mt-3">
            <button type="submit" class="btn btn-primary">Save Mapping</button>
            <a href="<?= base_url('subscription') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
