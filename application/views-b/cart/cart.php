<?php $this->load->view('common/header');?>
<div class="breadcrum-section">
      <div class="container">
			<div class="breadcrum">
				<ul>
					<li><a href="<?php echo base_url(); ?>">Home</a></li>
					<li><span class="icon icon-keyboard_arrow_right"></span></li>
					<li class="active">Cart</li>
				</ul>
			</div>
        </div>
      </div>
<div class="cart-page-full">
      <div class="container">
        <div class="col-md-12">
          <div class="row">
          	<?php (new CartList())->render(); ?>
          </div><!-- row -->
        </div><!-- col-md-12 -->
      </div><!-- container -->
    </div>

<?php $this->load->view('common/footer'); ?>
<script type="text/javascript" src="<?php echo SKIN_JS; ?>cart.js?v=<?php echo CSSJS_VERSION; ?>"></script>

</body>
</html>
