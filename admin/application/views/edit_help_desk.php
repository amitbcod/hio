<?php $this->load->view('common/fbc-user/header'); ?> 



<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <?php if($this->session->flashdata('success')): ?>

        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>

    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>

        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>

    <?php endif; ?>



    <form class="default-form" id="customer-personal-info-form" method="POST" action="<?php echo BASE_URL; ?>CustomerController/update_help_desk" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $ticket['id']; ?>">

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" class="form-control" name="subject" value="<?= $ticket['subject']; ?>" readonly>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" class="form-control select2 required-entry" style="width: 100%;" disabled>
                        <option value="">Select Yellow Market Support</option>
                        <option value="1" <?= $ticket['category'] == 1 ? 'selected' : ''; ?>>Error Report</option>
                        <option value="2" <?= $ticket['category'] == 2 ? 'selected' : ''; ?>>Yellow Markets Delivery</option>
                        <option value="3" <?= $ticket['category'] == 3 ? 'selected' : ''; ?>>Enquiry - General</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority_id" class="form-control select2 required-entry" style="width: 100%;" disabled>
                        <option value="">Select a priority</option>
                        <option value="1" <?= $ticket['priority'] == 1 ? 'selected' : ''; ?>>Low</option>
                        <option value="2" <?= $ticket['priority'] == 2 ? 'selected' : ''; ?>>Medium</option>
                        <option value="3" <?= $ticket['priority'] == 3 ? 'selected' : ''; ?>>High</option>
                        <option value="4" <?= $ticket['priority'] == 4 ? 'selected' : ''; ?>>Urgent</option>
                    </select>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Message</label>
                    <textarea class="form-control" rows="5" name="message" readonly><?= $ticket['message']; ?></textarea>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Attachment</label>
                    <?php if(!empty($ticket['attachment'])): ?>
                        <p>Current file: <a href="<?= base_url('uploads/help_desk_attachment/'.$ticket['attachment']); ?>" target="_blank"><?= $ticket['attachment']; ?></a></p>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="attachment">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Order</label>
                    <select name="order_id" class="form-control select" disabled>
                        <option value="">Select an order</option>
                        <?php foreach($orders as $order): 
                            $selected = ($ticket['order_id'] == $order->order_id) ? 'selected' : '';
                        ?>
                            <option value="<?= $order->order_id; ?>" <?= $selected; ?>>
                                #<?= $order->increment_id; ?> at <?= date('j M Y, H:i', $order->created_at); ?> 
                                (MUR <?= number_format($order->grand_total, 2); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Products</label>
                    <select name="product_id" class="form-control select" disabled>
                        <option value="">Select a product</option>
                        <?php foreach($products as $product): 
                            $selected = ($ticket['products'] == $product->product_id) ? 'selected' : '';
                        ?>
                            <option value="<?= $product->product_id; ?>" <?= $selected; ?>>
                                <?= $product->name; ?> (Qty: <?= $product->qty; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Your Reply</label>
                    <textarea class="form-control" rows="5" name="admin_reply"></textarea>
                </div>
            </div>

            <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary">Update Ticket</button>
            </div>
        </div>
    </form>



</main>



<?php $this->load->view('common/fbc-user/footer'); ?>

