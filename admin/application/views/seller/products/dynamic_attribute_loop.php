
<table class="table table-bordered table-style">
  <thead>
	<tr>
	  <th>Name</th>
	  <th>Value</th>
	  <th>ACTION</th>
	</tr>
  </thead>
  <tbody id="attr_tbody">
  <?php include('load_attributes.php'); ?>
  </tbody>
</table>
<?php  $selected_attributes=$CategoryDetail->selected_attributes;
		$selected_attributes = (isset($selected_attributes) && $selected_attributes!='')?substr($selected_attributes, 1, -1):'';?>
<input type="hidden" name="added_attr" id="added_attr" value="<?php echo $selected_attributes; ?>">
