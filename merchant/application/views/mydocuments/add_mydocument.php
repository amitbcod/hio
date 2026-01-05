<?php 
$this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner">
    <h1 class="head-name mb-4">Add Document</h1>
    <div class="card card-section-new">
        <div class="card-body">
           <form id="docForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="">Documents Name <span class="text-danger">*</span></label>
                    <input type="text" id="document_name" name="document_name" class="form-control" required>
                </div>
                <div class="form-group form-setion-new">
                    <input type="file" id="document_file" name="document_file" class="form-control">
                    <p>Please upload only .jpg, png, rtf, doc, pdf, ppt, xls, gif files and filesize should be not more then 5MB</p>
                </div>
                <button type="button" id="saveBtn" class="btn btn-primary">Save</button>
            </form>

            <div id="responseMsg"></div>


        </div>
    </div>
</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $("#saveBtn").on("click", function (e) {
        e.preventDefault();

        var formData = new FormData($("#docForm")[0]); // pick form with files

        $.ajax({
            url: "<?php echo BASE_URL('mydocuments/insert'); ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",   // ✅ IMPORTANT
            success: function (response) {
                if (response.status == 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect_url; // redirect
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: response.message
                    });
                }
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong!'
                });
            }
        });

    });

</script>
<?php $this->load->view('common/fbc-user/footer'); ?>
