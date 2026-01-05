<div class="main-inner">
	<div class="add-bulk-inner2  ">
		<h1 class="head-name">Upload CSV <a class="float-right" href="<?php echo SKIN_URL; ?>uploads/sample/yellowmarket_sample_product_import.csv"  target="_blank">Sample CSV</a></h1>
		<form method="POST" enctype="multipart/form-data">
		
			<div class="add-bulk-inner2-form">
			 
				 <div class="col-md-12 pr-5">
					 <div class="form-group row">
						<label for="" class="col-sm-4 col-form-label font-500">Select File</label>
						<div class="col-sm-8">
						  <input type="file" class="upload-csv" id="upload_csv_file"  name="upload_csv_file"  accept=".csv">
						</div>
					  </div>
				</div>
				
				
				
				<div class="col-md-12 error" id="csv_error"> 
				</div>
				
				<p class="note d-none">Note: Please upload all  product related images <b>/imports</b> directory. </p>
			</div>
		 
			
			 <div class="download-discard-small">
				<button class="white-btn" type="button" data-dismiss="modal">Discard</button>
				<button class="download-btn"  type="button" name="check_data" id="check_data" disabled="" onclick="CheckCSVData();">Check Data</button>
				<button class="download-btn d-none"  type="button" name="bulk_upload" id="bulk_upload" onclick="ImportProducts();">Upload</button>
			 </div>
		 </form>
	 </div>
	<!-- add-bulk-inner2 -->
 </div>