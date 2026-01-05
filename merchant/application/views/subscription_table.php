<?php $this->load->view('common/fbc-user/header'); ?>
<style>
    .plan-table thead th {
        text-align: center !important;
        vertical-align: middle !important;
        font-weight: 600;
        font-size: 16px;
    }
</style>

<link href="<?php echo SKIN_CSS; ?>dashboard1.css?v=<?php echo CSSJS_VERSION; ?>" rel="stylesheet">

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <!-- Flash messages -->
  <?php if($this->input->get('payment') === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        Payment successful! Your subscription has been activated.
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<!-- Error Message -->
<?php if($this->input->get('payment') === 'error'): ?>
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        Payment failed! Please try again or contact support.
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>
    <h4 class="mt-4 mb-3">Choose Your Subscription Plan</h4>

    <form method="post" action="<?= base_url('subscription/subscribe') ?>">

        <table class="plan-table table table-bordered table-striped text-center toped-table">
            <thead class="ym-basic-merchant-plan-2 ym-basic-plan-merchant-1">
                <tr>
                    <th>Features / Plans</th>
                    <?php foreach($plans as $plan): ?>
                        <th><?= htmlspecialchars($plan['name']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <!-- Yearly Subscription Price Row -->
                <tr>
                    <td><strong>Yearly Subscription</strong></td>
                    <?php foreach($plans as $plan): ?>
                        <td><?= number_format((float)$plan['final_price'], 2); ?></td>
                    <?php endforeach; ?>
                </tr>

                <!-- Features Rows -->
                <?php foreach($features as $feature): ?>
                    <tr>
                        <td class="<?= in_array($feature['id'], [1, 10, 15]) ? 'main_feature_name font-weight-bold' : '' ?>">
                            <?= htmlspecialchars($feature['feature_name']); ?>
                        </td>
                        <?php foreach($plans as $plan): ?>
                            <td class="<?= in_array($feature['id'], [1, 10, 15]) ? 'main_feature_name' : '' ?>">
                                <?= isset($plan_features[$feature['id']][$plan['id']]) 
                                    ? $plan_features[$feature['id']][$plan['id']] 
                                    : '-'; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>

                <!-- Select Plan Row -->
                <tr class="bg-light">
                    <td><strong>Select Plan</strong></td>
                    <?php foreach($plans as $plan): 
                        $is_active = (isset($active_plan['plan_id']) && $active_plan['plan_id'] == $plan['id']);
                    ?>
                        <td>
                            <?php if($is_active): ?>
                                <span class="badge badge-success">Active</span>
                                <br><small>Expires: <?= date('d M Y', strtotime($active_plan['end_date'] ?? '')); ?></small>
                            <?php else: ?>
                                <input type="radio" name="plan_id" value="<?= $plan['id']; ?>" required>
                                <label>Select</label>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>

        <!-- Proceed Button -->
        <div class="actions-toolbar mt-4 mb-5 text-right">
            <button type="submit" id="subscription-payment" class="btn btn-primary btn-lg px-5" title="Subscribe">
                <i class="fa fa-credit-card"></i> Proceed to Payment
            </button>
        </div>
    </form>

</main>
<script>
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(a => {
    a.classList.remove('show');
  });
}, 4000);
</script>

<?php $this->load->view('common/fbc-user/footer'); ?>
