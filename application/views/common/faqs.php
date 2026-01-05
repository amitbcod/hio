<?php $this->load->view('common/header') ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>

<div class="main">
    <div class="container">
        <!-- BEGIN SIDEBAR & CONTENT -->

        <div class="row margin-bottom-40 faqs-page box-center">
            <div class="col-md-12">
                <h1>Your Information</h1>
            </div>
            <!-- BEGIN CONTENT -->
            <div class="col-md-12">
                <div class="content-page shadow">
                    <form method="POST" id="customer-personal-info-form"
                        action="<?php echo BASE_URL; ?>HomeController/faqs_post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter your name"
                                        value="<?= (isset($_SESSION) && isset($fnln)) ? $fnln : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="text" class="form-control" id="email" placeholder="Enter your email"
                                        name="email"
                                        value="<?= (isset($_SESSION) && isset($_SESSION['EmailID'])) ? $_SESSION['EmailID'] : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Enter Your Question</label>
                                    <textarea placeholder="Question" class="form-control" rows="5" name="question"
                                        id="question"></textarea>
                                </div>
                            </div>

                        </div>
                        <div class="form-input">
                            <!-- Google reCAPTCHA box -->
                            <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY_V2; ?>"></div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 padding-top-20">
                                <button class="btn btn-primary" type="submit">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>


        <div class="row faqs-que-ans">
            <div class="col-md-12">
                <div class="table-responsive">
                    <h2>Faqs</h2>

                    <?php if (!empty($faq_list)): ?>
                     

                        <div class="accordion" id="accordionExample">
                            <?php $sr = 1; ?>
                            <?php foreach ($faq_list as $faq): ?>

                                <?php
                                $headingId = 'heading' . $sr;
                                $collapseId = 'collapse' . $sr;
                                $showClass = ($sr === 1) ? 'show' : ''; // Only first item open
                                $expanded = ($sr === 1) ? 'true' : 'false';
                                ?>

                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="<?= $headingId; ?>">
                                        <button class="accordion-button <?= $showClass ? '' : 'collapsed'; ?>" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#<?= $collapseId; ?>"
                                            aria-expanded="<?= $expanded; ?>" aria-controls="<?= $collapseId; ?>">
                                            <strong>Q.</strong><span class="capitalize"><?= htmlspecialchars($faq->question); ?></span>
                                        </button>
                                    </h2>
                                    <div id="<?= $collapseId; ?>" class="accordion-collapse collapse <?= $showClass; ?>"
                                        aria-labelledby="<?= $headingId; ?>" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <strong>A.</strong><span><?= htmlspecialchars($faq->answer); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php $sr++; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No FAQs found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>


 



        <!-- END SIDEBAR & CONTENT -->
    </div>
</div>
<?php $this->load->view('common/footer') ?>
<script>
    $(document).ready(function () {
        $('#customer-personal-info-form').on('submit', function (e) {
            e.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                beforeSend: function () {
                    $('#customer-personal-info-form button[type="submit"]').prop('disabled', true).text('Submitting...');
                },
                success: function (response) {
                    if (response.flag == 1) {
                        // Success swal
                        swal({
                            title: "Success!",
                            text: response.msg,
                            icon: "success",
                            buttons: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        // Error swal
                        swal({
                            title: "Error",
                            text: response.msg,
                            icon: "error",
                            button: "OK"
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                    swal({
                        title: "Oops!",
                        text: "Something went wrong. Please try again.",
                        icon: "error",
                        button: "OK"
                    });
                },
                complete: function () {
                    $('#customer-personal-info-form button[type="submit"]').prop('disabled', false).text('Submit Ticket');
                }
            });
        });
    });
</script>