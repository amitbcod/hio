<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="head-name">Drivers</h1>
        <button class="white-btn" onclick="window.location.href='<?= base_url('driver/edit') ?>';">+ Add New Driver</button>
    </div>

    <div class="table-responsive text-center">
        <table class="table table-bordered table-style">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Licence No</th>
                    <th>Plate No</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($drivers)): ?>
                    <?php foreach($drivers as $driver): ?>
                        <tr>
                            <td><?= $driver['first_name'].' '.$driver['last_name'] ?></td>
                            <td><?= $driver['mobile_no'] ?></td>
                            <td><?= $driver['email'] ?></td>
                            <td><?= $driver['driver_licence_no'] ?></td>
                            <td><?= $driver['licence_plate_no'] ?></td>
                            <td>
                                <a href="<?= base_url('driver/edit/'.$driver['id']) ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="<?= base_url('driver/delete/'.$driver['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No driver data found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
