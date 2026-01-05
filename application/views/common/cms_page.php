<?php $this->load->view('common/header'); ?>
<?php $cmsData = $pagedata->cms_page_detail; ?>

<div class="main">
    <div class="container">
        <ul class="breadcrumb">
            <li><a href="<?php echo BASE_URL; ?>"><?= lang('home') ?></a></li>
            <li class="active">
                <?= ($current_lang == 'french' && !empty($cmsData->lang_title))
                    ? $cmsData->lang_title
                    : $cmsData->title; ?>
            </li>
        </ul>

        <div class="row margin-bottom-40">
            <div class="col-md-12">
                <div class="product-page pad20">
                    <div class="row">
                        <div class="col-md-12">
                            <?= ($current_lang == 'french' && !empty($cmsData->lang_content))
                                ? $cmsData->lang_content
                                : $cmsData->content; ?>
                        </div>

                        <?php if ($identifier == 'giftcardchecker'): ?>
                            <?php (new GiftCardChecker())->render(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('common/footer'); ?>
