<?php $this->load->view('common/fbc-user/header'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/css/bootstrap-multiselect.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/js/bootstrap-multiselect.min.js"></script>



<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

    

    <!-- Breadcrumbs -->

    <?php //$this->load->view('seller/products/breadcrums'); ?>



    <div class="tab-content">

        <!-- Add-Ons Services Tab -->

        <div id="addons-services" class="tab-pane fade show active">



            <?php if ($this->session->flashdata('success')): ?>

                <div class="alert alert-success alert-dismissible fade show" role="alert">

                    <?= $this->session->flashdata('success'); ?>

                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">

                        <span aria-hidden="true">&times;</span>

                    </button>

                </div>

            <?php endif; ?>



            <h1 class="head-name mb-4">Product Badges</h1>


            <?php if (!empty($pb_category)): ?>
                <?php $catIndex = 1; ?>
                <div class="accordion accordion-new" id="categoryAccordion">
                    <?php foreach ($pb_category as $category): ?>
                        <div class="card mb-3">
                            <!-- Card header with category id -->
                            <div class="card-header category-title collapsed" data-bs-toggle="collapse" data-bs-target="#cat<?= $catIndex ?>" aria-expanded="false" aria-controls="cat<?= $catIndex ?>" data-cat-id="<?= $category['id'] ?>">
                                <h3 class="card-title card-new mb-0">
                                    <i class="fa fa-angle-right mr-2"></i> <?= htmlspecialchars($category['name']) ?>
                                </h3>
                            </div>

                            <div id="cat<?= $catIndex ?>" class="collapse" data-bs-parent="#categoryAccordion" data-cat-id="<?= $category['id'] ?>">
                                <div class="card-body">
                                    <div class="loader text-center py-3" style="display:none;">Loading...</div>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" class="select-all"></th> <!-- master checkbox -->
                                                <th>Product Name</th>
                                                <th>Product SKU</th>
                                                <th>Product Status</th>
                                                <th>Details</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-success btn-sm approve-btn">Approve</button>
                                        <button type="button" class="btn btn-danger btn-sm reject-btn">Reject</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $catIndex++; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No categories available.</div>
            <?php endif; ?>


        </div>



        <!-- Placeholder for other tabs if needed -->

        <?php $this->load->view('seller/products/add_new_type'); ?>

        <div id="dropshipping-products" class="tab-pane fade">

            <h3>Menu 2</h3>

            <p></p>

        </div>



    </div>

</main>



<?php $this->load->view('common/fbc-user/footer'); ?>



<!-- Page-specific CSS -->
<style>
    h2.category-title {
        padding-left: 20px;
        margin-bottom: 5px !important;
        padding-bottom: 0;
        padding-top: 10px
    }
    .card-header.category-title {
        background-color: #FFD703;
        color: #000000;
        cursor: pointer;
        padding: 18px;
        width: 100%;
        border: none;
        text-align: center;
        outline: none;
        font-size: 20px;
        transition: background-color 0.4s, color 0.4s;
    }

     .card-header.category-title.collapsed {
        background-color: #FFD703;
        color: #000000;
    }

    .card-header.category-title[aria-expanded="true"] {
        background-color: #0F5CD0;
        color: #fff;
        cursor: pointer;
        padding: 18px;
        width: 100%;
        border: none;
        text-align: center;
        outline: none;
        font-size: 20px;
        transition: background-color 0.4s, color 0.4s;
    }

    .card-header.category-title:hover {
      
    }
    .card-new {
        text-align: left !important;
    }
    .card-header h3.card-title.mb-0 i {
        display: none;
    }

    .multi-collapse.collapse.show {
        margin-top: 30px;
    }

    #categoryTabs-1 ul.nav.nav-pills li {
        border-right: 1px solid #ccc;
        color: #868686;
        line-height: 2;
        position: relative;
        font-weight: 600;
        background: #f1f1f1;
        margin: 0 !IMPORTANT;
        padding: 0 !important; 
    }

    .custom-multiselect {
        position: relative;
        width: 100%;
    }
    .custom-multiselect button {
        width: 100%;
        text-align: left;
    }
    .custom-multiselect .dropdown-menu {
        max-height: 250px;
        overflow-y: auto;
        width: 100%;
    }
</style>
<style>

.category-title {

    background: #f4f6f9;

    padding: 8px 12px;

    cursor: pointer;

    border-bottom: 1px solid #ddd;

    font-size: 16px;

    font-weight: 600;

}

.category-title h3 {

    margin: 0;

    font-size: inherit;

}

.category-title.collapsed i {

    transform: rotate(0deg);

    transition: transform 0.3s;

}

.category-title:not(.collapsed) i {

    transform: rotate(90deg);

    transition: transform 0.3s;

}



.service-card {

    border: 1px solid #ddd;

    border-radius: 6px;

    background: #fff;

    transition: 0.3s;

    height: 100%;

}

.service-card:hover {

    box-shadow: 0px 4px 12px rgba(0,0,0,0.1);

    transform: translateY(-3px);

}

.service-card img {

    border-radius: 6px;

    max-height: 140px;

    object-fit: cover;

}

.service-card h5 {

    font-size: 16px;

    font-weight: 600;

    margin-top: 10px;

}

.service-card p {

    font-size: 14px;

    color: #666;

    min-height: 40px;

}

.service-card strong {

    font-size: 15px;

    color: #222;

}

.service-card a.text-dark:hover {

    text-decoration: none;

}

.loader {
    POSITION: absolute;
    top: 42px;
    left: 48%;
}

@media (max-width:767px){
    .accordion  .card-body {
    overflow: scroll;
    }
}

