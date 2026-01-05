$(document).ready(function() {	
	console.log('loaded...')



});
	
//$('#upload_logo').validate({
//        rules: {
//            sitename: {
//                required: false,
//                maxlength: 100
//            },
//            site_logo: {
//                required: false,
//                extension: "jpg,jpeg,png,gif",
//                width: 100
//            }
//        },    
//	messages: {
//          width: { "required": "Image width should be below 100px." },
//            extension: { "required": "pload valid image. Only jpg, PNG and JPEG are allowed." },
//        }
	
	
	
// });	

// $('#upload_logo').on('submit',function(e){
	e.preventDefault();
	console.log('valid form data');
// if($(this).valid()) {
//		console.log('valid form data');
//
//	}
//}); 
   
// $('#change_email').on('submit',function(e){
	
// 	e.preventDefault();
// if($(this).valid()) {
// 			var formData = new FormData($("#change_email")[0]);
				
// 				$.ajax({
// 					type:"POST",
// 					url:$(this).attr('action'),
// 					dataType:"json",
// 					data:formData,
// 					processData: false,
// 					contentType: false,
// 					beforeSend:function()
// 					{
// 						//$("#add_submit").prop("disabled",true).css({"background":"#868686","color":"#fff"});
// 					},
// 					success:function(response){
// 						if( response.msg == "Success")
// 						{
// 							swal.fire({
// 								title: "",
// 								icon: "success",
// 								text: response.msg,
// 								buttons: false,						
// 							}, function(){ location.reload(); });
							
// 							 //location.reload();
// 						}
// 						else if(response.status == '202')
// 						{
// 							$("#change_email_modal").modal('show');
// 							$("#current_email").focus();		
// 							$("#current_email").focus(function() {
// 								$(this).next( "div" ).addClass("error")
// 								$(this).next( "div" ).html("Current Email Does't match")
								

// 							});
// 							// alert("Old Password Does't match");
// 						}
// 						else
// 						{
// 							swal({
// 								title: "",
// 								icon: "error",
// 								text: response.msg,
// 								buttons: false,						
// 							})
// 						}
// 					}
// 				});
// 	}
// });
