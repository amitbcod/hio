$(function () {
	$("#blogs-add").validate({
		rules: {
			blog_title: "required",
			blog_description: "required",
			sub_blog_title: "required",
			sub_blog_description: "required",
		},
		messages: {},
		submitHandler: function (form) {
			var blog_description_data =
				CKEDITOR.instances["blog_description"].getData();

			var formData = new FormData($("#blogs-add")[0]);
			formData.append("blog_description_data", blog_description_data);

			$.ajax({
				type: "POST",
				url: BASE_URL + "BlogController/add_blog_detail",
				dataType: "json",
				data: formData,
				cache: false,
				processData: false,
				contentType: false,
				success: function (res) {
					var dataResult = JSON.parse(JSON.stringify(res));
					if (dataResult.status == 200) {
						swal(
							{
								title: "",
								icon: "success",
								text: res.msg,
								buttons: false,
							},
							function () {
								window.location = dataResult.redirect;
							}
						);
					} else {
						swal({
							title: "",
							icon: "error",
							text: res.msg,
							buttons: false,
						});
					}
				},
			});
		},
	});
});

function delete_blog(id) {
	var confirmation = confirm("are you sure you want to delete the Blog ?");

	if (confirmation) {
		$.ajax({
			type: "post",
			dataType: "json",
			url: BASE_URL + "BlogController/delete_blog",
			data: { blog_id: id },
			beforeSend: function () {},
			success: function (response) {
				if (response.status == 200) {
					swal(
						{
							title: "",
							icon: "error",
							text: response.msg,
							buttons: false,
						},
						function () {
							$("#blogLists").DataTable().ajax.reload();
						}
					);
				}
			},
		});
	}
}

$(document).on("click", ".deleteBlock", function (e) {
	var id = $(this).attr("data-id");
	$("#blockID").val(id);
});

$(document).on("click", ".removeBlock", function (e) {
	if (confirm("Are you sure? you want to Remove Blog!")) {
		var id = $(this).attr("data-id");
		$("div#Sub-Title " + id).hide();
	} else {
		return false;
	}
});

$("#blockDeleteForm").validate({
	ignore: ":hidden",
	//ignore: ".ignore",
	submitHandler: function (form) {
		var formData = new FormData($("#blockDeleteForm")[0]);
		$.ajax({
			url: form.action,
			type: "ajax",
			method: form.method,
			dataType: "json",
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				if (response.flag == 1) {
					swal(
						{
							title: "",
							text: response.msg,
							button: false,
							icon: "success",
						},
						function () {
							$("#deleteModal").modal("toggle");
							window.location.reload();
						}
					);
				} else {
					swal({
						title: "",
						text: response.msg,
						button: false,
						icon: "error",
					});
					return false;
				}
			},
		});
	},
});
