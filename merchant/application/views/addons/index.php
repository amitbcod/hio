<?php $this->load->view('common/fbc-user/header'); ?>

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

            <h1 class="head-name mb-4">Add-Ons Services</h1>

            <?php if (!empty($addon_data)): ?>
                <?php $catIndex = 1; ?>
                <?php foreach ($addon_data as $category => $services): ?>

                    <!-- Category Header -->
                    <div class="card mb-3">
                        <div class="card-header category-title collapsed" data-toggle="collapse" data-target="#cat<?= $catIndex ?>">
                            <h3 class="card-title mb-0">
                                <i class="fa fa-angle-right mr-2"></i> <?= $category ?>
                            </h3>
                        </div>

                        <!-- Category Services -->
                        <div id="cat<?= $catIndex ?>" class="collapse card-body">
                            <div class="row">
                                <?php foreach ($services as $service): ?>
                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                        <div class="service-card text-center p-3">

                                            <!-- Clickable Image -->
                                            <a href="<?= site_url('addons/details/'.$service['service_id']); ?>">
                                                <img src="<?= !empty($service['image']) 
                                                    ? base_url('images/public/uploads/services/'.$service['image']) 
                                                    : base_url('public/images/placeholder.png') ?>" 
                                                    class="img-fluid mb-2" 
                                                    alt="<?= $service['title'] ?>">
                                            </a>

                                            <!-- Clickable Title -->
                                            <h5>
                                                <a href="<?= site_url('addons/details/'.$service['service_id']); ?>" class="text-dark">
                                                    <?= $service['title'] ?>
                                                </a>
                                            </h5>

                                            <!-- <p><?= $service['description'] ?></p> -->
                                            <strong class="d-block mb-2">MUR <?= number_format($service['price'], 0) ?></strong>

                                            <!-- Buy Now Button -->
                                            <a href="<?= site_url('addons/details/'.$service['service_id']); ?>" 
                                               class="btn btn-primary btn-sm">View</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <?php $catIndex++; ?>
                <?php endforeach; ?>
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
$(document).ready(function(){
    // Toggle arrow icon on collapse show/hide
    $('.category-title').on('click', function(){
        $(this).toggleClass('collapsed');
    });
});
</script>
