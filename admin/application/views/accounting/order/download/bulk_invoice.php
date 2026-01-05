	<div class="main-inner">
	<div class="add-bulk-inner2  ">
		<h1 class="head-name">Download <?php  echo (($type=='exportAll') ? 'All' : '' );?> CSV </h1>

		 <div class="download-discard-small">
			<button class="white-btn" type="button"data-dismiss="modal">Discard</button>
			<?php
			if($type == 'exportAll')
			{ ?>
				<button class="download-btn"  type="button" name="download_csv" id="download_csv"  onclick="DownloadInvoicingCSV();">Download</button>
			<?php } ?>

		 </div>
		 </div>
		 <!-- add-bulk-inner2 -->
		 </div>
