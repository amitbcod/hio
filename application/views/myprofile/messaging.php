<?php $this->load->view('common/header'); ?>

<div class="breadcrum-section">

    <div class="container">

        <div class="breadcrum">

            <ul class="breadcrumb">

                <li><a href="<?php echo base_url(); ?>">Home</a></li>

                <!--<li><span class="icon icon-keyboard_arrow_right"></span></li>-->

                <li class="active">Messaging</li>

            </ul>

        </div>

    </div>

</div><!-- breadcrum section -->


<div class="my-profile-page-full">

    <div class="container">

        <div class="row">

            <?php $this->load->view('common/profile_sidebar'); ?>

            <div class="col-sm-9 col-md-9">

                <div class="content-page">

                    <div class="row">

                        <div class="col-sm-4 col-md-6">

                            <h1>Messages</h1>

                        </div>

                        <div class="row">

                            <div class="col-md-12">
                            <table class="table table-bordered mt-3">
                                <thead>
                                    <tr>
                                        <th>SR No</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Message</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $sr = 1; ?>
                                    <?php foreach ($messaging_data as $msg): ?>
                                        <tr>
                                            <td><?= $sr++; ?></td>
                                            <td><?= $msg->name; ?></td>
                                            <td><?= $msg->category; ?></td>
                                            <td><?= $msg->message; ?></td>
                                            <td>
                                                <a href="<?= base_url('MyProfileController/viewMessage/' . $msg->product_id); ?>">
                                                    View Messages
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            </div>
                        </div>
                    </div><!-- .content-page ends -->

                </div>


            </div><!-- row -->

        </div><!-- container -->

    </div><!-- my-profile-page-full -->



    <?php $this->load->view('common/footer'); ?>
    <script>
        $(document).ready(function() {
            $('#order_id').on('change', function() {
                var order_id = $(this).val();

                $.ajax({
                    url: '<?= base_url("MyProfileController/get_order_products") ?>',
                    type: 'POST',
                    data: {
                        order_id: order_id
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#product_id').empty();
                        $('#product_id').append('<option value="">Select a product</option>');

                        if (response.length > 0) {
                            $.each(response, function(index, product) {
                                $('#product_id').append('<option value="' + product.product_id + '">' + product.name + ' (Qty: ' + product.qty + ')</option>');
                            });
                        }
                    }
                });
            });

        });
    </script>

    <!-- <script src="<?php echo SKIN_JS ?>myprofile.js?v=<?php echo CSSJS_VERSION; ?>"></script> -->


    </body>



    </html>