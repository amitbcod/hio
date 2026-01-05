<?php $this->load->view('common/fbc-user/header'); ?>
		<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.3/themes/ui-lightness/jquery-ui.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <div class="tab-content">
    <div id="Special Pricing" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
        <!-- form -->
        <form action="<?php echo base_url(); ?>B2BController/save_special_pricing" method="POST" name="b2b_special_pricing_form" id="b2b_special_pricing_form">
			<div class="customize-add-section pad-t-20">
				<div class="row">
				<div class="left-form-sec">

					<div class="col-sm-6 customize-add-inner-sec">
						<input type="text" class="form-control" id="sku" name="sku" placeholder="Product Name - SKU - Barcode">
						<input type="hidden" class="form-control" id="product_id" name="product_id" >
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 customize-add-inner-sec">
						<label >Selling Price :(<span class="symbol"><?php echo $currency_symbol; ?></label>
						<input type="text" class="form-control" readonly name="price" value="" id="price">
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 customize-add-inner-sec">
						<label>Special Price :</label>
						<input class="form-control" type="text" name="special_price" step="0.01" id="special_price" value="" required placeholder="Special Price">
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 customize-add-inner-sec  from-to">
						<label>From</label>
						<input class="form-control" type="date" required  name="from_date" id="from_date" value="" placeholder="">
					</div><!-- col-sm-6 -->
				</div>
				<div class="right-form-sec coupon-code-select">
					<div class="col-sm-6 customize-add-inner-sec">
						&nbsp;
					</div><!-- col-sm-6 -->
					<div class="col-sm-6 customize-add-inner-sec">
						<p>&nbsp;</p>
					</div><!-- col-sm-6 -->
					<div class="col-sm-6 customize-add-inner-sec">
						<p>&nbsp;</p>
					</div><!-- col-sm-6 -->

					<div class="col-sm-6 customize-add-inner-sec from-to">
						<label>To</label>
						<input class="form-control" type="date" required  name="to_date" id="to_date" value="" placeholder="">
					</div><!-- col-sm-6 -->
				</div>
				</div><!-- row -->
			</div><!-- customize-add-section -->
				<div class="download-discard-small mar-top">
					<input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
					<button class="white-btn" name="discard_btn" id="discard_btn" onclick="gotoLocation('<?= $special_pricing_link; ?>');">Discard</button>
				<?php if (empty($this->session->userdata('userPermission')) || in_array('b2webshop/customers/write', $this->session->userdata('userPermission'))) { ?>
					<button class="download-btn" name="save_special_pricing" id="save_special_pricing" value="save" >Save</button>
				<?php } ?>
			 	</div><!-- download-discard-small  -->
		</form>
        <!--end form-->
    </div>
  </div>
    </main>
  <script>
		$('#sku').autocomplete({
		    minLength: 3,
		    source: function(request, response) {
		        $.getJSON(BASE_URL+"InboundController/getProductSku", {
		            term: request.term,search_for_barcode_flag:1
		        }, function(data) {
		            var array = data.error ? [] : $.map(data, function(m) {
		                return {
		                    label: m.name+" - "+m.sku+" - "+m.barcode,
		                    value: m.sku,
		                    prod_id: m.id,
		                };
		            });
		            response(array);
		         });
		    },
		    select: function (event, ui) {
		        $('#sku').val(ui.item.label);
		        $( "#product_id" ).val( ui.item? ui.item.prod_id : "" );
		        return false;
		    },
		    focus: function( event, ui ) {

		        $('#sku').val(ui.item.label);
		        return false;
		    },
		    change: function( event, ui ) {
		        $( "#sku" ).val( ui.item? ui.item.label : "" );
		    }
		});
	</script>
  <script src="<?php echo SKIN_JS; ?>b2b_special_pricing_add.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
