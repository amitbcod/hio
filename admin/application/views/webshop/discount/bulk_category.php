	<div class="main-inner">
	<div class="add-bulk-inner2  ">
		<h1 class="head-name">Download <?php  echo (($type=='importAll') ? 'All' : '' );?> CSV </h1>
		<!-- <a class="float-right" href="<?php //echo SKIN_URL; ?>uploads/sample/SIS-Sample-SpecialPricing.csv"  target="_blank">Sample CSV</a> -->
		
			
		
		 <div class="download-discard-small">
			<button class="white-btn" type="button"data-dismiss="modal">Discard</button>
			<?php 
			if($type == 'importAll')
			{ ?>
				<button class="download-btn"  type="button" name="download_csv" id="download_csv"  onclick="DownloadAllProductCSV();">Download</button>
			<?php }
			else
			{ ?>
				<button class="download-btn"  type="button" name="download_csv" id="download_csv"  onclick="DownloadProductCSV();">Download</button>
			<?php 	}
			?>
			
		 </div>
		 </div>
		 <!-- add-bulk-inner2 -->
		 </div>