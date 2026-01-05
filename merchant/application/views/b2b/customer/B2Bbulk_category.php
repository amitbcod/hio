	<div class="main-inner">
	<div class="add-bulk-inner2  ">
		<h1 class="head-name">Download <?php  echo(($type=='importAll') ? 'All' : '');?> CSV </h1>
		<form method="post">
			 <div class="download-discard-small">
				<button class="white-btn" type="button"data-dismiss="modal">Discard</button>
				<?php if ($type == 'importAll') { ?>
					<button class="download-btn"  type="button" name="download_csv" id="download_csv"  onclick="DownloadAllProductCSV();">Download</button>
				<?php } else { ?>
					<button class="download-btn"  type="button" name="download_csv" id="download_csv"  onclick="DownloadProductCSV();">Download</button>
				<?php }	?>
			</div>
			<input type="hidden" name="B2Bshop_id" id="B2Bshop_id" value="<?php echo $B2Bshop_id ?>">
		</form>
	</div>
    <!-- add-bulk-inner2 -->
	</div>
