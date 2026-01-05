
<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
   <ul class="nav nav-pills">
      <li class="active"><a data-toggle="pill" href="#categories">Categories </a></li>
   </ul>
   <div class="main-inner min-height-480">
      <div class="tab-content">
         <div id="categories" class="tab-pane fade in active admin-shop-details-table" style="opacity:1;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
               <h1 class="head-name">Category List </h1>
               <a href="<?php echo base_url()?>category/add-category"> <button class="purple-btn">Create New</button></a>
            </div>
            <!-- form -->
            <div class="content-main form-dashboard">
               <div class="table-responsive text-center">
                  <table id="customMenuTabless" class="table table-bordered table-style" id="datatableCategory">
                     <thead>
                        <tr>
                           <th>CATEGORIES  </th>
                           <th>STATUS </th>
                           <th>DETAILS </th>
                        </tr>
                     </thead>
                     <tbody>
					
                        <?php if(isset($browse_category) && $browse_category!='' ) { ?>
                        <?php foreach ($browse_category as $block ) { 
                           $id = $block['id'];
                           ?>
                        <tr>
                           <td><?php echo $block['cat_name']; ?></td>
                           <td><?php echo isset($block['status']) && $block['status']==1 ? 'Active':'Inactive'; ?></td>
                           <td>
                              <a class="link-purple" href="<?= base_url('category/edit-category/'.$block['id']) ?>">View</a>/<?php $catLevel = isset($block['cat_level']) ? $block['cat_level'] : 0; ?>
<a class="link-purple deleteBlock" title="Delete" onclick="ConfirmCategoryDelete(<?php echo $block['id']; ?>,<?php echo $catLevel; ?>);">Delete</a>
                              <!-- / <a class="link-purple deleteBlock" title="Delete" onclick="ConfirmCategoryDelete(<?php echo $block['id']; ?>,<?php echo $block['cat_level']; ?>);">Delete</a> -->
                           </td>
                        </tr>
                        <?php 
                           if(isset($block['cat_level_1']) && $block['cat_level_1']!='') { 
                           	foreach($block['cat_level_1'] as $menu_level1) { 
                           
                           		?>
                        <tr>
                           <td><?php echo '-'.$menu_level1['cat_name']; ?></td>
                           <td><?php echo isset($menu_level1['status']) && $menu_level1['status']==1 ? 'Active':'Inactive'; ?></td>
                           <td>
                              <a class="link-purple" href="<?= base_url('category/edit-category/'.$menu_level1['id']) ?>">View</a><?php $catLevel1 = isset($menu_level1['cat_level']) ? $menu_level1['cat_level'] : 1; ?>
/ <a class="link-purple deleteBlock" title="Delete" onclick="ConfirmCategoryDelete(<?php echo $menu_level1['id']; ?>,<?php echo $catLevel1; ?>);">Delete</a>
                              <!-- / <a class="link-purple deleteBlock" title="Delete" onclick="ConfirmCategoryDelete(<?php echo $menu_level1['id']; ?>,<?php echo $menu_level1['cat_level']; ?>);">Delete</a> -->
                           </td>
                        </tr>
                        <?php 
                           if(isset($menu_level1['cat_level_2']) && $menu_level1['cat_level_2']!='') { 
                                    foreach($menu_level1['cat_level_2'] as $menu_level2) { ?>
                        <tr>
                           <td><?php echo '--'.$menu_level2['cat_name']; ?></td>
						   <td>  <?php 
      echo array_key_exists('status', $menu_level2) 
         ? ((int)$menu_level2['status'] === 1 ? 'Active' : 'Inactive') 
         : 'Unknown';
   ?></td>
                           <td>
                              <a class="link-purple" href="<?= base_url('category/edit-category/'.$menu_level2['id']) ?>">View</a><?php $catLevel2 = isset($menu_level2['cat_level']) ? $menu_level2['cat_level'] : 2; ?>
/ <a class="link-purple deleteBlock" title="Delete" onclick="ConfirmCategoryDelete(<?php echo $menu_level2['id']; ?>,<?php echo $catLevel2; ?>);">Delete</a>
                              <!-- / <a class="link-purple deleteBlock" title="Delete" onclick="ConfirmCategoryDelete(<?php echo $menu_level2['id']; ?>,<?php echo $menu_level2['cat_level']; ?>);">Delete</a> -->
                           </td>
                        </tr>
        
                        </tr><?php } } } } } } ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <!-- add new tab -->
   </div>
</main>
<?php $this->load->view('common/fbc-user/footer'); ?>
<script type="text/javascript">
   $('#addCatPage').on('click', () => {
   	let catformVal = $('input[name="catForm"]:checked').val();
   	if(catformVal == 1){
   		window.location.href = BASE_URL+'category/add-category';
   	}else if(catformVal == 2){
   		window.location.href = BASE_URL+'category/add-sub-category';
   	}
   });
   
</script>

<script>
function ConfirmCategoryDelete(id,cat_level){
	var pcount=0;
	if(id!=''){
		$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL+"sellerproduct/getcatproductcount/",
				data: {id:id,cat_level:cat_level},				
				beforeSend: function () { 
					$('#ajax-spinner').show();
				},			
				success: function(response) {
					$('#ajax-spinner').hide();
					if(response !='error'){
						pcount=response;
						if(pcount>0){

							var conf_message="There are some products already assigned to this category, Still you want to delete this category? You won't be able to revert this.";
						}else{
							var conf_message="You won't be able to revert this!";
							
						}
						
						
						swal({
							title: "Are you sure? ",
							text: conf_message,
							type: "warning",
							showCancelButton: true,
							confirmButtonColor: "#3085d6",
							 cancelButtonColor: '#d33',
							confirmButtonText: "Yes, delete it!",
							cancelButtonText: "Cancel",
							closeOnConfirm: false,
							closeOnCancel: false
						}, function(isConfirm) {
							if (isConfirm) {
								
								DeleteCategory(id,cat_level);
								
							} else {
								swal.close();
							}
						});
						
					}else{
						swal('Error','Something went wrong!','error');
					}
					
					
				}
			});
	}
}

function DeleteCategory(id,cat_level){
	if(id!=''){
		
			$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL+"sellerproduct/deletecategory/",
				data: {id:id,cat_level:cat_level},				
				beforeSend: function () { 
					$('#ajax-spinner').show();
				},			
				success: function(response) {
					$('#ajax-spinner').hide();
					
					if(response=='success'){
						swal('Success','Category deleted successfully!','success');
                  window.setTimeout( function() {
                  window.location.reload();
                  }, 1500);
					}else{
						return false;
					}
				}
			});
	}else{
		return false;
	}
					
	
}

</script>