
<?php $this->load->view('common/fbc-user/header'); ?>
<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<link href='//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css' rel='stylesheet' type='text/css'>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <div class="tab-content">
    <div id="catalogue-discounts-tab" class="tab-pane fade in active common-tab-section  min-height-480 admin-shop-details-table" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name">Testimonial List</h1>
  		  <div class="float-right">
        <button class="download-btn" type="button" name="bulk_testimonials_save" id="bulk_testimonials_save" onclick="DownloadAllTestimonialsCSV();">Download CSV</button>
            <a class="purple-btn delete-all-btn" href="<?= $add_testimonial; ?>">Add New</a>
  		  </div>
      </div>
      <div class="content-main form-dashboard admin-user-details-table new-height">
        <div class="table-responsive text-center">
          <table class="table table-bordered table-style" id="testimonialLists">
            <thead>
                <th>#</th>
                <th>Client Email</th>
                <th>Client Description</th>
                <th>Client Company</th>
                <th>Status</th>
                <th>Created Time</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>

            </tbody>
          </table>
        </div>
			</div><!--end form-->
    </div>
	</div>
</main>
<script src="<?php echo SKIN_JS; ?>testimonials.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<script src="<?php echo SKIN_JS; ?>testimonials_list.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<?php $this->load->view('common/fbc-user/footer'); ?>