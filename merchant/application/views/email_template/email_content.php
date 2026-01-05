<?php $this->load->view('email_template/email_header'); ?>

	<tr>

		<td style="padding: 20px 15px 51px;">

			<table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">

				<tr>

					<td class="white-sec" style="background: #fff; padding: 30px;"><?php echo $content;?></td>

				</tr>

			</table>

		</td>

	</tr>

<?php $this->load->view('email_template/email_footer'); ?>