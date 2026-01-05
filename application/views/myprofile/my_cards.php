<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul class="breadcrumb">
                <li><a href="<?= base_url() ?>">Home</a></li>
                <li class="active">My Gift Cards</li>
            </ul>
        </div>
    </div>
</div>

<div class="my-profile-page-full">
    <div class="container">
        <div class="row">
            <?php $this->load->view('common/profile_sidebar'); ?>

            <div class="col-sm-9 col-md-9">
                <div class="content-page">

                    <!-- Current Balance -->
                    <div class="card mb-4 gift-card-box">
                        <div class="card-body text-center">
                            <h2>Current Gift Card Balance</h2>
                            <h3 class="text-success">MUR <?= number_format($balance ?? 0, 2) ?></h3>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="card">
                        <div class="card-body">
                            <h2>Gift Card Transactions</h2>

                            <?php if (!empty($transactions)) :                                                            
                            ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mt-3">
                                       <thead class="thead-light">
    <tr>
        <th>SR No</th>
        <th>Gift Card Number</th>
        <th>Order Number</th>
        <th>Type</th>
        <th>Amount (MUR)</th>
        <th>Status</th>
        <th>Date</th>
    </tr>
</thead>
<tbody>
    <?php $sr = 1; ?>
    <?php foreach ($transactions as $txn) : 
        // Get gift card code
        $gcard_code = '-';
        if (isset($gift_cards_map[$txn->gift_card_id])) {
            $gcard_code = $gift_cards_map[$txn->gift_card_id]->code;
        }
    ?>
        <tr>
            <td><?= $sr++; ?></td>
            <td>
    <?= isset($gift_cards_map[$txn->gift_card_id]) ? $gift_cards_map[$txn->gift_card_id]->code : '-' ?>
</td>
            <td><?= !empty($txn->order_id) ? $txn->order_id : '-' ?></td>
            <td><?= ucfirst($txn->type) ?></td>
            <td><?= number_format($txn->amount, 2) ?></td>
            <td>
                <?php
                $statusLabels = [
                    0 => 'Pending',
                    1 => 'Completed',
                    2 => 'Failed'
                ];
                echo isset($statusLabels[$txn->status]) ? $statusLabels[$txn->status] : '-';
                ?>
            </td>
            <td><?= date('d M Y, H:i', strtotime($txn->created_at)) ?></td>
        </tr>
    <?php endforeach; ?>
</tbody>

                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mt-3" role="alert">
                                    You have no gift card transactions yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div><!-- row -->
    </div><!-- container -->
</div><!-- my-profile-page-full -->

<?php $this->load->view('common/footer'); ?>
