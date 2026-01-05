function openRestrictedAccessPopup()
{

		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"HomeController/restricted_access",
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				$("#WebShopCommonModal").modal();
				$("#modal-content").html(response);
			}
		});

}