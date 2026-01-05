<?php $this->load->view('common/header'); ?>

<?php if(isset($error_page) && !empty($error_page) && SHOP_ID !== 3 )  {
    echo htmlspecialchars_decode(stripslashes($error_page->content));
} else { 
    (new TopMenu('categorymenu'))->not_found_page(); 
    ?>



				<h1><?=lang('oops')?></h1>

				<h4><?=lang('error_404_pnf')?></h4>

				<p><?=lang('the_page_you_requested_was_not_found')?></p>
				
				<?php// (new NewArrivalProducts(12,'grid'))->render(); ?>

				<div class="continue-shop-btn"><button class="black-btn" onclick="gotoLocation('<?php //echo base_url(); ?>');"><?=lang('go_back_to_home')?></button></div>
			</div>
		</div>
	</div>
</div> -->
<?php //}?>

<script src="<?php echo SKIN_JS ?>recliner.js?v=<?php echo CSSJS_VERSION; ?>"></script>

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
<?php } ?>

<?php $this->load->view('common/footer'); ?>