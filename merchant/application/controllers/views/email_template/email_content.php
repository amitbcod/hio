<?php $this->load->view('email_template/email_header'); ?>
	<tr>
		<td style="padding: 20px 15px 51px;">
			<table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
				<tr>
					<td style="text-align: center;"><h1 style="color: #3e3e3e; font-size: 34px; line-height: 44px; font-weight: 400; margin: 0 0 7px;"><?php // echo $subject;?></h1></td>
				</tr>
				<tr>
					<td class="white-sec" style="background: #fff; text-align: center; padding: 30px;"><?php echo $content;?></td>
				</tr>
			</table>
		</td>
	</tr>
<?php $this->load->view('email_template/email_footer'); ?>