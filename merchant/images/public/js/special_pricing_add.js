$(document).ready(function(){
  $('#sku').change(function(){
              
      var product_id= $('#product_id').val();
      // console.log(product_id);
      
      $.ajax({
          type: "POST",
          url: BASE_URL+"WebshopController/get_webshop_price",
          data:{"product_id":product_id},
          datatype : "json",
          success: function(response) 
          {
          	var obj=$.parseJSON(response);
          	// console.log(obj.webshop_price.webshop_price);
          	$("#webshop_price").val(obj.webshop_price.webshop_price);
              
          }

      });
    $("#webshop_price").val('');

  });

  
    
  $('#special_price').change(function(){
  if(parseFloat($('#special_price').val()) >= $('#webshop_price').val())
            {
              swal({
              title: "",
              icon: "error",
              text: "Special Price Cannot be Greater than or equal to webshop price",
              buttons: false,           
              },
               function(){location.reload(); })
            }
      });

  $('#to_date').change(function(){
  if($('#to_date').val()  <= $('#from_date').val())
            {
              swal({
              title: "",
              icon: "error",
              text: "To date should be Greater than From Date",
              buttons: false,           
              },
               function(){location.reload(); })
            }
      });

  	 
});

$('#special_pricing_form').on('submit',function(e){
  e.preventDefault();
  
if($(this).valid()) {
      var formData = new FormData($("#special_pricing_form")[0]);
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
              function(){window.location = response.redirect })
            }
            else
            {
               console.log(response.msg);
              swal({
                title: "",
                html: true,
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
