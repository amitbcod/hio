
<table class="table table-bordered table-style">
   <thead>
		<tr>
			<?php
			$sv_arr=array();
			if(isset($VariantList) && count($VariantList)>0){
			foreach($VariantList as $attr){
				$sv_arr[]=$attr['id'];

			?>
		  <th><?php echo $attr['attr_name']; ?> </th>
			<?php }
			} ?>
		  <th>INVENTORY</th>
		  <th>COST PRICE </th>
		  <th>SELLING PRICE </th>
		  <th>TAX (%) </th>
		  <th>WEBSHOP PRICE </th>
		  <th>SKU </th>
		  <!-- <th>BARCODE </th> -->
		  <!-- <th>WEIGHT (KG) </th> -->
      <th>GIFTS</th>
		  <th>SUB ISSUE </th>
		  <!-- <th>MEDIA STATUS</th> -->
		  <th>ACTION</th>
		</tr>
   </thead>
  <tbody id="variant_tbody">
  <?php include('load_variants.php'); ?>
  </tbody>
</table>


<input type="hidden" name="added_variant" id="added_variant" value="<?php echo implode(',',$sv_arr); ?>">

<script>
$(document).ready(function(){



	$('.required-field').each(function() {
        $(this).rules("add",
		{
			required: true,
			messages: {
				required: "Field is required",
			}
		});
    });




	$('.unique-sku').each(function() {
			$(this).rules("add",
            {
                notEqualToGroup: ['.unique-sku']
            });




    });

	$('.unique-barcode').each(function() {
        $(this).rules("add",
            {
                notEqualToGroup: ['.unique-barcode']
            });
    });


	jQuery.validator.addMethod("notEqualToGroup", function (value, element, options) {
        // get all the elements passed here with the same class
        var elems = $(element).parents('form').find(options[0]);
        // the value of the current element
        var valueToCompare = value;
        // count
        var matchesFound = 0;
        // loop each element and compare its value with the current value
        // and increase the count every time we find one
        jQuery.each(elems, function () {
            thisVal = $(this).val();
            if (thisVal == valueToCompare) {
                matchesFound++;
            }
        });
        // count should be either 0 or 1 max
        if (this.optional(element) || matchesFound <= 1) {
            //elems.removeClass('error');
            return true;
        } else {
            //elems.addClass('error');
        }
    }, "Duplicate entry found for this column.");


	$(".single-file").change(function (e) {
	e.stopImmediatePropagation();

		alert("hii");
		  if(this.disabled) {
			return alert('File upload not supported!');
		  }

		  var F = this.files;
		  if (F && F[0]) {
			for (var i = 0; i < F.length; i++) {
			  readMediaFiles(F[i]);
			  $(this).parent().find('.md-status').html('Uploaded');
			}
		  }
	});




});


</script>
