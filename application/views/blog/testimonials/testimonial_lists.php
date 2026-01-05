<?php $this->load->view('common/header'); ?>

<div class="main">
    <div class="container">
        <ul class="breadcrumb">
            <li><a href="<?php echo BASE_URL; ?>"><?=lang('home')?>Home</a></li>
            <li class="active">Testimonial</li>
        </ul>
        <div class="row margin-bottom-40">
            <div class="col-md-12">
                <div class="product-page pad20">
                    <div class="row equal">
                        <?php if(isset($testimonialLists->TestimonialsList) && count($testimonialLists->TestimonialsList) > 0) { ?> 
                            <?php foreach($testimonialLists->TestimonialsList as $testimonial){ ?>
                                <?php if($testimonial->status == 1){ ?> 
                                    <div class="col-md-6">
                                        <figure class="snip1533">
                                            <figcaption>
                                                <p><?php echo $testimonial->testimonial; ?></p>
                                                <h3><?php echo $testimonial->client_name; ?></h3>
                                                <h4><?php echo $testimonial->client_company; ?></h4>
                                            </figcaption>
                                        </figure>
                                    </div>
                                <?php } ?> 
                            <?php } ?>
                        <?php } else { ?>
                            <div class="col-md-12">
                                <p>No Testimonial available!</p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('common/footer'); ?>