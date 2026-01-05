<?php $this->load->view('common/header'); ?>
<?php $cmsData = $pagedata->cms_page_detail; ?>
	<div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="<?php echo BASE_URL; ?>"><?=lang('home')?></a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active">
					<?php echo ((isset($cmsData->lang_cms_title) && $cmsData->lang_cms_title!='') ? $cmsData->lang_cms_title : $cmsData->title);?>	
					</li>
				</ul>
			</div>
        </div>
    </div><!-- breadcrum section -->
	  
    <div class="static-page">
		<div class="container">
        <div class="col-md-12">
          <!-- <div class="container"> -->
				<?php echo ((isset($cmsData->lang_cms_content) && $cmsData->lang_cms_content!='') ? $cmsData->lang_cms_content : $cmsData->content);?>
		  <!-- </div> --> <!-- container-->
		</div><!-- col-md-12-->
		<?php if($identifier == 'giftcardchecker'){ ?>
			<?php (new GiftCardChecker())->render(); ?>
		<?php } ?>
		</div><!-- container -->
	</div><!-- static pages -->

	<?php $this->load->view('common/footer'); ?>
	</body>
</html>