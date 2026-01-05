<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
			<li class="active"><a href="<?= base_url('email-template') ?>">Email Template</a></li>
			<!--<li><a href="<?= base_url('') ?>">Add New</a></li>-->
	</ul>
<div class="main-inner min-height-480">
	<div class="tab-content">
    <div id="attribute" class="tab-pane fade in active show admin-shop-details-table" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Email Template List </h1> 
		<?php if(empty($this->session->userdata('userPermission')) || in_array('system/email_template/write',$this->session->userdata('userPermission'))){  ?>
		 <a href="<?php echo base_url()?>email-template/details"> <button class="purple-btn">Create New</button></a>
		<?php } ?>
		 
        </div>
		
		
		
		 <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <label>Show 
		  <select name="page_length" id="page_length">
		  <option>10</option>
		  <option>20</option>
		  <option>30</option>
		  </select>
		  </label>
		  
		  <div class="float-right product-filter-div">
			  <div class="search-div">
				  <input class="form-control form-control-dark top-search" name="support_search" id="support_search" type="text" placeholder="Search" aria-label="Search">
				  <button type="button" class="btn btn-sm search-icon"><i class="fas fa-search"></i></button>
			 </div>
			
			</div>
        </div>
        <!-- form -->
        <div class="content-main form-dashboard">
            <form>

              <div class="table-responsive text-center">
                <table class="table table-bordered table-style" name="Email_table" id="Email_table">
                  <thead>
                    <tr>
                      <th>Email Code <!--<i class="float-right fa fa-fw fa-sort"></i>--></th>
                      <th>Title  <!--<i class="float-right fa fa-fw fa-sort"></i>--></th>
                      <th>Subject  <!--<i class="float-right fa fa-fw fa-sort"></i>--></th>
					  <th>Status</th>
                      <th class="no-sort">DETAILS <!--<i class="float-right fa fa-fw fa-sort"></i>--></th>
                    </tr>
                  </thead>
                  <tbody>
<?php
					if(isset($EmailTemplate) && !empty($EmailTemplate))
					{
						foreach($EmailTemplate as $tem_key=>$tem_val)
						{
				  
				  
?>
                    <tr>
                      <td><?php echo $tem_val['email_code'] ;?></td>
                      <td><?php echo $tem_val['title'] ;?></td>
                      <td><?php echo $tem_val['subject'] ;?></td>
					  <td>
						  <?php 
						  	if ($tem_val['status'] == 1) 
						  	{ 
							  	echo "Enabled"; 
							}
							else { 
								echo "Disabled"; 
							} 
							?>
					  </td>
                      <td><a class="link-purple" href="<?php echo base_url();?>email-template/details/<?php echo $tem_val['id']?>">View</a></td>
                    </tr>
<?php
						}
						
					}
					else
					{  
					
?>
					<tr>
                      <td colspan="4">No data found</td>
					</tr>
<?php						
					}
					
?>
					
					
                  </tbody>
                </table>
              </div>

            </form>
        </div>
        <!--end form-->
    </div>
   <!-- main-inner -->


    </div>
</div>	
</main>	
<?php $this->load->view('common/fbc-user/footer'); ?>
<script>
   var table1 = $('#Email_table').DataTable({"ordering": true,columnDefs: [{
      orderable: false,
      targets: "no-sort"
    }], "stateSave": false, "paging": true,"searching": true,"jQueryUI": false,"dom" : '<"top"tp>'});
	$.fn.dataTable.ext.errMode = 'none';
	
			$('#support_search').on('keyup change', function () 
			{
				console.log(this.value);
			    table1.search(this.value);
			    table1.draw();
			    // var dlr_count = table1.page.info();
			    
			});			
			
		$('#page_length').change( function() { 
			table1.page.len( $(this).val() ).draw();
		});
</script>
