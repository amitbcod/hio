<?php $this->load->view('common/fbc-user/header'); ?> 



<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    <?php if($this->session->flashdata('success')): ?>

        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>

    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>

        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>

    <?php endif; ?>



    <form method="post" action="<?= base_url('CustomerController/update_faqs') ?>">
        <input type="hidden" name="id" value="<?= $faqs['id']; ?>">

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?= $faqs['name']; ?>" readonly>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= $faqs['email']; ?>" readonly>
        </div>

        <div class="form-group">
            <label>Question</label>
            <textarea name="question" class="form-control" rows="3" readonly><?= $faqs['question']; ?></textarea>
        </div>

        <div class="form-group">
            <label>Answer</label>
            <textarea name="answer" class="form-control" rows="5"><?= $faqs['answer']; ?></textarea>
        </div>
        <div class="form-group row">
            <label for="" class="col-sm-2 col-form-label font-500">Status</label>
            <div class="col-sm-3">
                <?php $status = $faqs['status']; ?>
                <input type="radio" name="status" value="0" <?php if ($status == 0) echo "checked"; ?>>
                <label>Pending</label><br>
                <input type="radio" name="status" value="1" <?php if ($status == 1) echo "checked"; ?>>
                <label>Approve</label><br>
                <input type="radio" name="status" value="2" <?php if ($status == 2) echo "checked"; ?>>
                <label>Reject</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
    </form>


</main>



<?php $this->load->view('common/fbc-user/footer'); ?>

