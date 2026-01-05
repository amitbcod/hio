$(document).ready(function() {

	var table1 = $('#customer_type_list').DataTable({"ordering": true, columnDefs: [{
      orderable: false,
      targets: "no-sort"
    }], "stateSave": true, "paging": true,"searching": true,"jQueryUI": false,"dom" : '<"top"tp>'});

	if ($('#customer_type_list tr').length < 8) {

            $('.dataTables_paginate').hide();
        }
$('.validate-char').on('keypress', function(key) {
        //alert(111111)
		if((key.charCode < 97 || key.charCode > 122) && (key.charCode < 65 || key.charCode > 90) && (key.charCode != 45 && key.charCode != 32 && key.charCode != 0)) {
			return false;
		}
	});


    $('#members_table').DataTable( {
        "order": [[ 1, "desc" ]],
        "info" : false,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
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

    if ($('#members_table tr').length < 10) {

            $('.dataTables_paginate').hide();
        }

});


$('#customer_type').validate({
	 rules: {
	  customer_type: {
                required: true
            },
        },
});

$('#customer_type').on('submit',function(e){
	e.preventDefault();
if($(this).valid()) {
			var formData = new FormData($("#customer_type")[0]);
				$.ajax({
					type:"POST",
					url:$(this).attr('action'),
					dataType:"json",
					data:formData,
					processData: false,
					contentType: false,
					beforeSend:function()
					{
						$("#add_type").prop("disabled",true).css({"background":"#868686","color":"#fff"});
					},
					success:function(response){
						if( response.msg == "Success")
						{
							swal({
							title: "",
							icon: "success",
							text: response.msg,
							buttons: false,
							})

							setTimeout(function() {
							  location.reload();

							}, 1000);
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

$('#type_details_form').validate({
	 rules: {
	  customer_type_val: {
                required: true
            },
        },
});

$('#type_details_form').on('submit',function(e){
	e.preventDefault();
	console.log($(this).attr('action'),);
if($(this).valid()) {
			var formData = new FormData($("#type_details_form")[0]);
				$.ajax({
					type:"POST",
					url:$(this).attr('action'),
					dataType:"json",
					data:formData,
					processData: false,
					contentType: false,
					beforeSend:function()
					{
						
					},
					success:function(response){
						if( response.msg == "Success")
						{
							swal({
							title: "",
							icon: "success",
							text: response.msg,
							buttons: false,
							})

							setTimeout(function() {
							  location.reload();

							}, 1000);
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
