<?php $this->load->view('common/header'); ?>
<div class="breadcrum-section">
    <div class="container">
        <div class="breadcrum">
            <ul>
                <li><a href="<?php echo base_url(); ?>">Home</a></li>
                <li><span class="icon icon-keyboard_arrow_right"></span></li>
                <li class="active">Testimonial</li>
            </ul>
        </div>
    </div>
</div><!-- breadcrum section -->
<div class="my-profile-page-full">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="page-title testimonial-title">
                    <h4 class="manage-add-head upc-head"><span>Testimonial</span></h4>
                </div>
                <div class="grytext">
                    <?php //echo "<pre>";print_r($testimonialLists);die();
                $testimonialListsArr = json_decode(json_encode($testimonialLists), true);
                //echo "<pre>";print_r($testimonialListsArr);die();
                                    foreach ($testimonialListsArr['TestimonialsList'] as $key => $value) {
                                    //for($i=0; $i<count($value); $i++) {
                                        
                                ?>
                    <ul id="hmtestimonial-list">
                        <?php if($key %2 == 0) { ?>
                        <?php //echo 1;die(); ?>
                        <li class="hmtestimonial even">
                            <div class="testimonial_img">
                                <a href="<?= $value['website']; ?>" target="_blank"><img
                                        src="<?= base_url().'../'.$value['image_path']; ?>" width="170"
                                        alt="<?= $value['client_name']; ?>"></a>
                            </div>
                            <div class="testimonial_left">
                                <div class="testimonial_info">
                                    <p><?= $value['testimonial']; ?></p>
                                </div>
                                <div class="test_clean"></div>
                                <div class="client_detail"> <?= $value['client_name']; ?><br>
                                    <?= $value['client_description']; ?><br> <?= $value['client_company']; ?><br>
                                    <?php if($value['video_url'] != '') { ?>
                                    <a class="btn btn-danger attachvideo" href="<?= $value['video_url']; ?>"
                                        target="_blank">Read More</a>
                                    <?php } ?>
                                    <?php if($value['pdf_path'] != '') { ?>
                                    <a class="btn btn-danger attachpdf"
                                        href="<?= base_url().'../'.$value['pdf_path']; ?>" target="_blank">Download
                                        PDF</a>
                                    <?php } ?>
                                    <br>
                                </div>
                            </div>
                        </li>
                        <?php } else { ?>
                        <?php //echo 2;die(); ?>
                        <li class="hmtestimonial odd">
                            
                            <div class="testimonial_left">
                                <div class="testimonial_info">
                                    <p><?= $value['testimonial']; ?></p>
                                </div>
                                <div class="test_clean"></div>
                                <div class="client_detail"> <?= $value['client_name']; ?><br>
                                    <?= $value['client_description']; ?><br> <?= $value['client_company']; ?><br>
                                    <?php if($value['video_url'] != '') { ?>
                                    <a class="btn btn-danger attachvideo" href="<?= $value['video_url']; ?>"
                                        target="_blank">Read More</a>
                                    <?php } ?>
                                    <?php if($value['pdf_path'] != '') { ?>
                                    <a class="btn btn-danger attachpdf"
                                        href="<?= base_url().'../'.$value['pdf_path']; ?>" target="_blank">Download
                                        PDF</a>
                                    <?php } ?>
                                    <br>
                                </div>
                            </div>
                            <div class="testimonial_img">
                                <a href="<?= $value['website']; ?>" target="_blank"><img
                                        src="<?= base_url().'../'.$value['image_path']; ?>" width="170"
                                        alt="<?= $value['client_name']; ?>"></a>
                            </div>
                        </li>
                        <?php } ?>
                    </ul>
                    <div class="grytext"></div>
                    <?php } ?>
                </div>
            </div><!--col-md-9-->
        </div><!--row-->
    </div><!-- container -->
</div><!-- my-profile-page-full -->
<?php $this->load->view('common/footer'); ?>
</body>

</html>