

<?php $this->load->view('common/header'); ?>

  <?php (new HomeCategoryBanners('homebanner'))->render(); ?>

  <!-- <?php (new NewArrivalProducts(25,'slider'))->render(); ?> -->

 <?php //(new HomeCategoryBanners('homeblock1'))->render(); ?> 

  <?php 
 (new FeaturedProducts())->render(); ?>

<script src="<?php echo SKIN_JS ?>product.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script src="<?=base_url('public/js/recliner.js')?>"></script>

  <script type="text/javascript">
   $(function() {

            // instantiate recliner
            $('.lazy').recliner({
                attrib: "data-src", // selector for attribute containing the media src
                throttle: 300,      // millisecond interval at which to process events
                threshold: 100,     // scroll distance from element before its loaded
                live: true          // auto bind lazy loading to ajax loaded elements
            });

            // handle lazyload events
            $(document).on('lazyload', '.lazy', function() {
                var $e = $(this);
                // do something with the element to be loaded...
                console.log('lazyload', $e);
            });

            // handle lazyshow events
            $(document).on('lazyshow', '.lazy', function() {
                var $e = $(this);
                // do something with the loaded element...
                console.log('lazyshow', $e);
            });
        });
  </script>
  <?php $this->load->view('common/footer'); ?>

  </body>
</html>

<script type="text/javascript">
    function openFeedbackWindow(openFeedbackWindow){
        if(openFeedbackWindow != ''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"CustomerController/open_feedback_popup",
            //data: {customer_id:customer_id},
            //async:false,
            complete: function () {
            },
            beforeSend: function(){
                // $('#ajax-spinner').show();
            },
            success: function(response) {
                $("#WebShopCommonModal").modal();
                $("#modal-content").html(response);
            }
        });
    }else{
        return false;
    }
    }
</script>