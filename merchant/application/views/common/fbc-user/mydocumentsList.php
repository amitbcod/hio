<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    
    <h1 class="head-name mb-4">List of Documents</h1>
    <div class="text-end mb-3">
        <a href="<?php echo BASE_URL('mydocuments/add'); ?>" type="button" class="btn btn-primary">
            Add Document
        </a>

    </div>
    <table class="plan-table table table-bordered table-striped toped-table" border="1" cellpadding="8" cellspacing="0">
        <thead class="text-center ym-basic-merchant-plan-2 ym-basic-plan-merchant-1">
            <tr>
                <th>Sr No#</th>
                <th>Document Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($mydocument as $index => $doc): ?>
                <tr>
                    <td><?= $index+1 ?></td>
                    <td><?= $doc['document_name'] ?></td>
                    <td>
                        <a href="<?= base_url('mydocuments/edit/'.$doc['id']) ?>" class="btn btn-sm btn-primary">
                            Edit
                        </a>
                        <a href="/uploads/documents/<?= $doc['document_file'] ?>" download class="btn btn-sm btn-primary">
                             Download
                        </a>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>

