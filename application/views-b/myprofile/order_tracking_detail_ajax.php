<?php 
 //print_r($tracking_data);
if(isset($tracking_data)){
	
	?>
	<table class="table table-bordered">
		<tbody>
			<tr>
				<?php if ($tracking_data->order_id) {
					?>
					<th><?php lang('order_number') ?></th>
					<?php
				} ?>
				<th><?= lang('box_number') ?></th>
				<th><?= lang('tracking_detail') ?></th>
			</tr>
			<?php 

			if (isset($tracking_data->tracking_data) && $tracking_data->tracking_data != '') {
				foreach ($tracking_data->tracking_data as $key => $value) {
					if ($value->tracking_url != '' || $value->tracking_id != '') {
						?>
						<tr>
							<?php if ($tracking_data->order_id) {
								?>
								<td><?php echo $value->order_id; ?></td>
								<?php
							} ?>
							<td>Box <?php echo $value->box_number; ?></td>
							<?php 
								if ($value->tracking_url != '' && $value->tracking_id != '') {
									?>
									<td><a href="<?php echo $value->tracking_url; ?>" target="_blank"><?php echo $value->tracking_id; ?></a></td>
									<?php
								}elseif ($value->tracking_id != '') {
									?>
									<td><?php echo $value->tracking_id; ?></td>
									<?php
								}elseif ($value->tracking_url != '') {
									?>
									<td><?php echo $value->tracking_url; ?></td>
									<?php
								}else{
									?>
									<td>-</td>
									<?php
								}
							?>
							
						</tr>

						<?php
					}
				}

			}

			if (isset($tracking_data->b2b_tracking_data) && $tracking_data->b2b_tracking_data != '') {
				foreach ($tracking_data->b2b_tracking_data as $key => $value) {
					if ($value->tracking_url != '' || $value->tracking_id != '') {
						?>
						<tr>
							<?php if ($tracking_data->order_id) {
								?>
								<td><?php echo $value->order_id; ?></td>
								<?php
							} ?>
							<td>Box <?php echo $value->box_number; ?></td>
							<?php 
								if ($value->tracking_url != '' && $value->tracking_id != '') {
									?>
									<td><a href="<?php echo $value->tracking_url; ?>" target="_blank"><?php echo $value->tracking_id; ?></a></td>
									<?php
								}elseif ($value->tracking_id != '') {
									?>
									<td><?php echo $value->tracking_id; ?></td>
									<?php
								}elseif ($value->tracking_url != '') {
									?>
									<td><?php echo $value->tracking_url; ?></td>
									<?php
								}else{
									?>
									<td>-</td>
									<?php
								}
							?>
							
						</tr>

						<?php
					}
				}

			}

			?>
		</tbody>
	</table>
	<?php 
}else{
	?>
	<span><?php lang('tracking_not_found') ?></span>
	<?php
}


 ?>
