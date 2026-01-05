
// invoice save data
$(document).ready(function () {

	$("#dropShipChk").on("click",function() {
	    	$(".toggle-dropshp").toggle(this.checked);
	  	});
	
	$("#buyInChk").on("click",function() {
	    	$(".toggle-buy-in").toggle(this.checked);
	  	});
		
	$("#priceChk").on("click",function() {
	    	$(".toggle-price").toggle(this.checked);
	  	});





        $("#invoice").click(function () {
         	// console.log('test');
         	// invoiceClass
         	$('.orderClass').removeClass('active');   
         	$('.invoiceClass').addClass('active');   
         	$('#b2b-order-and-details').addClass('hideDiv');   
         	$('#b2b-order-and-invoices').removeClass('hideDiv');   
        });
        $("#order").click(function () {
         	// console.log('test');
         	// invoiceClass
         	$('.invoiceClass').removeClass('active');   
         	$('#b2b-order-and-invoices').addClass('hideDiv'); 
         	$('#b2b-order-and-details').removeClass('hideDiv'); 
         	$('.orderClass').addClass('active');  
        });

        $("#B2BCustomerInvoiceForm").submit(function(){
	        dataString = $("#B2BCustomerInvoiceForm").serialize();
	        console.log(dataString);
	        $.ajax({
	            type: "POST",
	            url: BASE_URL+"B2BController/postB2BCustomerInvoice",
	            data: dataString,
	            success: function(data){
	            	//swal("Success", response.message, "success");
	                console.log(data);
	                var obj = JSON.parse(data);
	                swal("Success", data.msg, "success");
	                window.location.href=BASE_URL+'b2b/customer/detail/'+obj.shop_id;
	                // $("#result").html('Successfully updated record!'); 
	                // $("#result").addClass("alert alert-success");
	            }

	        });

	        return false;  //stop the actual form post !important!

	    });


    });

 $('#Exclusive_term_form').on('submit',function(e){
  e.preventDefault();
if($(this).valid()) {
      var formData = new FormData($("#Exclusive_term_form")[0]);
      console.log(formData);
        $.ajax({
          type:"POST",
          url:$(this).attr('action'),
          dataType:"json",
          data:formData,
          processData: false,
          contentType: false,
          
          success:function(response){
            // console.log(response);
            if( response.msg == "Success")
            {
              swal({
              title: "",
              icon: "success",
              text: response.msg,
              buttons: false,           
              },
              function(){location.reload(); })
            }
            else
            {
              swal({
                title: "",
                icon: "error",
                text: response.msg,
                buttons: false,           
              },
              function(){location.reload(); })
            }
          }
        });
  }
});