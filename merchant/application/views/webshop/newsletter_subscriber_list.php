<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

	<ul class="nav nav-pills">
    <li class="active"><a data-toggle="pill" href="<?= base_url('webshop/newsletter-subscriber') ?>">Newsletter Subscriber </a></li>
    <!-- <li><a href="<?= base_url('webshop/edit-newsletter-subscriber-text') ?>">Edit Newsletter Subscriber Text </a></li> -->
    
  </ul>

  <div class="tab-content">
    <div id="shipping-charges" class="tab-pane fade in active min-height-480  common-tab-section" style="opacity:1;">
		
	<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
          <h1 class="head-name">Newsletter Subscriber </h1> 
        </div>

		
        <!-- form -->
        <div class="content-main form-dashboard admin-shop-details-table new-height">
            <form>

              <div class="table-responsive text-center">
                <table class="table table-bordered table-style" id="newsletter_subscriber_table">
                  <thead>
                    <tr>
                      <th>No. </th>
                      <th>Email</th>
  					  <th>Status </th>
  					 
                    </tr>
                  </thead>
                  <tbody>
            <?php if(isset($all_newsletter_subscriber) && $all_newsletter_subscriber!='')
            {
             foreach ($all_newsletter_subscriber as  $value) {
             $number= 1 ?>
                    <tr>
                      <td><?php echo $value->id; ?></td>
                      <td><?php echo $value->email;?></td>
                      <td><?php  if($value->status == 1){
                      	echo "Subscribed";
                      } else { echo "Unsubscribed" ; }
                      	?></td>
                    </tr>
           
            <?php $number++;  }
             } ?>
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
          "lengthMenu": [[10, 20, 50, -1], [10, 20, 50, "All"]],
          "language": {                
          "paginate": {
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'  
                       },
          "search": ""

         },
          'columnDefs': [{
                            "targets": [2],
                            "orderable": false
                         }],
        } );

  	 if ($('#newsletter_subscriber_table tr').length < 10) {
  
            $('.dataTables_paginate').hide();
        }

 });

</script>
<?php $this->load->view('common/fbc-user/footer'); ?>