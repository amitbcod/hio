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

                <div class="accordion" id="categoryAccordion">
                    <?php foreach ($pb_category as $category): ?>
                        <div class="card mb-3">
                            <div class="card-header category-title collapsed" data-toggle="collapse" data-target="#cat<?= $catIndex ?>" aria-expanded="false" aria-controls="cat<?= $catIndex ?>">
                                <h3 class="card-title mb-0">
                                    <i class="fa fa-angle-right mr-2"></i> <?= htmlspecialchars($category['name']) ?>
                                </h3>
                            </div>

                            <div id="cat<?= $catIndex ?>" 
                                class="collapse card-body" 
                                data-parent="#categoryAccordion">
                                <div class="row">
                                    <!-- Left side (tabs) -->
                                    <div class="col-md-3">
                                        <ul class="nav flex-column nav-pills" id="categoryTabs-<?= $catIndex ?>" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="tab-cond-<?= $catIndex ?>-tab" data-toggle="pill" href="#tab-cond-<?= $catIndex ?>" role="tab">
                                                    Condition
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-apply-<?= $catIndex ?>-tab" data-toggle="pill" href="#tab-apply-<?= $catIndex ?>" role="tab">
                                                    Apply
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-app-link-<?= $category['id'] ?>" href="#tab-app-<?= $category['id'] ?>" data-cat-id="<?= $category['id'] ?>" data-toggle="pill" role="tab">
                                                    Application
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Right side (tab panes) -->
                                    <div class="col-md-9">
                                        <div class="tab-content" id="categoryTabsContent-<?= $catIndex ?>">
                                            <div class="tab-pane fade show active" id="tab-cond-<?= $catIndex ?>" role="tabpanel">
                                                <?= $category['main_content'] ?? '' ?>
                                            </div>
                                            <div class="tab-pane fade" id="tab-apply-<?= $catIndex ?>" role="tabpanel">
                                                <form method="POST" action="<?= base_url('ProductBadges/submitApply') ?>" id="productBlockForm">
                                                    <div class="row">
                                                        <input type="hidden" name="prod_badge_cat_id" class="form-control" value="<?= $category['id'] ?>">

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="">Company Name:</label>
                                                                <input type="text" name="company_name" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">BRN :</label>
                                                                <input type="text" name="brn" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Contact Person :</label>
                                                                <input type="text" name="contact_person" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Contact Mobile :</label>
                                                                <input type="text" name="mobile" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Contact Email :</label>
                                                                <input type="text" name="email" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Production Location :</label>
                                                                <input type="text" name="location" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="custom-multiselect mb-3">
                                                                <label for="">Products Names :</label>
                                                                <button class="btn btn-light dropdown-toggle" type="button" data-toggle="dropdown">
                                                                    Select Products
                                                                </button>
                                                                <div class="dropdown-menu p-2">
                                                                    <?php if (!empty($productList)): ?>
                                                                        <?php foreach ($productList as $val): ?>
                                                                            <?php 
                                                                                $launchDate = is_numeric($val->launch_date) 
                                                                                    ? date("d-m-Y", $val->launch_date) 
                                                                                    : date("d-m-Y", strtotime($val->launch_date));
    
                                                                                $checked = (!empty($products_arr) && in_array($val->id, $products_arr)) 
                                                                                    ? 'checked' 
                                                                                    : '';
                                                                            ?>
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="checkbox" 
                                                                                    name="productList[]" 
                                                                                    id="product_<?= $val->id ?>" 
                                                                                    value="<?= htmlspecialchars($val->id) ?>" <?= $checked ?>>
                                                                                <label class="form-check-label" for="product_<?= $val->id ?>">
                                                                                    <?= htmlspecialchars($val->name . ' - ' . $val->product_code . ' - ' . $launchDate) ?>
                                                                                </label>
                                                                            </div>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div class="custom-multiselect mb-3">
                                                                <label for="">Documents Names :</label>
                                                                <button class="btn btn-light dropdown-toggle" type="button" data-toggle="dropdown">
                                                                    Select Documents
                                                                </button>
                                                                <div class="dropdown-menu p-2">
                                                                    <?php if (!empty($documentList)): ?>
                                                                        <?php foreach ($documentList as $val): ?>
                                                                            <?php 
                                                                                // Check if document is pre-selected
                                                                                $checked = (!empty($selectedDocuments) && in_array($val['id'], $selectedDocuments)) 
                                                                                            ? 'checked' 
                                                                                            : '';
                                                                            ?>
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" 
                                                                                    type="checkbox" 
                                                                                    name="documentList[]" 
                                                                                    id="document_<?= $val['id'] ?>" 
                                                                                    value="<?= htmlspecialchars($val['id']) ?>" <?= $checked ?>>
                                                                                <label class="form-check-label" for="document_<?= $val['id'] ?>">
                                                                                    <?= htmlspecialchars($val['document_name']) ?>
                                                                                </label>
                                                                            </div>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="">User Accept T&C :</label>
                                                                <input type="text" name="terms" class="form-control">
                                                            </div>
                                                            <button type="submit" id="saveBtn" class="btn btn-primary">Save</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="tab-app-<?= $catIndex ?>" role="tabpanel">
                                                <div class="loader text-center py-3" style="display:none;">Loading...</div>
                                                <table class="table table-responsive table-bordered table-striped product-table-<?= $catIndex ?>">
                                                    <thead>
                                                        <tr>
                                                            <th>Product Name</th>
                                                            <th>Product SKU</th>
                                                            <th>Product Description</th>
                                                            <th>Product Image</th>
                                                            <th>Product Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $catIndex++; ?>
                    <?php endforeach; ?>
                </div>



            <?php else: ?>

                <div class="alert alert-info">No add-ons available right now.</div>

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

</style>



<!-- Page-specific JS -->
<script>
$(document).ready(function () {
    // Listen for clicks on any "Application" tab by ID prefix
    $(document).on('click', '[id^="tab-app-link-"]', function (e) {
        e.preventDefault();

        let $tabLink = $(this);
        let catId = $tabLink.data('cat-id');     // ✅ actual category ID
        let tabId = $tabLink.attr('href');       // e.g. #tab-app-7
        let $tabPane = $(tabId);
        let $tableBody = $tabPane.find('tbody');
        let $loader = $tabPane.find('.loader');
        const productImageBase = "<?= SIS_SERVER_PATH . '/uploads/products/' ?>";
        // prevent duplicate load
        if ($tableBody.children().length > 0) {
            return;
        }

        $loader.show();

        $.ajax({
            url: "<?= base_url('ProductBadges/getAppliedProducts/') ?>" + catId,
            type: "GET",
            dataType: "json",
            success: function (response) {
                $loader.hide();
                if (response.length > 0) {
                    $.each(response, function (i, prod) {
                        let statusBadge = (prod.status == 1)
                            ? '<span class="badge badge-success">Enabled</span>'
                            : '<span class="badge badge-danger">Disabled</span>';

                        let imageHtml = prod.base_image
                            ? `<img src="${productImageBase}${prod.base_image}" width="80">`
                            : '';


                        $tableBody.append(`
                            <tr>
                                <td>${prod.name}</td>
                                <td>${prod.sku}</td>
                                <td>${prod.description}</td>
                                <td>${imageHtml}</td>
                                <td>${statusBadge}</td>
                            </tr>
                        `);
                    });
                } else {
                    $tableBody.append(`<tr><td colspan="5" class="text-center">No products found</td></tr>`);
                }
            },
            error: function () {
                $loader.hide();
                $tableBody.append(`<tr><td colspan="5" class="text-center text-danger">Error loading products</td></tr>`);
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

