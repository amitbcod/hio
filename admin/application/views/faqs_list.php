<?php $this->load->view('common/fbc-user/header'); ?> 



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <div class="content-main form-dashboard">

        <div class="table-responsive text-center">

            <h2>Faqs</h2>

            <table class="table table-bordered table-style">
                <thead>
                    <tr>
                        <th>SR No</th>
                        <th>Name</th>
                        <th>Question</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                
                    <?php $sr = 1; ?>
                    <?php foreach($faqs as $faq): ?>
                        <tr>
                            <td><?= $sr++; ?></td>
                            <td><?= $faq['name']; ?></td>
                            <td><?= $faq['question']; ?></td>
                            <td><?= $faq['email']; ?></td>
                            <td>
                                <?php
                                    if ($faq['status'] == 0) {
                                        echo 'Pending';
                                    } elseif ($faq['status'] == 1) {
                                        echo 'Approved';
                                    } elseif ($faq['status'] == 2) {
                                        echo 'Rejected';
                                    } else {
                                        echo 'Unknown';
                                    }
                                ?>
                            </td>

                            <td>
                                <a href="<?= base_url('faqs/edit/'.$faq['id']); ?>" class="btn btn-sm btn-primary">Reply</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>



<?php $this->load->view('common/fbc-user/footer'); ?>

