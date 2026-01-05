	$("#Variabletable").dataTable({
        "language": {
			"infoFiltered": "",
			"search": '',
			"searchPlaceholder": "Search",
			"paginate": {
				next: '<i class="fas fa-angle-right"></i>',
				previous: '<i class="fas fa-angle-left"></i>'
			}
		},
    });
	
	$("#add-custvariable").validate({
		rules: {
			VariableCode: {
				required: true,
			},
			VariableName: {
				required: true,
			},
			VariableValue: {
				required: true,
			}
		},
	   submitHandler: function(form) {
		    var fd = new FormData($('#add-custvariable')[0]);
			$.ajax({
				type: 'POST',
				url: "/variable-post",
				dataType: 'json',
				contentType: false,
				processData: false,
				data:fd,
				success: function(response){
					if (response.flag == '1') {
						$('#Modal_Add').modal('hide');
						swal({ title: "",icon: "success",text: response.msg,buttons: false})
						setTimeout(function() {window.location.reload(); }, 1000);
					} else {
						swal({ title: "",icon: "error",text: response.msg,buttons: false})
						setTimeout(function() {location.reload();}, 1000);
					}
				}
			}); 
		}
	});
	
	function editCustomVariable(id){
	    //alert(id);
		$('#Modal_Edit').modal('show');
		$('[name="Id"]').val(id);
		$.ajax({
			type : "POST",
			url  : "/variable-edit",
			dataType : "JSON",
			data : {id:id},
			success: function(response){
				 //console.log(response);
				if (response.flag == '1') {
					$('#VariableCodeEdit').val(response.data.identifier);
					$('#VariableNameEdit').val(response.data.name);
					$('#VariableValueEdit').val(response.data.value);
				}
			},
			error:function(response){
				console.log(response);
			}
		});
	}
	
	$("#edit-custvariable").validate({
		rules: {
			VariableCode: {
				required: true,
			},
			VariableName: {
				required: true,
			},
			VariableValue: {
				required: true,
			}
		},
	   submitHandler: function(form) {
		    var fd = new FormData($('#edit-custvariable')[0]);
			$.ajax({
				type: 'POST',
				url: BASE_URL + "CustomVariablesController/editVariablesPost",
				dataType: 'json',
				contentType: false,
				processData: false,
				data:fd,
				success: function(response){
					if (response.flag == '1') {
						$('#Modal_Edit').modal('hide');
						swal({ title: "",icon: "success",text: response.msg,buttons: false})
						setTimeout(function() {window.location.reload(); }, 1000);
					} else {
						swal({ title: "",icon: "error",text: response.msg,buttons: false})
						setTimeout(function() {location.reload();}, 1000);
					}
				}
			}); 
		}
	});