</style>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<!-- Page-specific JS -->
<script>
$(document).ready(function () {
    // Listen for clicks on category accordion headers
    document.addEventListener("DOMContentLoaded", function () {
        const accordions = document.querySelectorAll('#categoryAccordion .collapse');

        accordions.forEach(function (collapseEl) {
            collapseEl.addEventListener('shown.bs.collapse', function (e) {
                let icon = e.target.previousElementSibling.querySelector('.fa');
                if (icon) {
                    icon.classList.remove('fa-angle-right');
                    icon.classList.add('fa-angle-down');
                }
            });

            collapseEl.addEventListener('hidden.bs.collapse', function (e) {
                let icon = e.target.previousElementSibling.querySelector('.fa');
                if (icon) {
                    icon.classList.remove('fa-angle-down');
                    icon.classList.add('fa-angle-right');
                }
            });
        });
    });


    $(document).on('click', '.card-header.category-title', function () {
        let $header     = $(this);
        let catId       = $header.data('cat-id');  
        let targetId    = $header.attr('data-bs-target');    // <-- change from data('bs-target')
        let $tabPane    = $(targetId);
        let $tableBody  = $tabPane.find('tbody');
        let $loader     = $tabPane.find('.loader');

        const productImageBase = "<?= IMAGE_URL_SHOW . '/products/thumb/' ?>";
        const base_url = "<?= IMAGE_URL; ?>";

        if ($tableBody.children().length > 0) return;

        $loader.show();

        $.ajax({
            url: "<?= base_url('ProductBadges/getAppliedProducts/') ?>" + catId,
            type: "GET",
            dataType: "json",
            success: function (response) {
                $loader.hide();
                $tableBody.empty();
                if (response.length > 0) {
                    $.each(response, function (i, prod) {
                        let imageHtml = prod.base_image
                            ? `<img src="${encodeURI(productImageBase + prod.base_image)}" width="80">`
                            : '';
                        $tableBody.append(`
                            <tr data-product-id="${prod.id}">
                                <td><input type="checkbox" class="product-checkbox"></td>
                                <td>${prod.name}</td>
                                <td>${prod.sku}</td>
                                <td>
                                    ${prod.status === 'approve' 
                                        ? 'Approved' 
                                        : prod.status === 'pending' 
                                            ? 'Pending' 
                                            : prod.status === 'reject' 
                                                ? 'Rejected' 
                                                : prod.status}
                                </td>
                                <td><a class="link-purple" href="${base_url}/product-detail/${prod.url_key}">View</a></td>

                            </tr>
                        `);
                    });
                    $tabPane.addClass('show');
                } else {
                    $tableBody.append(`<tr><td colspan="5" class="text-center">No products found</td></tr>`);
                }
            },
            error: function () {
                $loader.hide();
                $tableBody.html(`<tr><td colspan="5" class="text-center text-danger">Error loading products</td></tr>`);
            }
        });
    });

   
    // ✅ Select/Deselect all checkboxes
    $(document).on('change', '.select-all', function () {
        let isChecked = $(this).is(':checked');
        $(this).closest('table').find('.product-checkbox').prop('checked', isChecked);
    });

    // ✅ If any single checkbox is unchecked → uncheck master
    $(document).on('change', '.product-checkbox', function () {
        let $table = $(this).closest('table');
        let allChecked = $table.find('.product-checkbox').length === $table.find('.product-checkbox:checked').length;
        $table.find('.select-all').prop('checked', allChecked);
    });

    
    $(document).on('click', '.approve-btn, .reject-btn', function () {
        let $accordionBody = $(this).closest('.collapse'); 
        let newStatus = $(this).hasClass('approve-btn') ? 'approve' : 'reject';
        let prodBadgeCatId = $accordionBody.data('cat-id'); 
        let updates = [];

        // ✅ Loop only checked rows
        $accordionBody.find('tbody tr').each(function () {
            let $row = $(this);
            if ($row.find('.product-checkbox').is(':checked')) { // only checked rows
                let productId = $row.data('product-id');

                // update UI
                $row.find('.status-cell').text(newStatus);   
                $row.attr('data-new-status', newStatus);     

                // add to updates array
                updates.push({
                    id: productId,
                    status: newStatus,
                    prod_badge_cat_id: prodBadgeCatId
                });
            }
        });

        if (updates.length === 0) {
            alert("Please select at least one product.");
            return;
        }

        $.ajax({
            url: "<?= base_url('ProductBadges/updateStatuses') ?>",
            type: "POST",
            data: { updates: updates },
            dataType: "json",
            success: function(response) {
                console.log(response);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Status updated successfully!',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: 'Failed to update statuses!',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Something went wrong while updating statuses!',
                    confirmButtonText: 'OK'
                });
            }

        });
    });

});


$(function() {
    $('#productList').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        nonSelectedText: 'Select Products',
        numberDisplayed: 2,
        maxHeight: 300
    });
});
</script>
<script>

$(document).ready(function(){	

    // Toggle arrow icon on collapse show/hide

    $('.category-title').on('click', function(){

        $(this).toggleClass('collapsed');

    });

});

</script>

<script>
$(document).ready(function () {
    $('.category-title').on('click', function () {
        const targetId = $(this).data('bsTarget');
        const $target = $(targetId);

        if ($target.hasClass('show')) {
            $target.removeClass('show');
            $(this).addClass('collapsed');
        } else {
            $('.collapse').removeClass('show');
            $('.category-title').addClass('collapsed');

            $target.addClass('show');
            $(this).removeClass('collapsed');
        }

        // Toggle arrow icon
        $(this).find('i').toggleClass('fa-angle-right fa-angle-down');
    });
});
</script>

