<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php $this->load->view('webshop/discount/breadcrums');?>

	<div class="tab-content">
	    <div id="product-discounts-details-tab" class="tab-pane fade in active common-tab-section" style="opacity:1;">
	      	<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
	        	<h1 class="head-name"><i class="fas  fa-angle-left"></i> &nbsp; Product List </h1> 
		  	</div>
		
			<!-- form -->
			<?php //echo '<pre>';print_r($productData);//exit;?>
	        <div class="content-main form-dashboard">
				<input type="hidden" id="totalListCount" value="<?php echo (is_array($productData) && count($productData) > 0)?count($productData):0;?>">
				<form id="productListForm" method="POST" action="<?php echo base_url('WebshopController/updateCheckedProductList') ?>" enctype="multipart/form-data">
					<input type="hidden" name="rules_id" id="rules_id" value="<?php echo $rule_id; ?>">

					<?php $i=0;foreach ($productData as $key=>$product){ $i++;?> 
					<div class="table-responsive text-center make-virtual-table">
			 			<table class="table table-bordered table-style" id="productList_<?php echo $i;?>">
		                  	<thead>
		                    	<tr>
							 		<th><label class="checkbox"><input type="checkbox" class="form-control"><span class="checked"></span></label></th>
		                      		<th>Categories</th>
			                  		<th>Product Name</th>
			                  		<th>Price</th>
			      			  		<th>Variants</th>
			      			  		<th>Details</th>
		                    	</tr>
		                  	</thead>
                  			<tbody>
		 						<?php foreach($product as $value) { ?> 
                				<tr class="trp-row">
					  				<td><label class="checkbox"><input type="checkbox" <?php echo (isset($productIdArr) && in_array($value->id, $productIdArr)) ? 'checked' : ''; ?> class="form-control chk-line-<?php echo $value->id;?> main-checkbox pid-<?php echo $value->category_id;?>-<?php echo $value->id;?>" data-product_id="<?php echo $value->id;?>" value="<?php echo $value->id;?>" id="checkedProduct_<?php echo $value->category_id;?>_<?php echo $value->id;?>" name="checkedProduct[]" onclick="getProductCheckUncheck(this.value,<?php echo $value->category_id;?>)" ><span class="checked"></span>
					  					</label>
					  					<?php if($value->product_type == 'configurable') { 
					  						$childProductIds = $this->WebshopModel->getConfigChildProductIds($value->id);
					  						$conf_simple_ids = '';
					  						if(!empty($childProductIds)) {
					  							$conf_simple_ids = $childProductIds->conf_simple_ids;
					  						}
					  					?>
					  					<input type="hidden" class="conf_simple_product_<?php echo $value->id;?>" name="conf_simple_product_<?php echo $value->id;?>" value="<?php echo $conf_simple_ids;?>">
					  					<?php } ?>
					  				</td>
					  				<td><?php echo $value->cat_name;?></td>
			                      	<td><?php echo $value->name;?></td>
			                      	<?php if($value->product_type == 'configurable') { ?>
	                      		 	<td>-</td>
			                      	<td>
			                			<a class="variant-popup-link" href="javascript:void(0)" onclick="openVariantListPopup(<?php echo $value->id; ?>,'<?php echo $value->cat_name; ?>')">View</a>
				                	</td>
				                	<?php } else { ?>
				                	<td><?php echo $value->webshop_price; ?></td>
						      		<td>-</td>
						      		<?php } ?>
									<td><a class="link-purple" href="<?php echo BASE_URL.'seller/product/edit/'.$value->id;?>" target="_blank">View</a></td>
								</tr>
								<?php } ?>	
                  			</tbody>
                		</table>
              		</div>
			  		<?php } ?>
				  	<div class="next-btn">
						<button type="submit" class="puple-btn short-btn new-btn-height" id="product_list_next_btn" name="product_list_next_btn">Save</button>
				  	</div><!-- next-btn -->
            	</form> <!--end form-->
        	</div>
        </div>
    </div>
</main>
<!-- Modal -->
<div class="modal fade" id="varientModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-style" role="document">
    <div class="modal-content" id="modal-content">
      
	  
    </div>
  </div>
</div>

<script type="text/javascript" src="<?php echo SKIN_JS; ?>discounts.js"></script> 
<script type="text/javascript">
function openVariantListPopup(product_id,cat_name)
{
	if(product_id!=''){
		var rule_id = $("#rules_id").val();
      	$.ajax({
	        type: "POST",
	        dataType: "html",
	        url: BASE_URL+"WebshopController/openProductVariantPopup",
	        data: {product_id:product_id, rule_id:rule_id},
	        //async:false,
	        complete: function () { 
	        },  
	        beforeSend: function(){
	          // $('#ajax-spinner').show();
	        },      
        	success: function(response) {
          		console.log(response);
          		$("#varientModal").modal();
	      		$("#modal-content").html(response);
	          	$(".vrnt_cat_name").text(cat_name);
        	}
      	});
    }else{
  		return false;
    }
}

function selectedVariantList(prod_parent_id)
{
	if(prod_parent_id!=''){
		var ids = new Array();
	 	$(".variant-checkbox:checked").each(function() {
	 		ids.push($(this).val());
		});
		if(ids!=''){
			$(".conf_simple_product_"+prod_parent_id).val(ids);
			//swal({ title: "",text: "Success", button: false, icon: 'success' }) 
		}else{
			return false;
		}
	}else{
	 	return false;
  	}
}
</script>
