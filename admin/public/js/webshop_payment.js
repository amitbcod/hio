$(document).ready(function() {

	var table1 = $('#webshop_gateway_details').DataTable({"ordering": true, columnDefs: [{
		orderable: false,
		targets: "no-sort"
		}], "stateSave": true, "paging": true,"searching": true,"jQueryUI": false,"dom" : '<"top"tp>'});
		$('#gateway_search').on('keyup change', function () 
			{
				console.log(this.value);
					table1.search(this.value);
					table1.draw();
					var dlr_count = table1.page.info();

			});

			if ($('#webshop_gateway_details tr').length < 8) {

				$('.dataTables_paginate').hide();
			}

			$('#page_length').change( function() { 
			table1.page.len( $(this).val() ).draw();
			});

$('#gatway_details_form').validate({

}); 

$('.gateway_cred').each(function() {
	$(this).rules('add', {
		required: true
	});
});



$('#gatway_details_form').on('submit',function(e){
	e.preventDefault();
if($(this).valid()) {
	for (instance in CKEDITOR.instances) 
{
	CKEDITOR.instances[instance].updateElement();
} 
	var formData = new FormData($("#gatway_details_form")[0]);
		formData.append('type_details', $("#type_details").val());


			$.ajax({
			type:"POST",
			url:$(this).attr('action'),
			dataType:"json",
			data:formData,
			processData: false,
			contentType: false,
			beforeSend:function()
			{
				//$("#add_submit").prop("disabled",true).css({"background":"#868686","color":"#fff"});
			},
			success:function(response){
				if( response.flag == 1)
				{
					swal({
						title: "",
						icon: "success",
						text: response.msg,
						buttons: false, 
					})
					window.location.href = BASE_URL+'webshop/payment';
				}

				else
				{
					swal({
						title: "",
						icon: "error",
						text: response.msg,
						buttons: false, 
					})
				}
			}
		});
}


});
});


// stripe account create
$('#stripeCreateAccount').click(function(){
	var stripeAccountType=$('#stripeAccountType').val();
	var payment_type_id=$('#payment_type_id').val();
	var stripeEmail=$('#stripeEmail').val();
	var stripeCountry=$('#stripeCountry').val();
	$.ajax({
		type:"POST",
		url:BASE_URL+'WebshopController/stripe_create_account',
		//dataType:"json",
		data:{payment_type_id:payment_type_id,stripeAccountType:stripeAccountType,stripeEmail:stripeEmail,stripeCountry:stripeCountry},

		beforeSend:function()
		{
			$('#ajax-spinner').show();
			//$("#add_submit").prop("disabled",true).css({"background":"#868686","color":"#fff"});
		},
		success:function(response){
			//alert(response);
			$('#ajax-spinner').hide();
			if( response == 1)
			{
				location.reload();
			}

		}
	});
});

// stripe create account link
//stripeCreateAccountLink
$('#stripeCreateAccountLink').click(function(){
	var stripeAccountType=$('#stripeAccountType').val();
	var payment_type_id=$('#payment_type_id').val();
	/*var stripeEmail=$('#stripeEmail').val();
	var stripeCountry=$('#stripeCountry').val();*/
	var account_id=$('#connect_account_id').val();
	var cUrl=$('#cuUrl').val();

		$.ajax({
		type:"POST",
			url:BASE_URL+'WebshopController/stripe_account_link',
		dataType:"json",
		data:{cUrl:cUrl,payment_type_id:payment_type_id,stripeAccountType:stripeAccountType,account_id:account_id},

		beforeSend:function()
		{
			$('#ajax-spinner').show();
			$("#stripeCreateAccountLink,.download-btn").prop("disabled",true).css({"background":"#868686","color":"#fff"});
		},
		success:function(response){
		if(response.flag == 1)
		{
			if(response.url){
				// location.reload(response.url);
				window.location.replace(response.url);
			}
		
		}

		}
		});


});


$('#stripeAccountSaveSecretKey').click(function(){
	var stripeAccountType=$('#stripeAccountType').val();
	var payment_type_id=$('#payment_type_id').val();
	var account_id=$('#connect_account_id').val();
	var secretKey=$('#key').val();
	if(secretKey!=''){
		$.ajax({
			type:"POST",
				url:BASE_URL+'WebshopController/stripe_account_Secret',
			dataType:"json",
			data:{secretKey:secretKey,payment_type_id:payment_type_id,stripeAccountType:stripeAccountType,account_id:account_id},

			beforeSend:function()
			{
				$('#ajax-spinner').show();
				$("#stripeCreateAccountLink,.download-btn").prop("disabled",true).css({"background":"#868686","color":"#fff"});
			},
			success:function(response){
				if(response.flag == 1)
				{
					swal({
					title: "",
					icon: "success",
					text: response.msg,
					buttons: false, 
					});
					location.reload();
				}else{
					swal({
						title: "",
						icon: "error",
						text: response.msg,
						buttons: false, 
					})
				}
			}
		});
	}else{
		$( "#key" ).focus();
	}
});
