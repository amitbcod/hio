<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php  $this->load->view('seller/products/breadcrums'); ?>
    <div class="tab-content">
		<div id="inbound" class="tab-pane fade common-tab-section" style="opacity:1; display:block;">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="head-name">Import Inbound</h1>
			</div>

                <!-- form -->
			<div class="content-main form-dashboard">
				<form method="POST" action="<?=base_url('seller/inbound/import-post') ?>" enctype="multipart/form-data">
					<div class="barcode-qty-box row">
                        <div class="col-sm-4 order-id">
                            <p><span class="huge-name">Name : </span> <input type="text" name="name" id="name" value="" placeholder="" required></p>
                        </div>
                    </div>
					<div class="row mt-4">
						<div class="col-sm-4">
							<input type="file" class="upload-csv" id="upload_csv_file"  name="upload_csv_file"  accept=".csv" required>
						</div>
					</div>
					<div class="row mt-4">
						<div class="col-sm-4">
							<button class="purple-btn ml-0" type="submit">Import</button>
						</div>
					</div>
				</form>
			</div>
		</div>
    </div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
