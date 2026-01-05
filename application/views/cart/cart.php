<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// print_r($data); ?>
<?php $this->load->view('common/header'); ?>
<div class="main">
    <div class="container">
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
            <?php (new CartList())->render(); ?>
        </div>
        <!-- END SIDEBAR & CONTENT -->


    </div>
</div>
<?php $this->load->view('common/footer'); ?>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>cart.js?v=<?php echo CSSJS_VERSION; ?>"></script>