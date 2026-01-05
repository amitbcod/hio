$(document).ready(function(){
    $('#tax_type').on('change', function() {
      if ( this.value == '3')
      {
        $("#add_percentage_div").show();
      }
      else
      {
        $("#add_percentage_div").hide();
      }
    });
    if($('#tax_type').val() ==3)
    {
      $("#add_percentage_div").show();
    }

    $('#based_on_cart').change(function() {

        if ($('#charge').attr('required')) {
            $('#charge').removeAttr('required');
        } 
        else {
            $('#charge').attr('required','required');
        }
    });

     $('#free_shipping').change(function() {

        if ($('#free_shipping_charge').attr('required')) {
            $('#free_shipping_charge').removeAttr('required');
        } 
        else {
            $('#free_shipping_charge').attr('required','required');
        }
    });


     $('#based_on_cart_weight').change(function() {

        if ($('*[id^="charge_on_cart_weight"]').attr('required')) {
            $('*[id^="charge_on_cart_weight"]').removeAttr('required');
        } 
        else {
            $('*[id^="charge_on_cart_weight"]').attr('required','required');
        }

        if ($('*[id^="min_weight"]').attr('required')) {
            $('*[id^="min_weight"]').removeAttr('required');
        } 
        else {
            $('*[id^="min_weight"]').attr('required','required');
        }

        if ($('*[id^="max_weight"]').attr('required')) {
            $('*[id^="max_weight"]').removeAttr('required');
        } 
        else {
            $('*[id^="max_weight"]').attr('required','required');
        }
    });

     $('#free_shipping').change(function() {

        if ($('#free_shipping_charge').attr('required')) {
            $('#free_shipping_charge').removeAttr('required');
        } 
        else {
            $('#free_shipping_charge').attr('required','required');
        }
    });

     $('#based_on_country').change(function() {

        if ($('#charge_in_own_country').attr('required')) {
            $('#charge_in_own_country').removeAttr('required');
        } 
        else {
            $('#charge_in_own_country').attr('required','required');
        }

        if ($('#charge_in_other_country').attr('required')) {
            $('#charge_in_other_country').removeAttr('required');
        } 
        else {
            $('#charge_in_other_country').attr('required','required');
        }
    });

    $('#shipping_charges_table').DataTable(
        {
          "order": [[ 0, "desc" ]],
          "info" : false,
          "lengthMenu": [[5,10, 20, 50, -1], [5, 10, 20, 50, "All"]],
          "language": {                
          "paginate": {
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'  
                       },
          "search": ""

         },
          'columnDefs': [{
                            "targets": [2,3,4],
                            "orderable": false
                         }],
        } );

      });
    if ($('#shipping_charges_table tr').length < 10) {
  
            $('.dataTables_paginate').hide();
        }

$('#shipping_charges_form').on('submit',function(e){
  e.preventDefault();
if($(this).valid()) {
      var formData = new FormData($("#shipping_charges_form")[0]);
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
              function() {
                    window.location = response.redirect;
                })
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

$('#createnew_shipping_charge_form').on('submit',function(e){
  e.preventDefault();
  $(this).find('input[type=checkbox]:not(:checked)').val(0);
if($(this).valid()) {
      var formData = new FormData($("#createnew_shipping_charge_form")[0]);
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

$("#shippingChargeDeleteForm").validate({
        ignore: ':hidden',      
        //ignore: ".ignore",
        submitHandler: function(form) {
            var formData = new FormData($('#shippingChargeDeleteForm')[0]);
            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                  console.log(response);
                    if (response.flag == 1) {
                        swal({ title: "",text: response.msg, button: false, icon: 'success' },
                        function() {window.location = response.redirect; })
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                }
            });
        }
    });

$("#deleteModalForRowForm").validate({
        ignore: ':hidden',      
        //ignore: ".ignore",
        submitHandler: function(form) {
            var formData = new FormData($('#deleteModalForRowForm')[0]);
            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.flag == 1) {
                        swal({ title: "",text: response.msg, button: false, icon: 'success' },
                        function() {location.reload(); })
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                }
            });
        }
    });

function myFunction(element) {
  var row_id=$(element).val();
    
     $('#row_id').val(row_id);
}