<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<ul class="nav nav-pills">
    <li class="active"><a data-toggle="pill" href="<?= base_url('webshop/newsletter-subscriber') ?>">Search Terms </a></li>
<!--     <li><a href="<?= base_url('webshop/edit-newsletter-subscriber-text') ?>">Edit Newsletter Subscriber Text </a></li> -->
    
  </ul>

  <div class="tab-content">
    <div id="shipping-charges" class="tab-pane fade in active min-height-480  common-tab-section admin-shop-details-table" style="opacity:1;">
		
	<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Search Terms </h1> 
        </div>

		
        <!-- form -->
        <div class="content-main form-dashboard">
            <form>

              <div class="table-responsive text-center">
                <table class="table table-bordered table-style" id="newsletter_subscriber_table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Search Term</th>
  					          <th>Popularity</th>
  					 
                    </tr>
                  </thead>
                  <tbody>                    
                    <?php if(isset($search_terms) && $search_terms!='')
                    {
                         foreach ($search_terms as  $value) {
                          ?>
                                <tr>
                                  <td><?php echo $value['id'];?></td>
                                  <td><?php echo $value['search_term'];?></td>
                                  <td><?php echo $value['popularity'];?></td>

                                  
                                </tr>
                       
                          <?php }
                    }?>
                  </tbody>
                </table>
              </div>

            </form>
        </div>
        <!--end form-->
    </div>

  </div>
        

    </main>
<script type="text/javascript">
$(document).ready(function(){
	 $('#newsletter_subscriber_table').DataTable(
        {
          "info" : false,
          "lengthMenu": [[50, -1], [50, "All"]],
          "order": [[ 2, "desc" ]],
          "language": {                
          "paginate": {
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'  
                       },
          "search": ""

         },
        } );

  	 if ($('#newsletter_subscriber_table tr').length < 10) {
  
            $('.dataTables_paginate').hide();
        }

 });

</script>
<?php $this->load->view('common/fbc-user/footer'); ?>