<?php $this->load->view('common/fbc-user/header'); ?> 



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <div class="content-main form-dashboard">

        <div class="table-responsive text-center">

            <h2>Your Tickets</h2>
            <?php
                // Group tickets by order_id + products to avoid duplicates
                $grouped_tickets = [];
                foreach ($help_desk as $ticket) {
                    $key = $ticket['order_id'] . '_' . $ticket['products'];
                    if (!isset($grouped_tickets[$key])) {
                        $grouped_tickets[$key] = [];
                    }
                    $grouped_tickets[$key][] = $ticket;
                }
                ?>

                <table class="table table-bordered table-style">
                    <thead>
                        <tr>
                            <th>SR No</th>
                            <th>Ticket Id</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sr = 1; ?>
                        <?php foreach($grouped_tickets as $tickets): ?>
                            <?php $first_ticket = $tickets[0]; ?>
                            <tr>
                                <td><?= $sr++; ?></td>
                                <td><?= $first_ticket['ticket_id']; ?></td>
                                <td><?= $first_ticket['subject']; ?></td>
                                <td>
                                    <?php
                                    $categories = [1 => 'Error Report', 2 => 'Yellow Markets Delivery', 3 => 'Enquiry - General'];
                                    echo $categories[$first_ticket['category']] ?? 'Unknown';
                                    ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('CustomerController/view/' . $first_ticket['order_id'] . '/' . $first_ticket['products']); ?>" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>



        </div>

    </div>

</main>



<?php $this->load->view('common/fbc-user/footer'); ?>

