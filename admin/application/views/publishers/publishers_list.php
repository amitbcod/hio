<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <ul class="nav nav-pills">
        <li class="active"><a data-toggle="pill" href="#publishers">Merchants</a></li>
    </ul>
    <div class="main-inner min-height-480">
        <div class="tab-content">
            <div id="variants" class="tab-pane fade in active " style="opacity:1;">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1 class="head-name">Merchants List </h1>
                    <a href="<?php echo base_url() ?>publishers/add-publishers"> <button class="purple-btn">Create New</button></a>
                </div>
                <!-- form -->
                <div class="content-main form-dashboard">
                    <div class="table-responsive text-center">
                        <table class="table table-bordered table-style" id="datatableattribute">
                            <thead>
                                <tr>
                                    <th>Merchant ID</th>
                                    <th>Trade NAME </th>
                                    <th>EMAIL</th>
                                    <th>Merchant NAME </th>
                                    <th>COMMISSION % </th>
                                    <!-- <th>Split Id</th> -->
                                    <th>PHONE NO </th>
                                    <th>STATUS </th>
                                    <th>DETAILS </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($getPublishers as $publishers) { ?>
                                    <tr>
                                        <td><?php echo $publishers['id']; ?></td>
                                        <td><?php echo $publishers['publication_name']; ?></td>
                                        <td><?php echo $publishers['email']; ?></td>
                                        <td><?php echo $publishers['vendor_name']; ?></td>
                                        <td><?php echo $publishers['commision_percent']; ?></td>
                                        <!-- <td><?php echo (isset($publishers['split_id']) && $publishers['split_id'] != null ? $publishers['split_id'] : 'N/A'); ?></td> -->
                                        <td><?php echo $publishers['phone_no']; ?></td>
                                        <td>
                                            <?php if ($publishers['status'] == 1) {
                                                echo "Active";
                                            } else {
                                                echo "In Active";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a class="link-purple" href="<?= base_url('PublisherController/editPublisher/') . $publishers['id'] ?>">
                                                View</a>
                                            <!-- / <a class="link-purple deleteBlock" title="Delete" onclick="ConfirmPublisherDelete('<?php echo $publishers['id']; ?>');">Delete</a> -->
                                            <?php if (empty($this->session->userdata('userPermission')) || in_array('seller/database/write', $this->session->userdata('userPermission'))) { ?>
                                                /
                                                <a class="link-purple deleteBlock" title="Delete" onclick="ConfirmPublisherDelete('<?php echo $publishers['id']; ?>');">Delete</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php  } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end form-->
            </div>
        </div><!-- add new tab -->
    </div>
    </div>
</main>
<?php $this->load->view('common/fbc-user/footer'); ?>
<script src="<?php echo SKIN_JS; ?>publisher.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $("#datatableattribute").dataTable({
            "language": {
                "infoFiltered": "",
                "search": '',
                "searchPlaceholder": "Search",
                "paginate": {
                    next: '<i class="fas fa-angle-right"></i>',
                    previous: '<i class="fas fa-angle-left"></i>'
                }
            },
        });
    });
</script>