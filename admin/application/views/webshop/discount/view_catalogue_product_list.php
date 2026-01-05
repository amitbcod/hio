<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	  <?php $this->load->view('webshop/discount/breadcrums');?>
	<div class="tab-content">
	    <div id="product-discounts-details-tab" class="tab-pane fade in active common-tab-section" style="opacity:1;">
	      	<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
	        	<h1 class="head-name"><i class="fas  fa-angle-left"></i> &nbsp; Product List </h1> 
		  	</div>
	      
	      <!-- form -->
			<?php //echo "<pre>";print_r($productData);exit;?>
			<div class="content-main form-dashboard">
	        	<div class="table-responsive text-center make-virtual-table">
		            <table class="table table-bordered table-style">
		              <thead>
		                <tr>
				              <th>Categories</th>
		                  <th>Product Name</th>
		                  <th>Price</th>
    		      			  <th>Variants</th>
    		      			  <th>Details</th>
		                </tr>
		              </thead>
		              <tbody>
		              	<?php if(isset($productData) && !empty($productData)) { ?>
			              	<?php foreach($productData as $list) { ?>
			                <tr>
			                	<td><?php echo $list->cat_name; ?></td>
				                <td><?php echo $list->name; ?></td>
				                <?php if($list->product_type == 'configurable') { ?>
				                <td>-</td>
								        <td>
				                	<a class="variant-popup-link" href="javascript:void(0)" onclick="openCatalogueVariantListPopup(<?php echo $list->id; ?>,'<?php echo $list->cat_name; ?>')">View</a>
				                </td>
				            	<?php } else { ?>
				            	<td><?php echo $list->webshop_price; ?></td>
								      <td>-</td>
				            	<?php } ?>
			                  	<td><a class="link-purple" href="<?php echo BASE_URL.'seller/product/edit/'.$list->id;?>" target="_blank">View</a></td> 
			                </tr>
			            	<?php } ?>
		            	<?php } ?>
		              </tbody>
		            </table>
	        	</div>
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

<script type="text/javascript">
  function openCatalogueVariantListPopup(product_id,cat_name)
  {
    if(product_id!=''){
      $.ajax({
        type: "POST",
        dataType: "html",
        url: BASE_URL+"WebshopController/openCatalogueVariantPopup",
        data: {product_id:product_id},
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
</script>>
