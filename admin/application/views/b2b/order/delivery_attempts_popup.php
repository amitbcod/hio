<?php if (!empty($deliveryAttempts)) : ?>
    <?php $lastAttempt = end($deliveryAttempts); ?>
    <table class="table table-bordered deliverytable">
        <thead>
            <tr>
                <th>Attempt No</th>
                <th>Delivery Date</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Reason for Failure/Success</th>
                <?php if($OrderData->shipment_type != 2) { ?><th>Action</th><?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($deliveryAttempts as $attempt) : ?>
                <tr>
                    <td><?= $attempt->delivery_attempt_no; ?></td>
                    <td><?= date('d M Y', strtotime($attempt->delivery_date)); ?></td>
                    <td>
                        <?php
                        switch ($attempt->delivery_status) {
                            case 1: echo "Shipped"; break;
                            case 2: echo "Failed Attempt 1"; break;
                            case 3: echo "Attempt 2"; break;
                            case 4: echo "Attempt 2 Failed"; break;
                            case 5: echo "Attempt 3"; break;
                            case 6: echo "Attempt 3 Failed"; break;
                            case 7: echo "Collect From Store"; break;
                            case 8: echo "Delivered"; break;
                            case 9: echo "Collected"; break;
                            default: echo "Pending"; break;
                        }
                        ?>
                    </td>
                    <td><?= !empty($attempt->remarks) ? $attempt->remarks : '-'; ?></td>
                    <td><?= !empty($attempt->reason_for_attempt_failed) ? $attempt->reason_for_attempt_failed : '-'; ?></td>
                    <?php if($OrderData->shipment_type != 2) { ?>
                        <td>
                            <?php if ($attempt->id == $lastAttempt->id && $attempt->delivery_status != 8) : ?>
                                <?php if (empty($attempt->reason_for_attempt_failed)) : ?>
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="MarkAsFailedPopup('<?= $attempt->order_id ?>', '<?= $attempt->delivery_attempt_no ?>')">
                                        Mark as Failed
                                    </button>
                                <?php elseif ($attempt->delivery_attempt_no < 3): ?>
                                    <button class="btn btn-primary btn-sm" 
                                            onclick="AssignNewDeliveryPopup('<?= $attempt->order_id ?>', '<?= $attempt->delivery_attempt_no ?>')">
                                        Attempt <?= $attempt->delivery_attempt_no+1; ?>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">Failed</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Failed</span>
                            <?php endif; ?>
                        </td>
                    <?php } ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-center">No delivery attempts found.</p>
<?php endif; ?>
