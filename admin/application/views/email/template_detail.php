<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
			<li class="active"><a href="<?= base_url('email-template') ?>">Email Template</a></li>
	</ul>
<div class="main-inner min-height-480">
	<div class="tab-content">
	<form id="email_detail_form"name="email_detail_form" method="post" >
		<div id="addnew-attribute" class="tab-pane fade active show">
				<div class="variant-common-block variant-list">
				<input type="hidden" name="id" id="id" value="<?php echo (isset($template_detail['id']) ? $template_detail['id'] : '') ; ?>">
					<h1 class="head-name pad-bottom-20">Template Details</h1>
					<div class="form-group row">
								<label for="" class="col-sm-2 col-form-label font-500">Template Code <span class="text-danger"> *</span></label>
								<div class="col-sm-3">
								 <input type="text" class="form-control" name="template_code" id="template_code" value="<?php echo(isset($template_detail['email_code'])) ? $template_detail['email_code'] : ''?>"  maxlength = "50" required>
								</div>
						  </div>
						<div class="form-group row">
								<label for="" class="col-sm-2 col-form-label font-500">Template Title <span class="text-danger"> *</span></label>
								<div class="col-sm-3">
								 <input type="text" class="form-control" name="title" id="title" value="<?php echo(isset($template_detail['title'])) ? $template_detail['title'] : ''?>"  maxlength = "50" required>
								</div>
						  </div><!-- form-group -->

						 <div class="form-group row">
								<label for="" class="col-sm-2 col-form-label font-500">Template Subject <span class="text-danger"> *</span></label>
								<div class="col-sm-3">
								   <input type="text" name="template_subject" id="template_subject" class="form-control" value="<?php echo(isset($template_detail['subject'])) ? $template_detail['subject'] : ''?>">
								</div>
						  </div><!-- form-group -->

						<div class="form-group row">
									<label for="" class="col-sm-2 col-form-label font-500">Template Content</label>
									<div class="col-sm-7">
									<textarea class="form-control" name="template_content" id="template_content" ><?php echo(isset($template_detail['content'])) ? $template_detail['content'] : ''?></textarea>
									</div>
							</div><!-- form-group -->

						  <div class="form-group row">
							<label class="col-sm-2 col-form-label font-500">Status</label>

								<div class="switch-onoff col-sm-7">
								<label class="checkbox">
									<input type="checkbox" name="template_status" id="template_status" autocomplete="off" <?php echo(isset($template_detail['status']) && $template_detail['status'] == 1) ?  'checked' :  '' ;?>>
									<span class="checked">

									</span>
								</label>

								</div>
							</div>

					<div class="download-discard-small pos-ab-bottom">
				<?php if(empty($this->session->userdata('userPermission')) || in_array('system/email_template/write',$this->session->userdata('userPermission'))){  ?>
							<!--<button class="white-btn">Discard</button>-->
<?php
							if($action == 'update')
							{
?>
								<input type="hidden" name="action" id="action" value="update">
								<button class="download-btn" type="submit" name="update_submit" id="update_submit">Save</button>

<?php
							}
							else
							{
?>
								<input type="hidden" name="action" id="action" value="insert">
								<button class="download-btn" type="submit" name="add_submit" id="add_submit">Add</button>
<?php
							}

?>
                <?php } ?>
						</form>
						 </div><!-- download-discard-small -->
				</div><!-- variant-common-block -->

			</div><!-- main-inner -->


    </div><!-- add new tab -->
</div>

</main>
<script src="<?php echo SKIN_JS; ?>emailTemplates.js"></script>

<script type="text/javascript">
	$(function () {
      	CKEDITOR.replace('template_content', {
	     extraPlugins :'justify',
     		allowedContent: true,
	    });
    });
	$(document).ready(function(){

$('#email_detail_form').validate({
	  ignore: [],
	rules: {
		template_code: {
			required: true
		},
		title: {
			required: true
		},
		template_subject: {
			required: true
		},
		template_content: {
			required: function()
			{
			 CKEDITOR.instances.template_content.updateElement();
			}
		}
	}



});
$('#email_detail_form').on('submit',function(e){
		e.preventDefault();
if($(this).valid()) {
	for (instance in CKEDITOR.instances)
{
    CKEDITOR.instances[instance].updateElement();
}

var id = $("#id").val()

	var formData = new FormData($("#email_detail_form")[0]);


					$.ajax({
					type:"POST",
					url:BASE_URL+'EmailTemplateController/submit_template_details/'+id,
					dataType:"json",
					data:formData,
					processData: false,
					contentType: false,
					beforeSend:function()
					{
						//$("#add_submit").prop("disabled",true).css({"background":"#868686","color":"#fff"});
					},
					success:function(response){
						console.log(response);
						if( response.flag == 1)
						{
							swal({
								title: "",
								icon: "success",
								text: response.msg,
								//buttons: false,
							},function(){ window.location = response.redirect;   }
							)
						}

						else
						{
							swal({
								title: "",
								icon: "error",
								text: response.msg,
								//buttons: false,
							},
							function(){location.reload(); })
						}
					}
				});
}

	});

});
</script>
<?php $this->load->view('common/fbc-user/footer'); ?>
