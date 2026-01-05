<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <div class="content-main form-dashboard">

        <div class="table-responsive text-center">

            <h2>Your Messages</h2>
            <table class="table table-bordered table-style">
                <thead class="text-center">
                    <tr>
                        <th>SR No</th>
                        <th>Product Name</th>
                        <th>Customer Name / Email</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sr = 1; ?>
                    <?php foreach ($messaging as $msg): ?>
                        <?php
                        $customer_display = ($msg['customer_id'] > 0 && !empty($msg['name'])) ? $msg['name'] : $msg['email'];
                        ?>
                        <tr>
                            <td><?= $sr++; ?></td>
                            <td><?= htmlspecialchars($msg['product_name']); ?></td>
                            <td><?= htmlspecialchars($customer_display); ?></td>
                            <td><?= htmlspecialchars($msg['category']); ?></td>
                            <td>
                                <?php
                                $customer_identifier = ($msg['customer_id'] > 0) ? $msg['customer_id'] : $msg['email'];
                                ?>
                                <a href="<?= base_url('Mydocuments/messages_view/' . $msg['product_id'] . '/' . urlencode($customer_identifier)); ?>" class="btn btn-sm btn-primary">View Messages</a>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>

    </div>

</main>



<?php $this->load->view('common/fbc-user/footer'); ?>