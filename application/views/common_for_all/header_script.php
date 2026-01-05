



<script src="<?php echo SKIN_URL ?>plugins/jquery.min.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript"></script>

<script src="<?php echo SKIN_URL ?>plugins/jquery-migrate.min.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript"></script>

<script src="<?php echo SKIN_URL ?>plugins/bootstrap/js/bootstrap.min.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript"></script>
<script src="<?php echo SKIN_URL ?>plugins/bootstrap/js/bootstrap.bundle.min.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript"></script>

<script src="<?php echo SKIN_URL ?>corporate/scripts/back-to-top.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript"></script>

<script src="<?php echo SKIN_URL ?>plugins/jquery-slimscroll/jquery.slimscroll.min.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript"></script>

<script src="<?php echo SKIN_URL ?>js/jquery.date-dropdowns.min.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript"></script>

<script src="<?php echo SKIN_JS; ?>jquery.validate.min.js"></script>

<script src="<?php echo SKIN_JS; ?>additional-methods.min.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<!-- END CORE PLUGINS -->



<!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) -->

<script src="<?php echo SKIN_URL ?>plugins/fancybox/source/jquery.fancybox.pack.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript"></script><!-- pop up -->

<script src="<?php echo SKIN_URL ?>plugins/owl.carousel/owl.carousel.min.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript"></script><!-- slider for products -->

<script src='<?php echo SKIN_URL ?>plugins/zoom/jquery.zoom.min.js?v=<?php echo CSSJS_VERSION; ?>' type="text/javascript"></script><!-- product zoom -->

<script src="<?php echo SKIN_URL ?>plugins/bootstrap-touchspin/bootstrap.touchspin.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript"></script><!-- Quantity -->





<!-- sweetalert start -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>





<script src="<?php echo SKIN_URL ?>corporate/scripts/layout.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript"></script>

<script src="<?php echo SKIN_URL ?>pages/scripts/bs-carousel.js?v=<?php echo CSSJS_VERSION; ?>" type="text/javascript"></script>



<script src="<?php echo SKIN_JS ?>main.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script src="<?php echo SKIN_JS ?>common.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script src="<?php echo SKIN_JS ?>navbar.js?v=<?php echo CSSJS_VERSION; ?>"></script>



<script type="text/javascript">

jQuery(document).ready(function() {

    Layout.init();

    Layout.initOWL();

    Layout.initImageZoom();

    Layout.initTouchspin();

    Layout.initTwitter();



    Layout.initFixHeaderWithPreHeader();

    Layout.initNavScrolling();



});



</script>



    <script type="text/javascript">

      var BASE_URL='<?php echo BASE_URL; ?>';
      var SHOP_FLAG='<?php echo $shop_flag ?? ''; ?>';
      var CAPTCHA_CHECK_FLAG='<?php echo $captcha_check_flag ?? ''; ?>';
    </script>

    
<script>
$(document).ready(function() {
    $('#switcher-language-trigger-nav').on('click', function() {
        $('.mage-dropdown-dialog').toggle();
    });

    $(document).on('click', '.changeLanguage', function(e) {
        e.preventDefault();
        var lang_id = $(this).data('id');

        $.ajax({
            url: "<?= base_url('HomeController/updateCurrentLanguage'); ?>",
            type: "POST",
            dataType: "json",
            data: { language_id: lang_id },
            success: function(response) {
                if (response.flag == 1) {
                    location.reload();
                } else {
                    alert(response.msg);
                }
            },
            error: function() {
                alert('Something went wrong while switching language.');
            }
        });
    });
});
</script>
